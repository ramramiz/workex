<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $tasks = Task::with(['project', 'assignee', 'comments', 'comments.views', 'timeLogs' => fn($q) => $q->where('status', 'running')])
            ->where('status', '!=', 'completed')
            ->when(!$user->isLeaderOrAbove(), fn($q) => $q->where('assigned_to', $user->id))
            ->when($user->isTeamLeader(), function($q) {
                $q->where(function($sq) {
                    $sq->whereDoesntHave('assignee')
                       ->orWhereHas('assignee.role', function($r) {
                           $r->where('slug', '!=', 'telecaller');
                       });
                });
            })
            ->orderByRaw("CASE WHEN title LIKE 'Bug:%' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE priority 
                WHEN 'critical' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
                ELSE 5 
             END")
            ->orderBy('updated_at', 'desc')
            ->get();
            
        $projects = \App\Models\Project::when(!$user->isAdminOrAbove(), fn($q) => $q->where('team_leader_id', $user->id))->get();
        $employees = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader']))->where('status', 'active')->get();
            
        $noSidebar = true;
        return view('chat.index', compact('tasks', 'projects', 'employees', 'noSidebar'));
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
        // Authorization check
        if (!$user->isLeaderOrAbove() && $task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized access to task chat.');
        }

        if ($user->isTeamLeader() && $task->assignee && $task->assignee->isTelecaller()) {
            abort(403, 'Unauthorized access to telecaller task chat.');
        }

        $task->load(['project', 'assignee', 'creator', 'comments.user', 'comments.views.user', 'files', 'timeLogs.user']);

        // Record views for comments
        foreach ($task->comments as $comment) {
            if ($comment->user_id !== $user->id) {
                \App\Models\TaskCommentView::firstOrCreate([
                    'task_comment_id' => $comment->id,
                    'user_id'         => $user->id,
                ]);
            }
        }

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

        $html = '';
        foreach ($feed as $item) {
            $isSent = $item->user_id === $user->id;
            $formattedTime = $item->created_at->format('h:i A');
            $html .= view('tasks.partials.feed_item', [
                'item' => $item,
                'isSent' => $isSent,
                'formattedTime' => $formattedTime,
                'task' => $task
            ])->render();
        }

        $activeLog = \App\Models\TaskTimeLog::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->first();
        $isButtonsDisabled = $task->status === 'completed' || $task->status === 'review';
        $isWorking = \App\Models\TaskTimeLog::where('task_id', $task->id)
            ->where('status', 'running')
            ->exists();

        $deadlineDays = null;
        if ($task->deadline) {
            $deadlineDays = now()->startOfDay()->diffInDays($task->deadline, false);
        }

        return response()->json([
            'html' => $html,
            'latest_time' => $feed->count() > 0 ? $feed->last()->created_at->toISOString() : now()->toISOString(),
            'task_title' => $task->title,
            'project_name' => $task->project->name ?? 'No Project',
            'project_id' => $task->project_id,
            'assignee_name' => $task->assignee->name ?? 'Unassigned',
            'assignee_id' => $task->assigned_to,
            'assignee_avatar' => $task->avatar_url,
            'task_url' => route('tasks.show', $task),
            'task_id' => $task->id,
            'store_url' => route('tasks.comments.store', $task),
            'active_log_id' => $activeLog?->id,
            'status' => $task->status,
            'is_buttons_disabled' => $isButtonsDisabled,
            'is_working' => $isWorking,
            'description' => $task->description ?? 'No description provided.',
            'priority' => ucfirst($task->priority),
            'priority_raw' => $task->priority,
            'deadline' => $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline',
            'deadline_raw' => $task->deadline ? $task->deadline->format('Y-m-d') : '',
            'deadline_days' => $deadlineDays,
            'estimated_hours' => $task->estimated_hours,
            'creator_name' => $task->creator->name ?? 'System',
            'creator_avatar' => $task->creator ? $task->creator->avatar_url : 'https://ui-avatars.com/api/?name=System',
            'status_text' => ucfirst(str_replace('_', ' ', $task->status)),
            'created_at' => $task->created_at->format('M d, Y h:i A'),
        ]);
    }
}
