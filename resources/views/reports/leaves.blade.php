@extends('layouts.app')

@section('title', 'Leaves Report')
@section('page-title', 'Leaves Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Leaves</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Leaves Registry</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Date Range</th>
                    <th>Total Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $leave->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                <span class="fs-7 fw-semibold">{{ $leave->user->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-capitalize badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ str_replace('_', ' ', $leave->leave_type) }}
                            </span>
                        </td>
                        <td>
                            {{ $leave->from_date ? $leave->from_date->format('d M Y') : '' }} to {{ $leave->to_date ? $leave->to_date->format('d M Y') : '' }}
                        </td>
                        <td class="fw-semibold">{{ $leave->total_days }} days</td>
                        <td class="text-muted fs-7" title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 40) }}</td>
                        <td>
                            @if($leave->status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                            @elseif($leave->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending Review</span>
                            @elseif($leave->status === 'team_leader_approved')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">TL Approved</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar2-x" style="font-size: 32px;"></i>
                            <div class="mt-2">No leave records.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leaves->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $leaves->links() }}
        </div>
    @endif
</div>
@endsection
