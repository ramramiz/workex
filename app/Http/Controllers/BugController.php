<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bug;
use App\Models\BugComment;
use App\Models\Project;
use App\Models\User;

class BugController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = $request->input('filter', 'pending');
        $bugs = Bug::with(['project', 'reportedBy', 'assignedTo'])
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when(!$request->status, function($q) use ($filter) {
                if ($filter === 'completed') {
                    $q->whereIn('status', ['completed', 'approved', 'cleared', 'closed']);
                } else {
                    $q->whereNotIn('status', ['completed', 'approved', 'cleared', 'closed']);
                }
            })
            ->latest()->paginate(20);
        $projects = Project::whereNotIn('status', ['completed','cancelled'])->get();
        return view('bugs.index', compact('bugs', 'projects'));
    }
    public function create()
    {
        if (!auth()->user()->hasPermission('bugs.create')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can report bugs.');
        }
        $projects = Project::whereNotIn('status', ['completed','cancelled'])->get();
        $developers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->where('status','active')->get();
        return view('bugs.create', compact('projects', 'developers'));
    }
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('bugs.create')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can report bugs.');
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'link' => 'nullable|string|max:1000',
            'screenshots' => 'nullable|array|max:3',
        ]);

        $screenshots = [];

        // Handle standard file uploads
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('screenshots', 'public');
                    $screenshots[] = $path;
                }
            }
        }

        // Handle base64 string uploads (for compatibility with tests)
        if ($request->has('screenshots') && is_array($request->screenshots)) {
            foreach ($request->screenshots as $index => $base64) {
                if (is_string($base64)) {
                    $image_parts = explode(";base64,", $base64);
                    if (count($image_parts) === 2) {
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1] ?? 'jpeg';
                        $image_base64 = base64_decode($image_parts[1]);
                        
                        $filename = 'screenshot_' . time() . '_' . $index . '_' . rand(1000, 9999) . '.' . $image_type;
                        $path = 'screenshots/' . $filename;
                        
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                        $screenshots[] = $path;
                    }
                }
            }
        }

        $bug = Bug::create(array_merge(
            $request->only(['title','description','link','project_id','task_id','assigned_to','priority','browser_info','os_info','steps_to_reproduce']),
            [
                'reported_by' => auth()->id(),
                'status' => 'open',
                'screenshots' => $screenshots
            ]
        ));

        // Create associated task so it shows up at the top of the developer's chat workspace
        $task = \App\Models\Task::create([
            'project_id' => $bug->project_id,
            'assigned_to' => $bug->assigned_to,
            'created_by' => $bug->reported_by,
            'title' => 'Bug: ' . $bug->title,
            'description' => "Bug ID: #{$bug->id}\nLink: " . ($bug->link ?? 'N/A') . "\n\n" . $bug->description,
            'priority' => $bug->priority,
            'status' => 'pending',
        ]);

        $bug->update(['task_id' => $task->id]);

        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            \App\Models\AppNotification::create([
                'user_id' => $task->assigned_to,
                'type'    => 'task_assigned',
                'title'   => 'New Bug Task Assigned',
                'message' => auth()->user()->name . ' assigned you a new bug task: "' . $task->title . '"',
                'url'     => route('tasks.show', $task),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bug reported!',
                'bug' => $bug,
                'task' => $task
            ]);
        }

        return redirect()->route('bugs.index')->with('success', 'Bug reported!');
    }
    public function show(Bug $bug) { $bug->load(['project', 'reportedBy', 'assignedTo', 'comments.user']); return view('bugs.show', compact('bug')); }
    public function edit(Bug $bug)
    {
        if (!auth()->user()->hasPermission('bugs.edit')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can manage bugs.');
        }
        $projects = Project::all();
        $developers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->get();
        return view('bugs.edit', compact('bug', 'projects', 'developers'));
    }
    public function update(Request $request, Bug $bug)
    {
        if (!auth()->user()->hasPermission('bugs.edit')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can manage bugs.');
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|string|max:100',
            'description' => 'required|string',
            'link' => 'nullable|string|max:1000',
        ]);
        $bug->update($request->only(['title','description','link','project_id','task_id','assigned_to','priority','status','browser_info','os_info','steps_to_reproduce']));
        
        if ($bug->task) {
            $oldAssignedTo = $bug->task->assigned_to;
            
            $taskStatus = $bug->task->status;
            if ($bug->status === 'closed' || $bug->status === 'completed' || $bug->status === 'approved' || $bug->status === 'cleared') {
                $taskStatus = 'completed';
            } elseif ($bug->status === 'under_review') {
                $taskStatus = 'review';
            } elseif ($bug->status === 'in_progress') {
                $taskStatus = 'in_progress';
            } elseif ($bug->status === 'reopened' || $bug->status === 'rejected') {
                $taskStatus = 'rework';
            } elseif ($bug->status === 'open' || $bug->status === 'assigned') {
                $taskStatus = 'pending';
            }

            $bug->task->update([
                'title' => 'Bug: ' . $bug->title,
                'project_id' => $bug->project_id,
                'assigned_to' => $bug->assigned_to,
                'priority' => $bug->priority,
                'status' => $taskStatus,
            ]);
            $bug->task->touch();
            
            if ($bug->assigned_to && $bug->assigned_to !== $oldAssignedTo && $bug->assigned_to !== auth()->id()) {
                \App\Models\AppNotification::create([
                    'user_id' => $bug->assigned_to,
                    'type'    => 'task_assigned',
                    'title'   => 'New Bug Task Assigned',
                    'message' => auth()->user()->name . ' assigned you a new bug task: "' . $bug->task->title . '"',
                    'url'     => route('tasks.show', $bug->task),
                ]);
            }
        } else {
            $task = \App\Models\Task::create([
                'project_id' => $bug->project_id,
                'assigned_to' => $bug->assigned_to,
                'created_by' => $bug->reported_by,
                'title' => 'Bug: ' . $bug->title,
                'description' => "Bug ID: #{$bug->id}\nLink: " . ($bug->link ?? 'N/A') . "\n\n" . $bug->description,
                'priority' => $bug->priority,
                'status' => 'pending',
            ]);
            $bug->update(['task_id' => $task->id]);
            
            if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
                \App\Models\AppNotification::create([
                    'user_id' => $task->assigned_to,
                    'type'    => 'task_assigned',
                    'title'   => 'New Bug Task Assigned',
                    'message' => auth()->user()->name . ' assigned you a new bug task: "' . $task->title . '"',
                    'url'     => route('tasks.show', $task),
                ]);
            }
        }
        
        return back()->with('success', 'Bug updated!');
    }
    public function destroy(Bug $bug)
    {
        if (!auth()->user()->hasPermission('bugs.edit')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can manage bugs.');
        }
        $bug->delete();
        return redirect()->route('bugs.index')->with('success', 'Bug deleted.');
    }
    public function addComment(Request $request, Bug $bug)
    {
        $request->validate(['comment' => 'required|string']);
        BugComment::create(['bug_id' => $bug->id, 'user_id' => auth()->id(), 'comment' => $request->comment]);
        
        if ($bug->task_id) {
            \App\Models\TaskComment::create([
                'task_id' => $bug->task_id,
                'user_id' => auth()->id(),
                'comment' => $request->comment,
            ]);
            $bug->task->touch();
        }
        
        return back()->with('success', 'Comment added!');
    }
    public function updateStatus(Request $request, Bug $bug)
    {
        $request->validate(['status' => 'required|string|max:100']);
        $bug->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }
}
