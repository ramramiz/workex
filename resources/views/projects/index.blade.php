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
        @if(auth()->user()->isAdminOrAbove())
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-kanban me-1"></i> Add Project
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search projects by name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="development" {{ request('status') === 'development' ? 'selected' : '' }}>Development</option>
                    <option value="testing" {{ request('status') === 'testing' ? 'selected' : '' }}>Testing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
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
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize fs-8" style="font-size: 10px;">
                        {{ str_replace('_', ' ', $project->status) }}
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
                            <span class="fw-semibold text-dark">₹{{ number_format($project->budget, 2) }}</span>
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
@endsection
