@extends('layouts.app')

@section('title', 'Daily Work Report')
@section('page-title', 'Daily Work Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Daily Work</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Work Sessions: {{ $date->format('d M Y') }}</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('reports.daily-work') }}" class="row g-3">
            <div class="col-12 col-md-8">
                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
            </div>
            <div class="col-12 col-md-4 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter Date</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Shift Started</th>
                    <th>Shift Ended</th>
                    <th>Total Hours</th>
                    <th>Productive Hours</th>
                    <th>Tasks Worked On</th>
                    <th>Work Completed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $session->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                <span class="fs-7 fw-semibold">{{ $session->user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $session->started_at ? \Carbon\Carbon::parse($session->started_at)->timezone('Asia/Kolkata')->format('h:i A') : '—' }}</td>
                        <td>{{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->timezone('Asia/Kolkata')->format('h:i A') : 'Active' }}</td>
                        <td>{{ number_format($session->total_minutes / 60, 2) }} hrs</td>
                        <td>
                            <span class="text-success fw-bold">{{ number_format($session->productive_minutes / 60, 2) }} hrs</span>
                        </td>
                        <td>
                            @php
                                $tasks = $session->timeLogs->pluck('task.title')->unique();
                            @endphp
                            @forelse($tasks as $t)
                                <div class="fs-8 text-dark">• {{ $t }}</div>
                            @empty
                                <span class="text-muted fs-8">No tasks logged</span>
                            @endforelse
                        </td>
                        <td>
                            @if($session->work_done)
                                <div class="fs-8 text-wrap text-muted" style="max-width: 250px; white-space: pre-line;">{{ $session->work_done }}</div>
                            @else
                                <span class="text-muted fs-8">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 32px;"></i>
                            <div class="mt-2">No work sessions logged for this date.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
