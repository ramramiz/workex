@extends('layouts.app')
@section('title', 'HR Dashboard')
@section('page-title', 'HR Dashboard')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">HR Dashboard</h1><p class="page-subtitle">{{ now()->format('l, d F Y') }}</p></div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:32px;font-weight:800;color:#6366f1;">{{ $stats['total_employees'] }}</div><div style="font-size:13px;color:#64748b;">Total Employees</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:32px;font-weight:800;color:#10b981;">{{ $stats['present_today'] }}</div><div style="font-size:13px;color:#64748b;">Present Today</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:32px;font-weight:800;color:#ef4444;">{{ $stats['absent_today'] }}</div><div style="font-size:13px;color:#64748b;">Absent Today</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:32px;font-weight:800;color:#f59e0b;">{{ $stats['pending_leaves'] }}</div><div style="font-size:13px;color:#64748b;">Pending Leaves</div></div></div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between"><span>Pending Leave Requests</span><a href="{{ route('leaves.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
    <div class="card-body p-0">
        @forelse($pendingLeaves as $leave)
        <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
            <img src="{{ $leave->user->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
            <div class="flex-grow-1">
                <div style="font-size:13px;font-weight:600;">{{ $leave->user->name }}</div>
                <div style="font-size:12px;color:#64748b;">{{ ucfirst($leave->leave_type) }} — {{ $leave->from_date->format('d M') }} to {{ $leave->to_date->format('d M Y') }} ({{ $leave->total_days }} days)</div>
            </div>
            <a href="{{ route('leaves.show', $leave) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;">Review</a>
        </div>
        @empty
        <div class="text-center py-5 text-muted"><i class="bi bi-check-circle text-success" style="font-size:36px;"></i><div class="mt-2">No pending leave requests!</div></div>
        @endforelse
    </div>
</div>
@endsection
