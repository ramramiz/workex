<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $tasks = Task::with(['project', 'assignee', 'comments.user', 'comments.views', 'comments.parent.user', 'timeLogs' => fn($q) => $q->where('status', 'running')])
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
            
        // Get all active users in the same company, excluding current user for unified direct messaging
        $users = \App\Models\User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->with(['role', 'employee.designation'])
            ->get()
            ->map(function ($u) use ($user) {
                // Calculate unread count from this user
                $u->unread_count = \App\Models\DirectMessage::where('sender_id', $u->id)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                // Get last message in the thread
                $u->last_message = \App\Models\DirectMessage::where(function ($q) use ($user, $u) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $u->id);
                    })
                    ->orWhere(function ($q) use ($user, $u) {
                        $q->where('sender_id', $u->id)->where('receiver_id', $user->id);
                    })
                    ->latest()
                    ->first();

                return $u;
            })
            ->sortByDesc(function ($u) {
                return $u->last_message ? $u->last_message->created_at->timestamp : 0;
            });

        $noSidebar = true;

        $unifiedItems = collect();

        foreach ($tasks as $t) {
            $unreadCommentsCount = $t->comments->filter(function($comment) use ($user) {
                return $comment->user_id !== $user->id && !$comment->views->contains('user_id', $user->id);
            })->count();
            
            $lastComment = $t->comments->sortByDesc('created_at')->first();
            $lastText = $lastComment ? $lastComment->comment : 'No messages yet';
            $lastTime = $lastComment ? $lastComment->created_at : $t->updated_at;
            
            $unifiedItems->push((object)[
                'type' => 'task',
                'id' => $t->id,
                'title' => $t->title,
                'subtitle' => $t->project->name ?? 'No Project',
                'avatar' => $t->avatar_url,
                'unread_count' => $unreadCommentsCount,
                'last_message' => $lastText,
                'timestamp' => $lastTime->timestamp,
                'time_formatted' => $lastTime->diffForHumans(null, true),
                'priority' => $t->priority,
                'priority_badge' => $t->priority_badge,
                'is_working' => $t->timeLogs->isNotEmpty(),
                'is_bug' => str_starts_with(strtolower($t->title), 'bug:'),
                'is_room_calling' => str_starts_with(strtolower($t->title), 'room calling:'),
                'task' => $t
            ]);
        }

        foreach ($users as $u) {
            $lastTime = $u->last_message ? $u->last_message->created_at : null;
            $lastText = $u->last_message ? ($u->last_message->message ?? '[Image]') : 'No messages yet';
            
            $unifiedItems->push((object)[
                'type' => 'direct',
                'id' => $u->id,
                'title' => $u->name,
                'subtitle' => '',
                'avatar' => $u->avatar_url,
                'unread_count' => $u->unread_count,
                'last_message' => $lastText,
                'timestamp' => $lastTime ? $lastTime->timestamp : 0,
                'time_formatted' => $lastTime ? $lastTime->diffForHumans(null, true) : '',
                'is_online' => $u->is_working_today,
                'user' => $u
            ]);
        }

        $unifiedItems = $unifiedItems->sortByDesc('timestamp')->values();

        $clients = \App\Models\Client::where('status', 'active')->get();
        $teamLeaders = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        $projectTypes = \App\Models\Project::whereNotNull('type')->distinct()->pluck('type')->toArray();

        return view('chat.index', compact('tasks', 'projects', 'employees', 'noSidebar', 'users', 'unifiedItems', 'clients', 'teamLeaders', 'projectTypes'));
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
            $formattedTime = $item->created_at->format('d M Y, h:i A');
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
        if (!$activeLog && (auth()->user()->isSuperAdmin() || auth()->user()->isTeamLeader() || auth()->user()->isAdmin())) {
            $activeLog = \App\Models\TaskTimeLog::where('task_id', $task->id)
                ->where('status', 'running')
                ->first();
        }
        $isButtonsDisabled = $task->status === 'completed' || $task->status === 'review';
        $isWorking = \App\Models\TaskTimeLog::where('task_id', $task->id)
            ->where('status', 'running')
            ->exists();

        $deadlineDays = null;
        if ($task->deadline) {
            $deadlineDays = now()->startOfDay()->diffInDays($task->deadline, false);
        }

        $lastComment = $feed->where('feed_type', 'comment')->last();
        $lastTimelog = $feed->where('feed_type', 'time_log')->last();

        return response()->json([
            'html' => $html,
            'latest_time' => $feed->count() > 0 ? $feed->last()->created_at->toISOString() : now()->toISOString(),
            'last_comment_id' => $lastComment?->id,
            'last_timelog_id' => $lastTimelog?->id,
            'task_title' => $task->title,
            'project_name' => $task->project->name ?? 'No Project',
            'project_id' => $task->project_id,
            'assignee_name' => $task->assignee->name ?? 'Unassigned',
            'assignee_id' => $task->assigned_to,
            'assignee_avatar' => $task->avatar_url,
            'assignee_real_avatar' => $task->assignee ? $task->assignee->avatar_url : 'https://ui-avatars.com/api/?name=Unassigned&background=cbd5e1&color=64748b',
            'task_url' => '/tasks/' . $task->id,
            'task_id' => $task->id,
            'store_url' => '/tasks/' . $task->id . '/comments',
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

    public function getUnreadCounts()
    {
        $user = auth()->user();
        
        $tasks = Task::select('id')
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
            ->get();
            
        $taskIds = $tasks->pluck('id');
        
        $unreadComments = \App\Models\TaskComment::whereIn('task_id', $taskIds)
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('views', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->select('task_id', \DB::raw('count(*) as count'))
            ->groupBy('task_id')
            ->get()
            ->pluck('count', 'task_id');

        $directUnreadCounts = \App\Models\DirectMessage::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->select('sender_id', \DB::raw('count(*) as count'))
            ->groupBy('sender_id')
            ->get()
            ->pluck('count', 'sender_id');

        $totalUnreadComments = $unreadComments->sum();
        $totalUnreadDirect = $directUnreadCounts->sum();
            
        return response()->json([
            'unread_counts' => $unreadComments,
            'direct_unread_counts' => $directUnreadCounts,
            'total_unread' => $totalUnreadComments + $totalUnreadDirect
        ]);
    }

    public function getUnifiedList()
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
            ->get();
            
        $users = \App\Models\User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->with(['role', 'employee.designation'])
            ->get()
            ->map(function ($u) use ($user) {
                $u->unread_count = \App\Models\DirectMessage::where('sender_id', $u->id)
                    ->where('receiver_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $u->last_message = \App\Models\DirectMessage::where(function ($q) use ($user, $u) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $u->id);
                    })
                    ->orWhere(function ($q) use ($user, $u) {
                        $q->where('sender_id', $u->id)->where('receiver_id', $user->id);
                    })
                    ->latest()
                    ->first();

                return $u;
            });
            
        $unifiedItems = [];
        foreach ($tasks as $t) {
            $unreadCommentsCount = $t->comments->filter(function($comment) use ($user) {
                return $comment->user_id !== $user->id && !$comment->views->contains('user_id', $user->id);
            })->count();
            
            $lastComment = $t->comments->sortByDesc('created_at')->first();
            $lastText = $lastComment ? $lastComment->comment : 'No messages yet';
            $lastTime = $lastComment ? $lastComment->created_at : $t->updated_at;
            
            $unifiedItems[] = [
                'type' => 'task',
                'id' => $t->id,
                'title' => $t->title,
                'subtitle' => $t->project->name ?? 'No Project',
                'avatar' => $t->avatar_url,
                'unread_count' => $unreadCommentsCount,
                'last_message' => $lastText,
                'timestamp' => $lastTime->timestamp,
                'time_formatted' => $lastTime->diffForHumans(null, true),
                'priority' => $t->priority,
                'priority_badge' => $t->priority_badge,
                'is_working' => $t->timeLogs->isNotEmpty(),
                'is_bug' => str_starts_with(strtolower($t->title), 'bug:'),
                'is_room_calling' => str_starts_with(strtolower($t->title), 'room calling:'),
            ];
        }
        
        foreach ($users as $u) {
            $lastTime = $u->last_message ? $u->last_message->created_at : null;
            $lastText = $u->last_message ? ($u->last_message->message ?? '[Image]') : 'No messages yet';
            
            $unifiedItems[] = [
                'type' => 'direct',
                'id' => $u->id,
                'title' => $u->name,
                'subtitle' => '',
                'avatar' => $u->avatar_url,
                'unread_count' => $u->unread_count,
                'last_message' => $lastText,
                'timestamp' => $lastTime ? $lastTime->timestamp : 0,
                'time_formatted' => $lastTime ? $lastTime->diffForHumans(null, true) : '',
                'is_online' => $u->is_working_today,
            ];
        }
        
        usort($unifiedItems, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        
        return response()->json([
            'success' => true,
            'items' => $unifiedItems
        ]);
    }

    public function getEmployeeTasks(\App\Models\User $employee)
    {
        $tasks = \App\Models\Task::with('project')
            ->where('assigned_to', $employee->id)
            ->where('status', '!=', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'project_name' => $t->project->name ?? 'No Project',
                    'status' => ucfirst(str_replace('_', ' ', $t->status)),
                    'priority' => ucfirst($t->priority),
                    'deadline' => $t->deadline ? $t->deadline->format('M d, Y') : 'No deadline',
                    'priority_badge' => $t->priority_badge
                ];
            });

        return response()->json([
            'success' => true,
            'employee_name' => $employee->name,
            'tasks' => $tasks
        ]);
    }
}
