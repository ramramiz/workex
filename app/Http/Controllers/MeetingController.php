<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;

class MeetingController extends Controller
{
    /**
     * Display a listing of meetings.
     */
    public function index(Request $request)
    {
        $meetings = Meeting::with(['creator'])
            ->withCount('tasks')
            ->orderBy('meeting_date', 'desc')
            ->paginate(15);

        return view('meetings.index', compact('meetings'));
    }

    /**
     * Show the form for creating a new meeting.
     */
    public function create()
    {
        return view('meetings.create', [
            'defaultLocation' => 'Kottakkal',
            'defaultDate' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created meeting in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $meeting = Meeting::create(array_merge($data, [
            'created_by' => auth()->id(),
        ]));

        \App\Models\ActivityLog::log('meeting_created', "Created meeting: {$meeting->title}", $meeting);

        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting created successfully! Add tasks to it below.');
    }

    /**
     * Display the specified meeting.
     */
    public function show(Meeting $meeting)
    {
        $meeting->load(['creator', 'tasks.assignee', 'tasks.creator']);
        return view('meetings.show', compact('meeting'));
    }

    /**
     * Show the form for editing the specified meeting.
     */
    public function edit(Meeting $meeting)
    {
        return view('meetings.edit', compact('meeting'));
    }

    /**
     * Update the specified meeting in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $meeting->update($data);

        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting updated successfully!');
    }

    /**
     * Remove the specified meeting from storage.
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Meeting deleted successfully.');
    }
}
