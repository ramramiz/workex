@extends('layouts.app')

@section('title', 'Attendance Report Sheet')
@section('page-title', 'Attendance Report Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Attendance Sheets: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('reports.attendance') }}" class="row g-3">
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
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Total Mins</th>
                    <th>Late Mins</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $rec->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                <span class="fs-7 fw-semibold">{{ $rec->user->name }}</span>
                            </div>
                        </td>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</td>
                        <td>{{ $rec->login_time ? \Carbon\Carbon::parse($rec->login_time)->format('h:i A') : '—' }}</td>
                        <td>{{ $rec->logout_time ? \Carbon\Carbon::parse($rec->logout_time)->format('h:i A') : 'Active' }}</td>
                        <td>{{ $rec->total_minutes ?? '—' }} mins</td>
                        <td class="{{ $rec->late_minutes > 0 ? 'text-danger fw-bold' : '' }}">{{ $rec->late_minutes ?? 0 }} mins</td>
                        <td>
                            @if($rec->status === 'present')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                            @elseif($rec->status === 'absent')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                            @elseif($rec->status === 'late')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Late</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Half Day</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 32px;"></i>
                            <div class="mt-2">No attendance logs found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
