<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Client;
use App\Models\LeadFollowUp;
use App\Models\User;

class LeadController extends Controller
{
    private function authorizeLead(Lead $lead)
    {
        $user = auth()->user();
        if ($user->isTelecaller()) {
            $hasRoomAccess = $lead->lead_room_id && $lead->room()->whereHas('users', fn($q) => $q->where('users.id', $user->id))->exists();
            $hasDirectAccess = !$lead->lead_room_id && $lead->assigned_to === $user->id;

            if (!$hasRoomAccess && !$hasDirectAccess) {
                abort(403, 'Unauthorized access to this lead.');
            }
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->isTelecaller()) {
            return redirect()->route('leads.start-work.index');
        }

        $leads = Lead::with(['assignedTo', 'createdBy', 'client'])
            ->forUser($user)
            ->when($request->room_id, fn($q) => $q->where('lead_room_id', $request->room_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('client_name', 'like', "%{$request->search}%")->orWhere('requirement', 'like', "%{$request->search}%"))
            ->latest()->paginate(15);

        $rooms = \App\Models\LeadRoom::latest()->get();

        return view('leads.index', compact('leads', 'rooms'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->get();
        $users = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin', 'telecaller']))->where('status', 'active')->get();
        $rooms = \App\Models\LeadRoom::latest()->get();
        return view('leads.create', compact('clients', 'users', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate(['client_name' => 'required|string|max:255', 'client_email' => 'nullable|email', 'requirement' => 'required|string']);
        $lead = Lead::create(array_merge($request->only(['client_id','lead_room_id','client_name','client_email','client_phone','location','business_type','source','requirement','estimated_budget','assigned_to','follow_up_date','notes']), ['created_by' => auth()->id(), 'status' => 'new']));
        return redirect()->route('leads.show', $lead)->with('success', 'Lead added!');
    }

    public function show(Lead $lead)
    {
        $this->authorizeLead($lead);
        $lead->load(['followUps.user', 'quotations', 'assignedTo', 'calls.telecaller', 'appointments.salesExecutive']);
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorizeLead($lead);
        $clients = Client::where('status', 'active')->get();
        $users = User::whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin', 'telecaller']))->get();
        $rooms = \App\Models\LeadRoom::latest()->get();
        return view('leads.edit', compact('lead', 'clients', 'users', 'rooms'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $lead->update($request->only(['client_id','lead_room_id','client_name','client_email','client_phone','location','business_type','source','requirement','estimated_budget','assigned_to','follow_up_date','status','notes']));
        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated!');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeLead($lead);
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function addFollowUp(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $request->validate([
            'note' => 'required|string',
            'follow_up_time' => 'nullable',
        ]);
        LeadFollowUp::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'next_follow_up' => $request->next_follow_up,
            'follow_up_time' => $request->follow_up_time,
            'status' => 'pending',
        ]);
        $lead->update(['follow_up_date' => $request->next_follow_up, 'status' => 'following_up']);
        return back()->with('success', 'Follow-up added!');
    }

    public function convert(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $lead->update(['status' => 'converted']);
        return redirect()->route('quotations.create', ['lead_id' => $lead->id])->with('success', 'Lead converted! Create a quotation now.');
    }

    public function updateRequirements(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);
        $request->validate([
            'service_required' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'preferred_date' => 'nullable|date',
            'company_details' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lead->update($request->only([
            'service_required',
            'estimated_budget',
            'preferred_date',
            'company_details',
            'notes',
        ]));

        return back()->with('success', 'Customer requirements updated!');
    }
}
