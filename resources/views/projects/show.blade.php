@extends('layouts.app')

@section('title', 'Project Details')
@section('page-title', 'Project Board')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Project Overview Details -->
    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Overview</h6>
                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                @endif
            </div>
            <div class="card-body">
                <h4 class="fw-bold mb-2">{{ $project->name }}</h4>
                <p class="text-muted fs-7 mb-3">{{ $project->description ?? 'No project description provided.' }}</p>

                <div class="mb-3">
                    <small class="text-muted d-block fs-8 text-uppercase font-monospace">Client</small>
                    @if($project->client)
                        <span class="fw-semibold text-primary"><i class="bi bi-building"></i> <a href="{{ route('clients.show', $project->client) }}">{{ $project->client->company_name }}</a></span>
                    @else
                        <span class="text-muted fw-semibold">Internal Project</span>
                    @endif
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Start Date</small>
                        <span class="fw-medium">{{ $project->start_date ? $project->start_date->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Deadline</small>
                        <span class="fw-medium {{ $project->is_delayed ? 'text-danger fw-bold' : '' }}">
                            {{ $project->deadline ? $project->deadline->format('d M Y') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Budget</small>
                        <span class="fw-semibold text-success">₹{{ number_format($project->budget, 2) }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Priority</small>
                        <span class="badge bg-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }}-subtle text-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }} border border-{{ $project->priority === 'critical' ? 'danger' : ($project->priority === 'high' ? 'warning' : 'secondary') }}-subtle text-capitalize">
                            {{ $project->priority }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize mt-1">
                        {{ str_replace('_', ' ', $project->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Technologies</small>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @forelse($project->technologies ?? [] as $tech)
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ trim($tech) }}</span>
                        @empty
                            <span class="text-muted fs-7">—</span>
                        @endforelse
                    </div>
                </div>

                @if($project->teamLeader)
                    <div class="border-top pt-3 mt-3">
                        <small class="text-muted d-block mb-1">Project Leader</small>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $project->teamLeader->avatar_url }}" alt="" class="avatar-circle" style="width: 32px; height: 32px;">
                            <div>
                                <span class="fw-semibold text-dark d-block fs-7">{{ $project->teamLeader->name }}</span>
                                <small class="text-muted fs-8">{{ $project->teamLeader->role->name ?? 'Team Leader' }}</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Project Work Area: Tabs & KPIs -->
    <div class="col-12 col-lg-8">
        <!-- KPIs Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-white border text-center py-3">
                    <div class="fs-4 fw-bold text-primary">{{ $taskStats['total'] }}</div>
                    <div class="text-muted fs-8">Total Tasks</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-white border text-center py-3">
                    <div class="fs-4 fw-bold text-success">{{ $taskStats['completed'] }}</div>
                    <div class="text-muted fs-8">Completed</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-white border text-center py-3">
                    <div class="fs-4 fw-bold text-warning">{{ $taskStats['in_progress'] }}</div>
                    <div class="text-muted fs-8">In Progress</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-white border text-center py-3">
                    <div class="fs-4 fw-bold text-danger">{{ $project->bugs->where('status', 'open')->count() }}</div>
                    <div class="text-muted fs-8">Open Bugs</div>
                </div>
            </div>
        </div>

        <!-- tab panel card -->
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0 px-3" id="projectTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">Tasks</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="completed-tasks-tab" data-bs-toggle="tab" data-bs-target="#completed-tasks" type="button" role="tab">Completed Tasks</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button" role="tab">Team</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="bugs-tab" data-bs-toggle="tab" data-bs-target="#bugs" type="button" role="tab">Bugs</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">Billing</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="projectTabsContent">
                    <!-- Tasks -->
                    <div class="tab-pane fade show active" id="tasks" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Tasks List</h6>
                            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-xs py-1 px-2 fs-7"><i class="bi bi-plus-lg"></i> Add Task</a>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Assignee</th>
                                        <th>Priority</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->tasks->where('status', '!=', 'completed') as $task)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">{{ $task->title }}</a>
                                                @if($task->meeting)
                                                    <div class="mt-1">
                                                        <span class="badge" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 9px; padding: 2px 4px;">
                                                            meeting-{{ $task->meeting->meeting_date->format('Y-m-d') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->assignee)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <img src="{{ $task->assignee->avatar_url }}" alt="" class="avatar-circle" style="width:20px; height:20px;">
                                                        <span class="fs-8">{{ $task->assignee->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-8">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $task->priority_badge }}-subtle text-{{ $task->priority_badge }} border border-{{ $task->priority_badge }}-subtle text-capitalize fs-8" style="font-size: 10px;">
                                                    {{ $task->priority }}
                                                </span>
                                            </td>
                                            <td>{{ $task->deadline ? $task->deadline->format('d M Y') : '—' }}</td>
                                            <td>
                                                @if($task->status === 'completed')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
                                                @elseif($task->status === 'in_progress')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">In Progress</span>
                                                @elseif($task->status === 'review')
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Review</span>
                                                @elseif($task->status === 'rework')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rework</span>
                                                @elseif($task->status === 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted fs-7">No tasks found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Completed Tasks -->
                    <div class="tab-pane fade" id="completed-tasks" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Completed Tasks</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Assignee</th>
                                        <th>Priority</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->tasks->where('status', 'completed') as $task)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">{{ $task->title }}</a>
                                                @if($task->meeting)
                                                    <div class="mt-1">
                                                        <span class="badge" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 9px; padding: 2px 4px;">
                                                            meeting-{{ $task->meeting->meeting_date->format('Y-m-d') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->assignee)
                                                    <div class="d-flex align-items-center gap-1">
                                                        <img src="{{ $task->assignee->avatar_url }}" alt="" class="avatar-circle" style="width:20px; height:20px;">
                                                        <span class="fs-8">{{ $task->assignee->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-8">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $task->priority_badge }}-subtle text-{{ $task->priority_badge }} border border-{{ $task->priority_badge }}-subtle text-capitalize fs-8" style="font-size: 10px;">
                                                    {{ $task->priority }}
                                                </span>
                                            </td>
                                            <td>{{ $task->deadline ? $task->deadline->format('d M Y') : '—' }}</td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted fs-7">No completed tasks found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Team -->
                    <div class="tab-pane fade" id="team" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Project Team Members</h6>
                            @if(auth()->user()->isAdminOrAbove() || $project->team_leader_id === auth()->id())
                                <button type="button" class="btn btn-primary btn-xs py-1 px-2 fs-7" data-bs-toggle="modal" data-bs-target="#manageTeamModal">
                                    <i class="bi bi-people-fill me-1"></i> Manage Team
                                </button>
                            @endif
                        </div>
                        <div class="row g-3">
                            @forelse($project->members as $member)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="d-flex align-items-center gap-3 border rounded p-3">
                                        <img src="{{ $member->avatar_url }}" alt="" class="avatar-circle" style="width: 40px; height: 40px;">
                                        <div>
                                            <span class="fw-bold d-block text-dark">{{ $member->name }}</span>
                                            <small class="text-muted fs-8">{{ $member->role->name ?? 'Developer' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-4 text-muted fs-7">No developers linked to this project board.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Bugs -->
                    <div class="tab-pane fade" id="bugs" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Open Bugs & Issues</h6>
                            <a href="{{ route('bugs.create', ['project_id' => $project->id]) }}" class="btn btn-outline-danger btn-xs py-1 px-2 fs-7"><i class="bi bi-bug"></i> Report Bug</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Bug Info</th>
                                        <th>Reporter</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->bugs as $bug)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('bugs.show', $bug) }}" class="text-decoration-none text-danger">{{ $bug->title }}</a>
                                            </td>
                                            <td>{{ $bug->reportedBy->name ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-capitalize fs-8" style="font-size: 10px;">
                                                    {{ $bug->priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-capitalize fs-8" style="font-size: 10px;">
                                                    {{ str_replace('_', ' ', $bug->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted fs-7">No bugs tracked yet for this project.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="tab-pane fade" id="invoices" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Associated Invoices</h6>
                            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
                                <a href="{{ route('invoices.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-xs py-1 px-2 fs-7"><i class="bi bi-receipt"></i> Generate Invoice</a>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->invoices as $inv)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">{{ $inv->invoice_number }}</a>
                                            </td>
                                            <td class="fw-semibold text-success">₹{{ number_format($inv->amount, 2) }}</td>
                                            <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                                            <td>
                                                @if($inv->status === 'paid')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                                @elseif($inv->status === 'sent')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Sent</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted fs-7">No invoices linked.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage Team Modal -->
@if(auth()->user()->isAdminOrAbove() || $project->team_leader_id === auth()->id())
<div class="modal fade" id="manageTeamModal" tabindex="-1" aria-labelledby="manageTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTeamModalLabel"><i class="bi bi-people-fill me-2 text-primary"></i>Manage Project Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('projects.team.update', $project) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted fs-7 mb-3">Select the developers / team members you want to assign to this project board.</p>
                    <div class="row g-2">
                        @php
                            $assignedMemberIds = $project->members->pluck('id')->toArray();
                        @endphp
                        @forelse($employees as $emp)
                            <div class="col-12 col-md-6">
                                <div class="form-check border rounded p-2">
                                    <input class="form-check-input ms-1" type="checkbox" name="members[]" value="{{ $emp->id }}" id="modal-chk-emp-{{ $emp->id }}" {{ in_array($emp->id, $assignedMemberIds) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 text-dark fs-7" for="modal-chk-emp-{{ $emp->id }}">
                                        {{ $emp->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-3">No active developers found in the system.</div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
