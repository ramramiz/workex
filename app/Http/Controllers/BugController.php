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
        $bugs = Bug::with(['project', 'reportedBy', 'assignedTo'])
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        $projects = Project::whereNotIn('status', ['completed','cancelled'])->get();
        return view('bugs.index', compact('bugs', 'projects'));
    }
    public function create()
    {
        $projects = Project::whereNotIn('status', ['completed','cancelled'])->get();
        $developers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->where('status','active')->get();
        return view('bugs.create', compact('projects', 'developers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|in:low,medium,high,critical',
            'screenshots' => 'nullable|array|max:5',
        ]);

        $screenshots = [];
        if ($request->has('screenshots')) {
            foreach ($request->screenshots as $index => $base64) {
                if ($base64) {
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

        Bug::create(array_merge(
            $request->only(['title','description','project_id','task_id','assigned_to','priority','browser_info','os_info','steps_to_reproduce']),
            [
                'reported_by' => auth()->id(),
                'status' => 'open',
                'screenshots' => $screenshots
            ]
        ));

        return redirect()->route('bugs.index')->with('success', 'Bug reported!');
    }
    public function show(Bug $bug) { $bug->load(['project', 'reportedBy', 'assignedTo', 'comments.user']); return view('bugs.show', compact('bug')); }
    public function edit(Bug $bug) { $projects = Project::all(); $developers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->get(); return view('bugs.edit', compact('bug', 'projects', 'developers')); }
    public function update(Request $request, Bug $bug) { $bug->update($request->only(['title','description','project_id','task_id','assigned_to','priority','browser_info','os_info','steps_to_reproduce'])); return back()->with('success', 'Bug updated!'); }
    public function destroy(Bug $bug) { $bug->delete(); return redirect()->route('bugs.index')->with('success', 'Bug deleted.'); }
    public function addComment(Request $request, Bug $bug)
    {
        $request->validate(['comment' => 'required|string']);
        BugComment::create(['bug_id' => $bug->id, 'user_id' => auth()->id(), 'comment' => $request->comment]);
        return back()->with('success', 'Comment added!');
    }
    public function updateStatus(Request $request, Bug $bug)
    {
        $request->validate(['status' => 'required|in:open,assigned,in_progress,resolved,closed,rejected']);
        $bug->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }
}
