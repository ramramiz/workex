<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadRoom;
use App\Models\Lead;
use App\Models\LeadRoomWorkSession;
use App\Models\LeadCall;
use Carbon\Carbon;

class LeadRoomWorkController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        // If they already have an active room work session, redirect them directly to it!
        if ($user->active_room_work_session_id) {
            $session = $user->activeRoomWorkSession;
            if ($session && $session->status === 'active') {
                return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                    ->with('info', 'Redirected to your active calling session.');
            }
        }

        $rooms = $user->rooms()->withCount('leads')->latest()->get();

        return view('leads.start-work.index', compact('rooms'));
    }

    public function room(LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$room->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to this room.');
        }

        // If they already have an active room session elsewhere, redirect them
        if ($user->active_room_work_session_id) {
            $session = $user->activeRoomWorkSession;
            if ($session && $session->status === 'active' && $session->lead_room_id != $room->id) {
                return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                    ->with('warning', 'You already have an active calling session in another room.');
            }
        }

        return view('leads.start-work.room', compact('room'));
    }

    public function startWork(Request $request, LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$room->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to this room.');
        }

        if ($user->active_room_work_session_id) {
            $session = $user->activeRoomWorkSession;
            if ($session && $session->status === 'active') {
                return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                    ->with('warning', 'You already have an active calling session.');
            }
        }

        // Create the session in database
        $session = LeadRoomWorkSession::create([
            'user_id' => $user->id,
            'lead_room_id' => $room->id,
            'started_at' => now(),
            'status' => 'active',
        ]);

        $user->update([
            'active_room_work_session_id' => $session->id,
        ]);

        session(['active_room_work' => [
            'room_id' => $room->id,
            'started_at' => $session->started_at->toISOString(),
            'status' => 'active',
            'accumulated_seconds' => 0,
        ]]);

        // Notify Admins on Session Start
        $admins = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('slug', ['super-admin', 'admin']))->get();
        foreach ($admins as $admin) {
            \App\Models\AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'work_session',
                'title' => 'Telecaller Work Started',
                'message' => $user->name . ' started room work in ' . $room->name,
                'url' => route('admin.telecaller-sessions.index'),
            ]);

            \App\Models\MailboxMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'subject' => 'Work Session Started: ' . $room->name . ' by ' . $user->name,
                'body' => $user->name . ' has started a Room Work session in ' . $room->name . ' at ' . now()->format('h:i A') . '.',
                'is_read' => false,
            ]);
        }
        // Create/find the room calling task and task time log
        $task = \App\Models\Task::firstOrCreate(
            [
                'title' => 'Room Calling: ' . $room->name,
                'assigned_to' => $user->id,
            ],
            [
                'project_id' => \App\Models\Project::first()?->id,
                'created_by' => \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'super-admin'))->first()?->id ?? $user->id,
                'description' => 'Telecaller calling work for room ' . $room->name,
                'status' => 'in_progress',
                'priority' => 'medium',
            ]
        );

        if ($task->status !== 'in_progress') {
            $task->update(['status' => 'in_progress']);
        }

        \App\Models\TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'status' => 'running',
            'note' => 'Started calling session in room: ' . $room->name,
        ]);

        return redirect()->route('leads.start-work.leads', $room)->with('success', 'Work session started!');
    }

    public function leads(Request $request, LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$room->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to this room.');
        }

        $session = $user->activeRoomWorkSession;

        if ($session && $session->lead_room_id == $room->id && !session()->has('active_room_work')) {
            session(['active_room_work' => [
                'room_id' => $room->id,
                'started_at' => $session->started_at ? $session->started_at->toISOString() : now()->toISOString(),
                'status' => $session->status,
                'accumulated_seconds' => $session->total_seconds,
            ]]);
        }

        $tab = $request->query('tab', 'uncalled');
        
        $uncalledCount = $room->leads()->whereDoesntHave('calls')->count();
        $notConnectedCount = $room->leads()->whereHas('latestCall', function($q) {
            $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
        })->count();
        $calledCount = $room->leads()->whereHas('latestCall', function($q) {
            $q->where('status', 'Connected');
        })->count();
        $interestedCount = $room->leads()->where('status', 'interested')->count();
        $todayFollowUpCount = $room->leads()->whereDate('follow_up_date', today())->count();

        if ($tab === 'called') {
            $leads = $room->leads()->whereHas('latestCall', function($q) {
                $q->where('status', 'Connected');
            })->latest()->paginate(15);
        } elseif ($tab === 'not_connected') {
            $leads = $room->leads()->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })->latest()->paginate(15);
        } elseif ($tab === 'interested') {
            $leads = $room->leads()->where('status', 'interested')->latest()->paginate(15);
        } elseif ($tab === 'today_follow_up') {
            $leads = $room->leads()->whereDate('follow_up_date', today())->latest()->paginate(15);
        } else {
            $leads = $room->leads()->whereDoesntHave('calls')->latest()->paginate(15);
        }

        return view('leads.start-work.leads', compact('room', 'leads', 'session', 'tab', 'uncalledCount', 'notConnectedCount', 'calledCount', 'interestedCount', 'todayFollowUpCount'));
    }

    public function pauseWork(Request $request, LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || $session->lead_room_id != $room->id || $session->status !== 'active') {
            return back()->with('error', 'No active session found to pause.');
        }

        // Calculate elapsed seconds since started_at (last resume/start)
        $elapsed = intval(abs(now()->diffInSeconds($session->started_at)));
        $newAccumulated = $session->total_seconds + $elapsed;

        $session->update([
            'status' => 'paused',
            'total_seconds' => $newAccumulated,
        ]);

        session(['active_room_work' => [
            'room_id' => $room->id,
            'status' => 'paused',
            'accumulated_seconds' => $newAccumulated,
        ]]);

        $task = \App\Models\Task::where('title', 'Room Calling: ' . $room->name)
            ->where('assigned_to', $user->id)
            ->first();
        if ($task) {
            $taskLog = \App\Models\TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->where('status', 'running')
                ->latest()
                ->first();
            if ($taskLog) {
                $elapsedMins = intval(abs(now()->diffInMinutes($taskLog->started_at)));
                $taskLog->update([
                    'paused_at' => now(),
                    'total_minutes' => $taskLog->total_minutes + $elapsedMins,
                    'status' => 'paused',
                    'note' => 'Paused calling session in room: ' . $room->name,
                ]);
            }
        }

        return back()->with('success', 'Work session paused.');
    }

    public function resumeWork(Request $request, LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || $session->lead_room_id != $room->id || $session->status !== 'paused') {
            return back()->with('error', 'No paused session found to resume.');
        }

        $session->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        session(['active_room_work' => [
            'room_id' => $room->id,
            'started_at' => $session->started_at->toISOString(),
            'status' => 'active',
            'accumulated_seconds' => $session->total_seconds,
        ]]);

        $task = \App\Models\Task::where('title', 'Room Calling: ' . $room->name)
            ->where('assigned_to', $user->id)
            ->first();
        if ($task) {
            $taskLog = \App\Models\TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->where('status', 'paused')
                ->latest()
                ->first();
            if ($taskLog) {
                $taskLog->update([
                    'resumed_at' => now(),
                    'started_at' => now(),
                    'status' => 'running',
                    'note' => 'Resumed calling session in room: ' . $room->name,
                ]);
            }
        }

        return back()->with('success', 'Work session resumed!');
    }

    public function stopWork()
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session) {
            return redirect()->route('leads.start-work.index')->with('error', 'No active session found.');
        }

        // Calculate final total seconds
        $finalSeconds = $session->total_seconds;
        if ($session->status === 'active') {
            $finalSeconds += intval(abs(now()->diffInSeconds($session->started_at)));
        }

        // Calculate metrics
        // We look at all calls logged by this user since the session was created
        $callsCount = LeadCall::where('telecaller_id', $user->id)
            ->whereBetween('created_at', [$session->created_at, now()])
            ->count();

        $leadIds = LeadCall::where('telecaller_id', $user->id)
            ->whereBetween('created_at', [$session->created_at, now()])
            ->pluck('lead_id');

        $convertedCount = Lead::whereIn('id', $leadIds)
            ->where('status', 'converted')
            ->count();

        // Query session's calls and interested leads for the PDF report
        $todayCalls = LeadCall::where('telecaller_id', $user->id)
            ->whereBetween('created_at', [$session->created_at, now()])
            ->with('lead')
            ->get();

        $interestedLeads = $session->room->leads()
            ->where('status', 'interested')
            ->whereHas('calls', function($q) use ($session) {
                $q->where('telecaller_id', $session->user_id)
                  ->whereBetween('created_at', [$session->created_at, now()]);
            })
            ->get();

        // Generate PDF report
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('leads.start-work.report_pdf', [
            'telecaller' => $user,
            'room' => $session->room,
            'todayCalls' => $todayCalls,
            'interestedLeads' => $interestedLeads,
            'startedAt' => $session->created_at,
            'endedAt' => now(),
            'totalSeconds' => $finalSeconds,
        ]);

        // Save PDF to public storage
        $fileName = 'Daily_Call_Report_' . $user->id . '_' . now()->format('YmdHis') . '.pdf';
        $pdfPath = 'reports/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($pdfPath, $pdf->output());

        // Finalize session
        $session->update([
            'ended_at' => now(),
            'total_seconds' => $finalSeconds,
            'calls_count' => $callsCount,
            'converted_count' => $convertedCount,
            'status' => 'pending',
        ]);

        // Free up the user
        $user->update([
            'active_room_work_session_id' => null,
        ]);

        session()->forget('active_room_work');

        $task = \App\Models\Task::where('title', 'Room Calling: ' . ($session->room->name ?? 'N/A'))
            ->where('assigned_to', $user->id)
            ->first();
        if ($task) {
            $taskLog = \App\Models\TaskTimeLog::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['running', 'paused'])
                ->latest()
                ->first();
            if ($taskLog) {
                $elapsedMins = 0;
                if ($taskLog->status === 'running') {
                    $elapsedMins = intval(abs(now()->diffInMinutes($taskLog->started_at)));
                }
                $taskLog->update([
                    'ended_at' => now(),
                    'total_minutes' => $taskLog->total_minutes + $elapsedMins,
                    'status' => 'ended',
                    'note' => 'Ended calling session in room: ' . ($session->room->name ?? 'N/A'),
                ]);
            }

            \App\Models\TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'comment' => "Ended calling session. Total calls logged: **{$callsCount}**, converted: **{$convertedCount}**.\n\n[Download Daily Call Report (PDF)](" . asset('storage/' . $pdfPath) . ")",
            ]);
        }

        // Notify Admins on Session Stop with attachment
        $admins = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('slug', ['super-admin', 'admin']))->get();
        foreach ($admins as $admin) {
            \App\Models\AppNotification::create([
                'user_id' => $admin->id,
                'type' => 'work_session',
                'title' => 'Telecaller Work Session Completed',
                'message' => $user->name . ' completed work session in ' . ($session->room->name ?? 'N/A') . ' (calls: ' . $callsCount . ')',
                'url' => route('admin.telecaller-sessions.index'),
            ]);

            \App\Models\MailboxMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'subject' => 'Daily Call Report & Session Ended: ' . ($session->room->name ?? 'N/A') . ' by ' . $user->name,
                'body' => $user->name . ' has ended the room work session in ' . ($session->room->name ?? 'N/A') . ' at ' . now()->format('h:i A') . '. Please find the daily called numbers and interested leads report PDF attached.',
                'attachment_path' => $pdfPath,
                'attachment_name' => $fileName,
                'is_read' => false,
            ]);
        }

        return redirect()->route('leads.start-work.summary', [$session->lead_room_id, $session->id])
            ->with('success', 'Work session completed and submitted for approval!');
    }

    public function summary(LeadRoom $room, LeadRoomWorkSession $session)
    {
        $user = auth()->user();
        if ($user->isTelecaller() && $session->user_id != $user->id) {
            abort(403, 'Unauthorized access to this summary.');
        }

        return view('leads.start-work.summary', compact('room', 'session'));
    }

    // Admin Session Review
    public function adminIndex()
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove()) {
            abort(403, 'Unauthorized access to approvals.');
        }

        $sessions = LeadRoomWorkSession::with(['user', 'room'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('leads.start-work.approvals', compact('sessions'));
    }

    public function adminApprove(LeadRoomWorkSession $session)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove()) {
            abort(403, 'Unauthorized.');
        }

        $session->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Work session approved successfully!');
    }

    public function adminReject(LeadRoomWorkSession $session)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove()) {
            abort(403, 'Unauthorized.');
        }

        $session->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Work session rejected.');
    }

    public function setCurrentCall(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string',
        ]);

        $user = auth()->user();
        \Illuminate\Support\Facades\Cache::put('user_current_call_' . $user->id, [
            'phone' => $request->phone,
            'name' => $request->name,
            'started_at' => now()->timestamp,
        ], 60);

        return response()->json(['success' => true]);
    }

    public function clearCurrentCall()
    {
        $user = auth()->user();
        \Illuminate\Support\Facades\Cache::forget('user_current_call_' . $user->id);
        return response()->json(['success' => true]);
    }
}
