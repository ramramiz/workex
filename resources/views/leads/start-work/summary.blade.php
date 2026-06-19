@extends('layouts.app')

@section('title', 'Session Work Summary')
@section('page-title', 'Work Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.start-work.index') }}">Start Work</a></li>
    <li class="breadcrumb-item active">Session Summary</li>
@endsection

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border shadow shadow-lg border-0 mb-4" style="border-radius: 20px; overflow: hidden; background: linear-gradient(145deg, #ffffff, #f8fafc);">
            
            @if($session->status === 'approved')
                <div class="bg-success text-white px-4 py-4 text-center border-bottom border-success-subtle" style="background: linear-gradient(135deg, #10b981, #059669) !important;">
                    <div class="fs-1 mb-2 text-white"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="fw-bold mb-0">Session Approved</h4>
                    <p class="mb-0 text-white opacity-75 fs-7.5">This work session has been approved by the Administrator</p>
                </div>
            @elseif($session->status === 'rejected')
                <div class="bg-danger text-white px-4 py-4 text-center border-bottom border-danger-subtle" style="background: linear-gradient(135deg, #ef4444, #dc2626) !important;">
                    <div class="fs-1 mb-2 text-white"><i class="bi bi-x-circle-fill"></i></div>
                    <h4 class="fw-bold mb-0">Session Rejected</h4>
                    <p class="mb-0 text-white opacity-75 fs-7.5">This work session has been rejected by the Administrator</p>
                </div>
            @else
                <div class="bg-warning text-dark px-4 py-4 text-center border-bottom border-warning-subtle" style="background: linear-gradient(135deg, #ffc107, #ffb300) !important;">
                    <div class="fs-1 mb-2 text-dark"><i class="bi bi-hourglass-split"></i></div>
                    <h4 class="fw-bold mb-0">Session Pending Approval</h4>
                    <p class="mb-0 text-dark opacity-75 fs-7.5">Your calling session metrics have been submitted for review</p>
                </div>
            @endif
            
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-person-fill text-secondary me-2"></i>Telecaller Name</span>
                        <span class="fw-bold text-dark">{{ $session->user->name }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-door-open-fill text-warning me-2"></i>Lead Room</span>
                        <span class="fw-bold text-dark">{{ $room->name ?? 'N/A' }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-clock-fill text-primary me-2"></i>Total Work Duration</span>
                        @php
                            $totalSeconds = $session->total_seconds;
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            
                            $formattedDuration = '';
                            if ($hours > 0) $formattedDuration .= $hours . 'h ';
                            if ($minutes > 0) $formattedDuration .= $minutes . 'm ';
                            $formattedDuration .= $seconds . 's';
                        @endphp
                        <span class="fw-extrabold text-primary font-monospace">{{ $formattedDuration }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-telephone-outbound-fill text-dark me-2"></i>Total Calls Logged</span>
                        <span class="fw-bold text-dark">{{ $totalCalls }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-telephone-fill text-success me-2"></i>Connected Calls</span>
                        <span class="fw-bold text-success">{{ $connectedCalls }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-star-fill text-warning me-2"></i>Interested Leads</span>
                        <span class="fw-bold text-warning">{{ $interestedCount }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-telephone-x-fill text-danger me-2"></i>Not Connected / Busy</span>
                        <span class="fw-bold text-danger">{{ $notConnectedCalls }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-arrow-up-right-circle-fill text-info me-2"></i>Leads Converted</span>
                        <span class="fw-bold text-info">{{ $session->converted_count }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-calendar-range-fill text-secondary me-2"></i>Time Window</span>
                        <span class="fw-semibold text-muted text-end fs-8">
                            {{ $session->started_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }} <br>to<br> 
                            {{ $session->ended_at ? $session->ended_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'N/A' }}
                        </span>
                    </div>

                    @if($session->status !== 'pending' && $session->approved_by)
                        <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                            <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-person-badge-fill text-secondary me-2"></i>Reviewed By</span>
                            <span class="fw-bold text-dark">{{ $session->approver->name ?? 'Admin' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                            <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-calendar-check-fill text-secondary me-2"></i>Reviewed At</span>
                            <span class="fw-bold text-dark">{{ $session->approved_at ? $session->approved_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') : 'N/A' }}</span>
                        </div>
                    @endif
                </div>

                <a href="{{ route('leads.start-work.index') }}" class="btn btn-outline-secondary w-100 fw-bold py-2.5 d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px;">
                    <i class="bi bi-arrow-left"></i> Back to Start Work Section
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
