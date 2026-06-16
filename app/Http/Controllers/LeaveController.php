<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $leaves = Leave::with(['user', 'teamLeader', 'hr'])
            ->when(!$user->isLeaderOrAbove() && !$user->isHR(), fn($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return view('leaves.index', compact('leaves'));
    }
    public function create() { return view('leaves.create'); }
    public function store(Request $request)
    {
        $request->validate(['leave_type' => 'required|string', 'from_date' => 'required|date', 'to_date' => 'required|date|after_or_equal:from_date', 'reason' => 'required|string']);
        $days = \Carbon\Carbon::parse($request->from_date)->diffInDays(\Carbon\Carbon::parse($request->to_date)) + 1;
        Leave::create(array_merge($request->only(['leave_type','from_date','to_date','reason']), ['user_id' => auth()->id(), 'total_days' => $days, 'status' => 'pending']));
        return redirect()->route('leaves.index')->with('success', 'Leave request submitted!');
    }
    public function show(Leave $leave) { $leave->load(['user', 'teamLeader', 'hr']); return view('leaves.show', compact('leave')); }
    public function edit(Leave $leave) { return view('leaves.edit', compact('leave')); }
    public function update(Request $request, Leave $leave) { $leave->update($request->only(['leave_type','from_date','to_date','reason'])); return back()->with('success', 'Leave request updated!'); }
    public function destroy(Leave $leave)
    {
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        \App\Models\Attendance::where('user_id', $leave->user_id)
            ->whereBetween('date', [
                $startDate->startOfDay()->toDateTimeString(),
                $endDate->endOfDay()->toDateTimeString()
            ])
            ->where('status', 'on_leave')
            ->delete();

        $leave->delete();
        return redirect()->route('leaves.index')->with('success', 'Leave request cancelled.');
    }
    public function approveTL(Request $request, Leave $leave)
    {
        $leave->update(['team_leader_status' => 'approved', 'team_leader_id' => auth()->id(), 'team_leader_comment' => $request->comment, 'team_leader_at' => now(), 'status' => 'team_leader_approved']);
        return back()->with('success', 'Leave approved at Team Leader level!');
    }
    public function approveHR(Request $request, Leave $leave)
    {
        $leave->update(['hr_status' => 'approved', 'hr_id' => auth()->id(), 'hr_comment' => $request->comment, 'hr_at' => now(), 'status' => 'approved']);

        // Log attendance as 'on_leave' for the duration of the leave
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            \App\Models\Attendance::updateOrCreate(
                ['user_id' => $leave->user_id, 'date' => $date->toDateString()],
                [
                    'status' => 'on_leave',
                    'login_time' => null,
                    'logout_time' => null,
                    'total_minutes' => 0,
                    'late_minutes' => 0,
                    'notes' => 'On Leave: ' . str_replace('_', ' ', $leave->leave_type),
                ]
            );
        }

        return back()->with('success', 'Leave fully approved!');
    }
    public function reject(Request $request, Leave $leave)
    {
        $request->validate(['reason' => 'required|string']);
        $leave->update(['status' => 'rejected', 'hr_id' => auth()->id(), 'hr_comment' => $request->reason, 'hr_at' => now()]);

        // Remove on_leave attendance records if they exist
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        \App\Models\Attendance::where('user_id', $leave->user_id)
            ->whereBetween('date', [
                $startDate->startOfDay()->toDateTimeString(),
                $endDate->endOfDay()->toDateTimeString()
            ])
            ->where('status', 'on_leave')
            ->delete();

        return back()->with('success', 'Leave rejected.');
    }
}
