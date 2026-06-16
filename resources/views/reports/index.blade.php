@extends('layouts.app')

@section('title', 'Reports Center')
@section('page-title', 'Reports Center')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Daily Work Session -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-calendar2-week fs-3"></i>
                </div>
                <h5 class="fw-bold">Daily Work Report</h5>
                <p class="text-muted fs-7 mb-4">View daily logs of employees' check-in and check-out work sessions.</p>
                <a href="{{ route('reports.daily-work') }}" class="btn btn-outline-primary btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>

    <!-- Project Progress -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-success-subtle text-success border border-success-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-kanban fs-3"></i>
                </div>
                <h5 class="fw-bold">Project Progress</h5>
                <p class="text-muted fs-7 mb-4">Audit active projects, task completion, and pending tasks progress.</p>
                <a href="{{ route('reports.project-progress') }}" class="btn btn-outline-success btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>

    <!-- Attendance -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-warning-subtle text-warning border border-warning-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-calendar-check fs-3"></i>
                </div>
                <h5 class="fw-bold">Attendance Sheets</h5>
                <p class="text-muted fs-7 mb-4">Check monthly attendance, present counts, and late login metrics.</p>
                <a href="{{ route('reports.attendance') }}" class="btn btn-outline-warning btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>

    <!-- Leaves -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-info-subtle text-info border border-info-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-calendar2-range fs-3"></i>
                </div>
                <h5 class="fw-bold">Leaves Breakdown</h5>
                <p class="text-muted fs-7 mb-4">Track leave balances, approved requests, and leave history log.</p>
                <a href="{{ route('reports.leaves') }}" class="btn btn-outline-info btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>

    <!-- Payments -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-secondary-subtle text-secondary border border-secondary-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <h5 class="fw-bold">Payments Received</h5>
                <p class="text-muted fs-7 mb-4">Sum of all payments collected from invoice clearing entries.</p>
                <a href="{{ route('reports.payments') }}" class="btn btn-outline-secondary btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>

    <!-- Profit & Loss -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 text-center py-4 px-3 border border-light">
            <div class="card-body">
                <div class="bg-danger-subtle text-danger border border-danger-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
                <h5 class="fw-bold">Profit & Loss</h5>
                <p class="text-muted fs-7 mb-4">Analyze income against office and project expenditures metrics.</p>
                <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-danger btn-sm w-100">View Report</a>
            </div>
        </div>
    </div>
</div>
@endsection
