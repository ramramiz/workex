@extends('layouts.app')

@section('title', 'Project Progress Report')
@section('page-title', 'Project Progress Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Project Progress</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Project Progress Overview</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Project Name & Client</th>
                    <th>Team Leader</th>
                    <th>Tasks (Completed/Total)</th>
                    <th>Progress bar</th>
                    <th>Deadline</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    @php
                        $total = $p->tasks->count();
                        $completed = $p->tasks->where('status', 'completed')->count();
                        $pct = $p->progress_percentage;
                        $overdue = $p->deadline && $p->deadline->isPast() && !in_array($p->status, ['completed', 'delivered', 'cancelled']);
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <small class="text-muted d-block" style="font-size: 11px;">Client: {{ $p->client->company_name ?? 'Internal' }}</small>
                        </td>
                        <td>
                            @if($p->teamLeader)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $p->teamLeader->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $p->teamLeader->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $completed }}</span> / <span class="text-muted">{{ $total }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="min-width: 140px;">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="fs-8 fw-semibold">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="{{ $overdue ? 'text-danger fw-bold' : '' }}">
                                {{ $p->deadline ? $p->deadline->format('d M Y') : '—' }}
                                @if($overdue)
                                    <small class="d-block text-danger font-monospace" style="font-size: 10px;">(Overdue)</small>
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-capitalize fs-8">
                                {{ str_replace('_', ' ', $p->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-kanban" style="font-size: 32px;"></i>
                            <div class="mt-2">No projects recorded.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
