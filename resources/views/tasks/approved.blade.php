@extends('layouts.app')

@section('title', 'Approved Tasks')
@section('page-title', 'Approved Tasks')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active">Approved Tasks</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Approved Tasks Queue</h5>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('tasks.approved') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search tasks..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
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
                    <option value="special" {{ request('priority') === 'special' ? 'selected' : '' }}>Special</option>
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
                    <th>Task Name</th>
                    <th>Project</th>
                    <th>Assignee</th>
                    <th>Priority</th>
                    <th>Est. Hours</th>
                    <th>Deadline</th>
                    <th>Completed Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-dark">{{ $task->title }}</a>
                            </div>
                            @if($task->meeting)
                                <div class="mt-1">
                                    <span class="badge" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 10px;">
                                        meeting-{{ $task->meeting->meeting_date->format('Y-m-d') }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="fs-7 text-secondary">
                            {{ $task->project->name ?? '—' }}
                        </td>
                        <td>
                            @if($task->assignee)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $task->assignee->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $task->assignee->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @php $badge = $task->priority_badge; @endphp
                            <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} border border-{{ $badge }}-subtle text-capitalize fs-8">
                                {{ $task->priority }}
                            </span>
                        </td>
                        <td>{{ $task->estimated_hours ?? '—' }} hrs</td>
                        <td>
                            @if($task->deadline)
                                <span class="{{ \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-danger fw-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($task->completed_date)
                                <span class="text-success fw-semibold">
                                    {{ \Carbon\Carbon::parse($task->completed_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if(auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
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
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-square" style="font-size: 32px;"></i>
                            <div class="mt-2">No approved tasks found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $tasks->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
