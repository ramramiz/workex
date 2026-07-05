<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadCall;

class LeadCallController extends Controller
{
    public function store(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->isTelecaller()) {
            $hasRoomAccess = $lead->lead_room_id && $lead->room()->whereHas('users', fn($q) => $q->where('users.id', $user->id))->exists();
            $hasDirectAccess = !$lead->lead_room_id && $lead->assigned_to === $user->id;

            if (!$hasRoomAccess && !$hasDirectAccess) {
                abort(403, 'Unauthorized access to this lead.');
            }
        }

        $request->validate([
            'status' => 'required|string|in:Connected,Not Connected,Busy,Switched Off',
            'customer_response' => 'nullable|string',
            'next_action' => 'nullable|string',
            'remarks' => 'nullable|string',
            'lead_status' => 'required_if:status,Connected|nullable|string|in:new,interested,not_interested,call_back_later,follow_up_required,converted,closed',
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $isFollowup = $request->boolean('is_followup');
        if (!$isFollowup) {
            if (session('active_room_work') && session('active_room_work')['room_id'] === 'followups') {
                $isFollowup = true;
            } else if ($user && $user->active_room_work_session_id) {
                $sess = $user->activeRoomWorkSession;
                if ($sess && $sess->lead_room_id === null) {
                    $isFollowup = \App\Models\TaskTimeLog::where('user_id', $user->id)
                        ->whereIn('status', ['running', 'paused'])
                        ->whereHas('task', function($q) {
                            $q->where('title', 'Room Calling: Today Follow-ups');
                        })
                        ->exists();
                }
            }
        }

        $unconnectedStatuses = ['Not Connected', 'Busy', 'Switched Off'];
        $previousUnconnectedCount = 0;
        if (in_array($request->status, $unconnectedStatuses)) {
            $previousUnconnectedCount = $lead->calls()
                ->whereIn('status', $unconnectedStatuses)
                ->count();
        }

        LeadCall::create([
            'lead_id' => $lead->id,
            'telecaller_id' => auth()->id(),
            'call_date_time' => now(),
            'status' => $request->status,
            'customer_response' => $request->customer_response,
            'next_action' => $request->next_action,
            'remarks' => $request->remarks,
            'duration' => $request->duration,
            'is_followup' => $isFollowup,
        ]);

        if (in_array($request->status, $unconnectedStatuses) && $previousUnconnectedCount >= 1) {
            $lead->update([
                'status' => 'permanently_not_connected',
                'follow_up_date' => null,
            ]);
        }

        if ($request->filled('next_follow_up_date')) {
            \App\Models\LeadFollowUp::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'note' => $request->next_action ?? 'Follow-up after call log',
                'next_follow_up' => $request->next_follow_up_date,
                'follow_up_time' => $request->next_follow_up_time ?? '10:00',
                'status' => 'pending',
            ]);
            $lead->update([
                'follow_up_date' => $request->next_follow_up_date,
            ]);
            if (!$request->filled('lead_status')) {
                $lead->update(['status' => 'following_up']);
            }
        } else {
            $lead->update([
                'follow_up_date' => null,
            ]);
        }

        if ($request->filled('lead_status')) {
            $lead->update(['status' => $request->lead_status]);
        }

        \Illuminate\Support\Facades\Cache::forget('user_current_call_' . auth()->id());

        if ($request->input('source') === 'room_work') {
            if ($isFollowup) {
                return redirect()->route('leads.start-work.followup-leads')
                    ->with('success', 'Call log registered successfully!');
            }

            return redirect()->route('leads.start-work.leads', [
                'room' => $lead->lead_room_id,
                'tab' => 'uncalled'
            ])->with('success', 'Call log registered successfully!');
        }

        return back()->with('success', 'Call log registered successfully!');
    }
}
