@extends('layouts.app')
@section('title', 'Team Leader Dashboard')
@section('page-title', 'Team Leader Dashboard')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Welcome, {{ explode(' ', $user->name)[0] }}! 👋</h1>
        <p class="page-subtitle">Manage your team and projects — {{ now()->format('l, d F Y') }}</p>
    </div>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Task</a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:32px;font-weight:800;color:#6366f1;">{{ $myProjects->count() }}</div>
            <div style="font-size:13px;color:#64748b;">My Projects</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:32px;font-weight:800;color:#10b981;">{{ $teamMembers->count() }}</div>
            <div style="font-size:13px;color:#64748b;">Team Members</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:32px;font-weight:800;color:#f59e0b;">{{ $pendingReports->count() }}</div>
            <div style="font-size:13px;color:#64748b;">Pending Reports</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div style="font-size:32px;font-weight:800;color:#ef4444;">{{ $pendingLeaves->count() }}</div>
            <div style="font-size:13px;color:#64748b;">Pending Leaves</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Team Status -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Team Status Today</div>
            <div class="card-body p-0">
                @forelse($teamMembers as $member)
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                    <img src="{{ $member->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;">{{ $member->name }}</div>
                        <div style="font-size:12px;color:#94a3b8;">{{ $member->employee?->designation?->name ?? 'Developer' }}</div>
                    </div>
                    @if($member->todayWorkSession && $member->todayWorkSession->status === 'active')
                        <span class="badge" style="background:#d1fae5;color:#065f46;"><span class="status-dot working me-1"></span>Working</span>
                    @else
                        <span class="badge" style="background:#f1f5f9;color:#64748b;">Not Started</span>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-muted">No team members assigned yet</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pending Reports -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Pending Reports to Review</span>
                <a href="{{ route('daily-reports.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($pendingReports as $report)
                <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                    <img src="{{ $report->user->avatar_url }}" class="rounded-circle" width="34" height="34" alt="">
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;">{{ $report->user->name }}</div>
                        <div style="font-size:12px;color:#94a3b8;">{{ $report->date->format('d M Y') }}</div>
                    </div>
                    <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;">Review</a>
                </div>
                @empty
                <div class="text-center py-4 text-success"><i class="bi bi-check-circle" style="font-size:28px;"></i><div class="mt-1" style="font-size:13px;">All reports reviewed!</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
