<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSession;
use App\Models\TaskTimeLog;
use App\Models\Task;
use App\Models\Attendance;
use Carbon\Carbon;

class WorkTimerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $session = WorkSession::where('user_id', $user->id)
            ->where(function($q) use ($today) {
                $q->where('status', 'active')
                  ->orWhereDate('date', $today);
            })
            ->latest()
            ->first();
        $myTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['project', 'timeLogs' => fn($q) => $q->where('status', 'running')])
            ->get();
        $activeLog = TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->with('task.project')->first();
        $todayLogs = $session
            ? TaskTimeLog::where('work_session_id', $session->id)->with('task')->get()
            : TaskTimeLog::where('user_id', $user->id)->whereDate('created_at', $today)->with('task')->get();
        $runningTimers = TaskTimeLog::where('status', 'running')
            ->with(['user', 'task.project'])
            ->orderBy('started_at', 'desc')
            ->get();

        $firstLog = $session
            ? TaskTimeLog::where('work_session_id', $session->id)->orderBy('started_at', 'asc')->first()
            : TaskTimeLog::where('user_id', $user->id)->whereDate('created_at', $today)->orderBy('started_at', 'asc')->first();
        $sessionStart = $firstLog ? $firstLog->started_at : ($session ? $session->started_at : null);

        return view('work-timer.index', compact('session', 'myTasks', 'activeLog', 'todayLogs', 'runningTimers', 'sessionStart'));
    }

    public function startDay(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $existing = WorkSession::where('user_id', $user->id)
            ->where(function($q) use ($today) {
                $q->where('status', 'active')
                  ->orWhereDate('date', $today);
            })
            ->latest()
            ->first();
        if ($existing) {
            if ($existing->status === 'active') {
                return back()->with('warning', 'You already have an active work session!');
            }
            return back()->with('warning', 'You have already started your day!');
        }

        $session = WorkSession::create([
            'user_id'     => $user->id,
            'date'        => $today,
            'started_at'  => now(),
            'ip_address'  => $request->ip(),
            'device_type' => $this->getDeviceType($request),
            'browser'     => $this->getBrowser($request),
            'device_info' => $request->userAgent(),
            'status'      => 'active',
        ]);

        // Mark attendance - initial start day registers record with login_time as null
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'login_time'   => null,
                'type'         => 'office',
                'status'       => 'present',
                'late_minutes' => 0,
            ]
        );

        \App\Models\ActivityLog::log('work_started', 'Started work day');

        return back()->with('success', 'Work day started! Have a productive day 🚀');
    }

    public function endDay(Request $request)
    {
        $request->validate([
            'work_done' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        $session = WorkSession::where('user_id', $user->id)->where('status', 'active')->latest()->first();

        if (!$session) {
            return back()->with('error', 'No active work session found.');
        }

        // End any running task logs
        TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->each(function ($log) {
            $mins = $log->started_at->diffInMinutes(now());
            $log->update(['ended_at' => now(), 'total_minutes' => $mins, 'status' => 'ended']);
        });

        // Calculate total mins based on first task start to last task end
        $firstLog = TaskTimeLog::where('work_session_id', $session->id)
            ->orderBy('started_at', 'asc')
            ->first();

        $lastLog = TaskTimeLog::where('work_session_id', $session->id)
            ->orderBy('ended_at', 'desc')
            ->first();

        $sessionStart = $session->started_at;
        $sessionEnd = now();

        if ($firstLog && $lastLog && $firstLog->started_at && $lastLog->ended_at) {
            $sessionStart = $firstLog->started_at;
            $sessionEnd = $lastLog->ended_at;
        }

        $totalMins = $sessionStart->diffInMinutes($sessionEnd);
        $productiveMins = TaskTimeLog::where('work_session_id', $session->id)->sum('total_minutes');

        $session->update([
            'started_at'        => $sessionStart,
            'ended_at'          => $sessionEnd,
            'total_minutes'     => $totalMins,
            'productive_minutes' => $productiveMins,
            'status'            => 'ended',
            'work_done'         => $request->work_done,
        ]);

        // Update attendance
        Attendance::where('user_id', $user->id)->whereDate('date', $session->date)->update([
            'login_time'    => $sessionStart,
            'logout_time'   => $sessionEnd,
            'total_minutes' => $totalMins,
        ]);

        \App\Models\ActivityLog::log('work_ended', 'Ended work day — ' . intdiv($totalMins, 60) . 'h ' . ($totalMins % 60) . 'min worked');

        return back()->with('success', 'Work day ended! Total: ' . intdiv($totalMins, 60) . 'h ' . ($totalMins % 60) . 'min. Great work! 🎉');
    }

    public function startTask(Request $request, Task $task)
    {
        $user = auth()->user();
        
        $session = null;
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $session = WorkSession::where('user_id', $user->id)
                ->where(function($q) {
                    $q->where('status', 'active')
                      ->orWhereDate('date', today());
                })
                ->latest()
                ->first();

            if (!$session) {
                $session = WorkSession::create([
                    'user_id'     => $user->id,
                    'date'        => today(),
                    'started_at'  => now(),
                    'ip_address'  => $request->ip(),
                    'device_type' => $this->getDeviceType($request),
                    'browser'     => $this->getBrowser($request),
                    'device_info' => $request->userAgent(),
                    'status'      => 'active',
                ]);
            } elseif ($session->status !== 'active') {
                $session->update(['status' => 'active']);
            }

            // Mark attendance if not already marked
            $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $session->date)->first();

            if (!$attendance) {
                Attendance::create([
                    'user_id'      => $user->id,
                    'date'         => $session->date,
                    'login_time'   => now(),
                    'type'         => 'office',
                    'status'       => 'present',
                    'late_minutes' => $this->calcLateMinutes(),
                ]);
            } elseif (is_null($attendance->login_time)) {
                $attendance->update([
                    'login_time'   => now(),
                    'late_minutes' => $this->calcLateMinutes(),
                ]);
            }
        }

        // Starting task timer (allows multiple active tasks simultaneously)
        $existingLog = TaskTimeLog::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->first();

        if ($existingLog) {
            return back()->with('warning', 'Timer is already running for this task.');
        }

        TaskTimeLog::create([
            'task_id'         => $task->id,
            'user_id'         => $user->id,
            'work_session_id' => $session->id,
            'started_at'      => now(),
            'status'          => 'running',
            'note'            => $request->note,
        ]);

        $task->update(['status' => 'in_progress']);

        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            $bug->update(['status' => 'open (' . $user->name . ')']);
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            \App\Models\TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'comment' => "⏱️ **Started work timer** — started at **" . now()->format('h:i A') . "**",
            ]);
        }

        return redirect()->route('chat.index', ['select_task' => $task->id])->with('success', 'Timer started for: ' . $task->title);
    }

    public function pauseTask(Request $request, TaskTimeLog $log)
    {
        $mins = $log->started_at->diffInMinutes(now());
        $log->update(['paused_at' => now(), 'total_minutes' => $mins, 'status' => 'paused']);
        return back()->with('success', 'Task paused.');
    }

    public function resumeTask(Request $request, TaskTimeLog $log)
    {
        // Resuming task timer (allows multiple active tasks simultaneously)

        $log->update(['resumed_at' => now(), 'status' => 'running']);

        $task = $log->task;
        $bug = \App\Models\Bug::where('task_id', $task->id)->first();
        if ($bug) {
            $bug->update(['status' => 'open (' . auth()->user()->name . ')']);
        }

        return back()->with('success', 'Task resumed!');
    }

    public function endTask(Request $request, TaskTimeLog $log)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,review,rework,completed,cancelled,rejected',
        ]);

        $mins = $log->started_at->diffInMinutes(now());
        $log->update([
            'ended_at'      => now(),
            'total_minutes' => $mins,
            'note'          => $request->note,
            'status'        => 'ended',
        ]);

        $newStatus = $request->status;
        $task = $log->task;
        $startTimeFormatted = $log->started_at->format('h:i A');
        $endTimeFormatted = now()->format('h:i A');
        $userName = auth()->user()->name;

        if ($newStatus && $task->status !== $newStatus) {
            if ($newStatus === 'completed') {
                $user = auth()->user();
                $isGlobalApprover = $user->isAdminOrAbove();
                if (!$isGlobalApprover) {
                    return back()->with('error', 'Only admins or global approvers can mark a task as completed directly.');
                }
            }
            $task->update(['status' => $newStatus]);

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

            if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
                $timeText = "worked **{$mins} mins** (started at **{$startTimeFormatted}**, ended at **{$endTimeFormatted}**)";
                \App\Models\TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'comment' => "🔄 status changed to **{$newLabel}** by **{$userName}** (on ending work timer, {$timeText})\n\n**Notes:** " . ($request->note ?? 'None'),
                ]);
            } else {
                \App\Models\TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'comment' => "🔄 status changed to **{$newLabel}** by **{$userName}** (on ending work timer)\n\n**Notes:** " . ($request->note ?? 'None'),
                ]);
            }
        } else {
            if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
                \App\Models\TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'comment' => "⏱️ **Completed time log** — worked **{$mins} mins** (started at **{$startTimeFormatted}**, ended at **{$endTimeFormatted}**)\n\n**Notes:** " . ($request->note ?? 'None'),
                ]);
            } elseif ($request->note) {
                \App\Models\TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'comment' => "⏱️ **Completed time log** — worked **{$mins} mins**\n\n**Notes:** {$request->note}",
                ]);
            }
        }

        return back()->with('success', 'Task time recorded: ' . $mins . ' minutes.');
    }

    public function status()
    {
        $user = auth()->user();
        $session = WorkSession::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('status', 'active')
                  ->orWhereDate('date', today());
            })
            ->latest()
            ->first();
        $activeLog = TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->with('task')->first();

        return response()->json([
            'session_status' => $session?->status ?? 'not_started',
            'total_minutes'  => $session ? $session->started_at->diffInMinutes(now()) : 0,
            'active_task'    => $activeLog?->task?->title,
        ]);
    }

    private function calcLateMinutes(): int
    {
        $workStart = \App\Models\Setting::get('work_start_time', '09:00');
        $expected = Carbon::today()->setTimeFromTimeString($workStart);
        return max(0, (int) $expected->diffInMinutes(now(), false));
    }

    private function getDeviceType(Request $request): string
    {
        $ua = strtolower($request->userAgent() ?? '');
        if (str_contains($ua, 'mobile')) return 'mobile';
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
        return 'desktop';
    }

    private function getBrowser(Request $request): string
    {
        $ua = $request->userAgent() ?? '';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        return 'Unknown';
    }
}
