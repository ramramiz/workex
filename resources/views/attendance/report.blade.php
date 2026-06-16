@extends('layouts.app')

@section('title', 'Attendance Monthly Summary')
@section('page-title', 'Attendance Monthly Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Monthly Report</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Monthly Breakdown: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h5>
        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Sheet</a>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('attendance.report') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <select name="month" class="form-select form-select-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-4">
                <select name="year" class="form-select form-select-sm">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter Summary</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th class="text-center">Days Present</th>
                    <th class="text-center">Late Entries</th>
                    <th class="text-center">Half Days</th>
                    <th class="text-center">Days Absent</th>
                    <th class="text-end">Total Mins Logged</th>
                    <th class="text-end">Total Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $userId => $userRecords)
                    @php
                        $user = $userRecords->first()->user;
                        $present = $userRecords->where('status', 'present')->count();
                        $late = $userRecords->where('status', 'late')->count();
                        $halfDay = $userRecords->where('status', 'half_day')->count();
                        $absent = $userRecords->where('status', 'absent')->count();
                        $totalMinutes = $userRecords->sum('total_minutes');
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $user->avatar_url }}" alt="" class="avatar-circle" style="width: 28px; height: 28px;">
                                <div>
                                    <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                    <small class="text-muted d-block" style="font-size:11px;">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-bold text-success">{{ $present }}</td>
                        <td class="text-center fw-bold text-warning">{{ $late }}</td>
                        <td class="text-center fw-bold text-info">{{ $halfDay }}</td>
                        <td class="text-center fw-bold text-danger">{{ $absent }}</td>
                        <td class="text-end font-monospace">{{ number_format($totalMinutes) }} m</td>
                        <td class="text-end fw-bold text-primary">{{ number_format($totalMinutes / 60, 2) }} h</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart" style="font-size: 32px;"></i>
                            <div class="mt-2">No summary records found for this period.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
