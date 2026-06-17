<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSession;
use App\Models\User;
use Carbon\Carbon;

class LiveStatusController extends Controller
{
    public function index()
    {
        return view('live-status.index');
    }

    public function data()
    {
        $today = Carbon::today();

        $employees = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee', 'team-leader', 'telecaller']))
            ->where('status', 'active')
            ->with([
                'role',
                'employee.department',
                'todayWorkSession',
                'timeLogs' => fn($q) => $q->where('status', 'running')->with('task.project'),
            ])
            ->get()
            ->map(function ($user) {
                $currentCall = \Illuminate\Support\Facades\Cache::get('user_current_call_' . $user->id);

                if ($user->isTelecaller()) {
                    $activeRoomSession = \App\Models\LeadRoomWorkSession::where('user_id', $user->id)
                        ->whereDate('created_at', Carbon::today())
                        ->latest()
                        ->first();
                        
                    $status = 'not_started';
                    $startedAt = null;
                    $totalHours = '00:00';
                    $currentTask = null;
                    $currentTaskTime = null;
                    $currentTaskStart = null;
                    $currentProject = null;

                    $callsCount = 0;

                    if ($activeRoomSession) {
                        if ($activeRoomSession->status === 'active') {
                            $status = 'working';
                        } elseif ($activeRoomSession->status === 'paused') {
                            $status = 'idle';
                        } else {
                            $status = 'completed';
                        }

                        $startedAt = $activeRoomSession->created_at->format('h:i A');

                        $secs = $activeRoomSession->total_seconds;
                        if ($activeRoomSession->status === 'active') {
                            $secs += intval(abs(now()->diffInSeconds($activeRoomSession->started_at)));
                        }
                        $totalHours = sprintf('%02d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60));

                        if (in_array($activeRoomSession->status, ['active', 'paused'])) {
                            $callsCount = \App\Models\LeadCall::where('telecaller_id', $user->id)
                                ->whereBetween('created_at', [$activeRoomSession->created_at, now()])
                                ->count();
                        } else {
                            $callsCount = $activeRoomSession->calls_count;
                        }

                        if ($currentCall) {
                            $currentTask = 'Calling: ' . $currentCall['name'] . ' (' . $currentCall['phone'] . ')';
                            $diffSecs = max(0, now()->timestamp - $currentCall['started_at']);
                            $currentTaskTime = sprintf('%02d:%02d', intdiv($diffSecs, 60), $diffSecs % 60);
                            $currentTaskStart = $currentCall['started_at'];
                            $currentProject = 'Room: ' . ($activeRoomSession->room->name ?? 'N/A');
                        } elseif ($activeRoomSession->status === 'active') {
                            $currentTask = 'Calling Session Active';
                            $currentProject = 'Room: ' . ($activeRoomSession->room->name ?? 'N/A');
                        }
                    }

                    return [
                        'id'            => $user->id,
                        'name'          => $user->name,
                        'avatar'        => $user->avatar_url,
                        'role'          => $user->role?->name,
                        'department'    => 'Telecalling',
                        'status'        => $status,
                        'status_label'  => match($status) {
                            'working'     => 'Working',
                            'idle'        => 'Idle',
                            'completed'   => 'Day Done',
                            'not_started' => 'Not Started',
                            default       => 'Unknown',
                        },
                        'started_at'    => $startedAt,
                        'total_hours'   => $totalHours,
                        'current_task'  => $currentTask,
                        'current_task_time' => $currentTaskTime,
                        'current_task_start' => $currentTaskStart,
                        'current_project' => $currentProject,
                        'calls_count'   => $callsCount,
                        'working_tasks' => [],
                    ];
                }

                // Standard employee flow
                $session = $user->todayWorkSession;
                $activeLogs = $user->timeLogs; // Direct running task logs of the employee
                $firstActiveLog = $activeLogs->first();
                $status = $this->getStatus($session, $activeLogs);

                $workingTasks = [];
                foreach ($activeLogs as $log) {
                    $workingTasks[] = [
                        'task_title' => $log->task->title ?? 'Unknown Task',
                        'project_name' => $log->task->project->name ?? 'No Project',
                        'task_time' => sprintf('%02d:%02d', intdiv($log->started_at->diffInMinutes(now()), 60), $log->started_at->diffInMinutes(now()) % 60),
                        'task_start' => $log->started_at->timestamp,
                    ];
                }

                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'avatar'        => $user->avatar_url,
                    'role'          => $user->role?->name,
                    'department'    => $user->employee?->department?->name,
                    'status'        => $status,
                    'status_label'  => $this->getStatusLabel($session, $activeLogs),
                    'started_at'    => $session?->started_at?->format('h:i A'),
                    'total_hours'   => $session?->total_hours ?? '00:00',
                    'current_task'  => $firstActiveLog?->task?->title,
                    'current_task_time' => $firstActiveLog ? sprintf('%02d:%02d', intdiv($firstActiveLog->started_at->diffInMinutes(now()), 60), $firstActiveLog->started_at->diffInMinutes(now()) % 60) : null,
                    'current_task_start' => $firstActiveLog ? $firstActiveLog->started_at->timestamp : null,
                    'current_project' => $firstActiveLog?->task?->project?->name,
                    'calls_count'   => null,
                    'working_tasks' => $workingTasks,
                ];
            });

        return response()->json(['employees' => $employees, 'updated_at' => now()->format('h:i:s A')]);
    }

    private function getStatus($session, $activeLogs = null): string
    {
        if (!$session) return 'not_started';
        if ($session->status === 'ended') return 'completed';
        
        $hasActiveTask = $activeLogs !== null
            ? $activeLogs->isNotEmpty()
            : ($session->relationLoaded('timeLogs')
                ? $session->timeLogs->where('status', 'running')->isNotEmpty()
                : $session->activeTaskLog !== null);

        if ($hasActiveTask) return 'working';
        // check if started but idle (no active task)
        if ($session->started_at && $session->started_at->diffInMinutes(now()) > 15) return 'idle';
        return 'working';
    }

    private function getStatusLabel($session, $activeLogs = null): string
    {
        return match($this->getStatus($session, $activeLogs)) {
            'working'     => 'Working',
            'idle'        => 'Idle',
            'completed'   => 'Completed',
            'not_started' => 'Not Started',
            default       => 'Unknown',
        };
    }
}
