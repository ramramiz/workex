@extends('layouts.app')

@section('title', 'Leave Management')
@section('page-title', 'Leave Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Applications</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Leave Applications</h5>
        @if(auth()->user()->isEmployee() || auth()->user()->isTeamLeader() || auth()->user()->isTelecaller())
            <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-plus me-1"></i> Apply for Leave
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('leaves.index') }}" class="row g-3">
            <div class="col-12 col-md-8">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="team_leader_approved" {{ request('status') === 'team_leader_approved' ? 'selected' : '' }}>Approved by TL</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Fully Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    <th>Leave Duration</th>
                    @if(auth()->user()->isLeaderOrAbove() || auth()->user()->isHR())
                        <th>Employee</th>
                    @endif
                    <th>Leave Type</th>
                    <th>Reason</th>
                    <th>Approval Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>
                            @if($leave->from_date)
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($leave->from_date)->format('d M') }} to {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}
                                </div>
                                <small class="text-muted d-block" style="font-size: 11px;">({{ $leave->total_days ?? (\Carbon\Carbon::parse($leave->from_date)->diffInDays(\Carbon\Carbon::parse($leave->to_date)) + 1) }} days)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if(auth()->user()->isLeaderOrAbove() || auth()->user()->isHR())
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $leave->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $leave->user->name }}</span>
                                </div>
                            </td>
                        @endif
                        <td>
                            <span class="text-capitalize badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ str_replace('_', ' ', $leave->leave_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted fs-7" title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 40) }}</span>
                        </td>
                        <td>
                            @if($leave->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Fully Approved</span>
                            @elseif($leave->status === 'team_leader_approved')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">TL Approved (HR Pending)</span>
                            @elseif($leave->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending Review</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('leaves.show', $leave) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($leave->user_id === auth()->id())
                                    @if($leave->status === 'pending')
                                        <a href="{{ route('leaves.edit', $leave) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('leaves.destroy', $leave) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to revoke and delete this leave request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Revoke">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 32px;"></i>
                            <div class="mt-2">No leave applications found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leaves->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $leaves->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
