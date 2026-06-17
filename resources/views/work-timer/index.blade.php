@extends('layouts.app')

@section('title', 'Work Timer')
@section('page-title', 'Work Timer')

@section('breadcrumb')
    <li class="breadcrumb-item active">Work Timer</li>
@endsection

@push('styles')
<style>
    /* Work Timer page specific dark overrides */
    .wt-card-header {
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 16px 20px;
        border-radius: 16px 16px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .wt-card-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .wt-task-info-box {
        background: var(--body-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 8px;
    }
    .wt-empty-icon-circle {
        width: 60px; height: 60px;
        background: var(--body-bg);
        border: 1px solid var(--border-color);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }
    .wt-ip-badge {
        background: var(--body-bg);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 1px 6px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
    }
    .wt-border-card {
        border: 1px solid var(--border-color) !important;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- Left Column: Work Session Controls -->
    <div class="col-12 col-md-5">
        <!-- Work Session Card -->
        <div class="card mb-4 wt-border-card">
            <div class="wt-card-header">
                <h5>Daily Shift Session</h5>
            </div>
            <div class="card-body text-center">
                @if(!$session || $session->status !== 'active')
                    <div class="py-4">
                        <i class="bi bi-clock-history text-muted" style="font-size: 48px;"></i>
                        <h4 class="mt-3">Shift Not Started</h4>
                        <p class="text-muted fs-7">Start your work day timer to record attendance and track tasks.</p>
                        
                        <form method="POST" action="{{ route('work-timer.start-day') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-4 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-play-circle-fill"></i> Start Work Day
                            </button>
                        </form>
                    </div>
                @else
                    <div class="py-3">
                        <span class="status-dot working mb-3"></span>
                        <h4 class="mt-1">Active Work Session</h4>
                        
                        <div class="text-muted fs-7 mb-4 mt-3">
                            Started Shift at: <strong>{{ $session->started_at->format('h:i A') }}</strong><br>
                            IP Recorded: <span class="wt-ip-badge">{{ $session->ip_address }}</span>
                        </div>

                        <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#endWorkDayModal">
                            <i class="bi bi-stop-circle-fill"></i> End Work Day
                        </button>

                        <!-- End Work Day Modal -->
                        <div class="modal fade" id="endWorkDayModal" tabindex="-1" aria-labelledby="endWorkDayModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content text-start">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="endWorkDayModalLabel">End Work Day & Record Progress</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('work-timer.end-day') }}">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="text-muted fs-7">Ending your day will also stop any active task timers. Please summarize the tasks/work you completed today:</p>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-semibold">Work Completed Today <span class="text-danger">*</span></label>
                                                <textarea name="work_done" class="form-control" rows="4" required placeholder="Describe what works you have completed today..." minlength="5" maxlength="2000"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">End Work Day & Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Task Time Log -->
        @if($session && $session->status === 'active')
            <div class="card wt-border-card">
                <div class="wt-card-header">
                    <h5>Active Task Timer</h5>
                </div>
                <div class="card-body">
                    @if($activeLog)
                        <div class="p-3 rounded-3 text-center mb-3" style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25);">
                            <span class="badge bg-primary text-white text-uppercase fs-8 font-monospace">Currently Tracking</span>
                            <h4 class="mt-2 mb-1 fw-bold" style="color: var(--text-primary);">{{ $activeLog->task->title }}</h4>
                            <p class="text-muted fs-7 mb-2">Project: {{ $activeLog->task->project->name ?? 'None' }}</p>
                            
                            <!-- Task Ticker -->
                            @php
                                $logStart = $activeLog->started_at->timestamp;
                                $logDiff = max(0, now()->timestamp - $logStart);
                            @endphp
                            <div class="fs-2 fw-bold text-primary font-monospace my-3" id="taskTimer" data-start="{{ $logStart }}">
                                {{ sprintf('%02d:%02d:%02d', ($logDiff/3600), ($logDiff/60)%60, $logDiff%60) }}
                            </div>

                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <form method="POST" action="{{ route('work-timer.pause-task', $activeLog) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-pause-fill"></i> Pause Timer
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#endTaskModal">
                                    <i class="bi bi-stop-fill"></i> End Timer
                                </button>
                            </div>
                        </div>

                        <!-- End Task Modal -->
                        <div class="modal fade" id="endTaskModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Record Task Notes</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('work-timer.end-task', $activeLog) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fs-7">Rework Notes / Work Done Details</label>
                                                <textarea name="note" class="form-control" rows="3" placeholder="What progress did you make during this log?"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Complete Time Log</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-stopwatch" style="font-size: 32px;"></i>
                            <div class="mt-2 fs-7">No task timer running. Start a task below to log progress.</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Running Timers, My Tasks List & Today's History -->
    <div class="col-12 col-md-7">
        <!-- Running Timers Card -->
        <div class="card mb-4 wt-border-card">
            <div class="wt-card-header">
                <h5 class="d-flex align-items-center gap-2">
                    <i class="bi bi-stopwatch text-primary"></i> Running Timers
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace">
                    {{ count($runningTimers) }} Active
                </span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($runningTimers as $log)
                        <div class="list-group-item p-3">
                            <div class="d-flex align-items-start gap-3">
                                <!-- User Avatar with Active Pulse Ring -->
                                <div class="position-relative flex-shrink-0">
                                    <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="avatar-circle rounded-circle border border-2 border-success-subtle" style="width: 42px; height: 42px; object-fit: cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 12px; height: 12px; transform: translate(2px, 2px); animation: pulse 2s infinite;"></span>
                                </div>
                                
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-truncate" style="color: var(--text-primary);">{{ $log->user->name }}</h6>
                                            <small class="text-muted fs-8">{{ $log->user->role?->name ?? 'Employee' }}</small>
                                        </div>
                                        <!-- Dynamic Ticking Counter Badge -->
                                        @php
                                            $logDiff = max(0, now()->timestamp - $log->started_at->timestamp);
                                        @endphp
                                        <span class="running-timer-ticker badge bg-success text-success bg-opacity-10 border border-success border-opacity-20 font-monospace fs-7 fw-semibold px-2 py-1" data-start="{{ $log->started_at->timestamp }}">
                                            {{ sprintf('%02d:%02d:%02d', ($logDiff/3600), ($logDiff/60)%60, $logDiff%60) }}
                                        </span>
                                    </div>
                                    
                                    <div class="wt-task-info-box">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="bi bi-clock-history text-primary fs-7"></i>
                                            <span class="fw-semibold text-primary fs-7 text-truncate d-inline-block" style="max-width: 100%;">{{ $log->task->title }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between text-muted fs-8 mt-1">
                                            <span>Proj: <strong class="text-secondary">{{ $log->task->project->name ?? 'No Project' }}</strong></span>
                                            <span>Started: <strong class="text-secondary">{{ $log->started_at->format('h:i A') }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 px-3 text-muted">
                            <div class="wt-empty-icon-circle">
                                <i class="bi bi-clock text-secondary" style="font-size: 24px;"></i>
                            </div>
                            <h6 class="fw-semibold mb-1" style="color: var(--text-primary);">No Active Timers</h6>
                            <p class="text-muted fs-7 mb-0">There are currently no active task timers running in the team.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($session && $session->status === 'active')
            <!-- My Assigned Tasks -->
            <div class="card mb-4 wt-border-card">
                <div class="wt-card-header">
                    <h5>My Active Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($myTasks as $task)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <div class="fw-semibold">{{ $task->title }}</div>
                                    <small class="text-muted">Proj: {{ $task->project->name ?? 'None' }} • Priority: <strong>{{ ucfirst($task->priority) }}</strong></small>
                                </div>
                                <div>
                                    @php
                                        $runningLog = $task->timeLogs->first();
                                    @endphp
                                    @if($runningLog)
                                        <span class="badge bg-primary">Tracking</span>
                                    @else
                                        <!-- Note popover or small input to start task -->
                                        <form method="POST" action="{{ route('work-timer.start-task', $task) }}" class="d-inline-flex gap-1 align-items-center">
                                            @csrf
                                            <input type="text" name="note" class="form-control form-control-xs py-1 fs-8" placeholder="Log Notes" style="max-width:110px;">
                                            <button type="submit" class="btn btn-outline-success btn-sm py-1 px-2 d-flex align-items-center gap-1" title="Start Task Timer">
                                                <i class="bi bi-play-fill"></i> Track
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted fs-7">No active tasks assigned to you.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Today's Log History -->
            <div class="card wt-border-card">
                <div class="wt-card-header">
                    <h5>Today's Time Logs</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0 fs-7">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Started At</th>
                                <th>Ended At</th>
                                <th>Duration</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayLogs as $log)
                                <tr>
                                    <td class="fw-medium">{{ $log->task->title }}</td>
                                    <td>{{ $log->started_at->format('h:i A') }}</td>
                                    <td>{{ $log->ended_at ? $log->ended_at->format('h:i A') : 'Running' }}</td>
                                    <td>
                                        @if($log->ended_at)
                                            {{ $log->total_minutes }} mins
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-muted fs-8">{{ Str::limit($log->note, 30) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No time logged today yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Javascript session clock ticker
    const sessionTimer = document.getElementById('sessionTimer');
    const taskTimer = document.getElementById('taskTimer');

    function pad(num) {
        return ("0" + num).slice(-2);
    }

    function tickTimer(element) {
        if (!element) return;
        const start = parseInt(element.getAttribute('data-start'));
        
        setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            const diff = Math.max(0, now - start);
            
            const hrs = Math.floor(diff / 3600);
            const mins = Math.floor((diff % 3600) / 60);
            const secs = Math.floor(diff % 60);
            
            element.textContent = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
        }, 1000);
    }

    // Update all running timer tickers dynamically
    function initRunningTimers() {
        const tickers = document.querySelectorAll('.running-timer-ticker');
        tickers.forEach(ticker => {
            const start = parseInt(ticker.getAttribute('data-start'));
            setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const diff = Math.max(0, now - start);
                
                const hrs = Math.floor(diff / 3600);
                const mins = Math.floor((diff % 3600) / 60);
                const secs = Math.floor(diff % 60);
                
                ticker.textContent = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
            }, 1000);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        tickTimer(sessionTimer);
        tickTimer(taskTimer);
        initRunningTimers();
    });
</script>
@endpush
