@extends('layouts.app')

@section('title', 'Projects')
@section('page-title', 'Projects')

@section('breadcrumb')
    <li class="breadcrumb-item active">Projects</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">All Projects</h5>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('projects.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i> Download Template
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importProjectsModal">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
                </button>
            @endif
            @if(auth()->user()->isLeaderOrAbove())
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-kanban me-1"></i> Add Project
                </a>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs px-3 mt-2" id="projectsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request('tab', 'working') === 'working' ? 'active fw-bold text-primary border-bottom-2' : 'text-secondary' }}" href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'working', 'status' => ''])) }}">
                <i class="bi bi-kanban me-1"></i> Working Projects
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('tab') === 'completed' ? 'active fw-bold text-primary border-bottom-2' : 'text-secondary' }}" href="{{ route('projects.index', array_merge(request()->query(), ['tab' => 'completed', 'status' => ''])) }}">
                <i class="bi bi-check-circle me-1"></i> Completed Projects
            </a>
        </li>
    </ul>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
            <input type="hidden" name="tab" value="{{ request('tab', 'working') }}">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search projects by name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                @php
                    $currentTab = request('tab', 'working');
                    if ($currentTab === 'completed') {
                        $statuses = [
                            'completed' => 'Completed',
                            'completed_started_amc' => 'Completed & Started AMC',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled'
                        ];
                    } else {
                        $statuses = [
                            'not_started' => 'Not Started',
                            'planning' => 'Planning',
                            'design' => 'Design',
                            'development' => 'Development',
                            'testing' => 'Testing',
                            'client_review' => 'Client Review',
                            'rework' => 'Rework',
                            'on_hold' => 'On Hold'
                        ];
                    }
                @endphp
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $val => $lbl)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($projects as $project)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="badge bg-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }}-subtle text-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }} border border-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }}-subtle text-uppercase fs-8" style="font-size: 10px;">
                        {{ $project->priority }} Priority
                    </span>
                    <span class="badge bg-{{ $project->status_badge }}-subtle text-{{ $project->status_badge }} border border-{{ $project->status_badge }}-subtle text-capitalize fs-8" style="font-size: 10px;">
                        {{ $project->status_label }}
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-1">
                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none text-dark fw-bold">{{ $project->name }}</a>
                    </h5>
                    <p class="text-muted fs-7 mb-3">Client: {{ $project->client->company_name ?? 'Internal Project' }}</p>

                    <!-- Progress Bar -->
                    @php $pct = $project->progress_percentage; @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between fs-7 mb-1 fw-medium">
                            <span class="text-secondary">Progress</span>
                            <span class="text-dark">{{ $pct }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <!-- Meta details -->
                    <div class="row g-2 fs-7 text-muted border-top pt-3">
                        <div class="col-6">
                            <i class="bi bi-calendar-event me-1"></i> Deadline:<br>
                            <span class="fw-semibold text-dark">{{ $project->deadline ? $project->deadline->format('d M Y') : '—' }}</span>
                        </div>
                        <div class="col-6">
                            <i class="bi bi-wallet2 me-1"></i> Budget:<br>
                            <span class="fw-semibold text-dark">
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                    ₹{{ number_format($project->budget, 2) }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- AMC Status / Due Date -->
                    <div class="mt-3 pt-3 border-top fs-7">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                            <span class="text-muted"><i class="bi bi-shield-check me-1"></i> AMC Status:</span>
                            @if($project->amc && $project->amc->start_date)
                                @php
                                    $dueDate = \Carbon\Carbon::parse($project->amc->end_date);
                                    $diff = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
                                @endphp
                                <span class="fw-semibold text-dark">
                                    {{ $dueDate->format('d M Y') }}
                                    @if($diff > 0)
                                        <span class="text-success fw-medium">({{ $diff }} days to go)</span>
                                    @elseif($diff === 0)
                                        <span class="text-warning fw-medium">(Due today)</span>
                                    @else
                                        <span class="text-danger fw-medium">(Expired {{ abs($diff) }} days ago)</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted italic">AMC Not Started</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center py-3">
                    <div class="d-flex align-items-center gap-2">
                        @if($project->teamLeader)
                            <img src="{{ $project->teamLeader->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;" title="Leader: {{ $project->teamLeader->name }}">
                            <span class="fs-8 text-muted text-truncate d-inline-block" style="max-width: 100px;">{{ $project->teamLeader->name }}</span>
                        @endif
                    </div>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">View Details</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card text-center py-5 text-muted">
                <div class="card-body">
                    <i class="bi bi-kanban" style="font-size: 48px; color: #6366f1;"></i>
                    <h5 class="mt-3">No Projects Found</h5>
                    <p class="fs-7">Create a project or modify filters to get started.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($projects->hasPages())
    <div class="mt-4">
        {{ $projects->withQueryString()->links() }}
    </div>
@endif
@if(auth()->user()->isSuperAdmin())
<div class="modal fade" id="importProjectsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Projects</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Upload Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required style="border-radius: 8px;">
                        <small class="text-muted d-block mt-2">
                            Download the template first. Clients are matched by <strong>Company Name</strong> and Team Leaders are matched by <strong>Email</strong>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
