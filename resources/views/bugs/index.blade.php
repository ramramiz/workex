@extends('layouts.app')

@section('title', request('filter') === 'completed' ? 'Completed Bugs' : 'Pending Bugs')
@section('page-title', request('filter') === 'completed' ? 'Completed Bugs' : 'Pending Bugs')

@section('breadcrumb')
    @if(request('filter') === 'completed')
        <li class="breadcrumb-item"><a href="{{ route('bugs.index') }}">Bugs</a></li>
        <li class="breadcrumb-item active">Completed</li>
    @else
        <li class="breadcrumb-item active">Pending</li>
    @endif
@endsection

@section('topnav-middle')
    @include('bugs.status_nav')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">{{ request('filter') === 'completed' ? 'Completed Bugs' : 'Pending Bugs & Logged Issues' }}</h5>
        @if(auth()->user()->isLeaderOrAbove())
            <a href="{{ route('bugs.create') }}" class="btn btn-danger btn-sm">
                <i class="bi bi-bug me-1"></i> Log a Bug
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('bugs.index') }}" class="row g-3">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <div class="col-12 col-md-4">
                <select name="project" class="form-select form-select-sm">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="cleared" {{ request('status') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Bug Details</th>
                    <th>Project</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bugs as $bug)
                    <tr>
                        <td>
                            <div class="fw-semibold text-danger">
                                <a href="{{ route('bugs.show', $bug) }}" class="text-decoration-none text-danger">{{ $bug->title }}</a>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Reported: {{ $bug->created_at->format('d M Y') }}</small>
                        </td>
                        <td>
                            <span class="fw-medium text-dark">{{ $bug->project->name ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }} border border-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-capitalize">
                                {{ $bug->priority }}
                            </span>
                        </td>
                        <td>
                            @if($bug->assignedTo)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $bug->assignedTo->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $bug->assignedTo->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">Unassigned</span>
                            @endif
                        </td>
                        <td>{{ $bug->reportedBy->name ?? 'System' }}</td>
                        <td>
                            @php
                                $statusBg = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                if ($bug->status === 'open' || str_starts_with($bug->status, 'open ')) {
                                    $statusBg = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif ($bug->status === 'assigned') {
                                    $statusBg = 'bg-primary-subtle text-primary border border-primary-subtle';
                                } elseif ($bug->status === 'in_progress') {
                                    $statusBg = 'bg-warning-subtle text-warning border border-warning-subtle';
                                } elseif ($bug->status === 'resolved' || $bug->status === 'under_review') {
                                    $statusBg = 'bg-info-subtle text-info border border-info-subtle';
                                } elseif (in_array($bug->status, ['completed', 'approved', 'cleared', 'closed'])) {
                                    $statusBg = 'bg-success-subtle text-success border border-success-subtle';
                                }
                            @endphp
                            <span class="badge {{ $statusBg }} text-capitalize px-2 py-1.5 fw-semibold" style="font-size: 11px;">
                                {{ str_replace('_', ' ', $bug->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('bugs.show', $bug) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isLeaderOrAbove())
                                    <a href="{{ route('bugs.edit', $bug) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('bugs.destroy', $bug) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bug log?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-bug" style="font-size: 32px;"></i>
                            <div class="mt-2">No bugs logged. Good job!</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($bugs->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $bugs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection


