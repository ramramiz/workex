@extends('layouts.app')

@section('title', 'Start Today Work - Select Room')
@section('page-title', 'Select Work Room')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.start-work.select-customer') }}">Select Customer</a></li>
    <li class="breadcrumb-item active">Select Room</li>
@endsection

@section('content')
<div class="row justify-content-center">
        <!-- Change Customer Action -->
        <div class="d-flex justify-content-end mb-3 mt-n2">
            <a href="{{ route('leads.start-work.select-customer') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" style="border-radius: 8px;">
                <i class="bi bi-arrow-left"></i> Change Customer
            </a>
        </div>

        <!-- Top Menu Stats Cards -->
        <div class="row g-3 mb-5 justify-content-center">
            <!-- Today's Follow-up -->
            <div class="col-12 col-md-4">
                <a href="{{ route('leads.start-work.select-followups', ['client_id' => $selectedClientId]) }}" class="text-decoration-none">
                    <div class="card h-100 border shadow-sm hover-stat-card" style="border-radius: 16px; background: #fff; border-color: rgba(239, 68, 68, 0.25) !important; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                                <i class="bi bi-bell-fill fs-4 animate-bell"></i>
                            </div>
                            <div>
                                <div class="text-secondary fw-semibold" style="font-size: 13px;">Today's Follow-up</div>
                                <h4 class="mb-0 fw-bold text-dark mt-1" style="font-size: 24px;">{{ count($todayFollowUps) }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Interested Leads -->
            <div class="col-12 col-md-4">
                <a href="{{ route('leads.start-work.interested-leads', ['client_id' => $selectedClientId]) }}" class="text-decoration-none">
                    <div class="card h-100 border shadow-sm hover-stat-card" style="border-radius: 16px; background: #fff; border-color: rgba(16, 185, 129, 0.25) !important; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                                <i class="bi bi-star-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="text-secondary fw-semibold" style="font-size: 13px;">Interested Leads</div>
                                <h4 class="mb-0 fw-bold text-dark mt-1" style="font-size: 24px;">{{ $interestedCount ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
 
            <!-- Not Connected Calls -->
            <div class="col-12 col-md-4">
                <a href="{{ route('leads.start-work.not-connected-leads', ['client_id' => $selectedClientId]) }}" class="text-decoration-none">
                    <div class="card h-100 border shadow-sm hover-stat-card" style="border-radius: 16px; background: #fff; border-color: rgba(245, 158, 11, 0.25) !important; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px; background-color: #fef3c7 !important; color: #d97706 !important;">
                                <i class="bi bi-telephone-x-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="text-secondary fw-semibold" style="font-size: 13px;">Not Connected Calls</div>
                                <h4 class="mb-0 fw-bold text-dark mt-1" style="font-size: 24px;">{{ $notConnectedCalls ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="text-center border-top pt-4 pb-0 mb-3">
            <h3 class="fw-bold text-dark mb-2">Select Your Assigned Work Room</h3>
            <p class="text-secondary fs-6 mb-0">Select a lead room assigned to you to start your telecalling work session.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($rooms as $room)
                <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                    <div class="card h-100 border shadow-xs hover-shadow-sm transition-all" style="border-radius: 16px; border-color: rgba(99, 102, 241, 0.15) !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                        <i class="bi bi-door-open-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 16px;">
                                            {{ $room->name }}
                                            @if(($room->leads_count > 0) && ($room->user_calls_count ?? 0) == 0)
                                                <span class="new-flash-badge ms-2">New</span>
                                            @endif
                                        </h5>
                                        <span class="badge bg-secondary-subtle text-secondary mt-1" style="font-size: 10px;">{{ $room->leads_count }} Leads</span>
                                    </div>
                                </div>
                                @if($room->client)
                                    <div class="mb-3 text-secondary" style="font-size: 13px;">
                                        <i class="bi bi-person-circle me-1"></i> {{ $room->client->company_name }}
                                    </div>
                                @endif
                                <p class="text-secondary fs-8 mb-4">{{ Str::limit($room->description ?? 'No description provided for this room.', 120) }}</p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('leads.start-work.select-room-join', $room) }}" class="btn btn-warning w-100 fw-bold btn-sm d-flex align-items-center justify-content-center gap-1.5 py-2.5 text-dark" style="border-radius: 10px;">
                                    Select Room <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 col-lg-8">
                    <div class="card border p-5 text-center bg-white" style="border-radius: 16px;">
                        <div class="fs-1 text-muted mb-3">
                            <i class="bi bi-door-closed text-secondary opacity-50" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">No Rooms Assigned</h5>
                        <p class="text-secondary mb-0">You don't have any calling rooms assigned to you today. Please contact your team leader or administrator.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .hover-shadow-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05) !important;
    }
    .hover-client-header:hover {
        background-color: #f8fafc !important;
    }
    .hover-client-header:not(.collapsed) .collapse-indicator {
        transform: rotate(180deg);
    }
    .collapse-indicator {
        transition: transform 0.25s ease;
    }
    .hover-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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
    @keyframes flash-badge {
        0%, 100% {
            background-color: #ef4444;
            color: #ffffff;
            box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
        }
        50% {
            background-color: #f59e0b;
            color: #ffffff;
            box-shadow: 0 0 6px rgba(245, 158, 11, 0.6);
        }
    }
    .new-flash-badge {
        animation: flash-badge 0.8s infinite;
        font-size: 10px;
        font-weight: 800;
        padding: 1.5px 5px;
        border-radius: 4px;
        letter-spacing: 0.5px;
        display: inline-block;
        vertical-align: middle;
        text-transform: uppercase;
        border: 1px solid rgba(255, 255, 255, 0.15);
        line-height: 1.2;
    }
</style>
@endsection
