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
            if ($session && in_array($session->status, ['active', 'paused'])) {
                if ($session->lead_room_id) {
                    return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                        ->with('info', 'Redirected to your active calling session.');
                } else {
                    return redirect()->route('leads.start-work.select-room')
                        ->with('info', 'Please select a room to start calling.');
                }
            }
        }

        // Get unique clients corresponding to the rooms assigned to the telecaller
        $clients = \App\Models\Client::whereIn('id', function($q) use ($user) {
            $q->select('client_id')
              ->from('lead_rooms')
              ->whereIn('id', function($sq) use ($user) {
                  $sq->select('lead_room_id')
                    ->from('lead_room_user')
                    ->where('user_id', $user->id);
              });
        })->get();

        return view('leads.start-work.index', compact('clients'));
    }

    public function startWorkSession(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->active_room_work_session_id) {
            $session = $user->activeRoomWorkSession;
            if ($session && in_array($session->status, ['active', 'paused'])) {
                if ($session->lead_room_id) {
                    return redirect()->route('leads.start-work.leads', $session->lead_room_id)
                        ->with('warning', 'You already have an active calling session.');
                } else {
                    return redirect()->route('leads.start-work.select-room')
                        ->with('warning', 'Please select a room to start calling.');
                }
            }
        }

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Create the session in database (lead_room_id is null)
        $session = LeadRoomWorkSession::create([
            'user_id' => $user->id,
            'lead_room_id' => null,
            'started_at' => now(),
            'status' => 'active',
        ]);

        $user->update([
            'active_room_work_session_id' => $session->id,
        ]);

        // Store selected customer in session
        if ($request->client_id) {
            session(['selected_client_id' => $request->client_id]);
        }

        session(['active_room_work' => [
            'room_id' => null,
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
                'message' => $user->name . ' started day work session',
                'url' => route('admin.telecaller-sessions.index'),
            ]);

            \App\Models\MailboxMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $admin->id,
                'subject' => 'Work Session Started: Day Work by ' . $user->name,
                'body' => $user->name . ' has started a Day Work session at ' . now()->format('h:i A') . '.',
                'is_read' => false,
            ]);
        }

        // Get unique clients corresponding to the rooms assigned to the telecaller
        $clients = \App\Models\Client::whereIn('id', function($q) use ($user) {
            $q->select('client_id')
              ->from('lead_rooms')
              ->whereIn('id', function($sq) use ($user) {
                  $sq->select('lead_room_id')
                    ->from('lead_room_user')
                    ->where('user_id', $user->id);
              });
        })->get();

        if ($clients->isNotEmpty()) {
            return redirect()->route('leads.start-work.select-customer')->with('success', 'Work session started! Please select a customer.');
        }

        return redirect()->route('leads.start-work.select-room')->with('success', 'Work session started! Please select a room.');
    }

    public function selectCustomerForm()
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Get unique clients corresponding to the rooms assigned to the telecaller
        $clients = \App\Models\Client::whereIn('id', function($q) use ($user) {
            $q->select('client_id')
              ->from('lead_rooms')
              ->whereIn('id', function($sq) use ($user) {
                  $sq->select('lead_room_id')
                    ->from('lead_room_user')
                    ->where('user_id', $user->id);
              });
        })->get();

        $selectedClientId = session('selected_client_id')
            ?? ($clients->isNotEmpty() ? $clients->first()->id : null);

        return view('leads.start-work.select_customer', compact('clients', 'selectedClientId'));
    }

    public function updateCustomer(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        session(['selected_client_id' => $request->client_id]);

        return redirect()->route('leads.start-work.select-room')->with('success', 'Customer selected successfully!');
    }

    public function selectRoomList(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Get unique clients corresponding to the rooms assigned to the telecaller
        $clients = \App\Models\Client::whereIn('id', function($q) use ($user) {
            $q->select('client_id')
              ->from('lead_rooms')
              ->whereIn('id', function($sq) use ($user) {
                  $sq->select('lead_room_id')
                    ->from('lead_room_user')
                    ->where('user_id', $user->id);
              });
        })->get();

        $selectedClientId = $request->query('client_id');
        if ($selectedClientId) {
            session(['selected_client_id' => $selectedClientId]);
        } else {
            $selectedClientId = session('selected_client_id');
        }

        if (!$selectedClientId) {
            if ($clients->isNotEmpty()) {
                $selectedClientId = $clients->first()->id;
                session(['selected_client_id' => $selectedClientId]);
            } else {
                $selectedClientId = null;
            }
        }

        $roomsQuery = $user->rooms()->with('client')->withCount(['leads' => function($q) use ($user) {
            $q->where(function($sq) use ($user) {
                $sq->where('assigned_to', $user->id)
                  ->orWhereNull('assigned_to');
            });
        }]);

        if ($selectedClientId) {
            $roomsQuery->where('client_id', $selectedClientId);
        }
        $rooms = $roomsQuery->latest()->get();

        // Fetch today's follow-up leads assigned to the telecaller's rooms or direct
        $todayFollowUpsQuery = Lead::forUser($user)
            ->whereDate('follow_up_date', today())
            ->forClient($selectedClientId)
            ->with('room');
        $todayFollowUps = $todayFollowUpsQuery->get();
 
        // Fetch interested leads assigned to the telecaller's rooms or direct
        $interestedQuery = Lead::forUser($user)
            ->where('status', 'interested')
            ->forClient($selectedClientId);
        $interestedCount = $interestedQuery->count();

        // Fetch not connected leads (any time, matching room calling counts)
        $notConnectedLeadsQuery = Lead::forUser($user)
            ->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })
            ->forClient($selectedClientId);
        $notConnectedCalls = $notConnectedLeadsQuery->count();

        return view('leads.start-work.select_room', compact('rooms', 'todayFollowUps', 'interestedCount', 'notConnectedCalls', 'clients', 'selectedClientId'));
    }

    public function selectRoom(LeadRoom $room)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        if (!$room->users()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized access to this room.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Update session's lead_room_id
        $session->update([
            'lead_room_id' => $room->id,
        ]);

        // Update session state
        $activeRoomWork = session('active_room_work', []);
        $activeRoomWork['room_id'] = $room->id;
        session(['active_room_work' => $activeRoomWork]);

        // Handle the task logging:
        // 1. End any existing active TaskTimeLog for a room task
        $runningLogs = \App\Models\TaskTimeLog::where('user_id', $user->id)
            ->where('status', 'running')
            ->whereHas('task', function($q) {
                $q->where('title', 'like', 'Room Calling:%');
            })
            ->get();
        
        foreach ($runningLogs as $log) {
            $elapsedMins = intval(abs(now()->diffInMinutes($log->started_at)));
            $log->update([
                'ended_at' => now(),
                'total_minutes' => $log->total_minutes + $elapsedMins,
                'status' => 'ended',
                'note' => 'Switched to calling room: ' . $room->name,
            ]);
        }

        // 2. Create/find the room calling task for the new room, and start a new TaskTimeLog
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
            'note' => 'Started/Switched calling session in room: ' . $room->name,
        ]);

        return redirect()->route('leads.start-work.leads', $room)
            ->with('success', 'Room ' . $room->name . ' selected!');
    }

    public function selectFollowupRoom(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Update session's lead_room_id
        $session->update([
            'lead_room_id' => null,
        ]);

        // Update session state
        $activeRoomWork = session('active_room_work', []);
        $activeRoomWork['room_id'] = 'followups';
        session(['active_room_work' => $activeRoomWork]);

        // Handle the task logging:
        // 1. End any existing active TaskTimeLog for a room task
        $runningLogs = \App\Models\TaskTimeLog::where('user_id', $user->id)
            ->where('status', 'running')
            ->whereHas('task', function($q) {
                $q->where('title', 'like', 'Room Calling:%');
            })
            ->get();
        
        foreach ($runningLogs as $log) {
            $elapsedMins = intval(abs(now()->diffInMinutes($log->started_at)));
            $log->update([
                'ended_at' => now(),
                'total_minutes' => $log->total_minutes + $elapsedMins,
                'status' => 'ended',
                'note' => 'Switched to calling room: Today Follow-ups',
            ]);
        }

        // 2. Create/find the room calling task for the followups, and start a new TaskTimeLog
        $task = \App\Models\Task::firstOrCreate(
            [
                'title' => 'Room Calling: Today Follow-ups',
                'assigned_to' => $user->id,
            ],
            [
                'project_id' => \App\Models\Project::first()?->id,
                'created_by' => \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'super-admin'))->first()?->id ?? $user->id,
                'description' => 'Telecaller calling work for Today Follow-ups',
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
            'note' => 'Started/Switched calling session in room: Today Follow-ups',
        ]);

        return redirect()->route('leads.start-work.followup-leads', ['client_id' => $request->query('client_id')])
            ->with('success', 'Joined Today\'s Follow-ups Room!');
    }

    public function followupLeads(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Automatically align room if session points to a different room
        $activeRoomWork = session('active_room_work', []);
        if (($activeRoomWork['room_id'] ?? null) !== 'followups') {
            $session->update([
                'lead_room_id' => null,
            ]);

            $activeRoomWork['room_id'] = 'followups';
            $activeRoomWork['started_at'] = $session->started_at ? $session->started_at->toISOString() : now()->toISOString();
            $activeRoomWork['status'] = $session->status;
            $activeRoomWork['accumulated_seconds'] = $session->total_seconds;
            session(['active_room_work' => $activeRoomWork]);

            // Handle the task logging:
            // End active TaskTimeLogs for previous room calling tasks
            $runningLogs = \App\Models\TaskTimeLog::where('user_id', $user->id)
                ->where('status', 'running')
                ->whereHas('task', function($q) {
                    $q->where('title', 'like', 'Room Calling:%');
                })
                ->get();
            
            foreach ($runningLogs as $log) {
                $elapsedMins = intval(abs(now()->diffInMinutes($log->started_at)));
                $log->update([
                    'ended_at' => now(),
                    'total_minutes' => $log->total_minutes + $elapsedMins,
                    'status' => 'ended',
                    'note' => 'Switched to calling room: Today Follow-ups',
                ]);
            }

            // Create/find new task log
            $task = \App\Models\Task::firstOrCreate(
                [
                    'title' => 'Room Calling: Today Follow-ups',
                    'assigned_to' => $user->id,
                ],
                [
                    'project_id' => \App\Models\Project::first()?->id,
                    'created_by' => \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'super-admin'))->first()?->id ?? $user->id,
                    'description' => 'Telecaller calling work for Today Follow-ups',
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
                'note' => 'Started/Switched calling session in room: Today Follow-ups',
            ]);
        }

        if (!session()->has('active_room_work') || session('active_room_work')['room_id'] !== 'followups') {
            session(['active_room_work' => [
                'room_id' => 'followups',
                'started_at' => $session->started_at ? $session->started_at->toISOString() : now()->toISOString(),
                'status' => $session->status,
                'accumulated_seconds' => $session->total_seconds,
            ]]);
        }

        $selectedClientId = $request->query('client_id');

        $leadsQuery = Lead::forUser($user)
            ->whereDate('follow_up_date', today())
            ->with('room');
            
        $leadsQuery->forClient($selectedClientId)->latest();

        $leads = $leadsQuery->paginate(15);
        $totalFollowUps = $leadsQuery->count();

        // Fetch interested leads (across all rooms/direct since follow-ups is a global view)
        $interestedQuery = Lead::forUser($user)
            ->where('status', 'interested')
            ->forClient($selectedClientId);
        $interestedCount = $interestedQuery->count();

        // Fetch today's not connected calls (across all rooms/direct since follow-ups is a global view)
        // Fetch not connected leads logged by this telecaller (any time, matching room calling counts)
        $notConnectedLeadsQuery = Lead::forUser($user)
            ->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })
            ->forClient($selectedClientId);
        $notConnectedCalls = $notConnectedLeadsQuery->count();

        return view('leads.start-work.followups', compact('leads', 'session', 'totalFollowUps', 'interestedCount', 'notConnectedCalls', 'selectedClientId'));
    }

    public function pauseFollowupWork(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || $session->lead_room_id !== null || $session->status !== 'active') {
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
            'room_id' => 'followups',
            'status' => 'paused',
            'accumulated_seconds' => $newAccumulated,
        ]]);

        $task = \App\Models\Task::where('title', 'Room Calling: Today Follow-ups')
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
                    'note' => 'Paused calling session in room: Today Follow-ups',
                ]);
            }
        }

        return back()->with('success', 'Work session paused.');
    }

    public function resumeFollowupWork(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || $session->lead_room_id !== null || $session->status !== 'paused') {
            return back()->with('error', 'No paused session found to resume.');
        }

        $session->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        session(['active_room_work' => [
            'room_id' => 'followups',
            'started_at' => $session->started_at->toISOString(),
            'status' => 'active',
            'accumulated_seconds' => $session->total_seconds,
        ]]);

        $task = \App\Models\Task::where('title', 'Room Calling: Today Follow-ups')
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
                    'note' => 'Resumed calling session in room: Today Follow-ups',
                ]);
            }
        }

        return back()->with('success', 'Work session resumed!');
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
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        // Automatically align room if session points to a different room
        if ($session->lead_room_id != $room->id) {
            $session->update([
                'lead_room_id' => $room->id,
            ]);

            // Handle the task logging:
            // End active TaskTimeLogs for previous room calling tasks
            $runningLogs = \App\Models\TaskTimeLog::where('user_id', $user->id)
                ->where('status', 'running')
                ->whereHas('task', function($q) {
                    $q->where('title', 'like', 'Room Calling:%');
                })
                ->get();
            
            foreach ($runningLogs as $log) {
                $elapsedMins = intval(abs(now()->diffInMinutes($log->started_at)));
                $log->update([
                    'ended_at' => now(),
                    'total_minutes' => $log->total_minutes + $elapsedMins,
                    'status' => 'ended',
                    'note' => 'Switched to calling room: ' . $room->name,
                ]);
            }

            // Create/find new task log
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
                'note' => 'Started/Switched calling session in room: ' . $room->name,
            ]);
        }

        if (!session()->has('active_room_work') || session('active_room_work')['room_id'] != $room->id) {
            session(['active_room_work' => [
                'room_id' => $room->id,
                'started_at' => $session->started_at ? $session->started_at->toISOString() : now()->toISOString(),
                'status' => $session->status,
                'accumulated_seconds' => $session->total_seconds,
            ]]);
        }

        $tab = $request->query('tab', 'uncalled');
        
        $leadsBase = $room->leads()->forUser($user);

        $uncalledCount = (clone $leadsBase)->whereDoesntHave('calls')->count();
        $todayFollowUpCount = (clone $leadsBase)->whereDate('follow_up_date', today())->count();
        $interestedCount = (clone $leadsBase)->where('status', 'interested')->count();
        $notConnectedCount = (clone $leadsBase)->whereHas('latestCall', function($q) {
            $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
        })->count();
        $allContactsCount = (clone $leadsBase)->count();

        if ($tab === 'interested') {
            $leads = (clone $leadsBase)->where('status', 'interested')->latest()->paginate(15);
        } elseif ($tab === 'not_connected') {
            $leads = (clone $leadsBase)->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })->latest()->paginate(15);
        } elseif ($tab === 'all_contacts') {
            $leads = (clone $leadsBase)->latest()->paginate(15);
        } elseif ($tab === 'today_follow_up') {
            $leads = (clone $leadsBase)->whereDate('follow_up_date', today())->latest()->paginate(15);
        } else {
            $tab = 'uncalled';
            $leads = (clone $leadsBase)->whereDoesntHave('calls')->latest()->paginate(15);
        }

        // Fetch not connected calls logged by this telecaller today in this room
        $notConnectedCalls = LeadCall::where('telecaller_id', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['Not Connected', 'Busy', 'Switched Off'])
            ->whereHas('lead', function($q) use ($room) {
                $q->where('lead_room_id', $room->id);
            })
            ->count();

        return view('leads.start-work.leads', compact(
            'room', 
            'leads', 
            'session', 
            'tab', 
            'uncalledCount',
            'todayFollowUpCount', 
            'interestedCount', 
            'notConnectedCount', 
            'allContactsCount', 
            'notConnectedCalls'
        ));
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

        // Get interested leads called during this session timeframe (across all rooms)
        $interestedLeads = Lead::whereIn('id', $leadIds)
            ->where('status', 'interested')
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

        return redirect()->route('leads.start-work.summary', [$session->lead_room_id ?: 0, $session->id])
            ->with('success', 'Work session completed and submitted for approval!');
    }

    public function summary($room, LeadRoomWorkSession $session)
    {
        $user = auth()->user();
        if ($user->isTelecaller() && $session->user_id != $user->id) {
            abort(403, 'Unauthorized access to this summary.');
        }

        $roomModel = null;
        if ($room && $room != '0' && $room != 0) {
            $roomModel = \App\Models\LeadRoom::find($room);
        }
        $room = $roomModel;

        // Get calls from session timeframe
        $startTime = $session->created_at;
        $endTime = $session->ended_at ?? now();

        $todayCalls = LeadCall::where('telecaller_id', $session->user_id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->get();

        $totalCalls = $todayCalls->count();
        $connectedCalls = $todayCalls->where('status', 'Connected')->count();
        
        $notConnectedStatuses = ['Not Connected', 'Busy', 'Switched Off'];
        $notConnectedCalls = $todayCalls->whereIn('status', $notConnectedStatuses)->count();

        $leadIds = $todayCalls->pluck('lead_id')->unique();
        $interestedCount = Lead::whereIn('id', $leadIds)
            ->where('status', 'interested')
            ->count();

        return view('leads.start-work.summary', compact(
            'room', 
            'session', 
            'totalCalls', 
            'connectedCalls', 
            'notConnectedCalls', 
            'interestedCount'
        ));
    }

    public function downloadReport(LeadRoomWorkSession $session)
    {
        $user = auth()->user();
        if (!$user->isAdminOrAbove()) {
            abort(403, 'Unauthorized access to this report.');
        }

        $session->load(['user', 'room']);
        
        $startTime = $session->created_at;
        $endTime = $session->ended_at ?? now();

        $todayCalls = LeadCall::where('telecaller_id', $session->user_id)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->with('lead')
            ->get();

        $leadIds = $todayCalls->pluck('lead_id')->unique();
        $interestedLeads = Lead::whereIn('id', $leadIds)
            ->where('status', 'interested')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('leads.start-work.report_pdf', [
            'telecaller' => $session->user,
            'room' => $session->room,
            'todayCalls' => $todayCalls,
            'interestedLeads' => $interestedLeads,
            'startedAt' => $startTime,
            'endedAt' => $endTime,
            'totalSeconds' => $session->total_seconds,
        ]);

        $fileName = 'Work_Session_Report_' . $session->user->name . '_' . $session->created_at->format('Ymd_His') . '.pdf';
        
        return $pdf->download($fileName);
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

    public function interestedLeads(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        $selectedClientId = $request->query('client_id');

        $leadsQuery = Lead::forUser($user)
            ->where('status', 'interested')
            ->forClient($selectedClientId)
            ->with('room');
        $leadsQuery->latest();

        $leads = $leadsQuery->paginate(15);
        $totalLeads = $leadsQuery->count();

        // Pass the other counts for the top cards
        $totalFollowUpsQuery = Lead::forUser($user)
            ->whereDate('follow_up_date', today())
            ->forClient($selectedClientId);
        $totalFollowUps = $totalFollowUpsQuery->count();
            
        $interestedCount = $totalLeads;

        // Fetch not connected leads (any time, matching room calling counts)
        $notConnectedLeadsQuery = Lead::forUser($user)
            ->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })
            ->forClient($selectedClientId);
        $notConnectedCalls = $notConnectedLeadsQuery->count();

        return view('leads.start-work.interested_leads', compact('leads', 'session', 'totalLeads', 'totalFollowUps', 'interestedCount', 'notConnectedCalls', 'selectedClientId'));
    }

    public function notConnectedLeads(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        $selectedClientId = $request->query('client_id');

        // Get leads where their latest call was not connected
        $leadsQuery = Lead::forUser($user)
            ->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })
            ->forClient($selectedClientId);
        $leadsQuery->with('room')->latest();

        $leads = $leadsQuery->paginate(15);
        $totalLeads = $leadsQuery->count();

        // Pass the other counts for the top cards
        $totalFollowUpsQuery = Lead::forUser($user)
            ->whereDate('follow_up_date', today())
            ->forClient($selectedClientId);
        $totalFollowUps = $totalFollowUpsQuery->count();
            
        $interestedQuery = Lead::forUser($user)
            ->where('status', 'interested')
            ->forClient($selectedClientId);
        $interestedCount = $interestedQuery->count();

        // Fetch not connected leads (any time, matching room calling counts)
        $notConnectedLeadsQuery = Lead::forUser($user)
            ->whereHas('latestCall', function($q) {
                $q->whereIn('status', ['Not Connected', 'Busy', 'Switched Off']);
            })
            ->forClient($selectedClientId);
        $notConnectedCalls = $notConnectedLeadsQuery->count();

        return view('leads.start-work.not_connected_leads', compact('leads', 'session', 'totalLeads', 'totalFollowUps', 'interestedCount', 'notConnectedCalls', 'selectedClientId'));
    }

    public function exportInterestedLeads(Request $request)
    {
        $user = auth()->user();
        if (!$user->isTelecaller()) {
            abort(403, 'Unauthorized access.');
        }

        $session = $user->activeRoomWorkSession;
        if (!$session || !in_array($session->status, ['active', 'paused'])) {
            return redirect()->route('leads.start-work.index')
                ->with('error', 'Please start your day work session first.');
        }

        $leads = Lead::forUser($user)
            ->where('status', 'interested')
            ->latest()
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Phone Number');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');

        $rowNumber = 2;
        foreach ($leads as $lead) {
            $phone = $lead->client_phone;
            $cleanPhone = preg_replace('/\D/', '', $phone);
            if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
                $cleanPhone = substr($cleanPhone, 2);
            }

            // Set cell values as string to preserve formatting/leading zeros
            $sheet->setCellValueExplicit('A' . $rowNumber, $cleanPhone, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rowNumber, $lead->client_name);
            $sheet->setCellValue('C' . $rowNumber, $lead->client_email);

            $rowNumber++;
        }

        return new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="Interested_Leads_' . now()->format('Ymd_His') . '.xls"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
