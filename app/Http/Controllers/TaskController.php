<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskComment;
use App\Models\TaskFile;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::with(['project', 'assignee', 'creator', 'meeting'])
            ->when(!$user->isLeaderOrAbove(), fn($q) => $q->where('assigned_to', $user->id))
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        $tasks = $query->orderBy('deadline')->paginate(20);
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))->get();

        return view('tasks.index', compact('tasks', 'projects'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))
            ->whereNotIn('status', ['completed', 'cancelled'])->get();
        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))->where('status', 'active')->get();
        $selectedProject = $request->project_id ? Project::find($request->project_id) : null;
        $selectedMeeting = $request->meeting_id ? \App\Models\Meeting::find($request->meeting_id) : null;

        return view('tasks.create', compact('projects', 'employees', 'selectedProject', 'selectedMeeting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'project_id'   => 'nullable|exists:projects,id',
            'assigned_to'  => 'required|exists:users,id',
            'priority'     => 'required|in:low,medium,high,critical',
            'deadline'     => 'nullable|date',
            'attachment'   => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'meeting_id'   => 'nullable|exists:meetings,id',
        ]);

        $task = Task::create([
            'title'        => $request->title,
            'description'  => $request->description,
            'project_id'   => $request->project_id ?: null,
            'assigned_to'  => $request->assigned_to,
            'priority'     => $request->priority,
            'deadline'     => $request->deadline,
            'estimated_hours' => $request->estimated_hours ?: 0,
            'status'       => 'pending',
            'created_by'   => auth()->id(),
            'meeting_id'   => $request->meeting_id ?: null,
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tasks/' . $task->id, 'public');
            TaskFile::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(),
                'file_name' => $request->file('attachment')->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $request->file('attachment')->getSize(),
                'file_type' => $request->file('attachment')->getMimeType(),
            ]);
        }

        \App\Models\ActivityLog::log('task_created', "Created task: {$task->title}", $task);

        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            \App\Models\AppNotification::create([
                'user_id' => $task->assigned_to,
                'type'    => 'task_assigned',
                'title'   => 'New Task Assigned',
                'message' => auth()->user()->name . ' assigned you a new task: "' . $task->title . '"',
                'url'     => route('tasks.show', $task),
            ]);
        }

        if ($task->meeting_id) {
            return redirect()->route('meetings.show', $task->meeting_id)->with('success', 'Task created and added to meeting!');
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task created!');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'meeting', 'assignee', 'creator', 'comments.user', 'comments.views.user', 'files', 'timeLogs.user']);
        $totalMinutes = $task->timeLogs->sum('total_minutes');

        // Record views for all existing comments on this task for the logged-in user
        foreach ($task->comments as $comment) {
            if ($comment->user_id !== auth()->id()) {
                \App\Models\TaskCommentView::firstOrCreate([
                    'task_comment_id' => $comment->id,
                    'user_id'         => auth()->id(),
                ]);
            }
        }

        // Reload task comments and views to include the newly recorded views
        $task->load('comments.views.user');

        $feed = collect()
            ->concat($task->comments->map(function($c) {
                $c->feed_type = 'comment';
                return $c;
            }))
            ->concat($task->timeLogs->map(function($l) {
                $l->feed_type = 'time_log';
                return $l;
            }))
            ->sortBy('created_at');

        return view('tasks.show', compact('task', 'totalMinutes', 'feed'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))->get();
        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))->where('status', 'active')->get();
        return view('tasks.edit', compact('task', 'projects', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'project_id'   => 'nullable|exists:projects,id',
            'priority'     => 'required',
            'attachment'   => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
        ]);
        $data = $request->only(['title','description','assigned_to','priority','deadline','status']);
        $data['project_id'] = $request->project_id ?: null;
        $data['estimated_hours'] = $request->estimated_hours ?: 0;
        
        $oldStatus = $task->status;
        $oldAssignedTo = $task->assigned_to;
        $task->update($data);
        $newStatus = $task->status;
        $newAssignedTo = $task->assigned_to;

        if ($oldAssignedTo !== $newAssignedTo && $newAssignedTo && $newAssignedTo !== auth()->id()) {
            \App\Models\AppNotification::create([
                'user_id' => $newAssignedTo,
                'type'    => 'task_assigned',
                'title'   => 'New Task Assigned',
                'message' => auth()->user()->name . ' assigned you a new task: "' . $task->title . '"',
                'url'     => route('tasks.show', $task),
            ]);
        }

        if ($oldStatus !== $newStatus) {
            $statusLabels = [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'review' => 'Review',
                'rework' => 'Rework',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
            $newLabel = $statusLabels[$newStatus] ?? ucfirst($newStatus);
            $userName = auth()->user()->name;
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'comment' => "🔄 status changed to **{$newLabel}** by **{$userName}**",
            ]);
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('tasks/' . $task->id, 'public');
            TaskFile::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(),
                'file_name' => $request->file('attachment')->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $request->file('attachment')->getSize(),
                'file_type' => $request->file('attachment')->getMimeType(),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated!');
    }

    public function destroy(Task $task)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can delete tasks.');
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:pending,in_progress,review,rework,completed,cancelled']);
        
        $oldStatus = $task->status;
        $newStatus = $request->status;

        if ($oldStatus !== $newStatus) {
            $task->update(['status' => $newStatus]);

            $statusLabels = [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'review' => 'Review',
                'rework' => 'Rework',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
            $newLabel = $statusLabels[$newStatus] ?? ucfirst($newStatus);
            $userName = auth()->user()->name;
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'comment' => "🔄 status changed to **{$newLabel}** by **{$userName}**",
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated!');
    }

    public function addComment(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'nullable|string|max:2000',
            'image_data' => 'nullable|string',
        ]);
        
        $commentText = $request->comment ?? '';
        $imagePath = null;
        
        if ($request->filled('image_data')) {
            $imageData = $request->image_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
                
                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $imageData = base64_decode($imageData);
                    if ($imageData !== false) {
                        $fileName = 'comment_' . uniqid() . '.' . $type;
                        $path = 'comments/' . $task->id . '/' . $fileName;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
                        $imagePath = $path;
                    }
                }
            }
        }
        
        if (empty($commentText) && empty($imagePath)) {
            return back()->withErrors(['comment' => 'Please enter a comment or attach an image.']);
        }
        
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $commentText,
            'image_path' => $imagePath,
        ]);

        // Sync comment back to associated Bug if it exists
        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            \App\Models\BugComment::create([
                'bug_id' => $bug->id,
                'user_id' => auth()->id(),
                'comment' => $commentText . ($imagePath ? "\n\n[Attached Image]" : ""),
            ]);
        }

        // Find mentioned users in the comment
        $activeUsers = User::where('status', 'active')->get();
        foreach ($activeUsers as $u) {
            if ($u->id === auth()->id()) continue;
            
            $mentionName = '@' . $u->name;
            $mentionEmail = '@' . $u->email;
            
            if (stripos($commentText, $mentionName) !== false || stripos($commentText, $mentionEmail) !== false) {
                \App\Models\AppNotification::create([
                    'user_id' => $u->id,
                    'type'    => 'mention',
                    'title'   => 'You were mentioned',
                    'message' => auth()->user()->name . ' mentioned you in a comment on task: "' . $task->title . '"',
                    'url'     => route('tasks.show', $task),
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully!'
            ]);
        }

        return back()->with('success', 'Comment added!');
    }

    public function uploadFile(Request $request, Task $task)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('tasks/' . $task->id, 'public');
        TaskFile::create([
            'task_id'       => $task->id,
            'user_id'       => auth()->id(),
            'file_name'     => $request->file('file')->getClientOriginalName(),
            'file_path'     => $path,
            'file_size'     => $request->file('file')->getSize(),
            'file_type'     => $request->file('file')->getMimeType(),
        ]);
        return back()->with('success', 'File uploaded!');
    }

    public function completedApprovals()
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove() && $user->email !== 'souban.techsoul@gmail.com') {
            abort(403, 'Unauthorized action.');
        }

        $tasks = Task::with(['project', 'assignee', 'creator'])
            ->where('status', 'review')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('tasks.completed_approvals', compact('tasks'));
    }

    public function submitCompletion(Request $request, Task $task)
    {
        $request->validate([
            'completed_description' => 'required|string',
            'completed_link' => 'required|url',
        ]);

        $task->update([
            'status' => 'review',
            'completed_description' => $request->completed_description,
            'completed_link' => $request->completed_link,
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => "🚀 **Submitted task for completion review**\n\n**Description:** {$request->completed_description}\n**Test URL:** [{$request->completed_link}]({$request->completed_link})",
        ]);

        \App\Models\ActivityLog::log('task_completed_submitted', "Submitted task for completion review: {$task->title}", $task);

        if (str_contains(url()->previous(), '/chat')) {
            return redirect()->route('chat.index')->with('success', 'Task submitted for completion review!');
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task submitted for completion review!');
    }

    public function approveCompletion(Task $task)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove() && $user->email !== 'souban.techsoul@gmail.com') {
            abort(403, 'Unauthorized action.');
        }

        $task->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => "✅ **Approved completion and closed task**",
        ]);

        \App\Models\ActivityLog::log('task_completed_approved', "Approved task completion: {$task->title}", $task);

        return redirect()->route('tasks.completed-approvals')->with('success', 'Task completion approved and task closed!');
    }

    public function rejectCompletion(Request $request, Task $task)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove() && $user->email !== 'souban.techsoul@gmail.com') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $task->update([
            'status' => 'pending',
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => "❌ **Task Rejection Feedback:** " . $request->comment,
        ]);

        \App\Models\ActivityLog::log('task_completed_rejected', "Rejected task completion: {$task->title}", $task);

        return redirect()->route('tasks.completed-approvals')->with('success', 'Task completion rejected and sent back for rework!');
    }

    public function getFeedUpdates(Request $request, Task $task)
    {
        $since = $request->input('since');
        if (!$since) {
            return response()->json(['html' => '', 'latest_time' => null, 'has_updates' => false, 'play_sound' => false]);
        }

        $sinceDate = \Carbon\Carbon::parse($since)->setTimezone(config('app.timezone'));

        $newComments = $task->comments()
            ->with(['user', 'views.user'])
            ->where('created_at', '>', $sinceDate)
            ->get()
            ->map(function($c) {
                $c->feed_type = 'comment';
                return $c;
            });

        $newTimeLogs = $task->timeLogs()
            ->with('user')
            ->where('created_at', '>', $sinceDate)
            ->get()
            ->map(function($l) {
                $l->feed_type = 'time_log';
                return $l;
            });

        $newFeed = $newComments->concat($newTimeLogs)->sortBy('created_at');

        // Record views for all new comments for the logged-in user
        foreach ($newComments as $comment) {
            if ($comment->user_id !== auth()->id()) {
                \App\Models\TaskCommentView::firstOrCreate([
                    'task_comment_id' => $comment->id,
                    'user_id'         => auth()->id(),
                ]);
            }
        }

        $html = '';
        foreach ($newFeed as $item) {
            $isSent = $item->user_id === auth()->id();
            $formattedTime = $item->created_at->format('h:i A');
            $html .= view('tasks.partials.feed_item', [
                'item' => $item,
                'isSent' => $isSent,
                'formattedTime' => $formattedTime,
                'task' => $task
            ])->render();
        }

        $latestTime = $newFeed->count() > 0 ? $newFeed->last()->created_at->toISOString() : $since;

        return response()->json([
            'html' => $html,
            'latest_time' => $latestTime,
            'has_updates' => $newFeed->count() > 0,
            'play_sound' => $newFeed->contains(fn($item) => $item->user_id !== auth()->id())
        ]);
    }
}
