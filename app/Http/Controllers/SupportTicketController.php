<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tickets = SupportTicket::with(['client', 'project', 'assignedTo'])
            ->when($user->isClient(), fn($q) => $q->where('client_id', \App\Models\Client::where('email', $user->email)->first()?->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->latest()->paginate(20);
        return view('support.index', compact('tickets'));
    }
    public function create()
    {
        $clients  = Client::where('status', 'active')->get();
        $projects = Project::whereNotIn('status', ['cancelled'])->get();
        $users    = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->where('status','active')->get();
        return view('support.create', compact('clients', 'projects', 'users'));
    }
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255', 'priority' => 'required|string', 'description' => 'required|string']);
        $ticket = SupportTicket::create(array_merge($request->only(['client_id','project_id','title','description','priority','assigned_to','amc_start_date','amc_end_date']), [
            'created_by' => auth()->id(),
            'status' => 'open',
            'ticket_number' => 'TKT-' . str_pad(SupportTicket::count() + 1, 6, '0', STR_PAD_LEFT),
        ]));
        return redirect()->route('support.show', $ticket)->with('success', 'Support ticket created!');
    }
    public function show(SupportTicket $support) { $support->load(['client', 'project', 'assignedTo', 'replies.user']); return view('support.show', compact('support')); }
    public function edit(SupportTicket $support) { $clients = Client::all(); $projects = Project::all(); $users = User::whereHas('role', fn($q) => $q->whereIn('slug', ['employee','team-leader']))->get(); return view('support.edit', compact('support', 'clients', 'projects', 'users')); }
    public function update(Request $request, SupportTicket $support) { $support->update($request->only(['title','description','priority','status','assigned_to','amc_start_date','amc_end_date'])); return back()->with('success', 'Ticket updated!'); }
    public function destroy(SupportTicket $support) { $support->delete(); return redirect()->route('support.index')->with('success', 'Ticket deleted.'); }
    public function addReply(Request $request, SupportTicket $support)
    {
        $request->validate(['message' => 'required|string']);
        TicketReply::create(['support_ticket_id' => $support->id, 'user_id' => auth()->id(), 'message' => $request->message]);
        $support->update(['status' => 'in_progress']);
        return back()->with('success', 'Reply added!');
    }
    public function close(Request $request, SupportTicket $support)
    {
        $support->update(['status' => 'closed']);
        return back()->with('success', 'Ticket closed.');
    }
}
