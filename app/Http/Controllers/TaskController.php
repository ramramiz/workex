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
            ->where('status', '!=', 'completed')
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
        if (!$user->hasPermission('tasks.create')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can create tasks.');
        }
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))
            ->whereNotIn('status', ['completed', 'cancelled'])->get();
        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))->where('status', 'active')->get();
        $selectedProject = $request->project_id ? Project::find($request->project_id) : null;
        $selectedMeeting = $request->meeting_id ? \App\Models\Meeting::find($request->meeting_id) : null;

        return view('tasks.create', compact('projects', 'employees', 'selectedProject', 'selectedMeeting'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermission('tasks.create')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can create tasks.');
        }
        $request->validate([
            'title'        => 'required|string|max:255',
            'project_id'   => 'nullable|exists:projects,id',
            'assigned_to'  => 'required|exists:users,id',
            'priority'     => 'required|in:low,medium,high,critical,special',
            'deadline'     => 'nullable|date',
            'attachment'   => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'attachments'  => 'nullable|array|max:3',
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

        if ($request->has('attachments') && is_array($request->attachments)) {
            foreach ($request->attachments as $index => $attachmentData) {
                if ($attachmentData instanceof \Illuminate\Http\UploadedFile && $attachmentData->isValid()) {
                    $path = $attachmentData->store('tasks/' . $task->id, 'public');
                    TaskFile::create([
                        'task_id'   => $task->id,
                        'user_id'   => auth()->id(),
                        'file_name' => $attachmentData->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $attachmentData->getSize(),
                        'file_type' => $attachmentData->getMimeType(),
                    ]);
                } elseif (is_string($attachmentData)) {
                    $image_parts = explode(";base64,", $attachmentData);
                    if (count($image_parts) === 2) {
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1] ?? 'jpeg';
                        $image_base64 = base64_decode($image_parts[1]);
                        
                        $filename = 'task_attachment_' . time() . '_' . $index . '_' . rand(1000, 9999) . '.' . $image_type;
                        $path = 'tasks/' . $task->id . '/' . $filename;
                        
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                        
                        TaskFile::create([
                            'task_id'   => $task->id,
                            'user_id'   => auth()->id(),
                            'file_name' => $filename,
                            'file_path' => $path,
                            'file_size' => strlen($image_base64),
                            'file_type' => 'image/' . $image_type,
                        ]);
                    }
                }
            }
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created!',
                'task' => $task
            ]);
        }

        if ($task->meeting_id) {
            return redirect()->route('meetings.show', $task->meeting_id)->with('success', 'Task created and added to meeting!');
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task created!');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'meeting', 'assignee', 'creator', 'comments.user', 'comments.views.user', 'comments.parent.user', 'files', 'timeLogs.user']);
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
        if (!$user->hasPermission('tasks.edit')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can edit tasks.');
        }
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))->get();
        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))->where('status', 'active')->get();
        return view('tasks.edit', compact('task', 'projects', 'employees'));
    }

    public function update(Request $request, Task $task)
    {
        $user = auth()->user();
        if (!$user->hasPermission('tasks.edit')) {
            abort(403, 'Unauthorized action. Only Team Leaders and Admins can edit tasks.');
        }

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
        if (isset($data['status']) && $data['status'] === 'completed' && $oldStatus !== 'completed') {
            return back()->with('error', 'Task completion must be approved through the approvals queue.');
        }

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
                'rejected' => 'Rejected',
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

        if (str_contains(url()->previous(), '/chat')) {
            return redirect()->route('chat.index')->with('success', 'Task updated!');
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
        $request->validate(['status' => 'required|in:pending,in_progress,review,rework,completed,cancelled,rejected']);
        
        $oldStatus = $task->status;
        $newStatus = $request->status;

        if ($newStatus === 'completed') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Task completion must be approved through the approvals queue.'], 403);
            }
            return back()->with('error', 'Task completion must be approved through the approvals queue.');
        }

        if ($oldStatus !== $newStatus) {
            $task->update(['status' => $newStatus]);

            $bug = \App\Models\Bug::where('task_id', $task->id)->first();
            if ($bug) {
                if ($newStatus === 'review') {
                    $bug->update(['status' => 'under_review']);
                } elseif ($newStatus === 'completed' || $newStatus === 'cancelled') {
                    $bug->update(['status' => 'closed']);
                } elseif ($newStatus === 'in_progress') {
                    $bug->update(['status' => 'in_progress']);
                } elseif ($newStatus === 'rework' || $newStatus === 'rejected') {
                    $bug->update(['status' => 'reopened']);
                } elseif ($newStatus === 'pending') {
                    $bug->update(['status' => 'open']);
                }
            }

            $statusLabels = [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'review' => 'Review',
                'rework' => 'Rework',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'rejected' => 'Rejected',
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
            'parent_id' => 'nullable|exists:task_comments,id',
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
            'parent_id' => $request->parent_id,
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
        $isGlobalApprover = $user->isAdminOrAbove();
        if (!$isGlobalApprover) {
            abort(403, 'Unauthorized action.');
        }

        $tasks = Task::with(['project', 'assignee', 'creator'])
            ->where('status', 'review')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('tasks.completed_approvals', compact('tasks'));
    }

    public function approvedTasks(Request $request)
    {
        $user = auth()->user();

        $query = Task::with(['project', 'assignee', 'creator', 'meeting'])
            ->where('status', '=', 'completed')
            ->when(!$user->isLeaderOrAbove(), fn($q) => $q->where('assigned_to', $user->id))
            ->when($request->project, fn($q) => $q->where('project_id', $request->project))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        $tasks = $query->orderBy('completed_date', 'desc')->paginate(20);
        $projects = Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))->get();

        return view('tasks.approved', compact('tasks', 'projects'));
    }

    public function submitCompletion(Request $request, Task $task)
    {
        $request->validate([
            'completed_description' => 'required|string',
            'completed_link' => 'nullable|url',
        ]);

        $task->update([
            'status' => 'review',
            'completed_description' => $request->completed_description,
            'completed_link' => $request->completed_link,
        ]);

        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            $bug->update(['status' => 'under_review']);
        }

        $commentMsg = "🚀 **Submitted task for completion review**\n\n**Description:** {$request->completed_description}";
        if ($request->filled('completed_link')) {
            $commentMsg .= "\n**Test URL:** [{$request->completed_link}]({$request->completed_link})";
        }
        $commentMsg .= "\n\n@Admin, please review and approve my work.";

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $commentMsg,
        ]);

        // Send notifications to all Admin and Super Admin users
        $admins = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($admins as $admin) {
            \App\Models\AppNotification::create([
                'user_id' => $admin->id,
                'type'    => 'task_review',
                'title'   => 'Task Review Request',
                'message' => auth()->user()->name . ' requested approval for task: "' . $task->title . '"',
                'url'     => route('tasks.show', $task),
            ]);
        }

        \App\Models\ActivityLog::log('task_completed_submitted', "Submitted task for completion review: {$task->title}", $task);

        if (str_contains(url()->previous(), '/chat')) {
            return redirect()->route('chat.index')->with('success', 'Task submitted for completion review!');
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task submitted for completion review!');
    }

    public function approveCompletion(Request $request, Task $task)
    {
        $user = auth()->user();

        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $isGlobalApprover = $user->isAdminOrAbove();
        if (!$isGlobalApprover) {
            abort(403, 'Unauthorized action.');
        }

        $task->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            $bug->update(['status' => 'closed']);
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => "✅ **Approved completion and closed task**\n\n**Notes:** " . $request->comment,
        ]);

        \App\Models\ActivityLog::log('task_completed_approved', "Approved task completion: {$task->title}", $task);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task completion approved and task closed!'
            ]);
        }

        return redirect()->route('tasks.completed-approvals')->with('success', 'Task completion approved and task closed!');
    }

    public function rejectCompletion(Request $request, Task $task)
    {
        $user = auth()->user();

        $isGlobalApprover = $user->isAdminOrAbove();
        if (!$isGlobalApprover) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $task->update([
            'status' => 'rejected',
            'team_leader_approved' => false,
            'team_leader_approved_by' => null,
            'team_leader_approved_at' => null,
        ]);

        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            $bug->update(['status' => 'reopened']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('comments/' . $task->id, 'public');
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => "❌ **Task Rejection Feedback (Super Admin):** " . $request->comment,
            'image_path' => $imagePath,
        ]);

        if ($task->assigned_to) {
            \App\Models\AppNotification::create([
                'user_id' => $task->assigned_to,
                'type'    => 'task_rejected',
                'title'   => 'Task Rework Required',
                'message' => auth()->user()->name . ' rejected your completion for task: "' . $task->title . '"',
                'url'     => route('tasks.show', $task),
            ]);
        }

        \App\Models\ActivityLog::log('task_completed_rejected', "Rejected task completion: {$task->title}", $task);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task completion rejected and sent back for rework!'
            ]);
        }

        return redirect()->route('tasks.completed-approvals')->with('success', 'Task completion rejected and sent back for rework!');
    }

    public function getFeedUpdates(Request $request, Task $task)
    {
        $since = $request->input('since');
        $lastCommentId = $request->input('last_comment_id');
        $lastTimelogId = $request->input('last_timelog_id');

        \Log::info("getFeedUpdates polling", [
            'task_id' => $task->id,
            'since' => $since,
            'last_comment_id' => $lastCommentId,
            'last_timelog_id' => $lastTimelogId,
            'user_id' => auth()->id(),
            'request_ip' => $request->ip()
        ]);
        if (!$since) {
            return response()->json([
                'html' => '',
                'latest_time' => null,
                'last_comment_id' => null,
                'last_timelog_id' => null,
                'has_updates' => false,
                'play_sound' => false
            ]);
        }

        $sinceDate = \Carbon\Carbon::parse($since)->setTimezone(config('app.timezone'));

        $commentsQuery = $task->comments()
            ->with(['user', 'views.user', 'parent.user']);

        if ($lastCommentId) {
            $commentsQuery->where('id', '>', $lastCommentId);
        } else {
            $commentsQuery->where('created_at', '>=', $sinceDate->copy()->subSeconds(15)->toDateTimeString());
        }

        $newComments = $commentsQuery->get()
            ->map(function($c) {
                $c->feed_type = 'comment';
                return $c;
            });

        $timeLogsQuery = $task->timeLogs()
            ->with('user');

        if ($lastTimelogId) {
            $timeLogsQuery->where('id', '>', $lastTimelogId);
        } else {
            $timeLogsQuery->where('created_at', '>=', $sinceDate->copy()->subSeconds(15)->toDateTimeString());
        }

        $newTimeLogs = $timeLogsQuery->get()
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
            $formattedTime = $item->created_at->format('d M Y, h:i A');
            $html .= view('tasks.partials.feed_item', [
                'item' => $item,
                'isSent' => $isSent,
                'formattedTime' => $formattedTime,
                'task' => $task
            ])->render();
        }

        $latestTime = $newFeed->count() > 0 ? $newFeed->last()->created_at->toISOString() : $since;
        
        $latestCommentId = $newComments->count() > 0 ? $newComments->last()->id : $lastCommentId;
        $latestTimelogId = $newTimeLogs->count() > 0 ? $newTimeLogs->last()->id : $lastTimelogId;

        return response()->json([
            'html' => $html,
            'latest_time' => $latestTime,
            'last_comment_id' => $latestCommentId,
            'last_timelog_id' => $latestTimelogId,
            'has_updates' => $newFeed->count() > 0,
            'play_sound' => $newFeed->contains(fn($item) => $item->user_id !== auth()->id())
        ]);
    }

    public function editComment(Request $request, \App\Models\TaskComment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403, 'Unauthorized.');
        abort_if($comment->created_at->diffInMinutes(now()) >= 30, 400, 'Editing window expired.');

        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment->comment = $request->comment;
        $comment->is_edited = true;
        $comment->save();

        return response()->json([
            'success' => true,
            'comment' => $comment->comment,
        ]);
    }

    public function toggleCommentPin(\App\Models\TaskComment $comment)
    {
        $user = auth()->user();
        $task = $comment->task;
        if (!$user->isLeaderOrAbove() && $task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $comment->is_pinned = !$comment->is_pinned;
        $comment->save();

        return response()->json([
            'success' => true,
            'is_pinned' => $comment->is_pinned,
        ]);
    }

    public function toggleCommentImportant(\App\Models\TaskComment $comment)
    {
        $user = auth()->user();
        $task = $comment->task;
        if (!$user->isLeaderOrAbove() && $task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $comment->is_important = !$comment->is_important;
        $comment->save();

        return response()->json([
            'success' => true,
            'is_important' => $comment->is_important,
        ]);
    }
}
