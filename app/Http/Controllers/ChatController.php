<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $tasks = Task::with(['project', 'assignee', 'comments', 'comments.views'])
            ->when(!$user->isLeaderOrAbove(), fn($q) => $q->where('assigned_to', $user->id))
            ->when($user->isTeamLeader(), function($q) {
                $q->where(function($sq) {
                    $sq->whereDoesntHave('assignee')
                       ->orWhereHas('assignee.role', function($r) {
                           $r->where('slug', '!=', 'telecaller');
                       });
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('chat.index', compact('tasks'));
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

        return response()->json([
            'html' => $html,
            'latest_time' => $feed->count() > 0 ? $feed->last()->created_at->toISOString() : now()->toISOString(),
            'task_title' => $task->title,
            'project_name' => $task->project->name ?? 'No Project',
            'assignee_name' => $task->assignee->name ?? 'Unassigned',
            'assignee_avatar' => $task->assignee ? $task->assignee->avatar_url : 'https://ui-avatars.com/api/?name=Unassigned',
            'task_url' => route('tasks.show', $task),
            'task_id' => $task->id,
            'store_url' => route('tasks.comments.store', $task)
        ]);
    }
}
