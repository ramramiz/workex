@extends('layouts.app')

@section('title', 'Support / AMC')
@section('page-title', 'Support / AMC')

@section('breadcrumb')
    <li class="breadcrumb-item active">Support Tickets</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Support & AMC Board</h5>
        <a href="{{ route('support.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-headset me-1"></i> Open Ticket
        </a>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('support.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
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
                    <th>Ticket #</th>
                    <th>Ticket Title</th>
                    <th>Client & Project</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary font-monospace">{{ $ticket->ticket_number }}</div>
                            <small class="text-muted d-block" style="font-size:11px;">Created: {{ $ticket->created_at->format('d M Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                <a href="{{ route('support.show', $ticket) }}" class="text-decoration-none text-dark">{{ $ticket->title }}</a>
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $ticket->client->company_name ?? '—' }}</span>
                            <small class="text-muted d-block" style="font-size:11px;">Project: {{ $ticket->project->name ?? '—' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $ticket->priority === 'critical' || $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $ticket->priority === 'critical' || $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'secondary') }} border border-{{ $ticket->priority === 'critical' || $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-capitalize fs-8">
                                {{ $ticket->priority }}
                            </span>
                        </td>
                        <td>
                            @if($ticket->assignedTo)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $ticket->assignedTo->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $ticket->assignedTo->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->status === 'open')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Open</span>
                            @elseif($ticket->status === 'in_progress')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">In Progress</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Closed</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('support.show', $ticket) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                    <a href="{{ route('support.edit', $ticket) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('support.destroy', $ticket) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-headset" style="font-size: 32px;"></i>
                            <div class="mt-2">No support tickets found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $tickets->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
