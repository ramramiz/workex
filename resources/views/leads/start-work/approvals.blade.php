@extends('layouts.app')

@section('title', 'Room Work Approvals')
@section('page-title', 'Room Work Approvals')

@section('breadcrumb')
    <li class="breadcrumb-item active">Room Work Approvals</li>
@endsection

@section('content')
<div class="card mb-4 border border-primary shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-primary-subtle py-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background: rgba(99, 102, 241, 0.08) !important; border-bottom: 1px solid rgba(99, 102, 241, 0.2) !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-clipboard-check fs-5"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold text-dark">Pending Room Work Approvals</h5>
                <span class="text-secondary fs-8">Review and approve telecaller daily calling sessions</span>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Telecaller</th>
                    <th>Lead Room</th>
                    <th>Worked Duration</th>
                    <th>Time Window</th>
                    <th class="text-center">Calls</th>
                    <th class="text-center">Converted</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $session->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($session->user?->name ?? 'User') }}" alt="{{ $session->user?->name ?? 'User' }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark">{{ $session->user?->name ?? 'N/A' }}</div>
                                    <small class="text-muted d-block" style="font-size: 11px;">{{ $session->user?->email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $session->room?->name ?? "Today's Follow-ups" }}</div>
                        </td>
                        <td>
                            @php
                                $totalSeconds = $session->total_seconds;
                                $hours = floor($totalSeconds / 3600);
                                $minutes = floor(($totalSeconds % 3600) / 60);
                                $seconds = $totalSeconds % 60;
                                
                                $formattedDuration = '';
                                if ($hours > 0) $formattedDuration .= $hours . 'h ';
                                if ($minutes > 0) $formattedDuration .= $minutes . 'm ';
                                $formattedDuration .= $seconds . 's';
                            @endphp
                            <span class="fw-bold text-primary font-monospace">{{ $formattedDuration }}</span>
                        </td>
                        <td>
                            <div style="font-size: 12.5px;">
                                <span class="text-muted">Start:</span> {{ $session->started_at->timezone('Asia/Kolkata')->format('d M, h:i A') }}
                            </div>
                            <div style="font-size: 12.5px;">
                                <span class="text-muted">End:</span> {{ $session->ended_at ? $session->ended_at->timezone('Asia/Kolkata')->format('d M, h:i A') : 'N/A' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 border rounded-pill fw-semibold">{{ $session->calls_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success px-2.5 py-1 border border-success-subtle rounded-pill fw-semibold">{{ $session->converted_count }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 align-items-center">
                                <a href="{{ route('leads.start-work.summary', [$session->lead_room_id ?: 0, $session->id]) }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-file-earmark-bar-graph"></i> View Report
                                </a>
                                <a href="{{ route('leads.start-work.download-report', $session->id) }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1">
                                    <i class="bi bi-file-earmark-pdf"></i> Download PDF
                                </a>
                                <form method="POST" action="{{ route('admin.telecaller-sessions.approve', $session) }}" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.telecaller-sessions.reject', $session) }}" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clipboard-x text-secondary opacity-50" style="font-size: 32px;"></i>
                            <div class="mt-2">No pending work sessions to approve.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sessions->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection
