@extends('layouts.app')

@section('title', 'Meeting Details')
@section('page-title', 'Meeting Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('meetings.index') }}">Meetings & Discussions</a></li>
    <li class="breadcrumb-item active">{{ $meeting->title }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Meeting details card -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Meeting Info</h5>
                <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-outline-primary btn-xs py-1 px-2 fs-7" title="Edit details">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            </div>
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-3">{{ $meeting->title }}</h4>
                
                <div class="d-flex flex-column gap-3 fs-7">
                    <div>
                        <span class="text-muted d-block mb-1">Date:</span>
                        <span class="fw-semibold text-dark fs-6">
                            <i class="bi bi-calendar-event text-primary me-1"></i>
                            {{ $meeting->meeting_date->format('l, d M Y') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-muted d-block mb-1">Location:</span>
                        <span class="fw-semibold text-dark fs-6">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            {{ $meeting->location }}
                        </span>
                    </div>

                    <div>
                        <span class="text-muted d-block mb-1">Organizer:</span>
                        @if($meeting->creator)
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <img src="{{ $meeting->creator->avatar_url }}" alt="" class="avatar-circle" style="width: 28px; height: 28px;">
                                <span class="fw-semibold text-dark fs-7">{{ $meeting->creator->name }}</span>
                            </div>
                        @else
                            <span class="fw-semibold text-dark">—</span>
                        @endif
                    </div>

                    @if($meeting->description)
                        <div class="border-top pt-3 mt-2">
                            <span class="text-muted d-block mb-2">Agenda / Description:</span>
                            <div class="p-3 bg-light rounded text-secondary" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.5;">{{ $meeting->description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks list card -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Meeting Tasks</h5>
                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                    <a href="{{ route('tasks.create', ['meeting_id' => $meeting->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Task
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Task Name</th>
                                <th>Project</th>
                                <th>Assignee</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($meeting->tasks as $task)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-dark">{{ $task->title }}</a>
                                        </div>
                                        <span class="badge mt-1" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 10px;">
                                            meeting-{{ $meeting->meeting_date->format('Y-m-d') }}
                                        </span>
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
                                    <td>
                                        @if($task->status === 'completed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
                                        @elseif($task->status === 'in_progress')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">In Progress</span>
                                        @elseif($task->status === 'review')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">Review</span>
                                        @elseif($task->status === 'rework')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rework</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-square" style="font-size: 32px;"></i>
                                        <div class="mt-2">No tasks assigned to this meeting yet.</div>
                                        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                            <div class="mt-3">
                                                <a href="{{ route('tasks.create', ['meeting_id' => $meeting->id]) }}" class="btn btn-sm btn-primary">
                                                    Add First Task
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
