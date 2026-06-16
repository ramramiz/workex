@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'System Activity Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Activity Logs</li>
@endsection

@php
    $users = \App\Models\User::orderBy('name')->get();
    $actions = \App\Models\ActivityLog::select('action')->distinct()->pluck('action');
@endphp

@section('content')
<!-- Filters -->
<div class="card border border-light shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-funnel-fill text-primary"></i> Filter Audit Logs</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3">
            <!-- User -->
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action -->
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">Action</label>
                <select name="action" class="form-select form-select-sm text-capitalize">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $act) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div class="col-12 col-md-2">
                <label class="form-label fs-7 fw-semibold text-secondary">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>

            <!-- Date To -->
            <div class="col-12 col-md-2">
                <label class="form-label fs-7 fw-semibold text-secondary">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>

            <!-- Filter Buttons -->
            <div class="col-12 col-md-2 d-grid mt-md-4 pt-md-2">
                <div class="btn-group btn-group-sm">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Filter</button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs list -->
<div class="card border border-light shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover fs-7">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Metadata</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                        $actLower = strtolower($log->action);
                        if (str_contains($actLower, 'create') || str_contains($actLower, 'store') || str_contains($actLower, 'add')) {
                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                        } elseif (str_contains($actLower, 'update') || str_contains($actLower, 'edit')) {
                            $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                        } elseif (str_contains($actLower, 'delete') || str_contains($actLower, 'destroy') || str_contains($actLower, 'remove')) {
                            $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                        } elseif (str_contains($actLower, 'login') || str_contains($actLower, 'authenticate')) {
                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                        }
                    @endphp
                    <tr>
                        <!-- Collapse Toggle Button (only if changes exist) -->
                        <td class="text-center">
                            @if(!empty($log->old_values) || !empty($log->new_values))
                                <button class="btn btn-link p-0 text-decoration-none text-secondary" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#changes-{{ $log->id }}" 
                                        aria-expanded="false" 
                                        aria-controls="changes-{{ $log->id }}">
                                    <i class="bi bi-chevron-down fs-6"></i>
                                </button>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $log->created_at->format('d M Y') }}</div>
                            <div class="text-muted fs-8">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $log->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <div>
                                        <div class="fw-bold text-dark fs-8">{{ $log->user->name }}</div>
                                        <div class="text-muted fs-9 text-uppercase">{{ $log->user->role?->name }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted italic fs-8">System / Guest</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-capitalize {{ $badgeClass }} fs-8">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="fw-semibold text-dark text-wrap" style="max-width: 250px;">
                            {{ $log->description }}
                            @if($log->model_type)
                                <div class="text-muted fs-8">
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fs-8 text-secondary"><i class="bi bi-pc-display"></i> {{ $log->ip_address }}</div>
                            <div class="text-muted fs-9 text-truncate" style="max-width: 180px;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent }}
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Collapsible Row for Data Changes -->
                    @if(!empty($log->old_values) || !empty($log->new_values))
                        <tr class="collapse-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse" id="changes-{{ $log->id }}">
                                    <div class="p-3 bg-light border-bottom">
                                        <div class="row g-3">
                                            @if(!empty($log->old_values))
                                                <div class="col-12 col-md-6">
                                                    <h6 class="fs-8 fw-bold text-danger mb-2"><i class="bi bi-dash-circle"></i> Previous State</h6>
                                                    <div class="bg-white border rounded p-2 text-dark font-monospace fs-8 pre-wrap" style="max-height: 200px; overflow-y: auto;">
                                                        @foreach($log->old_values as $key => $val)
                                                            <div><strong>{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            @if(!empty($log->new_values))
                                                <div class="col-12 col-md-{{ empty($log->old_values) ? '12' : '6' }}">
                                                    <h6 class="fs-8 fw-bold text-success mb-2"><i class="bi bi-plus-circle"></i> Updated State</h6>
                                                    <div class="bg-white border rounded p-2 text-dark font-monospace fs-8 pre-wrap" style="max-height: 200px; overflow-y: auto;">
                                                        @foreach($log->new_values as $key => $val)
                                                            <div><strong>{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-3"></i>
                            <div class="mt-2">No activity logs recorded matching criteria.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
