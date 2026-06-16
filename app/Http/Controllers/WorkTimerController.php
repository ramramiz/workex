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
        $session = WorkSession::where('user_id', $user->id)->whereDate('date', $today)->first();
        $myTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['project', 'timeLogs' => fn($q) => $q->where('status', 'running')])
            ->get();
        $activeLog = TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->with('task.project')->first();
        $todayLogs = TaskTimeLog::where('user_id', $user->id)->whereDate('created_at', $today)->with('task')->get();
        $runningTimers = TaskTimeLog::where('status', 'running')
            ->with(['user', 'task.project'])
            ->orderBy('started_at', 'desc')
            ->get();

        return view('work-timer.index', compact('session', 'myTasks', 'activeLog', 'todayLogs', 'runningTimers'));
    }

    public function startDay(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $existing = WorkSession::where('user_id', $user->id)->whereDate('date', $today)->first();
        if ($existing) {
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

        // Mark attendance
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'login_time'   => now(),
                'type'         => 'office',
                'status'       => 'present',
                'late_minutes' => $this->calcLateMinutes(),
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
        $session = WorkSession::where('user_id', $user->id)->whereDate('date', today())->where('status', 'active')->first();

        if (!$session) {
            return back()->with('error', 'No active work session found.');
        }

        // End any running task logs
        TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->each(function ($log) {
            $mins = $log->started_at->diffInMinutes(now());
            $log->update(['ended_at' => now(), 'total_minutes' => $mins, 'status' => 'ended']);
        });

        // Calculate total mins based on first task start to last task end
        $firstLog = TaskTimeLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->orderBy('started_at', 'asc')
            ->first();

        $lastLog = TaskTimeLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->orderBy('ended_at', 'desc')
            ->first();

        $sessionStart = $session->started_at;
        $sessionEnd = now();

        if ($firstLog && $lastLog && $firstLog->started_at && $lastLog->ended_at) {
            $sessionStart = $firstLog->started_at;
            $sessionEnd = $lastLog->ended_at;
        }

        $totalMins = $sessionStart->diffInMinutes($sessionEnd);
        $productiveMins = TaskTimeLog::where('user_id', $user->id)->whereDate('created_at', today())->sum('total_minutes');

        $session->update([
            'started_at'        => $sessionStart,
            'ended_at'          => $sessionEnd,
            'total_minutes'     => $totalMins,
            'productive_minutes' => $productiveMins,
            'status'            => 'ended',
            'work_done'         => $request->work_done,
        ]);

        // Update attendance
        Attendance::where('user_id', $user->id)->whereDate('date', today())->update([
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
        
        $session = WorkSession::where('user_id', $user->id)->whereDate('date', today())->first();

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
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', today())->first();

        if (!$attendance) {
            Attendance::create([
                'user_id'      => $user->id,
                'date'         => today(),
                'login_time'   => now(),
                'type'         => 'office',
                'status'       => 'present',
                'late_minutes' => $this->calcLateMinutes(),
            ]);
        }

        // Pause any running task
        TaskTimeLog::where('user_id', $user->id)->where('status', 'running')->each(function ($log) {
            $mins = $log->started_at->diffInMinutes(now());
            $log->update(['paused_at' => now(), 'total_minutes' => $mins, 'status' => 'paused']);
        });

        TaskTimeLog::create([
            'task_id'         => $task->id,
            'user_id'         => $user->id,
            'work_session_id' => $session->id,
            'started_at'      => now(),
            'status'          => 'running',
            'note'            => $request->note,
        ]);

        $task->update(['status' => 'in_progress']);

        return back()->with('success', 'Timer started for: ' . $task->title);
    }

    public function pauseTask(Request $request, TaskTimeLog $log)
    {
        $mins = $log->started_at->diffInMinutes(now());
        $log->update(['paused_at' => now(), 'total_minutes' => $mins, 'status' => 'paused']);
        return back()->with('success', 'Task paused.');
    }

    public function resumeTask(Request $request, TaskTimeLog $log)
    {
        // Pause any other running logs
        TaskTimeLog::where('user_id', auth()->id())->where('status', 'running')->each(function ($l) {
            $mins = $l->started_at->diffInMinutes(now());
            $l->update(['paused_at' => now(), 'total_minutes' => $mins, 'status' => 'paused']);
        });

        $log->update(['resumed_at' => now(), 'status' => 'running']);
        return back()->with('success', 'Task resumed!');
    }

    public function endTask(Request $request, TaskTimeLog $log)
    {
        $request->validate(['note' => 'nullable|string|max:500']);

        $mins = $log->started_at->diffInMinutes(now());
        $log->update([
            'ended_at'      => now(),
            'total_minutes' => $mins,
            'note'          => $request->note,
            'status'        => 'ended',
        ]);

        return back()->with('success', 'Task time recorded: ' . $mins . ' minutes.');
    }

    public function status()
    {
        $user = auth()->user();
        $session = WorkSession::where('user_id', $user->id)->whereDate('date', today())->first();
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
