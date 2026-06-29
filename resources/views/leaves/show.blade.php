@extends('layouts.app')

@section('title', 'Leave Details')
@section('page-title', 'Leave Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Request Details -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Leave Request Details</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $leave->user->avatar_url }}" alt="" class="avatar-circle" style="width: 48px; height: 48px;">
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $leave->user->name }}</h5>
                        <small class="text-muted">{{ $leave->user->role->name ?? 'Developer' }}</small>
                    </div>
                    <div class="ms-auto">
                        @if($leave->status === 'approved')
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-7 py-2 px-3">Fully Approved</span>
                        @elseif($leave->status === 'team_leader_approved')
                            <span class="badge bg-info-subtle text-info border border-info-subtle fs-7 py-2 px-3">TL Approved (HR Pending)</span>
                        @elseif($leave->status === 'pending')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-7 py-2 px-3">Pending Review</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-7 py-2 px-3">Rejected</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <small class="text-muted d-block">Leave Type</small>
                        <span class="fw-semibold text-capitalize text-dark">
                            {{ str_replace('_', ' ', $leave->leave_type) }}
                            @if($leave->leave_type === 'half_day' && $leave->half_day_session)
                                <span class="text-muted text-lowercase">({{ $leave->half_day_session }} shift)</span>
                            @endif
                        </span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Duration</small>
                        <span class="fw-semibold text-dark">
                            {{ $leave->from_date ? $leave->from_date->format('d M Y') : '' }} to {{ $leave->to_date ? $leave->to_date->format('d M Y') : '' }}
                            <small class="text-muted font-monospace">({{ $leave->total_days }} days)</small>
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Reason for Request</small>
                    <div class="bg-light p-3 border rounded text-dark fs-7" style="white-space: pre-wrap;">{{ $leave->reason }}</div>
                </div>

                @if($leave->attachments && count($leave->attachments) > 0)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Medical Document Attachment</small>
                    <div class="d-flex align-items-center gap-2">
                        @foreach($leave->attachments as $path)
                            <a href="{{ asset('storage/' . $path) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-medical"></i> View Medical Document
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Approval Status Steps -->
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Approval Chain Log</h6></div>
            <div class="card-body">
                <!-- TL Step -->
                <div class="d-flex gap-3 mb-4">
                    <div class="flex-shrink-0 mt-1">
                        @if($leave->team_leader_status === 'approved')
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px;"><i class="bi bi-check"></i></div>
                        @else
                            <div class="bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px;"><i class="bi bi-circle"></i></div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold">1. Team Leader Approval</div>
                        @if($leave->team_leader_status === 'approved')
                            <small class="text-success fw-medium">Approved by {{ $leave->teamLeader->name ?? 'Leader' }}</small>
                            <p class="text-muted fs-8 mb-0 mt-1" style="white-space: pre-wrap;">Remarks: {{ $leave->team_leader_comment ?? 'No comment.' }}</p>
                        @else
                            <small class="text-muted">Pending decision or comments</small>
                        @endif
                    </div>
                </div>

                <!-- HR Step -->
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0 mt-1">
                        @if($leave->hr_status === 'approved')
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px;"><i class="bi bi-check"></i></div>
                        @else
                            <div class="bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:28px; height:28px;"><i class="bi bi-circle"></i></div>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold">2. HR Department Approval</div>
                        @if($leave->hr_status === 'approved')
                            <small class="text-success fw-medium">Approved by {{ $leave->hr->name ?? 'HR Manager' }}</small>
                            <p class="text-muted fs-8 mb-0 mt-1" style="white-space: pre-wrap;">Remarks: {{ $leave->hr_comment ?? 'No comment.' }}</p>
                        @else
                            <small class="text-muted">Pending decision or comments</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Approver Controls -->
    <div class="col-12 col-lg-4">
        <!-- TL Controls -->
        @if($leave->status === 'pending' && auth()->user()->isTeamLeader() && $leave->user_id !== auth()->id())
            <div class="card border border-warning-subtle mb-4">
                <div class="card-header bg-warning-subtle border-warning-subtle"><h6 class="mb-0 text-warning-emphasis">Team Leader Review</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('leaves.approve-tl', $leave) }}" id="tlReviewForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fs-7">Review Comments</label>
                            <textarea name="comment" id="tl_comment" class="form-control form-control-sm" rows="3" placeholder="Approval comments or reason..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1"><i class="bi bi-check-circle"></i> Approve</button>
                            <button type="button" class="btn btn-danger btn-sm flex-grow-1" onclick="rejectTL()"><i class="bi bi-x-circle"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- HR Controls -->
        @if(($leave->status === 'team_leader_approved' || $leave->status === 'pending') && (auth()->user()->isHR() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()) && $leave->user_id !== auth()->id())
            <div class="card border border-warning-subtle mb-4">
                <div class="card-header bg-warning-subtle border-warning-subtle"><h6 class="mb-0 text-warning-emphasis">HR Department Review</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('leaves.approve-hr', $leave) }}" id="hrReviewForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fs-7">Review Comments</label>
                            <textarea name="comment" id="hr_comment" class="form-control form-control-sm" rows="3" placeholder="Approval comments or reasons..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1"><i class="bi bi-check-circle"></i> Approve</button>
                            <button type="button" class="btn btn-danger btn-sm flex-grow-1" onclick="rejectHR()"><i class="bi bi-x-circle"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function rejectTL() {
        const comment = document.getElementById('tl_comment').value.trim();
        if (!comment) {
            alert('A comment is required to reject leaves.');
            return;
        }
        const form = document.getElementById('tlReviewForm');
        form.action = "{{ route('leaves.reject', $leave) }}";
        // Rename parameter to match validation in controller ('reason')
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'reason';
        hiddenInput.value = comment;
        form.appendChild(hiddenInput);
        form.submit();
    }

    function rejectHR() {
        const comment = document.getElementById('hr_comment').value.trim();
        if (!comment) {
            alert('A comment is required to reject leaves.');
            return;
        }
        const form = document.getElementById('hrReviewForm');
        form.action = "{{ route('leaves.reject', $leave) }}";
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'reason';
        hiddenInput.value = comment;
        form.appendChild(hiddenInput);
        form.submit();
    }
</script>
@endpush
