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
    public function create() 
    { 
        $user = auth()->user();
        $month = now()->month;
        $year = now()->year;

        $casualLeave = Leave::where('user_id', $user->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'casual_leave')
            ->where('status', '!=', 'rejected')
            ->first();

        $halfDayCount = Leave::where('user_id', $user->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'half_day')
            ->where('status', '!=', 'rejected')
            ->count();

        $sickLeave = Leave::where('user_id', $user->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'sick_leave')
            ->where('status', '!=', 'rejected')
            ->first();

        $casualDisabled = ($casualLeave !== null || $halfDayCount >= 2);
        $sickDisabled = ($sickLeave !== null);

        $casualDate = $casualLeave ? $casualLeave->from_date->format('d-m-Y') : null;
        $sickDate = $sickLeave ? $sickLeave->from_date->format('d-m-Y') : null;

        return view('leaves.create', compact('casualDisabled', 'sickDisabled', 'casualDate', 'sickDate')); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|string', 
            'half_day_session' => 'required_if:leave_type,half_day|nullable|string|in:morning,evening',
            'from_date' => 'required|date', 
            'to_date' => 'required|date|after_or_equal:from_date', 
            'reason' => 'required|string',
            'medical_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        $from = \Carbon\Carbon::parse($request->from_date);
        $to = \Carbon\Carbon::parse($request->to_date);
        $month = $from->month;
        $year = $from->year;
        $user = auth()->user();
        
        if ($request->leave_type === 'casual_leave') {
            $casualCount = Leave::where('user_id', $user->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'casual_leave')
                ->where('status', '!=', 'rejected')
                ->count();
            
            $halfDayCount = Leave::where('user_id', $user->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'half_day')
                ->where('status', '!=', 'rejected')
                ->count();

            if ($casualCount >= 1) {
                return back()->withInput()->withErrors(['leave_type' => 'You have already exhausted your casual leaves for this month (max 1 allowed).']);
            }
            if ($halfDayCount >= 2) {
                return back()->withInput()->withErrors(['leave_type' => 'Casual leave is disabled since you have taken 2 or more half day leaves this month.']);
            }
        }

        if ($request->leave_type === 'sick_leave') {
            $sickCount = Leave::where('user_id', $user->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'sick_leave')
                ->where('status', '!=', 'rejected')
                ->count();

            if ($sickCount >= 1) {
                return back()->withInput()->withErrors(['leave_type' => 'You have already exhausted your sick leaves for this month (max 1 allowed).']);
            }
        }

        if ($request->leave_type === 'half_day') {
            $to = $from;
            $days = 0.5;
        } elseif ($request->leave_type === 'casual_leave' || $request->leave_type === 'sick_leave') {
            $to = $from;
            $days = 1.0;
        } else {
            $days = $from->diffInDays($to) + 1;
        }

        // Check for date overlap with any non-rejected leaves
        $existingOverlap = Leave::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($from, $to) {
                $q->whereDate('from_date', '<=', $to->toDateString())
                  ->whereDate('to_date', '>=', $from->toDateString());
            })
            ->exists();

        if ($existingOverlap) {
            return back()->withInput()->withErrors(['from_date' => 'You have already requested leave for one or more dates in this range.']);
        }

        $attachments = null;
        if ($request->hasFile('medical_document') && $request->leave_type === 'sick_leave') {
            $path = $request->file('medical_document')->store('medical_documents', 'public');
            $attachments = [$path];
        }

        $leave = Leave::create([
            'leave_type' => $request->leave_type,
            'half_day_session' => $request->leave_type === 'half_day' ? $request->half_day_session : null,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'reason' => $request->reason,
            'user_id' => $user->id,
            'total_days' => $days,
            'status' => 'pending',
            'attachments' => $attachments
        ]);

        $typeLabel = str_replace('_', ' ', $leave->leave_type);
        if ($leave->leave_type === 'half_day' && $leave->half_day_session) {
            $typeLabel .= " ({$leave->half_day_session} shift)";
        }
        $fromStr = $leave->from_date->format('d-m-Y');
        $toStr = $leave->to_date->format('d-m-Y');
        $daysStr = $leave->total_days;
        
        $messageText = "Hello, I have submitted a leave request.\n"
            . "*Type:* " . ucwords($typeLabel) . "\n"
            . "*Duration:* {$fromStr} to {$toStr} ({$daysStr} days)\n"
            . "*Reason:* {$leave->reason}\n"
            . "You can view and manage it here: " . route('leaves.show', $leave);

        $receiverIds = collect();

        // 1. Team Leader
        if ($user->employee && $user->employee->team_leader_id) {
            $receiverIds->push($user->employee->team_leader_id);
        }

        // 2. HR Users
        $hrIds = \App\Models\User::whereHas('role', function($q) {
            $q->where('slug', 'hr');
        })->pluck('id');
        $receiverIds = $receiverIds->concat($hrIds);

        // 3. Admin / Super Admin Users
        $adminIds = \App\Models\User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'super-admin']);
        })->pluck('id');
        $receiverIds = $receiverIds->concat($adminIds);

        // Remove duplicates and exclude the sender themselves
        $receiverIds = $receiverIds->unique()->reject(fn($id) => $id == $user->id);

        $companyId = $user->company_id ?: (\App\Models\Company::first()?->id ?: \App\Models\Company::create(['name' => 'Default Company'])->id);

        foreach ($receiverIds as $receiverId) {
            \App\Models\DirectMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'message' => $messageText,
                'company_id' => $companyId,
            ]);
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted!');
    }

    public function show(Leave $leave) { $leave->load(['user', 'teamLeader', 'hr']); return view('leaves.show', compact('leave')); }

    public function edit(Leave $leave) 
    { 
        $user = auth()->user();
        $month = $leave->from_date->month;
        $year = $leave->from_date->year;

        $casualLeave = Leave::where('user_id', $user->id)
            ->where('id', '!=', $leave->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'casual_leave')
            ->where('status', '!=', 'rejected')
            ->first();

        $halfDayCount = Leave::where('user_id', $user->id)
            ->where('id', '!=', $leave->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'half_day')
            ->where('status', '!=', 'rejected')
            ->count();

        $sickLeave = Leave::where('user_id', $user->id)
            ->where('id', '!=', $leave->id)
            ->whereMonth('from_date', $month)->whereYear('from_date', $year)
            ->where('leave_type', 'sick_leave')
            ->where('status', '!=', 'rejected')
            ->first();

        $casualDisabled = ($casualLeave !== null || $halfDayCount >= 2);
        $sickDisabled = ($sickLeave !== null);

        $casualDate = $casualLeave ? $casualLeave->from_date->format('d-m-Y') : null;
        $sickDate = $sickLeave ? $sickLeave->from_date->format('d-m-Y') : null;

        return view('leaves.edit', compact('leave', 'casualDisabled', 'sickDisabled', 'casualDate', 'sickDate')); 
    }

    public function update(Request $request, Leave $leave) 
    {
        $request->validate([
            'leave_type' => 'required|string', 
            'half_day_session' => 'required_if:leave_type,half_day|nullable|string|in:morning,evening',
            'from_date' => 'required|date', 
            'to_date' => 'required|date|after_or_equal:from_date', 
            'reason' => 'required|string',
            'medical_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        $from = \Carbon\Carbon::parse($request->from_date);
        $to = \Carbon\Carbon::parse($request->to_date);
        $month = $from->month;
        $year = $from->year;
        $user = auth()->user();

        if ($request->leave_type === 'casual_leave') {
            $casualCount = Leave::where('user_id', $user->id)
                ->where('id', '!=', $leave->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'casual_leave')
                ->where('status', '!=', 'rejected')
                ->count();
            
            $halfDayCount = Leave::where('user_id', $user->id)
                ->where('id', '!=', $leave->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'half_day')
                ->where('status', '!=', 'rejected')
                ->count();

            if ($casualCount >= 1) {
                return back()->withInput()->withErrors(['leave_type' => 'You have already exhausted your casual leaves for this month (max 1 allowed).']);
            }
            if ($halfDayCount >= 2) {
                return back()->withInput()->withErrors(['leave_type' => 'Casual leave is disabled since you have taken 2 or more half day leaves this month.']);
            }
        }

        if ($request->leave_type === 'sick_leave') {
            $sickCount = Leave::where('user_id', $user->id)
                ->where('id', '!=', $leave->id)
                ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                ->where('leave_type', 'sick_leave')
                ->where('status', '!=', 'rejected')
                ->count();

            if ($sickCount >= 1) {
                return back()->withInput()->withErrors(['leave_type' => 'You have already exhausted your sick leaves for this month (max 1 allowed).']);
            }
        }
        
        if ($request->leave_type === 'half_day') {
            $to = $from;
            $days = 0.5;
        } elseif ($request->leave_type === 'casual_leave' || $request->leave_type === 'sick_leave') {
            $to = $from;
            $days = 1.0;
        } else {
            $days = $from->diffInDays($to) + 1;
        }

        // Check for date overlap with any non-rejected leaves (excluding the current leave)
        $existingOverlap = Leave::where('user_id', $user->id)
            ->where('id', '!=', $leave->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($from, $to) {
                $q->whereDate('from_date', '<=', $to->toDateString())
                  ->whereDate('to_date', '>=', $from->toDateString());
            })
            ->exists();

        if ($existingOverlap) {
            return back()->withInput()->withErrors(['from_date' => 'You have already requested leave for one or more dates in this range.']);
        }

        $attachments = $leave->attachments;
        if ($request->hasFile('medical_document') && $request->leave_type === 'sick_leave') {
            $path = $request->file('medical_document')->store('medical_documents', 'public');
            $attachments = [$path];
        } elseif ($request->leave_type !== 'sick_leave') {
            $attachments = null;
        }

        $leave->update([
            'leave_type' => $request->leave_type,
            'half_day_session' => $request->leave_type === 'half_day' ? $request->half_day_session : null,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'reason' => $request->reason,
            'total_days' => $days,
            'attachments' => $attachments
        ]);

        return back()->with('success', 'Leave request updated!');
    }
    public function destroy(Leave $leave)
    {
        if ($leave->user_id !== auth()->id() && !auth()->user()->isLeaderOrAbove() && !auth()->user()->isHR()) {
            abort(403);
        }

        // Delete any related attendance records for this leave
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        \App\Models\Attendance::where('user_id', $leave->user_id)
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->whereIn('status', ['on_leave', 'half_day'])
            ->delete();

        // Also delete any direct message leave notification threads sent for this leave request
        \App\Models\DirectMessage::where('message', 'like', "%leaves/{$leave->id}%")->delete();

        $leave->forceDelete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Leave request revoked and deleted.']);
        }

        return back()->with('success', 'Leave request revoked and deleted.');
    }
    public function approveTL(Request $request, Leave $leave)
    {
        $leave->update(['team_leader_status' => 'approved', 'team_leader_id' => auth()->id(), 'team_leader_comment' => $request->comment, 'team_leader_at' => now(), 'status' => 'team_leader_approved']);
        
        $typeLabel = str_replace('_', ' ', $leave->leave_type);
        if ($leave->leave_type === 'half_day' && $leave->half_day_session) {
            $typeLabel .= " ({$leave->half_day_session} shift)";
        }
        $fromStr = $leave->from_date->format('d-m-Y');
        $toStr = $leave->to_date->format('d-m-Y');
        
        $statusMessage = "Leave request for " . $leave->user->name . " has been approved at Team Leader level by " . auth()->user()->name . ".\n"
            . "*Type:* " . ucwords($typeLabel) . "\n"
            . "*Duration:* {$fromStr} to {$toStr}\n"
            . "*Comment:* " . ($request->comment ?? 'No comment') . "\n"
            . "View here: " . route('leaves.show', $leave);

        $this->sendLeaveStatusNotification($leave, $statusMessage, auth()->user());

        return back()->with('success', 'Leave approved at Team Leader level!');
    }
    public function approveHR(Request $request, Leave $leave)
    {
        $leave->update(['hr_status' => 'approved', 'hr_id' => auth()->id(), 'hr_comment' => $request->comment, 'hr_at' => now(), 'status' => 'approved']);

        // Log attendance as 'on_leave' or 'half_day' for the duration of the leave
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            \App\Models\Attendance::updateOrCreate(
                ['user_id' => $leave->user_id, 'date' => $date->toDateString()],
                [
                    'status' => $leave->leave_type === 'half_day' ? 'half_day' : 'on_leave',
                    'login_time' => null,
                    'logout_time' => null,
                    'total_minutes' => 0,
                    'late_minutes' => 0,
                    'notes' => ($leave->leave_type === 'half_day' ? 'Half Day Leave' : 'On Leave') . ': ' . str_replace('_', ' ', $leave->leave_type),
                ]
            );
        }

        $typeLabel = str_replace('_', ' ', $leave->leave_type);
        if ($leave->leave_type === 'half_day' && $leave->half_day_session) {
            $typeLabel .= " ({$leave->half_day_session} shift)";
        }
        $fromStr = $leave->from_date->format('d-m-Y');
        $toStr = $leave->to_date->format('d-m-Y');
        
        $statusMessage = "Leave request for " . $leave->user->name . " has been fully approved by " . auth()->user()->name . ".\n"
            . "*Type:* " . ucwords($typeLabel) . "\n"
            . "*Duration:* {$fromStr} to {$toStr}\n"
            . "*Comment:* " . ($request->comment ?? 'No comment') . "\n"
            . "View here: " . route('leaves.show', $leave);

        $this->sendLeaveStatusNotification($leave, $statusMessage, auth()->user());

        return back()->with('success', 'Leave fully approved!');
    }
    public function reject(Request $request, Leave $leave)
    {
        $request->validate(['reason' => 'required|string']);
        $leave->update(['status' => 'rejected', 'hr_id' => auth()->id(), 'hr_comment' => $request->reason, 'hr_at' => now()]);

        // Remove on_leave/half_day attendance records if they exist
        $startDate = \Carbon\Carbon::parse($leave->from_date);
        $endDate = \Carbon\Carbon::parse($leave->to_date);

        \App\Models\Attendance::where('user_id', $leave->user_id)
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->whereIn('status', ['on_leave', 'half_day'])
            ->delete();

        $typeLabel = str_replace('_', ' ', $leave->leave_type);
        if ($leave->leave_type === 'half_day' && $leave->half_day_session) {
            $typeLabel .= " ({$leave->half_day_session} shift)";
        }
        $fromStr = $leave->from_date->format('d-m-Y');
        $toStr = $leave->to_date->format('d-m-Y');
        
        $statusMessage = "Leave request for " . $leave->user->name . " has been rejected by " . auth()->user()->name . ".\n"
            . "*Type:* " . ucwords($typeLabel) . "\n"
            . "*Duration:* {$fromStr} to {$toStr}\n"
            . "*Reason:* " . $request->reason . "\n"
            . "View here: " . route('leaves.show', $leave);

        $this->sendLeaveStatusNotification($leave, $statusMessage, auth()->user());

        return back()->with('success', 'Leave rejected.');
    }

    protected function sendLeaveStatusNotification(Leave $leave, string $statusMessage, $actor)
    {
        $employee = $leave->user;
        $companyId = $actor->company_id ?: ($employee->company_id ?: (\App\Models\Company::first()?->id ?: \App\Models\Company::create(['name' => 'Default Company'])->id));

        // 1. Notify the employee themselves (sent from the approver)
        if ($employee->id != $actor->id) {
            \App\Models\DirectMessage::create([
                'sender_id' => $actor->id,
                'receiver_id' => $employee->id,
                'message' => $statusMessage,
                'company_id' => $companyId,
            ]);
        }

        // 2. Notify other managers (Team Leader, HR, Admin)
        // Send under the employee's ID so that the message stays inside the employee's chat thread for those managers,
        // and doesn't pollute the approver's chat list with manager-to-manager threads.
        $managerIds = collect();

        // Team Leader of the employee
        if ($employee->employee && $employee->employee->team_leader_id) {
            $managerIds->push($employee->employee->team_leader_id);
        }

        // All HR Users
        $hrIds = \App\Models\User::whereHas('role', function($q) {
            $q->where('slug', 'hr');
        })->pluck('id');
        $managerIds = $managerIds->concat($hrIds);

        // All Admin / Super Admin Users
        $adminIds = \App\Models\User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'super-admin']);
        })->pluck('id');
        $managerIds = $managerIds->concat($adminIds);

        // Remove duplicates, exclude the employee, and exclude the actor (since they approved/rejected it)
        $managerIds = $managerIds->unique()->reject(fn($id) => $id == $employee->id || $id == $actor->id);

        foreach ($managerIds as $managerId) {
            \App\Models\DirectMessage::create([
                'sender_id' => $employee->id,
                'receiver_id' => $managerId,
                'message' => $statusMessage,
                'company_id' => $companyId,
            ]);
        }
    }
}
