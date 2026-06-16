@extends('layouts.app')

@section('title', 'Adjust Attendance Log')
@section('page-title', 'Adjust Attendance Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Adjust</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Manual Attendance Adjustment</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 fs-7 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Adjusting logs directly overrides automatically recorded work timers.
                </div>

                <form method="POST" action="{{ route('attendance.update', $attendance) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Employee</label>
                        <input type="text" class="form-control bg-light fw-semibold" value="{{ $attendance->user->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Date</label>
                        <input type="text" class="form-control bg-light fw-semibold" value="{{ \Carbon\Carbon::parse($attendance->date)->format('d F Y (l)') }}" disabled>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Login Time</label>
                            <!-- Input type datetime-local or text? The database holds datetime, but in migration it might be time or datetime. Let's look at controller: login_time is parsed by Carbon, so we can pass datetime-local or Y-m-d H:i:s or time -->
                            <!-- Wait, since in database it is datetime or timestamp, let's prefill with format Y-m-d\TH:i -->
                            @php
                                $loginFormatted = $attendance->login_time ? \Carbon\Carbon::parse($attendance->login_time)->format('Y-m-d\TH:i') : '';
                            @endphp
                            <input type="datetime-local" name="login_time" class="form-control" value="{{ $loginFormatted }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Logout Time</label>
                            @php
                                $logoutFormatted = $attendance->logout_time ? \Carbon\Carbon::parse($attendance->logout_time)->format('Y-m-d\TH:i') : '';
                            @endphp
                            <input type="datetime-local" name="logout_time" class="form-control" value="{{ $logoutFormatted }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ $attendance->status === 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ $attendance->status === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Adjustment Remarks / Reason</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Reason for adjusting employee's logs...">{{ $attendance->notes }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
