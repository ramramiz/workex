@extends('layouts.app')

@section('title', 'Telecaller Performance Report')
@section('page-title', 'Telecaller Performance Report')

@section('breadcrumb')
    @if($isManager)
        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    @endif
    <li class="breadcrumb-item active">Telecaller Performance</li>
@endsection

@section('content')
<!-- Filter bar -->
<div class="card border border-light shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.telecaller-performance') }}" class="row g-3 align-items-end">
            @if($isManager)
                <div class="col-12 col-md-4">
                    <label class="form-label fs-7 fw-semibold text-secondary">Select Telecaller</label>
                    <select name="telecaller_id" class="form-select form-select-sm">
                        @foreach($telecallers as $tc)
                            <option value="{{ $tc->id }}" {{ $telecaller->id == $tc->id ? 'selected' : '' }}>
                                {{ $tc->name }} ({{ $tc->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->toDateString() }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->toDateString() }}">
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Performance Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Total Leads -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Leads Assigned</span>
                    <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-funnel fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-primary mb-2">{{ $totalLeads }}</h2>
                <p class="text-muted fs-7 mb-0">Leads assigned in selected date range</p>
            </div>
        </div>
    </div>

    <!-- Calls Logged -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Calls Logged</span>
                    <div class="bg-success-subtle text-success border border-success-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-telephone-outbound fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-success mb-2">{{ $callsCount }}</h2>
                <p class="text-muted fs-7 mb-0">Completed call activities</p>
            </div>
        </div>
    </div>

    <!-- Conversion Rate -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Conversion Rate</span>
                    <div class="bg-info-subtle text-info border border-info-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-percent fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-info mb-2">{{ $conversionRate }}%</h2>
                <div class="progress mb-1" style="height: 6px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $conversionRate }}%"></div>
                </div>
                <p class="text-muted fs-7 mb-0">{{ $convertedCount }} Leads Converted</p>
            </div>
        </div>
    </div>

    <!-- Follow Up Rate -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Follow-up Completion</span>
                    <div class="bg-warning-subtle text-warning border border-warning-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-check2-circle fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-bold text-warning mb-2">{{ $followUpCompletionRate }}%</h2>
                <div class="progress mb-1" style="height: 6px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $followUpCompletionRate }}%"></div>
                </div>
                <p class="text-muted fs-7 mb-0">{{ $completedFollowUps }} / {{ $totalFollowUps }} Follow-ups done</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Additional statuses -->
    <div class="col-12 col-sm-6">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success rounded-3 p-3 fs-3">
                    <i class="bi bi-bookmark-star"></i>
                </div>
                <div>
                    <div class="text-secondary fs-7 fw-medium mb-1">Interested Leads</div>
                    <h3 class="mb-0 fw-bold">{{ $interestedCount }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning-subtle text-warning rounded-3 p-3 fs-3">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <div class="text-secondary fs-7 fw-medium mb-1">Pending Leads</div>
                    <h3 class="mb-0 fw-bold">{{ $pendingLeadsCount }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calls Logging breakdown -->
<div class="card border border-light shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Call Volume Activity</h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th style="width: 250px;">Date</th>
                    <th>Calls Completed</th>
                    <th style="width: 40%;">Activity Density</th>
                </tr>
            </thead>
            <tbody>
                @forelse($callsPerDay as $cpd)
                    @php
                        $maxCalls = $callsPerDay->max('count') ?: 1;
                        $percentage = ($cpd->count / $maxCalls) * 100;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($cpd->date)->format('d M Y') }}</td>
                        <td class="fw-bold">{{ $cpd->count }} calls</td>
                        <td>
                            <div class="progress" style="height: 12px; border-radius: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" title="{{ $cpd->count }} calls"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="bi bi-telephone-x fs-3 text-secondary"></i>
                            <div class="mt-2">No calls logged during this period.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
