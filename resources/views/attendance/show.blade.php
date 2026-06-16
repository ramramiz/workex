@extends('layouts.app')

@section('title', 'Attendance Details')
@section('page-title', 'Attendance Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Check-in Details</h5>
                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Adjust</a>
                @endif
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $attendance->user->avatar_url }}" alt="" class="avatar-circle" style="width: 48px; height: 48px;">
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $attendance->user->name }}</h5>
                        <small class="text-muted">{{ $attendance->user->role->name ?? 'Developer' }}</small>
                    </div>
                </div>

                <hr>

                <div class="mb-3 row">
                    <div class="col-6">
                        <small class="text-muted d-block">Log Date</small>
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y (l)') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        @if($attendance->status === 'present')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                        @elseif($attendance->status === 'absent')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                        @elseif($attendance->status === 'late')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Late</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Half Day</span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-6">
                        <small class="text-muted d-block">In-Time</small>
                        <span class="fw-semibold text-dark">{{ $attendance->login_time ? \Carbon\Carbon::parse($attendance->login_time)->format('h:i A') : '—' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Out-Time</small>
                        <span class="fw-semibold text-dark">{{ $attendance->logout_time ? \Carbon\Carbon::parse($attendance->logout_time)->format('h:i A') : 'Active' }}</span>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-6">
                        <small class="text-muted d-block">Total Working Hours</small>
                        <span class="fw-semibold text-primary">
                            @if($attendance->total_minutes)
                                {{ number_format($attendance->total_minutes / 60, 2) }} hrs
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Late Duration</small>
                        <span class="fw-semibold {{ $attendance->late_minutes > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' mins' : 'None' }}
                        </span>
                    </div>
                </div>

                @if($attendance->notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Adjustment Notes</small>
                        <p class="text-muted fs-7 mb-0 mt-1">{{ $attendance->notes }}</p>
                    </div>
                @endif
                
                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                    <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
