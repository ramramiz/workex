@extends('layouts.app')

@section('title', 'Start Today Work - Select Room')
@section('page-title', 'Start Today Work')

@section('breadcrumb')
    <li class="breadcrumb-item active">Start Work</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-10">
        <div class="mb-4 text-center py-4">
            <h3 class="fw-bold text-dark mb-2">Select Your Assigned Work Room</h3>
            <p class="text-secondary fs-6">Select a lead room assigned to you to start your telecalling work session.</p>
        </div>

        <div class="row g-4">
            @forelse($rooms as $room)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-sm hover-shadow-sm transition-all" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body p-4 d-flex flex-direction-column justify-content-between" style="height: 100%; display: flex; flex-direction: column;">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                        <i class="bi bi-door-open-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0">{{ $room->name }}</h5>
                                        <span class="badge bg-secondary-subtle text-secondary mt-1">{{ $room->leads_count }} Leads</span>
                                    </div>
                                </div>
                                <p class="text-secondary fs-7.5 mb-4">{{ Str::limit($room->description ?? 'No description provided for this room.', 120) }}</p>
                            </div>
                            <div class="mt-auto">
                                <a href="{{ route('leads.start-work.room', $room) }}" class="btn btn-warning w-100 fw-bold d-flex align-items-center justify-content-center gap-2 py-2 text-dark" style="border-radius: 10px;">
                                    Select Room <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border p-5 text-center bg-white" style="border-radius: 16px;">
                        <div class="fs-1 text-muted mb-3">
                            <i class="bi bi-door-closed text-secondary opacity-50" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">No Rooms Assigned</h5>
                        <p class="text-secondary mb-0">You don't have any lead rooms assigned to you today. Please contact your team leader or administrator.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .hover-shadow-sm:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
        border-color: #ffc107 !important;
    }
</style>
@endsection
