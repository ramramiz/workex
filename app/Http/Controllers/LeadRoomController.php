<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadRoom;
use App\Models\User;

class LeadRoomController extends Controller
{
    private function checkAccess()
    {
        if (auth()->user()->isTelecaller()) {
            abort(403, 'Unauthorized access to room management.');
        }
    }

    public function index()
    {
        $this->checkAccess();

        $rooms = LeadRoom::with(['creator', 'users'])->withCount([
            'leads',
            'leads as contacted_leads_count' => function ($query) {
                $query->whereHas('calls', function ($q) {
                    $q->where('status', 'Connected');
                });
            },
            'leads as interested_leads_count' => function ($query) {
                $query->where('status', 'interested');
            }
        ])->latest()->get();
        $telecallers = User::whereHas('role', fn($q) => $q->where('slug', 'telecaller'))
            ->where('status', 'active')
            ->get();

        return view('leads.rooms', compact('rooms', 'telecallers'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'name' => 'required|string|max:255|unique:lead_rooms,name',
            'description' => 'nullable|string',
        ]);

        LeadRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('lead-rooms.index')->with('success', 'Lead room created successfully!');
    }

    public function update(Request $request, LeadRoom $room)
    {
        $this->checkAccess();

        $request->validate([
            'name' => 'required|string|max:255|unique:lead_rooms,name,' . $room->id,
            'description' => 'nullable|string',
        ]);

        $room->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('lead-rooms.index')->with('success', 'Lead room updated successfully!');
    }

    public function destroy(LeadRoom $room)
    {
        $this->checkAccess();

        // Preserve leads: Null out lead_room_id on delete (Option A)
        $room->leads()->update(['lead_room_id' => null]);
        $room->delete();

        return redirect()->route('lead-rooms.index')->with('success', 'Lead room deleted. Leads inside have been preserved.');
    }

    public function assign(Request $request, LeadRoom $room)
    {
        $this->checkAccess();

        $request->validate([
            'telecaller_ids' => 'nullable|array',
            'telecaller_ids.*' => 'exists:users,id',
        ]);

        $room->users()->sync($request->input('telecaller_ids', []));

        return redirect()->route('lead-rooms.index')->with('success', 'Telecallers assigned to room successfully!');
    }
}
