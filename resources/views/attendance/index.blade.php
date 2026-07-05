@extends('layouts.app')

@section('title', 'Attendance Logs')
@section('page-title', 'Attendance Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Time Attendance Sheets</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <a href="{{ route('attendance.report') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Monthly Summary Report
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('attendance.index') }}" class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 text-muted mb-1">Select Month</label>
                <select name="month" class="form-select form-select-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 text-muted mb-1">Select Year</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            @if(auth()->user()->isLeaderOrAbove() || auth()->user()->isHR())
                <div class="col-12 col-md-3">
                    <label class="form-label fs-8 text-muted mb-1">Select Employee</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-3 d-grid align-items-end">
                <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Filter Sheet</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    @if(auth()->user()->isLeaderOrAbove() || auth()->user()->isHR())
                        <th>Employee</th>
                    @endif
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Working Hours</th>
                    <th>Late mins</th>
                    <th>Status</th>
                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                        <th class="text-end">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                    <tr>
                        <td class="fw-semibold">
                            {{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}
                            <small class="text-muted d-block" style="font-size:11px;">{{ \Carbon\Carbon::parse($rec->date)->format('l') }}</small>
                        </td>
                        @if(auth()->user()->isLeaderOrAbove() || auth()->user()->isHR())
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $rec->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $rec->user->name }}</span>
                                </div>
                            </td>
                        @endif
                        @php
                            $recDateStr = ($rec->date instanceof \Carbon\Carbon) ? $rec->date->format('Y-m-d') : \Carbon\Carbon::parse($rec->date)->format('Y-m-d');
                            $taskKey    = ($rec->user_id ?? 0) . '_' . $recDateStr;
                            $taskIn     = $taskInOut[$taskKey]['in']  ?? null;
                            $taskOut    = $taskInOut[$taskKey]['out'] ?? null;
                            $inDisplay  = $taskIn  ? $taskIn->format('h:i A')  : ($rec->login_time  ? \Carbon\Carbon::parse($rec->login_time)->format('h:i A')  : '—');
                            $outDisplay = $taskOut ? $taskOut->format('h:i A') : ($rec->logout_time ? \Carbon\Carbon::parse($rec->logout_time)->format('h:i A') : ($rec->status === 'present' || $rec->status === 'late' ? 'Active' : '—'));
                        @endphp
                        <td>{{ $inDisplay }}</td>
                        <td>{{ $outDisplay }}</td>
                        <td>
                            @if($rec->total_minutes)
                                <span class="fw-medium">{{ number_format($rec->total_minutes / 60, 2) }} hrs</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($rec->late_minutes > 0)
                                <span class="text-danger fw-semibold">{{ $rec->late_minutes }} mins</span>
                            @else
                                <span class="text-success">—</span>
                            @endif
                        </td>
                        <td>
                            @if($rec->status === 'present')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                            @elseif($rec->status === 'absent')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                            @elseif($rec->status === 'late')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Late</span>
                            @elseif($rec->status === 'on_leave')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">On Leave</span>
                            @elseif($rec->status === 'holiday')
                                <span class="badge bg-success text-white border border-success"><i class="bi bi-pin-angle-fill me-1"></i>Holiday</span>
                            @elseif($rec->status === 'weekly_off')
                                <span class="badge bg-secondary text-white border border-secondary">Weekly Off</span>
                            @elseif($rec->status === 'pending')
                                <span class="badge bg-light text-muted border border-secondary-subtle">Not Started</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Half Day</span>
                            @endif

                            @if($rec->status === 'holiday' && !empty($rec->notes))
                                <div class="text-success small fw-bold mt-1" style="font-size: 0.75rem;">{{ $rec->notes }}</div>
                            @elseif(!empty($rec->notes) && $rec->status !== 'weekly_off' && $rec->notes !== 'Absent')
                                <div class="text-muted small mt-1" style="font-size: 0.75rem;">{{ $rec->notes }}</div>
                            @endif
                        </td>
                        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                            <td class="text-end">
                                <a href="{{ route('attendance.edit', $rec) }}" class="btn btn-outline-primary btn-sm" title="Edit Log">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 32px;"></i>
                            <div class="mt-2">No attendance logs recorded for this month.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($records->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $records->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
