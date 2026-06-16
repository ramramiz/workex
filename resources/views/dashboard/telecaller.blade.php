@extends('layouts.app')

@section('title', 'Telecaller Dashboard')
@section('page-title', 'Telecaller Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Total Leads -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-primary-subtle text-primary rounded-3 p-3 fs-3">
                <i class="bi bi-funnel-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Total Leads Assigned</div>
                <h3 class="mb-0 fw-bold">{{ $stats['total_leads'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Calls Completed -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-success-subtle text-success rounded-3 p-3 fs-3">
                <i class="bi bi-telephone-outbound-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Calls Completed</div>
                <h3 class="mb-0 fw-bold">{{ $stats['calls_completed'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Pending Calls -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-warning-subtle text-warning rounded-3 p-3 fs-3">
                <i class="bi bi-telephone-x-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Pending Calls</div>
                <h3 class="mb-0 fw-bold">{{ $stats['pending_calls'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Follow-up Calls Today -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-info-subtle text-info rounded-3 p-3 fs-3">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Follow-up Today</div>
                <h3 class="mb-0 fw-bold">{{ $stats['follow_ups_today'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Converted Leads -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-success-subtle text-success rounded-3 p-3 fs-3">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Converted Leads</div>
                <h3 class="mb-0 fw-bold">{{ $stats['converted_leads'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Failed/Missed Calls -->
    <div class="col-12 col-sm-6 col-xxl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="bg-danger-subtle text-danger rounded-3 p-3 fs-3">
                <i class="bi bi-telephone-minus-fill"></i>
            </div>
            <div>
                <div class="text-secondary fs-7 fw-medium mb-1">Failed/Missed Calls</div>
                <h3 class="mb-0 fw-bold">{{ $stats['failed_calls'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upcoming Follow-Ups -->
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>Upcoming Follow-ups</h5>
                <a href="{{ route('leads.index', ['status' => 'following_up']) }}" class="btn btn-link btn-sm text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Client Name</th>
                                <th>Next Follow Up</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingFollowUps as $followUp)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="{{ route('leads.show', $followUp->lead_id) }}" class="text-decoration-none text-dark">
                                                {{ $followUp->lead->client_name }}
                                            </a>
                                        </div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $followUp->note }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $followUp->next_follow_up->format('d M Y') }}</div>
                                        @if($followUp->follow_up_time)
                                            <small class="text-muted">{{ Carbon\Carbon::parse($followUp->follow_up_time)->format('h:i A') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ ucfirst($followUp->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-check fs-2"></i>
                                        <div class="mt-2 text-secondary">No pending follow-ups scheduled.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Calls Logged -->
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-telephone-outbound me-2 text-success"></i>Recent Call Logs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Client Name</th>
                                <th>Call Time</th>
                                <th>Call Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCalls as $call)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <a href="{{ route('leads.show', $call->lead_id) }}" class="text-decoration-none text-dark">
                                                {{ $call->lead->client_name }}
                                            </a>
                                        </div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ $call->remarks }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $call->call_date_time->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $call->call_date_time->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($call->status === 'Connected')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Connected</span>
                                        @elseif($call->status === 'Busy')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Busy</span>
                                        @elseif($call->status === 'Not Connected')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No Connection</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Switched Off</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-telephone-x fs-2"></i>
                                        <div class="mt-2 text-secondary">No calls logged yet.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
