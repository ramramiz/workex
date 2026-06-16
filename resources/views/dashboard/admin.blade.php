@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', $user->name)[0] }}! 👋
        </h1>
        <p class="page-subtitle">Here's what's happening today — {{ now()->format('l, d F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#currentWorksUpdateModal">
            <i class="bi bi-broadcast"></i> Current Works Update
        </button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i> Export Report
        </a>
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Project
        </a>
    </div>
</div>

<!-- KPI Stats Row 1 -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#ede9fe;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-people-fill" style="font-size:22px;color:#6366f1;"></i>
                </div>
                <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:11px;">Active</span>
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $stats['total_employees'] }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Total Employees</div>
            <div style="font-size:12px;color:#10b981;margin-top:8px;font-weight:500;">
                <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>{{ $stats['working_today'] }} working today
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-kanban-fill" style="font-size:22px;color:#2563eb;"></i>
                </div>
                @if($stats['delayed_projects'] > 0)
                    <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size:11px;">{{ $stats['delayed_projects'] }} delayed</span>
                @else
                    <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:11px;">On track</span>
                @endif
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $stats['total_projects'] }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Total Projects</div>
            <div style="font-size:12px;color:#2563eb;margin-top:8px;font-weight:500;">
                <i class="bi bi-arrow-up me-1"></i>{{ $stats['active_projects'] }} active
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-check2-square" style="font-size:22px;color:#16a34a;"></i>
                </div>
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $stats['pending_tasks'] }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Pending Tasks</div>
            <div style="font-size:12px;color:#16a34a;margin-top:8px;font-weight:500;">
                <i class="bi bi-check2 me-1"></i>{{ $stats['completed_tasks'] }} completed today
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#fef9c3;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-receipt" style="font-size:22px;color:#ca8a04;"></i>
                </div>
                @if($stats['pending_invoices'] > 0)
                    <span class="badge bg-warning-subtle text-warning rounded-pill" style="font-size:11px;">{{ $stats['pending_invoices'] }} pending</span>
                @endif
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $stats['pending_invoices'] }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Pending Invoices</div>
            <div style="font-size:12px;color:#ca8a04;margin-top:8px;font-weight:500;">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $stats['open_bugs'] }} open bugs
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Pending Alerts -->
@if($stats['pending_leaves'] > 0 || $stats['pending_reports'] > 0 || $stats['delayed_projects'] > 0)
<div class="row g-3 mb-4">
    @if($stats['delayed_projects'] > 0)
    <div class="col-md-4">
        <a href="{{ route('projects.index', ['filter' => 'delayed']) }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3" style="border-left:4px solid #ef4444;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:20px;color:#ef4444;"></i>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#0f172a;">{{ $stats['delayed_projects'] }}</div>
                    <div style="font-size:13px;color:#ef4444;font-weight:500;">Delayed Projects</div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($stats['pending_leaves'] > 0)
    <div class="col-md-4">
        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3" style="border-left:4px solid #f59e0b;">
                <div style="width:44px;height:44px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-calendar-x-fill" style="font-size:20px;color:#f59e0b;"></i>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#0f172a;">{{ $stats['pending_leaves'] }}</div>
                    <div style="font-size:13px;color:#f59e0b;font-weight:500;">Pending Leave Requests</div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($stats['pending_reports'] > 0)
    <div class="col-md-4">
        <a href="{{ route('daily-reports.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="stat-card d-flex align-items-center gap-3" style="border-left:4px solid #6366f1;">
                <div style="width:44px;height:44px;border-radius:10px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-journal-text" style="font-size:20px;color:#6366f1;"></i>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#0f172a;">{{ $stats['pending_reports'] }}</div>
                    <div style="font-size:13px;color:#6366f1;font-weight:500;">Pending Daily Reports</div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
@endif

