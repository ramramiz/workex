@extends('layouts.app')
@section('title', 'Telecaller Room Call Details')
@section('page-title', 'Telecaller Room Call Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('live-status') }}">Live Status Board</a></li>
    <li class="breadcrumb-item active">Call Details</li>
@endsection

@push('styles')
<style>
    .stat-card-detail {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .stat-card-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.05);
    }
    .badge-status {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .badge-Connected {
        background-color: rgba(16, 185, 129, 0.15) !important;
        color: #10b981 !important;
    }
    .badge-Switched_Off {
        background-color: rgba(100, 116, 139, 0.15) !important;
        color: #64748b !important;
    }
    .badge-Busy {
        background-color: rgba(245, 158, 11, 0.15) !important;
        color: #f59e0b !important;
    }
    .badge-Not_Connected {
        background-color: rgba(239, 68, 68, 0.15) !important;
        color: #ef4444 !important;
    }
    [data-bs-theme="dark"] .badge-Connected {
        background-color: rgba(16, 185, 129, 0.25) !important;
        color: #34d399 !important;
    }
    [data-bs-theme="dark"] .badge-Switched_Off {
        background-color: rgba(148, 163, 184, 0.25) !important;
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] .badge-Busy {
        background-color: rgba(245, 158, 11, 0.25) !important;
        color: #fbbf24 !important;
    }
    [data-bs-theme="dark"] .badge-Not_Connected {
        background-color: rgba(239, 68, 68, 0.25) !important;
        color: #f87171 !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-telephone-outbound text-primary"></i> Daily Calling Logs
        </h1>
        <p class="page-subtitle">
            Telecaller: <strong>{{ $user->name }}</strong> | Room: <strong>{{ $roomName }}</strong> | Date: <strong>{{ $today->format('M d, Y') }}</strong>
        </p>
    </div>
    <div>
        <a href="{{ route('live-status') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back to Board
        </a>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card-detail d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-telephone fs-5"></i>
            </div>
            <div>
                <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Total Calls</div>
                <h4 class="fw-bold mb-0 text-dark">{{ $totalCalls }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-detail d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-telephone-inbound fs-5"></i>
            </div>
            <div>
                <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Connected</div>
                <h4 class="fw-bold mb-0 text-dark">{{ $connectedCalls }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-detail d-flex align-items-center gap-3">
            <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-telephone-x fs-5"></i>
            </div>
            <div>
                <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Not Connected</div>
                <h4 class="fw-bold mb-0 text-dark">{{ $notConnectedCalls }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card-detail d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-heart fs-5"></i>
            </div>
            <div>
                <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Interested</div>
                <h4 class="fw-bold mb-0 text-dark">{{ $interestedLeads }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card border-0 shadow-sm" style="border-radius: 16px; background: var(--card-bg);">
    <div class="card-header bg-transparent py-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Call History Logs</h5>
        <span class="badge bg-secondary-subtle text-secondary fs-8">Sorted by Status</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Lead Name & Phone</th>
                    <th>Call Status</th>
                    <th>Response / Interest</th>
                    <th>Remarks</th>
                    <th class="text-end">Duration & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($calls as $call)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $call->lead->client_name ?? 'Unknown Lead' }}</div>
                            <small class="text-muted font-monospace">{{ $call->lead->client_phone ?? 'N/A' }}</small>
                        </td>
                        <td>
                            @php
                                $normalizedStatus = str_replace(' ', '_', $call->status);
                            @endphp
                            <span class="badge-status badge-{{ $normalizedStatus }}">
                                <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>
                                {{ $call->status }}
                            </span>
                        </td>
                        <td>
                            @if($call->lead && $call->lead->status === 'interested')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8 px-2 py-1">
                                    <i class="bi bi-heart-fill me-1"></i> Interested
                                </span>
                            @else
                                <span class="text-secondary fs-8">{{ $call->customer_response ?: '—' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-dark fs-7" style="white-space: pre-wrap;">{{ $call->remarks ?: 'No remarks' }}</span>
                        </td>
                        <td class="text-end font-monospace">
                            <div class="fw-bold text-dark">
                                @if($call->duration)
                                    @if($call->duration >= 60)
                                        {{ intdiv($call->duration, 60) }}m {{ $call->duration % 60 }}s
                                    @else
                                        {{ $call->duration }}s
                                    @endif
                                @else
                                    0s
                                @endif
                            </div>
                            <small class="text-muted">{{ $call->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-telephone-minus fs-3 mb-2 d-block"></i>
                            No calls logged for this room today.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
