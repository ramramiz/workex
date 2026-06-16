<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadAppointment;
use App\Models\AppNotification;
use App\Models\User;

class LeadAppointmentController extends Controller
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
            'sales_executive_id' => 'required|exists:users,id',
            'meeting_date_time' => 'required|date',
            'type' => 'required|string|in:Demo,Visit,Call,Online',
            'notes' => 'nullable|string',
        ]);

        $appointment = LeadAppointment::create([
            'lead_id' => $lead->id,
            'sales_executive_id' => $request->sales_executive_id,
            'meeting_date_time' => $request->meeting_date_time,
            'type' => $request->type,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Update status of the Lead
        $lead->update(['status' => 'follow_up_required']);

        // Notify Sales Executive
        AppNotification::create([
            'user_id' => $request->sales_executive_id,
            'type' => 'appointment',
            'title' => 'New Lead Appointment Assigned',
            'message' => auth()->user()->name . ' booked a ' . $request->type . ' appointment for lead ' . $lead->client_name . ' on ' . $request->meeting_date_time,
            'url' => route('leads.show', $lead->id),
        ]);

        // Notify Managers
        $managers = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($managers as $mgr) {
            AppNotification::create([
                'user_id' => $mgr->id,
                'type' => 'appointment',
                'title' => 'New Lead Appointment Booked',
                'message' => auth()->user()->name . ' scheduled a demo/visit for ' . $lead->client_name . ' assigned to ' . $appointment->salesExecutive->name,
                'url' => route('leads.show', $lead->id),
            ]);
        }

        return back()->with('success', 'Appointment booked and executive notified!');
    }
}
