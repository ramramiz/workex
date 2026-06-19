<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToRoomWork
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $this->cleanupStaleSessions();
        }

        if ($user && $user->company_id) {
            $company = \App\Models\Company::find($user->company_id);
            if ($company && $company->status === 'suspended') {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Your company account has been suspended. Please contact the reseller.');
            }
        }

        if ($user && $user->isTelecaller()) {
            $allowedRoutesForTelecaller = [
                'dashboard',
                'profile.edit',
                'profile.update',
                'profile.destroy',
                'notifications.index',
                'notifications.unread-count',
                'notifications.mark-read',
                'notifications.mark-all-read',
                'leads.start-work.index',
                'leads.start-work.room',
                'leads.start-work.start',
                'leads.start-work.leads',
                'leads.start-work.pause',
                'leads.start-work.resume',
                'leads.start-work.stop',
                'leads.start-work.summary',
                'leads.start-work.start-session',
                'leads.start-work.select-room',
                'leads.start-work.select-room-join',
                'leads.start-work.select-followups',
                'leads.start-work.followup-leads',
                'leads.start-work.pause-followups',
                'leads.start-work.resume-followups',
                'leads.calls.store',
                'leads.appointments.store',
                'leads.requirements.update',
                'reports.telecaller-performance',
                'logout',
                'ai.correct',
                'leads.index',
                'leads.show',
                'leads.follow-up',

                // Chat routes
                'chat.index',
                'chat.show',

                // Mailbox routes
                'mailbox.index',
                'mailbox.official.index',
                'mailbox.fetch-new',
                'mailbox.official.show',
                'mailbox.store',
                'mailbox.official.destroy',
                'mailbox.settings.save',

                // Leaves routes
                'leaves.index',
                'leaves.create',
                'leaves.store',
                'leaves.show',
                'leaves.edit',
                'leaves.update',
                'leaves.destroy',
            ];

            $currentRoute = $request->route()?->getName();

            if ($currentRoute && !in_array($currentRoute, $allowedRoutesForTelecaller)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized.'], 403);
                }
                abort(403, 'Access Denied. You do not have permission to view this page.');
            }
        }

        if ($user && $user->isTelecaller() && $user->active_room_work_session_id) {
            $session = $user->activeRoomWorkSession;
            if ($session) {
                // Keep the Laravel session in sync
                if (!session()->has('active_room_work')) {
                    $isFollowup = \App\Models\TaskTimeLog::where('user_id', $user->id)
                        ->whereIn('status', ['running', 'paused'])
                        ->whereHas('task', function($q) {
                            $q->where('title', 'Room Calling: Today Follow-ups');
                        })
                        ->exists();

                    session(['active_room_work' => [
                        'room_id' => $isFollowup ? 'followups' : $session->lead_room_id,
                        'started_at' => $session->started_at ? $session->started_at->toISOString() : null,
                        'status' => $session->status,
                        'accumulated_seconds' => $session->total_seconds,
                    ]]);
                }

                if ($session->status === 'active') {
                    $allowedRoutes = [
                        'leads.start-work.leads',
                        'leads.start-work.followup-leads',
                        'leads.calls.store',
                        'leads.start-work.stop',
                        'leads.start-work.pause',
                        'leads.start-work.pause-followups',
                        'leads.start-work.resume-followups',
                        'leads.start-work.select-room',
                        'leads.start-work.select-room-join',
                        'leads.start-work.select-followups',
                        'logout',
                    ];

                    $currentRoute = $request->route()?->getName();

                    if (!in_array($currentRoute, $allowedRoutes)) {
                        if (session('active_room_work') && session('active_room_work')['room_id'] === 'followups') {
                            return redirect()->route('leads.start-work.followup-leads')
                                ->with('warning', 'Please pause or stop your active calling session before accessing other pages.');
                        }
                        if (!$session->lead_room_id) {
                            return redirect()->route('leads.start-work.select-room')
                                ->with('warning', 'Please select a room to start calling.');
                        }
                        return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                            ->with('warning', 'Please pause or stop your active calling session before accessing other pages.');
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Automatically completes active/paused work sessions from previous days.
     */
    private function cleanupStaleSessions(): void
    {
        // Limit cleanup run to once every 5 minutes to avoid DB overhead
        if (\Illuminate\Support\Facades\Cache::has('last_stale_session_cleanup')) {
            return;
        }

        \Illuminate\Support\Facades\Cache::put('last_stale_session_cleanup', true, now()->addMinutes(5));

        $today = \Carbon\Carbon::today();

        // 1. Clean up stale standard WorkSessions
        $staleWorkSessions = \App\Models\WorkSession::where('status', 'active')
            ->whereDate('date', '<', $today)
            ->get();

        foreach ($staleWorkSessions as $session) {
            // Find running task logs for this session and end them
            $runningLogs = \App\Models\TaskTimeLog::where('work_session_id', $session->id)
                ->where('status', 'running')
                ->get();
                
            foreach ($runningLogs as $log) {
                $log->update([
                    'ended_at' => $log->started_at,
                    'total_minutes' => 0,
                    'status' => 'ended',
                    'note' => 'Auto-ended by system (stale task timer)',
                ]);
            }
            
            // Find the last ended task log to estimate when they actually left
            $lastLog = \App\Models\TaskTimeLog::where('work_session_id', $session->id)
                ->where('status', 'ended')
                ->orderBy('ended_at', 'desc')
                ->first();
                
            $sessionEnd = $session->started_at->addHours(8);
            if ($lastLog && $lastLog->ended_at && $lastLog->ended_at->isSameDay($session->date)) {
                $sessionEnd = $lastLog->ended_at;
            }
            
            $dayEnd = \Carbon\Carbon::parse($session->date)->endOfDay();
            if ($sessionEnd->gt($dayEnd)) {
                $sessionEnd = $dayEnd;
            }
            
            $totalMins = max(0, $session->started_at->diffInMinutes($sessionEnd));
            $productiveMins = \App\Models\TaskTimeLog::where('work_session_id', $session->id)->sum('total_minutes');
            
            $session->update([
                'ended_at' => $sessionEnd,
                'total_minutes' => $totalMins,
                'productive_minutes' => $productiveMins,
                'status' => 'ended',
                'work_done' => 'Auto-closed by system (forgot to clock out)',
            ]);
            
            $attendance = \App\Models\Attendance::where('user_id', $session->user_id)
                ->whereDate('date', $session->date)
                ->first();
                
            if ($attendance) {
                $attendance->update([
                    'logout_time' => $sessionEnd,
                    'total_minutes' => $totalMins,
                ]);
            }
        }

        // 2. Clean up stale LeadRoomWorkSessions (telecaller sessions)
        $staleRoomSessions = \App\Models\LeadRoomWorkSession::whereIn('status', ['active', 'paused'])
            ->whereDate('created_at', '<', $today)
            ->get();

        foreach ($staleRoomSessions as $session) {
            $finalSeconds = $session->total_seconds;
            if ($session->status === 'active') {
                $elapsed = intval(abs($session->started_at->diffInSeconds(now())));
                $maxEnd = $session->started_at->addHours(8);
                $dayEnd = $session->created_at->endOfDay();
                if ($maxEnd->gt($dayEnd)) {
                    $maxEnd = $dayEnd;
                }
                $diff = max(0, $session->started_at->diffInSeconds($maxEnd));
                $finalSeconds += $diff;
            }
            
            // Auto-end any running calling task time logs for this telecaller
            $runningLogs = \App\Models\TaskTimeLog::where('user_id', $session->user_id)
                ->where('status', 'running')
                ->whereHas('task', function($q) {
                    $q->where('title', 'like', 'Room Calling:%');
                })
                ->get();
                
            foreach ($runningLogs as $log) {
                $log->update([
                    'ended_at' => $log->started_at,
                    'total_minutes' => 0,
                    'status' => 'ended',
                    'note' => 'Auto-ended by system (stale calling timer)',
                ]);
            }
            
            $callsCount = \App\Models\LeadCall::where('telecaller_id', $session->user_id)
                ->whereBetween('created_at', [$session->created_at, $session->created_at->endOfDay()])
                ->count();
                
            $leadIds = \App\Models\LeadCall::where('telecaller_id', $session->user_id)
                ->whereBetween('created_at', [$session->created_at, $session->created_at->endOfDay()])
                ->pluck('lead_id');
                
            $convertedCount = \App\Models\Lead::whereIn('id', $leadIds)
                ->where('status', 'converted')
                ->count();
                
            $session->update([
                'ended_at' => $session->created_at->endOfDay(),
                'total_seconds' => $finalSeconds,
                'calls_count' => $callsCount,
                'converted_count' => $convertedCount,
                'status' => 'pending',
            ]);
            
            $u = \App\Models\User::find($session->user_id);
            if ($u && $u->active_room_work_session_id == $session->id) {
                $u->update(['active_room_work_session_id' => null]);
            }
        }
    }
}
