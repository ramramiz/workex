@extends('layouts.app')

@section('title', $room->name . ' - Leads list')
@section('page-title', $room->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.start-work.index') }}">Start Work</a></li>
    <li class="breadcrumb-item active">{{ $room->name }}</li>
@endsection

@section('content')
<div class="card mb-4 border border-warning shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-warning-subtle py-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: rgba(255, 193, 7, 0.08) !important; border-bottom: 1px solid rgba(255, 193, 7, 0.2) !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-broadcast fs-5"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">{{ $room->name }}</h5>
                <span class="text-secondary fs-8">Calling active room leads</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Timer Display -->
            @if(session('active_room_work') && session('active_room_work')['room_id'] == $room->id)
                @if((session('active_room_work')['status'] ?? '') === 'active')
                    <div class="d-flex align-items-center gap-2 bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill border border-warning-subtle" id="leads-timer-badge">
                        <span class="status-dot working animate-pulse" style="background: #d97706; width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        <span class="fw-semibold font-monospace" id="leads-timer-counter">00:00:00</span>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 bg-secondary-subtle text-secondary-emphasis px-3 py-1.5 rounded-pill border border-secondary" id="leads-timer-badge">
                        <span class="status-dot paused" style="background: #6b7280; width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                        <span class="fw-semibold font-monospace" id="leads-timer-counter">00:00:00</span>
                        <span class="badge bg-secondary text-white font-monospace ms-1" style="font-size: 10px; padding: 2px 6px;">Paused</span>
                    </div>
                @endif
            @endif

            <!-- Pause / Resume Work Form -->
            @if($session && $session->status === 'active')
                <form method="POST" action="{{ route('leads.start-work.pause', $room) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5 text-dark" style="border-radius: 20px;">
                        <i class="bi bi-pause-circle-fill"></i> Pause Work
                    </button>
                </form>
            @elseif($session && $session->status === 'paused')
                <form method="POST" action="{{ route('leads.start-work.resume', $room) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5 text-white" style="border-radius: 20px;">
                        <i class="bi bi-play-circle-fill"></i> Resume Work
                    </button>
                </form>
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

    <!-- Tabs Navigation -->
    <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #f8f9fa !important;">
        <ul class="nav nav-pills" id="leadWorkTabs" style="gap: 8px;">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'uncalled') === 'uncalled' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                   href="{{ route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'uncalled']) }}"
                   style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                    <i class="bi bi-telephone"></i>
                    Uncalled Leads
                    <span class="badge {{ ($tab ?? 'uncalled') === 'uncalled' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                        {{ $uncalledCount ?? 0 }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'uncalled') === 'not_connected' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                   href="{{ route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'not_connected']) }}"
                   style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                    <i class="bi bi-telephone-x"></i>
                    Not Connected Leads
                    <span class="badge {{ ($tab ?? 'uncalled') === 'not_connected' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                        {{ $notConnectedCount ?? 0 }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'uncalled') === 'called' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                   href="{{ route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'called']) }}"
                   style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                    <i class="bi bi-telephone-outbound"></i>
                    Called Leads
                    <span class="badge {{ ($tab ?? 'uncalled') === 'called' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                        {{ $calledCount ?? 0 }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'uncalled') === 'interested' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                   href="{{ route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'interested']) }}"
                   style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                    <i class="bi bi-star"></i>
                    Interested
                    <span class="badge {{ ($tab ?? 'uncalled') === 'interested' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                        {{ $interestedCount ?? 0 }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'uncalled') === 'today_follow_up' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                   href="{{ route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'today_follow_up']) }}"
                   style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                    @if(($todayFollowUpCount ?? 0) > 0)
                        <i class="bi bi-bell-fill text-danger animate-pulse"></i>
                    @else
                        <i class="bi bi-calendar-check"></i>
                    @endif
                    Today Follow-ups
                    <span class="badge {{ ($tab ?? 'uncalled') === 'today_follow_up' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                        {{ $todayFollowUpCount ?? 0 }}
                    </span>
                </a>
            </li>
        </ul>
        
        @if(($tab ?? 'uncalled') === 'uncalled' && $leads->count() > 0)
            <div class="text-secondary" style="font-size: 12px; font-weight: 500;">
                <i class="bi bi-info-circle me-1 text-warning"></i>
                The first row is styled for your next call.
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
                    <th>Next Follow Up</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    @php
                        $isFirstUncalled = (($tab ?? 'uncalled') === 'uncalled' && $loop->first);
                    @endphp
                    <tr @if($isFirstUncalled) style="background: rgba(255, 193, 7, 0.04); border-left: 4px solid #ffc107 !important;" @endif>
                        <td>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="fw-semibold text-dark">{{ $lead->client_name }}</div>
                                @if($isFirstUncalled)
                                    <span class="badge bg-warning text-dark fw-extrabold" style="font-size: 10px; letter-spacing: 0.05em; padding: 3px 8px; border-radius: 4px;">
                                        <i class="bi bi-star-fill me-1 animate-pulse"></i> NEXT CALL
                                    </span>
                                @endif
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
                            @if($lead->follow_up_date)
                                <span class="{{ \Carbon\Carbon::parse($lead->follow_up_date)->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-danger fw-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($lead->status === 'new')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">New Lead</span>
                            @elseif($lead->status === 'following_up')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Following Up</span>
                            @elseif($lead->status === 'interested')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Interested</span>
                            @elseif($lead->status === 'not_interested')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Not Interested</span>
                            @elseif($lead->status === 'call_back_later')
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Call Back Later</span>
                            @elseif($lead->status === 'follow_up_required')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Follow-up Required</span>
                            @elseif($lead->status === 'converted')
                                <span class="badge bg-success text-white">Converted</span>
                            @elseif($lead->status === 'closed')
                                <span class="badge bg-dark text-white">Closed</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                            @endif
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
                                    @if($isFirstUncalled)
                                        <button type="button" class="btn btn-warning btn-sm d-flex align-items-center gap-1.5 fw-bold text-dark shadow-sm" style="border-radius: 4px;" title="Log Call" 
                                            data-bs-toggle="modal" data-bs-target="#logCallModal" 
                                            data-bs-action="{{ route('leads.calls.store', $lead) }}"
                                            data-bs-client-name="{{ $lead->client_name }}"
                                            data-bs-client-phone="{{ $lead->client_phone ?? '—' }}">
                                            <i class="bi bi-telephone-outbound-fill"></i> Start Next Call
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1.5" title="Log Call" 
                                            data-bs-toggle="modal" data-bs-target="#logCallModal" 
                                            data-bs-action="{{ route('leads.calls.store', $lead) }}"
                                            data-bs-client-name="{{ $lead->client_name }}"
                                            data-bs-client-phone="{{ $lead->client_phone ?? '—' }}">
                                            <i class="bi bi-telephone-outbound"></i> Log Call
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-outline-secondary btn-sm" title="Pause session to view details" disabled>
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-funnel text-secondary opacity-50" style="font-size: 32px;"></i>
                            <div class="mt-2">No {{ str_replace('_', ' ', $tab ?? 'uncalled') }} leads found in this room.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leads->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $leads->appends(['tab' => $tab ?? 'uncalled'])->links() }}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const counterEl = document.getElementById('leads-timer-counter');
        
        @if(session('active_room_work') && session('active_room_work')['room_id'] == $room->id)
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
</style>
@endpush
@endsection
