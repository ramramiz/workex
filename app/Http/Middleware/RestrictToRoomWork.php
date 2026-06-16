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
                    session(['active_room_work' => [
                        'room_id' => $session->lead_room_id,
                        'started_at' => $session->started_at ? $session->started_at->toISOString() : null,
                        'status' => $session->status,
                        'accumulated_seconds' => $session->total_seconds,
                    ]]);
                }

                if ($session->status === 'active') {
                    $allowedRoutes = [
                        'leads.start-work.leads',
                        'leads.calls.store',
                        'leads.start-work.stop',
                        'leads.start-work.pause',
                        'logout',
                    ];

                    $currentRoute = $request->route()?->getName();

                    if (!in_array($currentRoute, $allowedRoutes)) {
                        return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                            ->with('warning', 'Please pause or stop your active calling session before accessing other pages.');
                    }

                    if ($currentRoute === 'leads.start-work.leads') {
                        $room = $request->route('room');
                        $roomId = is_object($room) ? $room->id : $room;
                        if ($roomId != $session->lead_room_id) {
                            return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                                ->with('warning', 'You have an active calling session in another room.');
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