<!-- Main content: 2 columns -->
<div class="row g-4">
    <!-- Left: Live Work Status -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="status-dot working"></span>
                    <span>Live Work Status</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#currentWorksUpdateModal">
                        <i class="bi bi-broadcast"></i> Current Works Update
                    </button>
                    <a href="{{ route('live-status') }}" class="btn btn-sm btn-outline-primary">View Full Board</a>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($activeEmployees as $session)
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                    <img src="{{ $session->user->avatar_url }}" class="rounded-circle" width="38" height="38" alt="">
                    <div class="flex-grow-1">
                        <div style="font-size:14px;font-weight:600;">{{ $session->user->name }}</div>
                        <div style="font-size:12px;color:#64748b;">
                            @if($session->activeTaskLog)
                                @php
                                    $taskDiff = max(0, now()->timestamp - $session->activeTaskLog->started_at->timestamp);
                                @endphp
                                <i class="bi bi-play-circle-fill text-success me-1"></i>
                                {{ Str::limit($session->activeTaskLog->task->title ?? 'Working...', 40) }}
                                <span class="badge bg-success-subtle text-success ms-1" style="font-size: 10px;">
                                    Task: <span class="running-timer-ticker" data-start="{{ $session->activeTaskLog->started_at->timestamp }}">{{ sprintf('%02d:%02d:%02d', ($taskDiff/3600), ($taskDiff/60)%60, $taskDiff%60) }}</span>
                                </span>
                            @else
                                <i class="bi bi-clock text-warning me-1"></i> Active (No task running)
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:13px;font-weight:600;color:#10b981;">{{ $session->total_hours }}</div>
                        <div style="font-size:11px;color:#94a3b8;">worked</div>
                    </div>
                    <span class="badge rounded-pill" style="background:#d1fae5;color:#065f46;font-size:11px;">Working</span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people" style="font-size:40px;"></i>
                    <div class="mt-2">No employees working right now</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right: Recent Projects + Pending Reports -->
    <div class="col-lg-5">
        <!-- Recent Projects -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Recent Projects</span>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentProjects->take(5) as $project)
                <a href="{{ route('projects.show', $project) }}" class="d-flex align-items-center gap-3 px-4 py-3 border-bottom text-decoration-none" style="color:inherit;">
                    <div style="width:38px;height:38px;border-radius:10px;background:{{ ['#ede9fe','#dbeafe','#dcfce7','#fef9c3','#fee2e2'][($project->id-1)%5] }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="bi bi-kanban" style="color:{{ ['#6366f1','#2563eb','#16a34a','#ca8a04','#ef4444'][($project->id-1)%5] }};"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $project->name }}</div>
                        <div style="font-size:11px;color:#94a3b8;">{{ $project->client?->company_name ?? 'Internal' }}</div>
                    </div>
                    <div>
                        @php
                            $colorMap = ['not_started'=>'secondary','planning'=>'info','design'=>'primary','development'=>'warning','testing'=>'warning','client_review'=>'info','rework'=>'danger','completed'=>'success','delivered'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                            $badge = $colorMap[$project->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}" style="font-size:10px;white-space:nowrap;">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                        @if($project->is_delayed)
                            <div style="font-size:10px;color:#ef4444;margin-top:2px;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Delayed</div>
                        @else
                            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                {{ $project->deadline ? $project->deadline->diffForHumans() : 'No deadline' }}
                            </div>
                        @endif
                    </div>
                </a>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:13px;">No projects yet</div>
                @endforelse
            </div>
        </div>

        <!-- Pending Daily Reports -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Pending Reports</span>
                <a href="{{ route('daily-reports.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($pendingReports as $report)
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                    <img src="{{ $report->user->avatar_url }}" class="rounded-circle" width="34" height="34" alt="">
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;">{{ $report->user->name }}</div>
                        <div style="font-size:12px;color:#94a3b8;">{{ $report->date->format('d M Y') }}</div>
                    </div>
                    <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-xs btn-sm btn-outline-primary" style="font-size:11px;padding:3px 10px;">Review</a>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:13px;">
                    <i class="bi bi-check-circle text-success" style="font-size:24px;"></i>
                    <div class="mt-1">All reports reviewed!</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Current Works Update Modal -->
<div class="modal fade" id="currentWorksUpdateModal" tabindex="-1" aria-labelledby="currentWorksUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success-subtle text-success p-2 rounded-3">
                        <i class="bi bi-broadcast text-success fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="currentWorksUpdateModalLabel">Current Works Update</h5>
                        <p class="text-muted fs-8 mb-0">Live tracking status of all team members</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @forelse($employees as $emp)
                        @php
                            $session = $emp->todayWorkSession;
                            $activeLog = $session?->activeTaskLog;
                            
                            $status = 'not_started';
                            if ($session) {
                                if ($session->status === 'ended') {
                                    $status = 'completed';
                                } elseif ($activeLog) {
                                    $status = 'working';
                                } else {
                                    $status = ($session->started_at && $session->started_at->diffInMinutes(now()) > 15) ? 'idle' : 'working';
                                }
                            }
                            
                            $statusConfig = [
                                'working' => [
                                    'badge' => 'bg-success-subtle text-success border-success-subtle',
                                    'label' => 'Working',
                                    'dot' => 'working',
                                ],
                                'idle' => [
                                    'badge' => 'bg-warning-subtle text-warning border-warning-subtle',
                                    'label' => 'Idle',
                                    'dot' => 'break',
                                ],
                                'completed' => [
                                    'badge' => 'bg-primary-subtle text-primary border-primary-subtle',
                                    'label' => 'Completed',
                                    'dot' => 'not-started',
                                ],
                                'not_started' => [
                                    'badge' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                    'label' => 'Not Started',
                                    'dot' => 'not-started',
                                ],
                            ];
                            
                            $config = $statusConfig[$status] ?? $statusConfig['not_started'];
                        @endphp
                        
                        <div class="col-12">
                            <div class="card border border-light-subtle shadow-xs">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative">
                                                <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}" class="avatar-circle rounded-circle border border-2 border-light" style="width: 44px; height: 44px; object-fit: cover;">
                                                <span class="position-absolute bottom-0 end-0 status-dot {{ $config['dot'] }}" style="width: 12px; height: 12px; border: 2px solid white; transform: translate(2px, 2px);"></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark fw-bold">{{ $emp->name }}</h6>
                                                <small class="text-muted fs-8">{{ $emp->role?->name ?? 'Employee' }} • {{ $emp->employee?->department?->name ?? 'No Dept' }}</small>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $config['badge'] }} border px-2.5 py-1 fs-8 fw-semibold rounded-pill">
                                                {{ $config['label'] }}
                                            </span>
                                            @if($session)
                                                <span class="badge bg-light text-dark border border-light-subtle px-2 py-1 fs-8 fw-semibold rounded-pill">
                                                    {{ $session->total_hours }} worked
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        @if($status === 'working' && $activeLog)
                                            @php
                                                $logDiff = max(0, now()->timestamp - $activeLog->started_at->timestamp);
                                            @endphp
                                            <div class="p-3 bg-success-subtle bg-opacity-25 border border-success-subtle rounded-3">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <i class="bi bi-play-circle-fill text-success fs-6"></i>
                                                            <span class="fw-bold text-success fs-7 text-truncate d-inline-block" style="max-width: 100%;">{{ $activeLog->task->title }}</span>
                                                        </div>
                                                        <div class="text-muted fs-8">
                                                            Project: <strong class="text-dark">{{ $activeLog->task->project->name ?? 'No Project' }}</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="text-sm-end flex-shrink-0">
                                                        <div class="running-timer-ticker font-monospace fw-bold text-success fs-6 bg-success bg-opacity-10 border border-success border-opacity-20 rounded px-2.5 py-1" data-start="{{ $activeLog->started_at->timestamp }}">
                                                            {{ sprintf('%02d:%02d:%02d', ($logDiff/3600), ($logDiff/60)%60, $logDiff%60) }}
                                                        </div>
                                                        <div class="text-muted fs-8 mt-1">Started: {{ $activeLog->started_at->format('h:i A') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($status === 'idle')
                                            <div class="p-3 bg-warning-subtle bg-opacity-25 border border-warning-subtle rounded-3 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-7 fw-semibold">
                                                    <i class="bi bi-exclamation-circle-fill"></i>
                                                    <span>Active Session but not tracking a task</span>
                                                </div>
                                            </div>
                                        @elseif($status === 'completed')
                                            <div class="p-3 bg-primary-subtle bg-opacity-25 border border-primary-subtle rounded-3 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-primary fs-7 fw-semibold">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    <span>Completed shift at {{ $session->ended_at?->format('h:i A') ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 bg-light border border-light-subtle rounded-3 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-muted fs-7">
                                                    <i class="bi bi-moon-fill text-secondary"></i>
                                                    <span>Not working yet today</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-people fs-1"></i>
                            <p class="mt-2 mb-0">No employees registered in the system.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pad(num) {
        return ("0" + num).slice(-2);
    }

    document.addEventListener('DOMContentLoaded', () => {
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
    });
</script>
@endpush
