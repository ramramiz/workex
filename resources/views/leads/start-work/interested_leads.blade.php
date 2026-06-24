@extends('layouts.app')

@section('title', 'Interested Leads - Leads list')
@section('page-title', 'Interested Leads')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.start-work.index') }}">Start Work</a></li>
    <li class="breadcrumb-item active">Interested Leads</li>
@endsection

@section('content')


<div class="card mb-4 border border-success shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-success-subtle py-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: rgba(25, 135, 84, 0.08) !important; border-bottom: 1px solid rgba(25, 135, 84, 0.2) !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-star-fill fs-5"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Interested Leads</h5>
                <span class="text-secondary fs-8">Calling interested leads across all rooms</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Timer Display -->
            @if(session('active_room_work') && session('active_room_work')['room_id'])
                <div class="d-flex align-items-center gap-2 bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill border border-warning-subtle" id="leads-timer-badge">
                    <span class="status-dot working animate-pulse" style="background: #d97706; width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                    <span class="fw-semibold font-monospace" id="leads-timer-counter">00:00:00</span>
                </div>
            @endif

            <!-- Change Room Button -->
            <a href="{{ route('leads.start-work.select-room') }}" class="btn btn-outline-warning btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5 text-dark" style="border-radius: 20px;">
                <i class="bi bi-door-open-fill"></i> Change Room
            </a>

            <!-- Stop Work Button -->
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5" style="border-radius: 20px;" data-bs-toggle="modal" data-bs-target="#confirmStopWorkModal">
                <i class="bi bi-stop-circle-fill"></i> Stop Work
            </button>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #f8f9fa !important;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-success text-white rounded-pill px-3 py-1.5" style="font-size: 13px;">
                {{ $totalLeads }} Total Interested leads
            </span>
            <a href="{{ route('leads.start-work.interested-leads.export') }}" class="btn btn-outline-success btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5" style="border-radius: 20px;">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i> Download Excel (XLS)
            </a>
        </div>
        
        @if($leads->count() > 0)
            <div class="text-secondary" style="font-size: 12px; font-weight: 500;">
                <i class="bi bi-info-circle me-1 text-warning"></i>
                You can log calls or view details directly.
            </div>
        @endif
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Contact Number</th>
                    <th>Room</th>
                    <th>Next Follow Up</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    @php
                        $isFirstUncalled = $loop->first;
                    @endphp
                    <tr @if($isFirstUncalled) style="background: rgba(25, 135, 84, 0.03); border-left: 4px solid #198754 !important;" @endif>
                        <td>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="fw-semibold text-dark">{{ $lead->client_name }}</div>
                            </div>
                            @if($lead->client_email)
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $lead->client_email }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded-circle" style="width: 26px; height: 26px;">
                                <i class="bi bi-telephone-fill" style="font-size: 11px;"></i>
                            </span>
                            <span class="text-muted font-monospace ms-2" style="font-size: 14px; letter-spacing: 0.1em;">**********</span>
                        </td>
                        <td>
                            @if($lead->room)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold">
                                    <i class="bi bi-door-open-fill me-1"></i>{{ $lead->room->name }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary fw-semibold">
                                    Direct Lead
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($lead->follow_up_date)
                                <span class="{{ \Carbon\Carbon::parse($lead->follow_up_date)->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-danger fw-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Interested</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                @if($session && $session->status === 'paused')
                                    <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1.5 opacity-50" title="Resume session to Log Call" disabled>
                                        <i class="bi bi-telephone-outbound"></i> Log Call
                                    </button>

                                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1.5" title="Log Call" 
                                        data-bs-toggle="modal" data-bs-target="#logCallModal" 
                                        data-bs-action="{{ route('leads.calls.store', $lead) }}"
                                        data-bs-client-name="{{ $lead->client_name }}"
                                        data-bs-client-phone="{{ $lead->client_phone ?? '—' }}">
                                        <i class="bi bi-telephone-outbound"></i> Log Call
                                    </button>

                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Pause session to view details" disabled>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-star text-secondary opacity-50" style="font-size: 32px;"></i>
                            <div class="mt-2">No interested leads found!</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leads->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $leads->links() }}
        </div>
    @endif
</div>

@include('leads.modals')

<!-- Confirm Stop Work Modal -->
<div class="modal fade" id="confirmStopWorkModal" tabindex="-1" aria-labelledby="confirmStopWorkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="confirmStopWorkModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Stop Work Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="mb-0 text-dark fw-semibold fs-6">Are you sure you want to stop your today work?</p>
                <small class="text-secondary d-block mt-2" style="font-size: 13px;">This will generate your daily call report PDF, email it to your administrator, and mark your session as completed.</small>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('leads.start-work.stop') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Yes, Stop Work
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const counterEl = document.getElementById('leads-timer-counter');
        
        @if(session('active_room_work'))
            const status = "{{ session('active_room_work')['status'] ?? '' }}";
            const accumulatedSeconds = parseInt("{{ session('active_room_work')['accumulated_seconds'] ?? 0 }}");
            const startTimeStr = "{{ session('active_room_work')['started_at'] ?? '' }}";
            
            if (status === 'active' && startTimeStr && counterEl) {
                const startTime = new Date(startTimeStr).getTime();
                
                const updateTimer = () => {
                    const now = new Date().getTime();
                    const diffSeconds = Math.floor((now - startTime) / 1000);
                    const totalSec = accumulatedSeconds + (diffSeconds > 0 ? diffSeconds : 0);
                    
                    const hours = Math.floor(totalSec / 3600);
                    const minutes = Math.floor((totalSec % 3600) / 60);
                    const seconds = totalSec % 60;
                    
                    counterEl.textContent = [
                        String(hours).padStart(2, '0'),
                        String(minutes).padStart(2, '0'),
                        String(seconds).padStart(2, '0')
                    ].join(':');
                };
                
                updateTimer();
                setInterval(updateTimer, 1000);
            } else if (status === 'paused' && counterEl) {
                const totalSec = accumulatedSeconds;
                const hours = Math.floor(totalSec / 3600);
                const minutes = Math.floor((totalSec % 3600) / 60);
                const seconds = totalSec % 60;
                
                counterEl.textContent = [
                    String(hours).padStart(2, '0'),
                    String(minutes).padStart(2, '0'),
                    String(seconds).padStart(2, '0')
                ].join(':');
            }
        @endif
    });
</script>
@endpush

@push('styles')
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .hover-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }
    .shadow-md {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08) !important;
    }
    @keyframes bellRing {
        0%, 100% { transform: rotate(0); }
        20%, 60% { transform: rotate(12deg); }
        40%, 80% { transform: rotate(-12deg); }
    }
    .animate-bell {
        display: inline-block;
        animation: bellRing 3s ease infinite;
    }
</style>
@endpush
