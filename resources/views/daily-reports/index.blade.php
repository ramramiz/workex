@extends('layouts.app')

@section('title', 'Daily Work Reports')
@section('page-title', 'Daily Work Reports')

@section('breadcrumb')
    <li class="breadcrumb-item active">Daily Reports</li>
@endsection

@section('content')
@if($todaySessions->isNotEmpty())
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Today's Done Works & Sessions</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        @if(auth()->user()->isAdminOrAbove())
                            <th>Employee</th>
                        @endif
                        <th>Shift Started</th>
                        <th>Shift Ended</th>
                        <th>Total Hours</th>
                        <th>Tasks Worked On</th>
                        <th>Work Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todaySessions as $session)
                        <tr>
                            @if(auth()->user()->isAdminOrAbove())
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $session->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                        <span class="fs-7 fw-medium text-dark">{{ $session->user->name }}</span>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <span class="fs-7 text-dark fw-medium">{{ $session->started_at ? $session->started_at->timezone('Asia/Kolkata')->format('h:i A') : '—' }}</span>
                            </td>
                            <td>
                                @if($session->ended_at)
                                    <span class="fs-7 text-dark fw-medium">{{ $session->ended_at->timezone('Asia/Kolkata')->format('h:i A') }}</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-8">Active / Running</span>
                                @endif
                            </td>
                            <td>
                                @if($session->ended_at)
                                    <span class="text-success fw-bold fs-7">{{ $session->total_hours }}</span>
                                @else
                                    <span class="text-danger fw-bold fs-7">Live</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $tasks = $session->timeLogs->pluck('task.title')->unique();
                                @endphp
                                @forelse($tasks as $t)
                                    <div class="fs-8 text-dark">• {{ $t }}</div>
                                @empty
                                    <span class="text-muted fs-8">No tasks logged</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="text-muted fs-7 text-wrap" style="white-space: pre-line;">{{ $session->work_done ?? 'No notes recorded yet.' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">All Work Reports</h5>
        @if(auth()->user()->isEmployee() || auth()->user()->isTeamLeader())
            <a href="{{ route('daily-reports.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-diff me-1"></i> Submit Daily Report
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('daily-reports.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Needs Revision</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Report Date</th>
                    @if(auth()->user()->isAdminOrAbove())
                        <th>Employee</th>
                    @endif
                    <th>Completed Work</th>
                    <th>Git Commit Link</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th title="Whether team leader visited the project previews page on this date">Projects Checked</th>
                    @endif
                    <th>Reviewer</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $rep)
                    <tr>
                        <td class="fw-semibold">
                            {{ \Carbon\Carbon::parse($rep->date)->format('d M Y') }}
                        </td>
                        @if(auth()->user()->isAdminOrAbove())
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $rep->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $rep->user->name }}</span>
                                </div>
                            </td>
                        @endif
                        <td>
                            <span class="text-muted fs-7" title="{{ $rep->completed_work }}">{{ Str::limit($rep->completed_work, 45) }}</span>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            @php
                                $checkedPreviews = \App\Models\ActivityLog::where('user_id', $rep->user_id)
                                    ->where('action', 'view_project_previews')
                                    ->whereDate('created_at', $rep->date)
                                    ->exists();
                            @endphp
                            <td>
                                @if($checkedPreviews)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-8 px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i>Checked
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8 px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>Not Checked
                                    </span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($rep->git_commit_link)
                                <a href="{{ $rep->git_commit_link }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 140px;">
                                    <i class="bi bi-git me-1 text-danger"></i> {{ $rep->git_commit_link }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($rep->reviewer)
                                <span class="fs-7 text-dark fw-medium">{{ $rep->reviewer->name }}</span>
                            @else
                                <span class="text-muted fs-8">—</span>
                            @endif
                        </td>
                        <td>
                            @if($rep->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                            @elseif($rep->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('daily-reports.show', $rep) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($rep->status === 'pending' && $rep->user_id === auth()->id())
                                    <a href="{{ route('daily-reports.edit', $rep) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text" style="font-size: 32px;"></i>
                            <div class="mt-2">No daily work reports submitted.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($reports->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $reports->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
