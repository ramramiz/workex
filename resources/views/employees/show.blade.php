@extends('layouts.app')

@section('title', 'Employee Profile')
@section('page-title', 'Employee Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: User details card -->
    <div class="col-12 col-lg-4">
        <div class="card text-center mb-4">
            <div class="card-body">
                <img src="{{ $employee->user->avatar_url }}" alt="{{ $employee->name }}" class="avatar-circle mb-3 border p-1" style="width: 100px; height: 100px;">
                <h4>{{ $employee->name }}</h4>
                <p class="text-muted mb-2">{{ $employee->designation->name ?? 'N/A' }}</p>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-3">{{ $employee->department->name ?? 'N/A' }}</span>
                
                <hr>
                
                <div class="text-start">
                    <div class="mb-3">
                        <small class="text-muted d-block">Employee Code</small>
                        <span class="fw-semibold">{{ $employee->employee_code }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-semibold">{{ $employee->user->email }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-semibold">{{ $employee->phone ?? $employee->user->phone ?? '—' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Role</small>
                        <span class="fw-semibold">{{ $employee->user->role->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Financial & Status</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Salary Details</small>
                        @if($employee->salary)
                            <span class="fw-semibold text-success">₹{{ number_format($employee->salary, 2) }}</span>
                            <span class="text-muted fs-7">/ {{ $employee->salary_type }}</span>
                        @else
                            <span class="text-muted">Not Configured</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Joining Date</small>
                        <span class="fw-semibold">{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status</small>
                        @if($employee->status === 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Tabs -->
    <div class="col-12 col-lg-8">
        <!-- Stats Widgets -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-primary">{{ number_format($employee->user->workSessions->sum('total_minutes') / 60, 1) }}</div>
                    <div class="text-muted fs-7">Total Hours</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-success">{{ $employee->user->workSessions->count() }}</div>
                    <div class="text-muted fs-7">Work Sessions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-warning">{{ $employee->user->leaves->where('status', 'approved')->count() }}</div>
                    <div class="text-muted fs-7">Leaves Taken</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-info">{{ $employee->user->dailyReports->count() }}</div>
                    <div class="text-muted fs-7">Daily Reports</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0 px-3" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3" id="work-sessions-tab" data-bs-toggle="tab" data-bs-target="#work-sessions" type="button" role="tab">Work Sessions</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button" role="tab">Leaves</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">Daily Reports</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabsContent">
                    <!-- Work Sessions -->
                    <div class="tab-pane fade show active" id="work-sessions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Login</th>
                                        <th>Logout</th>
                                        <th>Total Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->workSessions as $session)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                                            <td>{{ $session->started_at ? \Carbon\Carbon::parse($session->started_at)->format('h:i A') : '—' }}</td>
                                            <td>{{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->format('h:i A') : 'Active' }}</td>
                                            <td>{{ number_format($session->total_minutes / 60, 2) }} hrs</td>
                                            <td>
                                                <span class="badge bg-{{ $session->ended_at ? 'secondary' : 'success' }}-subtle text-{{ $session->ended_at ? 'secondary' : 'success' }} border border-{{ $session->ended_at ? 'secondary' : 'success' }}-subtle">
                                                    {{ $session->ended_at ? 'Completed' : 'Active' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No work sessions recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Leaves -->
                    <div class="tab-pane fade" id="leaves" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->leaves as $leave)
                                        <tr>
                                            <td>{{ ucwords(str_replace('_', ' ', $leave->leave_type)) }}</td>
                                            <td>
                                                {{ $leave->from_date->format('d M') }} to {{ $leave->to_date->format('d M Y') }}
                                                <small class="text-muted d-block">({{ $leave->from_date->diffInDays($leave->to_date) + 1 }} days)</small>
                                            </td>
                                            <td>{{ Str::limit($leave->reason, 40) }}</td>
                                            <td>
                                                @if($leave->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                                @elseif($leave->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No leaves recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Daily Reports -->
                    <div class="tab-pane fade" id="reports" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Completed Work</th>
                                        <th>Git Link</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->dailyReports as $report)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</td>
                                            <td>{{ Str::limit($report->completed_work, 40) }}</td>
                                            <td>
                                                @if($report->git_link)
                                                    <a href="{{ $report->git_link }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 150px;">{{ $report->git_link }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($report->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                                @elseif($report->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No daily reports submitted.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
