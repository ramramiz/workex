@extends('layouts.app')
@section('title', 'Client Dashboard')
@section('page-title', 'Client Dashboard')
@section('content')
<div class="page-header"><div><h1 class="page-title">Welcome, {{ explode(' ', $user->name)[0] }}!</h1><p class="page-subtitle">Track your projects and payments</p></div></div>
<div class="row g-4">
    @forelse($projects as $project)
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-700">{{ $project->name }}</h5>
                <div class="d-flex justify-content-between mb-2"><span style="font-size:13px;color:#64748b;">Progress</span><span style="font-size:13px;font-weight:600;">{{ $project->progress_percentage }}%</span></div>
                <div class="progress mb-3" style="height:8px;border-radius:4px;"><div class="progress-bar" style="width:{{ $project->progress_percentage }}%;background:#6366f1;border-radius:4px;"></div></div>
                <div class="row text-center">
                    <div class="col"><div style="font-size:11px;color:#94a3b8;">Start</div><div style="font-size:13px;font-weight:600;">{{ $project->start_date?->format('d M Y') }}</div></div>
                    <div class="col"><div style="font-size:11px;color:#94a3b8;">Deadline</div><div style="font-size:13px;font-weight:600;">{{ $project->deadline?->format('d M Y') }}</div></div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted"><i class="bi bi-kanban" style="font-size:48px;"></i><div class="mt-3">No projects found</div></div>
    @endforelse
</div>
@endsection
