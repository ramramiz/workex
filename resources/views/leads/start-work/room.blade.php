@extends('layouts.app')

@section('title', 'Start Work - Room Setup')
@section('page-title', 'Start Work Room')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.start-work.index') }}">Start Work</a></li>
    <li class="breadcrumb-item active">{{ $room->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border shadow shadow-lg border-0" style="border-radius: 20px; overflow: hidden; background: linear-gradient(145deg, #ffffff, #f8fafc);">
            <div class="bg-warning text-dark px-4 py-4 text-center border-bottom border-warning-subtle" style="background: linear-gradient(135deg, #ffc107, #ffb300) !important;">
                <div class="fs-1 mb-2 text-dark"><i class="bi bi-play-circle-fill"></i></div>
                <h4 class="fw-bold mb-0">Start Work Session</h4>
                <p class="mb-0 text-dark opacity-75 fs-7.5">Confirm details and click Start Work to initialize session tracker</p>
            </div>
            
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-person-fill text-secondary me-2"></i>Employee Name</span>
                        <span class="fw-bold text-dark">{{ auth()->user()->name }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-door-open-fill text-warning me-2"></i>Selected Room</span>
                        <span class="fw-bold text-dark">{{ $room->name }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-calendar-check-fill text-primary me-2"></i>Current Date</span>
                        <span class="fw-bold text-dark">{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border">
                        <span class="text-secondary fw-medium fs-7.5"><i class="bi bi-clock-fill text-success me-2"></i>Current Time</span>
                        <span class="fw-bold text-success font-monospace" id="live-session-clock">00:00:00</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('leads.start-work.start', $room) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100 fw-bold py-3 text-dark d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; font-size: 16px; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.25);">
                        <i class="bi bi-play-fill fs-5"></i> Start Work Day
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clockEl = document.getElementById('live-session-clock');
        const updateClock = () => {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        };
        updateClock();
        setInterval(updateClock, 1000);
    });
</script>
@endpush
@endsection
