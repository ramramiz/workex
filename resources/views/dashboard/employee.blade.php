@extends('layouts.app')
@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Hi, {{ explode(' ', $user->name)[0] }}! 👋</h1>
        <p class="page-subtitle">{{ now()->format('l, d F Y') }}</p>
    </div>
    @if(!$todaySession || $todaySession->status !== 'active')
        <a href="{{ route('work-timer.index') }}" class="btn btn-success"><i class="bi bi-play-fill me-1"></i>Start My Day</a>
    @else
        <a href="{{ route('work-timer.index') }}" class="btn btn-outline-danger"><i class="bi bi-stop-fill me-1"></i>End Day</a>
    @endif
</div>

<!-- Work Timer Card -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;">
            <div style="font-size:13px;opacity:0.85;">Today's Work</div>
            <div style="font-size:32px;font-weight:800;margin:8px 0;" id="work-clock">
                {{ $todaySession ? $todaySession->total_hours : '00:00' }}
            </div>
            <div style="font-size:12px;opacity:0.75;">
                @if($todaySession && $todaySession->status === 'active')
                    <span class="status-dot working me-1"></span>Working since {{ $todaySession->started_at->format('h:i A') }}
                @else
                    Day not started
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:28px;font-weight:700;color:#f59e0b;">{{ $myTasks->count() }}</div>
            <div style="font-size:13px;color:#64748b;">Pending Tasks</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:28px;font-weight:700;color:#10b981;">{{ $completedToday }}</div>
            <div style="font-size:13px;color:#64748b;">Completed Today</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            @if(!$todayReport)
                <a href="{{ route('daily-reports.create') }}" class="text-decoration-none">
                    <div style="font-size:24px;color:#ef4444;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div style="font-size:13px;color:#ef4444;font-weight:600;">Submit Today's Report</div>
                </a>
            @else
                <div style="font-size:24px;color:#10b981;"><i class="bi bi-check-circle-fill"></i></div>
                <div style="font-size:13px;color:#10b981;font-weight:600;">Report Submitted</div>
                <div style="font-size:11px;color:#64748b;margin-top:4px;">Status: {{ ucfirst($todayReport->status) }}</div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- My Tasks -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>My Tasks</span>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Task</th><th>Project</th><th>Priority</th><th>Deadline</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                        @forelse($myTasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none fw-500" style="font-size:13px;">{{ Str::limit($task->title, 35) }}</a>
                            </td>
                            <td style="font-size:12px;color:#64748b;">{{ $task->project ? Str::limit($task->project->name, 20) : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $task->priority_badge }}-subtle text-{{ $task->priority_badge }}" style="font-size:10px;">{{ ucfirst($task->priority) }}</span>
                            </td>
                            <td style="font-size:12px;{{ $task->is_delayed ? 'color:#ef4444;' : 'color:#64748b;' }}">
                                {{ $task->deadline ? $task->deadline->format('d M') : 'N/A' }}
                                @if($task->is_delayed) <i class="bi bi-exclamation-circle ms-1"></i>@endif
                            </td>
                            <td>
                                <span class="badge" style="font-size:10px;background:#ede9fe;color:#6366f1;">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>
                            </td>
                            <td>
                                @if($task->timeLogs->isNotEmpty())
                                    <form action="{{ route('work-timer.pause-task', $task->timeLogs->first()) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-warning" style="font-size:11px;padding:3px 8px;"><i class="bi bi-pause-fill"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('work-timer.start-task', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" style="font-size:11px;padding:3px 8px;" {{ (!$todaySession || $todaySession->status !== 'active') ? 'disabled' : '' }}>
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No pending tasks 🎉</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Upcoming Deadlines (7 Days)</div>
            <div class="card-body p-0">
                @forelse($upcomingDeadlines as $task)
                <a href="{{ route('tasks.show', $task) }}" class="d-flex align-items-center gap-3 px-4 py-3 border-bottom text-decoration-none" style="color:inherit;">
                    <div style="min-width:44px;text-align:center;">
                        <div style="font-size:18px;font-weight:700;color:#0f172a;line-height:1;">{{ $task->deadline->format('d') }}</div>
                        <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;">{{ $task->deadline->format('M') }}</div>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:500;">{{ Str::limit($task->title, 30) }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $task->project->name ?? '—' }}</div>
                    </div>
                    <span class="badge bg-{{ $task->priority_badge }}-subtle text-{{ $task->priority_badge }}" style="font-size:10px;">{{ ucfirst($task->priority) }}</span>
                </a>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-check" style="font-size:32px;color:#10b981;"></i>
                    <div class="mt-2" style="font-size:13px;">No deadlines this week!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
