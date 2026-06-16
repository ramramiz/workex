@extends('layouts.app')

@section('title', 'Daily Report Details')
@section('page-title', 'Daily Report Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Daily Reports</a></li>
    <li class="breadcrumb-item active">{{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Report Contents -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Progress Report: {{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</h5>
                @if($report->status === 'pending' && $report->user_id === auth()->id())
                    <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                @endif
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $report->user->avatar_url }}" alt="" class="avatar-circle" style="width: 48px; height: 48px;">
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $report->user->name }}</h5>
                        <small class="text-muted">{{ $report->user->role->name ?? 'Developer' }}</small>
                    </div>
                    <div class="ms-auto">
                        @if($report->status === 'approved')
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-7 py-2 px-3">Approved</span>
                        @elseif($report->status === 'pending')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-7 py-2 px-3">Pending Review</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-7 py-2 px-3">Revision Needed</span>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Completed Work Today</h6>
                    <div class="bg-light p-3 border rounded text-dark fs-7" style="white-space: pre-wrap;">{{ $report->completed_work }}</div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Pending Work</h6>
                    <div class="bg-light p-3 border rounded text-muted fs-7" style="white-space: pre-wrap;">{{ $report->pending_work ?? 'None' }}</div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Issues / Blockers Faced</h6>
                    <div class="bg-light p-3 border rounded text-muted fs-7 {{ $report->issues_faced ? 'text-danger border-danger-subtle' : '' }}" style="white-space: pre-wrap;">{{ $report->issues_faced ?? 'No issues encountered today.' }}</div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Plan for Tomorrow</h6>
                    <div class="bg-light p-3 border rounded text-muted fs-7" style="white-space: pre-wrap;">{{ $report->tomorrow_plan ?? 'Not specified' }}</div>
                </div>

                @if($report->git_commit_link)
                    <div>
                        <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Git Commit Link</h6>
                        <a href="{{ $report->git_commit_link }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-git text-danger"></i> View Commit Diff
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Review Decisions -->
    <div class="col-12 col-lg-4">
        <!-- Reviewer Feedback -->
        @if($report->reviewer)
            <div class="card mb-4 border border-light">
                <div class="card-header bg-white"><h6 class="mb-0">Review Feedback</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Reviewed By</small>
                        <span class="fw-semibold text-dark">{{ $report->reviewer->name }}</span>
                    </div>
                    @if($report->reviewed_at)
                        <div class="mb-3">
                            <small class="text-muted d-block">Reviewed At</small>
                            <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($report->reviewed_at)->format('d M Y h:i A') }}</span>
                        </div>
                    @endif
                    <div>
                        <small class="text-muted d-block">Reviewer Comments</small>
                        <p class="text-muted fs-7 mb-0 mt-1" style="white-space: pre-wrap;">{{ $report->reviewer_comment ?? 'No comment provided.' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Review Controls for Managers -->
        @if($report->status === 'pending' && auth()->user()->isLeaderOrAbove() && $report->user_id !== auth()->id())
            <div class="card border border-warning-subtle">
                <div class="card-header bg-warning-subtle border-warning-subtle"><h6 class="mb-0 text-warning-emphasis">Manager Review Panel</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('daily-reports.approve', $report) }}" id="approveForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fs-7">Review Comment / Instructions</label>
                            <textarea name="comment" id="reviewer_comment" class="form-control form-control-sm" rows="3" placeholder="Leave remarks for approval or rejection..."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1"><i class="bi bi-check-circle"></i> Approve</button>
                            <button type="button" class="btn btn-danger btn-sm flex-grow-1" onclick="submitRejection()"><i class="bi bi-x-circle"></i> Reject / Revise</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($report->status === 'pending' && auth()->user()->isLeaderOrAbove() && $report->user_id !== auth()->id())
<script>
    function submitRejection() {
        const comment = document.getElementById('reviewer_comment').value.trim();
        if (!comment) {
            alert('A review comment is required to send back a report for revision.');
            return;
        }

        const form = document.getElementById('approveForm');
        form.action = "{{ route('daily-reports.reject', $report) }}";
        form.submit();
    }
</script>
@endif
@endpush
