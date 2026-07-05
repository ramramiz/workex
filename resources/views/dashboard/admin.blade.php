@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
/* ========================
   DASHBOARD PREMIUM STYLES
   ======================== */

/* Animated greeting banner */
.dash-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 70%, #1e1b4b 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(99,102,241,0.2);
}
.dash-hero::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%);
    top: -100px; right: -60px;
    border-radius: 50%;
}
.dash-hero::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);
    bottom: -60px; right: 200px;
    border-radius: 50%;
}
.dash-hero-title {
    font-size: 26px; font-weight: 800; color: #fff; margin: 0 0 4px;
    position: relative; z-index: 1;
}
.dash-hero-subtitle {
    font-size: 14px; color: #94a3b8; margin: 0;
    position: relative; z-index: 1;
}
.dash-hero-actions { position: relative; z-index: 1; }

/* KPI metric cards */
.kpi-card {
    background: var(--card-bg);
    border-radius: 18px;
    padding: 22px 24px 18px;
    border: 1px solid var(--border-color);
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    cursor: default;
}
.kpi-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, transparent 50%, rgba(255,255,255,0.03) 100%);
    pointer-events: none;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.1);
    border-color: rgba(99,102,241,0.25);
}
.kpi-card-accent {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 18px 18px 0 0;
}
.kpi-icon-wrap {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.kpi-value {
    font-size: 32px; font-weight: 800; line-height: 1; letter-spacing: -1px;
    margin: 12px 0 4px;
}
.kpi-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; }
.kpi-sub {
    font-size: 12px; font-weight: 600; margin-top: 10px;
    display: flex; align-items: center; gap: 4px;
}

/* Alert banner */
.alert-strip {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px; border-radius: 14px;
    border: 1px solid;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}
.alert-strip:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }

/* Section cards */
.dash-section-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 18px;
    overflow: hidden;
}
.dash-section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 22px;
    border-bottom: 1px solid var(--border-color);
    font-weight: 700; font-size: 15px;
}
.dash-section-header .section-icon {
    width: 34px; height: 34px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; margin-right: 10px; flex-shrink: 0;
}

/* Live status rows */
.live-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
}
.live-row:last-child { border-bottom: none; }
.live-row:hover { background: var(--body-bg); }

/* Project row */
.project-row {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border-color);
    text-decoration: none;
    color: inherit;
    transition: background 0.15s;
}
.project-row:last-child { border-bottom: none; }
.project-row:hover { background: var(--body-bg); }
.project-logo-thumb {
    width: 40px; height: 40px; border-radius: 10px;
    object-fit: contain; background: #f8fafc;
    border: 1px solid var(--border-color);
    padding: 4px; flex-shrink: 0;
}
.project-logo-thumb-fallback {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}

/* Report row */
.report-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 22px;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
}
.report-row:last-child { border-bottom: none; }
.report-row:hover { background: var(--body-bg); }

/* Status dot animation */
.status-dot-live {
    width: 9px; height: 9px; border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16,185,129,0.4);
    animation: pulse-live 1.8s infinite;
    flex-shrink: 0;
}
@keyframes pulse-live {
    0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.5); }
    70% { box-shadow: 0 0 0 7px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}

/* Skeleton number counter animation */
.kpi-value { animation: fadeInUp 0.5s both; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Avatar stack */
.avatar-xs {
    width: 28px; height: 28px; border-radius: 50%;
    border: 2px solid var(--card-bg); margin-left: -8px; object-fit: cover;
}
.avatar-xs:first-child { margin-left: 0; }

/* Dark mode adjustments */
[data-bs-theme="dark"] .kpi-card { background: #1e293b; border-color: #334155; }
[data-bs-theme="dark"] .kpi-card:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.4); }
[data-bs-theme="dark"] .dash-section-card { background: #1e293b; border-color: #334155; }
[data-bs-theme="dark"] .dash-section-header { border-color: #334155; }
[data-bs-theme="dark"] .live-row, [data-bs-theme="dark"] .project-row, [data-bs-theme="dark"] .report-row { border-color: #334155; }
[data-bs-theme="dark"] .live-row:hover, [data-bs-theme="dark"] .project-row:hover, [data-bs-theme="dark"] .report-row:hover { background: #0f172a; }
[data-bs-theme="dark"] .project-logo-thumb { background: #0f172a; border-color: #334155; }
[data-bs-theme="dark"] .dash-hero { border-color: rgba(99,102,241,0.3); }

/* Pending project cards */
.pending-proj-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.07);
    border-color: rgba(99,102,241,0.2) !important;
}
[data-bs-theme="dark"] .pending-proj-card { background: #0f172a !important; border-color: #334155 !important; }
[data-bs-theme="dark"] .pending-proj-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
</style>
@endpush

@section('content')

{{-- ===== HERO GREETING BANNER ===== --}}
<div class="dash-hero mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="dash-hero-title">
                @php
                    $h = now()->hour;
                    $greet = $h < 12 ? '🌅 Good Morning' : ($h < 17 ? '☀️ Good Afternoon' : '🌙 Good Evening');
                @endphp
                {{ $greet }}, {{ explode(' ', $user->name)[0] }}!
            </div>
            <p class="dash-hero-subtitle mt-1">
                Here's your workspace overview — {{ now()->format('l, d F Y') }}
            </p>

            <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 text-white" style="font-size:13px;">
                    <span class="status-dot-live"></span>
                    <span><strong class="text-success">{{ $stats['working_today'] }}</strong> <span style="color:#94a3b8">employees active right now</span></span>
                </div>
                @if($stats['delayed_projects'] > 0)
                <div class="badge" style="background:rgba(239,68,68,0.2);color:#f87171;border:1px solid rgba(239,68,68,0.3);font-size:12px;padding:6px 12px;border-radius:20px;font-weight:600;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $stats['delayed_projects'] }} projects delayed
                </div>
                @endif
            </div>
        </div>
        <div class="dash-hero-actions d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-2"
                style="background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.3);border-radius:10px;padding:8px 16px;font-weight:600;"
                data-bs-toggle="modal" data-bs-target="#currentWorksUpdateModal">
                <i class="bi bi-broadcast"></i> Team Update
            </button>
            <a href="{{ route('projects.previews') }}" class="btn btn-sm d-inline-flex align-items-center gap-2"
                style="background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);border-radius:10px;padding:8px 16px;font-weight:600;">
                <i class="bi bi-folder-check"></i> Check Projects
            </a>
            <a href="{{ route('chat.index') }}" class="btn btn-sm d-inline-flex align-items-center gap-2"
                style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3);border-radius:10px;padding:8px 16px;font-weight:600;">
                <i class="bi bi-chat-fill"></i> Chat Workspace
            </a>
            <a href="{{ route('mailbox.index') }}" class="btn btn-sm d-inline-flex align-items-center gap-2"
                style="background:rgba(255,255,255,0.07);color:#e2e8f0;border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:8px 16px;font-weight:600;">
                <i class="bi bi-envelope-fill"></i> Open mailbox
            </a>
        </div>
    </div>
</div>

{{-- ===== KPI METRIC CARDS ===== --}}
<div class="row g-3 mb-4">

    {{-- Employees --}}
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-card-accent" style="background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="kpi-icon-wrap" style="background:rgba(99,102,241,0.1);">
                    <i class="bi bi-people-fill" style="color:#6366f1;"></i>
                </div>
                <span class="badge rounded-pill" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:11px;padding:5px 10px;font-weight:600;">Active</span>
            </div>
            <div class="kpi-value" style="color:var(--text-primary);">{{ $stats['total_employees'] }}</div>
            <div class="kpi-label">Total Employees</div>
            <div class="kpi-sub" style="color:#10b981;">
                <span class="status-dot-live"></span> {{ $stats['working_today'] }} working now
            </div>
        </div>
    </div>

    {{-- Projects --}}
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-card-accent" style="background:linear-gradient(90deg,#2563eb,#0891b2);"></div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="kpi-icon-wrap" style="background:rgba(37,99,235,0.1);">
                    <i class="bi bi-kanban-fill" style="color:#2563eb;"></i>
                </div>
                @if($stats['delayed_projects'] > 0)
                    <span class="badge rounded-pill" style="background:rgba(239,68,68,0.12);color:#ef4444;font-size:11px;padding:5px 10px;font-weight:600;">{{ $stats['delayed_projects'] }} delayed</span>
                @else
                    <span class="badge rounded-pill" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:11px;padding:5px 10px;font-weight:600;">On track</span>
                @endif
            </div>
            <div class="kpi-value" style="color:var(--text-primary);">{{ $stats['total_projects'] }}</div>
            <div class="kpi-label">Total Projects</div>
            <div class="kpi-sub" style="color:#2563eb;">
                <i class="bi bi-arrow-up-right"></i> {{ $stats['active_projects'] }} active
                &nbsp;·&nbsp; <span style="color:#10b981;">{{ $stats['completed_projects'] }} completed</span>
            </div>
        </div>
    </div>

    {{-- Tasks --}}
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-card-accent" style="background:linear-gradient(90deg,#16a34a,#0d9488);"></div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="kpi-icon-wrap" style="background:rgba(22,163,74,0.1);">
                    <i class="bi bi-check2-square" style="color:#16a34a;"></i>
                </div>
                @if($stats['open_bugs'] > 0)
                    <span class="badge rounded-pill" style="background:rgba(239,68,68,0.12);color:#ef4444;font-size:11px;padding:5px 10px;font-weight:600;">{{ $stats['open_bugs'] }} bugs</span>
                @else
                    <span class="badge rounded-pill" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:11px;padding:5px 10px;font-weight:600;">Bug-free</span>
                @endif
            </div>
            <div class="kpi-value" style="color:var(--text-primary);">{{ $stats['pending_tasks'] }}</div>
            <div class="kpi-label">Pending Tasks</div>
            <div class="kpi-sub" style="color:#16a34a;">
                <i class="bi bi-check2-all"></i> {{ $stats['completed_tasks'] }} completed today
            </div>
        </div>
    </div>

    {{-- Tasks in Review --}}
    <div class="col-6 col-sm-6 col-lg-3">
        <a href="{{ route('chat.index') }}?filter=review" class="text-decoration-none" style="display:block;height:100%;">
            <div class="kpi-card" style="transition:transform 0.2s ease,box-shadow 0.2s ease;cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(234,88,12,0.12)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
                <div class="kpi-card-accent" style="background:linear-gradient(90deg,#ea580c,#f97316);"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="kpi-icon-wrap" style="background:rgba(234,88,12,0.1);">
                        <i class="bi bi-clock-history" style="color:#ea580c;"></i>
                    </div>
                    @if($stats['tasks_in_review'] > 0)
                        <span class="badge rounded-pill" style="background:rgba(234,88,12,0.12);color:#ea580c;font-size:11px;padding:5px 10px;font-weight:600;">Requires attention</span>
                    @else
                        <span class="badge rounded-pill" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:11px;padding:5px 10px;font-weight:600;">All clear</span>
                    @endif
                </div>
                <div class="kpi-value" style="color:var(--text-primary);">{{ $stats['tasks_in_review'] }}</div>
                <div class="kpi-label">Tasks in Review</div>
                <div class="kpi-sub" style="color:#ea580c;">
                    <i class="bi bi-arrow-right-short"></i> Click to view chats under review
                </div>
            </div>
        </a>
    </div>


</div>

{{-- ===== ALERT BANNERS ===== --}}
@if($stats['pending_leaves'] > 0 || $stats['pending_reports'] > 0 || $stats['delayed_projects'] > 0)
<div class="row g-3 mb-4">
    @if($stats['delayed_projects'] > 0)
    <div class="{{ ($stats['pending_leaves'] > 0 && $stats['pending_reports'] > 0) ? 'col-md-4' : (($stats['pending_leaves'] > 0 || $stats['pending_reports'] > 0) ? 'col-md-6' : 'col-12') }}">
        <a href="{{ route('projects.index', ['filter' => 'delayed']) }}" class="alert-strip" style="background:rgba(239,68,68,0.06);border-color:rgba(239,68,68,0.25);color:inherit;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(239,68,68,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:18px;color:#ef4444;"></i>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#ef4444;line-height:1;">{{ $stats['delayed_projects'] }}</div>
                <div style="font-size:12.5px;color:#ef4444;font-weight:600;margin-top:1px;">Delayed Projects</div>
                <div style="font-size:11px;color:var(--text-secondary);">Click to view all delayed</div>
            </div>
            <i class="bi bi-arrow-right ms-auto" style="color:#ef4444;font-size:16px;"></i>
        </a>
    </div>
    @endif
    @if($stats['pending_leaves'] > 0)
    <div class="{{ ($stats['delayed_projects'] > 0 && $stats['pending_reports'] > 0) ? 'col-md-4' : (($stats['delayed_projects'] > 0 || $stats['pending_reports'] > 0) ? 'col-md-6' : 'col-12') }}">
        <a href="{{ route('leaves.index', ['status' => 'pending']) }}" class="alert-strip" style="background:rgba(245,158,11,0.06);border-color:rgba(245,158,11,0.25);color:inherit;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(245,158,11,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-calendar-x-fill" style="font-size:18px;color:#f59e0b;"></i>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#f59e0b;line-height:1;">{{ $stats['pending_leaves'] }}</div>
                <div style="font-size:12.5px;color:#f59e0b;font-weight:600;margin-top:1px;">Leave Requests</div>
                <div style="font-size:11px;color:var(--text-secondary);">Awaiting your approval</div>
            </div>
            <i class="bi bi-arrow-right ms-auto" style="color:#f59e0b;font-size:16px;"></i>
        </a>
    </div>
    @endif
    @if($stats['pending_reports'] > 0)
    <div class="{{ ($stats['delayed_projects'] > 0 && $stats['pending_leaves'] > 0) ? 'col-md-4' : (($stats['delayed_projects'] > 0 || $stats['pending_leaves'] > 0) ? 'col-md-6' : 'col-12') }}">
        <a href="{{ route('daily-reports.index', ['status' => 'pending']) }}" class="alert-strip" style="background:rgba(99,102,241,0.06);border-color:rgba(99,102,241,0.25);color:inherit;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-journal-text" style="font-size:18px;color:#6366f1;"></i>
            </div>
            <div>
                <div style="font-size:20px;font-weight:800;color:#6366f1;line-height:1;">{{ $stats['pending_reports'] }}</div>
                <div style="font-size:12.5px;color:#6366f1;font-weight:600;margin-top:1px;">Daily Reports</div>
                <div style="font-size:11px;color:var(--text-secondary);">Pending review for today</div>
            </div>
            <i class="bi bi-arrow-right ms-auto" style="color:#6366f1;font-size:16px;"></i>
        </a>
    </div>
    @endif
</div>
@endif

{{-- ===== PENDING PROJECTS SECTION (full width) ===== --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon" style="background:rgba(245,158,11,0.1);">
                        <i class="bi bi-hourglass-split" style="color:#d97706;"></i>
                    </div>
                    <span>Pending Projects &amp; Progress Updates</span>
                    <span class="badge ms-1" style="background:rgba(245,158,11,0.12);color:#d97706;border-radius:20px;font-size:11px;padding:3px 10px;font-weight:700;">
                        {{ $recentProjects->whereNotIn('status', ['completed','delivered','cancelled'])->count() }} Active
                    </span>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-sm"
                    style="background:rgba(99,102,241,0.08);color:#6366f1;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:12px;font-weight:600;">
                    <i class="bi bi-arrow-up-right-square me-1"></i> View All Projects
                </a>
            </div>

            <div class="p-3">
                @php
                    $pendingProjects = $recentProjects->whereNotIn('status', ['completed','delivered','cancelled','completed_started_amc'])->values();
                    $statusColors = [
                        'not_started'   => ['bg'=>'rgba(100,116,139,0.12)', 'color'=>'#64748b', 'label'=>'Not Started'],
                        'planning'      => ['bg'=>'rgba(6,182,212,0.12)',   'color'=>'#0891b2', 'label'=>'Planning'],
                        'design'        => ['bg'=>'rgba(139,92,246,0.12)',  'color'=>'#7c3aed', 'label'=>'Design'],
                        'development'   => ['bg'=>'rgba(245,158,11,0.12)', 'color'=>'#d97706', 'label'=>'Development'],
                        'testing'       => ['bg'=>'rgba(249,115,22,0.12)', 'color'=>'#ea580c', 'label'=>'Testing'],
                        'client_review' => ['bg'=>'rgba(59,130,246,0.12)', 'color'=>'#2563eb', 'label'=>'Client Review'],
                        'rework'        => ['bg'=>'rgba(239,68,68,0.12)',  'color'=>'#dc2626', 'label'=>'Rework'],
                        'on_hold'       => ['bg'=>'rgba(245,158,11,0.12)', 'color'=>'#d97706', 'label'=>'On Hold'],
                        'completed_started_amc' => ['bg'=>'rgba(16,185,129,0.12)', 'color'=>'#059669', 'label'=>'Completed & Started AMC'],
                    ];
                    $fallbackColors = ['#6366f1','#2563eb','#16a34a','#ca8a04','#ef4444','#0891b2','#7c3aed','#e11d48'];
                    $fallbackBgs    = ['rgba(99,102,241,0.12)','rgba(37,99,235,0.12)','rgba(22,163,74,0.12)','rgba(202,138,4,0.12)','rgba(239,68,68,0.12)','rgba(6,182,212,0.12)','rgba(124,58,237,0.12)','rgba(225,29,72,0.12)'];
                @endphp

                @if($pendingProjects->isEmpty())
                    <div class="text-center py-4" style="color:var(--text-secondary);">
                        <div style="width:56px;height:56px;background:rgba(16,185,129,0.08);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="bi bi-check2-circle" style="font-size:26px;color:#10b981;"></i>
                        </div>
                        <div style="font-size:14px;font-weight:700;color:#10b981;">All projects completed!</div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">No pending projects at the moment</div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($pendingProjects->take(8) as $project)
                        @php
                            $sc = $statusColors[$project->status] ?? ['bg'=>'rgba(100,116,139,0.12)','color'=>'#64748b','label'=>ucfirst($project->status)];
                            $fi = ($project->id - 1) % count($fallbackColors);
                            $pct = $project->progress_percentage;
                            $barColor = $pct >= 75 ? '#10b981' : ($pct >= 50 ? '#6366f1' : ($pct >= 25 ? '#f59e0b' : '#ef4444'));
                            $daysLeft = $project->deadline ? now()->diffInDays($project->deadline, false) : null;
                        @endphp
                        <div class="col-12 col-md-6 col-xl-3">
                            <div style="background:var(--body-bg);border:1px solid var(--border-color);border-radius:14px;padding:16px;transition:all 0.2s;position:relative;overflow:hidden;"
                                 class="pending-proj-card">

                                {{-- Colored left accent --}}
                                <div style="position:absolute;top:0;left:0;bottom:0;width:3px;background:{{ $barColor }};border-radius:14px 0 0 14px;"></div>

                                {{-- Logo + Name --}}
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    @if($project->logo_path)
                                        <img src="{{ asset('storage/' . $project->logo_path) }}"
                                            alt="{{ $project->name }}"
                                            style="width:40px;height:40px;border-radius:10px;object-fit:contain;background:var(--card-bg);border:1px solid var(--border-color);padding:4px;flex-shrink:0;">
                                    @else
                                        <div style="width:40px;height:40px;border-radius:10px;background:{{ $fallbackBgs[$fi] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <span style="font-size:17px;font-weight:800;color:{{ $fallbackColors[$fi] }};">
                                                {{ strtoupper(substr($project->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="text-decoration-none fw-bold d-block"
                                            style="font-size:13.5px;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:130px;">
                                            {{ $project->name }}
                                        </a>
                                        <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border-radius:20px;font-size:10px;font-weight:700;padding:2px 9px;display:inline-block;margin-top:2px;">
                                            {{ $sc['label'] }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Progress Bar --}}
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span style="font-size:11px;color:var(--text-secondary);font-weight:600;">Progress</span>
                                        <span style="font-size:13px;font-weight:800;color:{{ $barColor }};">{{ $pct }}%</span>
                                    </div>
                                    <div style="height:7px;background:var(--border-color);border-radius:10px;overflow:hidden;">
                                        <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:10px;transition:width 1.2s ease;"></div>
                                    </div>
                                </div>

                                {{-- Deadline / Delayed info --}}
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    @if($project->is_delayed)
                                        <span style="font-size:11px;color:#ef4444;font-weight:700;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue
                                        </span>
                                    @elseif($daysLeft !== null && $daysLeft >= 0)
                                        <span style="font-size:11px;color:{{ $daysLeft <= 3 ? '#ef4444' : ($daysLeft <= 7 ? '#f59e0b' : 'var(--text-secondary)') }};font-weight:600;">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ $daysLeft === 0 ? 'Due today' : ($daysLeft === 1 ? '1 day left' : $daysLeft . ' days left') }}
                                        </span>
                                    @else
                                        <span style="font-size:11px;color:var(--text-secondary);">No deadline</span>
                                    @endif
                                    @if($project->teamLeader)
                                        <img src="{{ $project->teamLeader->avatar_url }}"
                                            class="rounded-circle"
                                            style="width:22px;height:22px;object-fit:cover;border:2px solid var(--card-bg);"
                                            title="{{ $project->teamLeader->name }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== AMC UPDATES SECTION (full width) ===== --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="section-icon" style="background:rgba(16,185,129,0.1);">
                        <i class="bi bi-shield-check" style="color:#10b981;"></i>
                    </div>
                    <span>AMC Renewals &amp; Updates (Near 20 Days)</span>
                    <span class="badge ms-1" style="background:rgba(16,185,129,0.12);color:#10b981;border-radius:20px;font-size:11px;padding:3px 10px;font-weight:700;">
                        {{ $upcomingAmcs->count() }} Near Renewal
                    </span>
                </div>
                <a href="{{ route('project-amcs.index') }}" class="btn btn-sm"
                    style="background:rgba(99,102,241,0.08);color:#6366f1;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:12px;font-weight:600;">
                    <i class="bi bi-arrow-up-right-square me-1"></i> View All AMCs
                </a>
            </div>

            <div class="p-3">
                @if($upcomingAmcs->isEmpty())
                    <div class="text-center py-4" style="color:var(--text-secondary);">
                        <div style="width:56px;height:56px;background:rgba(16,185,129,0.08);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="bi bi-shield-check" style="font-size:26px;color:#10b981;"></i>
                        </div>
                        <div style="font-size:14px;font-weight:700;color:#10b981;">No AMCs near renewal</div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">All active AMCs are safe and up to date</div>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($upcomingAmcs as $amc)
                        @php
                            $daysLeft = now()->startOfDay()->diffInDays($amc->end_date, false);
                            $badgeBg = $daysLeft <= 5 ? 'rgba(239,68,68,0.12)' : ($daysLeft <= 10 ? 'rgba(245,158,11,0.12)' : 'rgba(6,182,212,0.12)');
                            $badgeText = $daysLeft <= 5 ? '#ef4444' : ($daysLeft <= 10 ? '#d97706' : '#0891b2');
                        @endphp
                        <div class="col-12 col-md-6 col-xl-3">
                            <div style="background:var(--body-bg);border:1px solid var(--border-color);border-radius:14px;padding:16px;transition:all 0.2s;position:relative;overflow:hidden;"
                                 class="pending-proj-card">

                                <div style="position:absolute;top:0;left:0;bottom:0;width:3px;background:{{ $badgeText }};border-radius:14px 0 0 14px;"></div>

                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('projects.show', $amc->project) }}"
                                            class="text-decoration-none fw-bold d-block"
                                            style="font-size:13.5px;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;">
                                            {{ $amc->project->name }}
                                        </a>
                                        <span style="font-size:11px;color:var(--text-secondary);">
                                            {{ $amc->project->client?->company_name ?? 'Internal Project' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 10px;">AMC Amount</small>
                                        <strong style="font-size: 12.5px; color: var(--text-primary);">₹{{ number_format($amc->amount, 2) }}</strong>
                                    </div>
                                    <div class="text-end">
                                        <span style="background:{{ $badgeBg }};color:{{ $badgeText }};border-radius:20px;font-size:10px;font-weight:700;padding:2px 9px;display:inline-block;">
                                            @if($daysLeft < 0)
                                                Expired {{ abs($daysLeft) }} days ago
                                            @elseif($daysLeft == 0)
                                                Expires today
                                            @elseif($daysLeft == 1)
                                                1 day left
                                            @else
                                                {{ $daysLeft }} days left
                                            @endif
                                        </span>
                                        <small class="text-muted d-block mt-1" style="font-size: 10px;">Expires: {{ $amc->end_date->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== MAIN CONTENT ROW ===== --}}
<div class="row g-4">

    {{-- LEFT: Live Work Status --}}
    <div class="col-lg-7">
        <div class="dash-section-card h-100">
            <div class="dash-section-header">
                <div class="d-flex align-items-center">
                    <div class="section-icon" style="background:rgba(16,185,129,0.1);">
                        <i class="bi bi-broadcast" style="color:#10b981;"></i>
                    </div>
                    <span>Live Work Status</span>
                    <span class="ms-2 status-dot-live"></span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-1"
                        style="background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.25);border-radius:8px;font-size:12.5px;font-weight:600;"
                        data-bs-toggle="modal" data-bs-target="#currentWorksUpdateModal">
                        <i class="bi bi-broadcast"></i> Full Update
                    </button>
                    <a href="{{ route('live-status') }}" class="btn btn-sm"
                        style="background:rgba(99,102,241,0.08);color:#6366f1;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:12.5px;font-weight:600;">
                        Full Board
                    </a>
                </div>
            </div>

            <div style="max-height: 420px; overflow-y: auto;">
                @forelse($activeEmployees as $session)
                <div class="live-row">
                    <div class="position-relative flex-shrink-0">
                        <img src="{{ $session->user->avatar_url }}" class="rounded-circle" width="40" height="40"
                            style="object-fit:cover;border:2px solid var(--border-color);" alt="">
                        @if($session->timeLogs->isNotEmpty())
                            <span class="position-absolute bottom-0 end-0" style="width:11px;height:11px;background:#10b981;border-radius:50%;border:2px solid var(--card-bg);"></span>
                        @else
                            <span class="position-absolute bottom-0 end-0" style="width:11px;height:11px;background:#f59e0b;border-radius:50%;border:2px solid var(--card-bg);"></span>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div style="font-size:14px;font-weight:700;">{{ $session->user->name }}</div>
                        <div style="font-size:12px;color:var(--text-secondary);">
                            @if($session->activeTaskLog)
                                @php
                                    $taskDiff = max(0, now()->timestamp - $session->activeTaskLog->started_at->timestamp);
                                @endphp
                                <span style="color:#10b981;font-weight:600;">
                                    <i class="bi bi-play-circle-fill me-1"></i>{{ Str::limit($session->activeTaskLog->task->title ?? 'Working...', 36) }}
                                </span>
                                <span class="badge ms-1" style="background:rgba(16,185,129,0.12);color:#10b981;font-size:10px;border-radius:6px;padding:2px 7px;">
                                    <span class="running-timer-ticker" data-start="{{ $session->activeTaskLog->started_at->timestamp }}">
                                        {{ sprintf('%02d:%02d:%02d', ($taskDiff/3600), ($taskDiff/60)%60, $taskDiff%60) }}
                                    </span>
                                </span>
                            @else
                                <span style="color:#f59e0b;font-weight:500;"><i class="bi bi-clock me-1"></i>Active · No task running</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0" style="min-width: 90px; padding-right: 5px;">
                        @if($session->user->isSuperAdmin() || $session->user->isAdmin())
                            @if($session->activeTaskLog)
                                <div style="font-size:11px;font-weight:700;color:#10b981;line-height:1.2;">Started</div>
                                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ $session->activeTaskLog->started_at->format('h:i A') }}</div>
                            @else
                                <div style="font-size:11px;font-weight:700;color:#64748b;line-height:1.2;">Idle</div>
                                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">No active task</div>
                            @endif
                        @else
                            <div style="font-size:14px;font-weight:700;color:#10b981;">{{ $session->total_hours }}</div>
                            <div style="font-size:11px;color:var(--text-secondary);">worked</div>
                        @endif
                    </div>
                    <div>
                        @if($session->timeLogs->isNotEmpty())
                            <span style="background:rgba(16,185,129,0.12);color:#10b981;border-radius:20px;font-size:11px;font-weight:700;padding:4px 12px;white-space:nowrap;">Working</span>
                        @else
                            <span style="background:rgba(245,158,11,0.12);color:#f59e0b;border-radius:20px;font-size:11px;font-weight:700;padding:4px 12px;">Idle</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:var(--text-secondary);">
                    <div style="width:60px;height:60px;background:var(--body-bg);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="bi bi-people" style="font-size:28px;color:#94a3b8;"></i>
                    </div>
                    <div style="font-size:14px;font-weight:600;">No employees working right now</div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Active sessions will appear here</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-5">

        {{-- Recent Projects --}}
        <div class="dash-section-card mb-4">
            <div class="dash-section-header">
                <div class="d-flex align-items-center">
                    <div class="section-icon" style="background:rgba(37,99,235,0.1);">
                        <i class="bi bi-kanban" style="color:#2563eb;"></i>
                    </div>
                    <span>Recent Projects</span>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-sm"
                    style="background:rgba(99,102,241,0.08);color:#6366f1;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:12px;font-weight:600;">
                    View All
                </a>
            </div>

            @forelse($recentProjects->whereNotIn('status', ['completed', 'delivered', 'completed_started_amc'])->take(5) as $project)
            @php
                $statusColors = [
                    'not_started'   => ['bg'=>'rgba(100,116,139,0.12)', 'color'=>'#64748b'],
                    'planning'      => ['bg'=>'rgba(6,182,212,0.12)',    'color'=>'#0891b2'],
                    'design'        => ['bg'=>'rgba(139,92,246,0.12)',   'color'=>'#7c3aed'],
                    'development'   => ['bg'=>'rgba(245,158,11,0.12)',   'color'=>'#d97706'],
                    'testing'       => ['bg'=>'rgba(249,115,22,0.12)',   'color'=>'#ea580c'],
                    'client_review' => ['bg'=>'rgba(59,130,246,0.12)',   'color'=>'#2563eb'],
                    'rework'        => ['bg'=>'rgba(239,68,68,0.12)',    'color'=>'#dc2626'],
                    'completed'     => ['bg'=>'rgba(16,185,129,0.12)',   'color'=>'#059669'],
                    'delivered'     => ['bg'=>'rgba(16,185,129,0.12)',   'color'=>'#059669'],
                    'on_hold'       => ['bg'=>'rgba(245,158,11,0.12)',   'color'=>'#d97706'],
                    'cancelled'     => ['bg'=>'rgba(239,68,68,0.12)',    'color'=>'#dc2626'],
                    'completed_started_amc' => ['bg'=>'rgba(16,185,129,0.12)', 'color'=>'#059669'],
                ];
                $sc = $statusColors[$project->status] ?? ['bg'=>'rgba(100,116,139,0.12)','color'=>'#64748b'];
                $fallbackColors = ['#6366f1','#2563eb','#16a34a','#ca8a04','#ef4444','#0891b2','#7c3aed'];
                $fallbackBgs   = ['rgba(99,102,241,0.12)','rgba(37,99,235,0.12)','rgba(22,163,74,0.12)','rgba(202,138,4,0.12)','rgba(239,68,68,0.12)','rgba(6,182,212,0.12)','rgba(124,58,237,0.12)'];
                $fi = ($project->id - 1) % 7;
            @endphp
            <a href="{{ route('projects.show', $project) }}" class="project-row">
                {{-- Logo --}}
                @if($project->logo_path)
                    <img src="{{ asset('storage/' . $project->logo_path) }}" alt="{{ $project->name }}"
                        class="project-logo-thumb">
                @else
                    <div class="project-logo-thumb-fallback" style="background:{{ $fallbackBgs[$fi] }};">
                        <span style="font-size:16px;font-weight:800;color:{{ $fallbackColors[$fi] }};">
                            {{ strtoupper(substr($project->name, 0, 1)) }}
                        </span>
                    </div>
                @endif

                {{-- Info --}}
                <div class="flex-grow-1 min-w-0" style="padding-right: 8px;">
                    <div style="font-size:12px;font-weight:700;line-height:1.35;margin-bottom:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;word-break:break-word;">
                        {{ $project->name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);">
                        {{ $project->client?->company_name ?? 'Internal Project' }}
                    </div>
                </div>

                {{-- Status + deadline --}}
                <div class="text-end flex-shrink-0">
                    <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border-radius:20px;font-size:10.5px;font-weight:700;padding:3px 10px;white-space:nowrap;">
                        {{ $project->status_label }}
                    </span>
                    @if($project->is_delayed)
                        <div style="font-size:10px;color:#ef4444;margin-top:3px;font-weight:600;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Delayed
                        </div>
                    @elseif($project->deadline)
                        <div style="font-size:10.5px;color:var(--text-secondary);margin-top:3px;">
                            {{ $project->deadline->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </a>
            @empty
            <div class="text-center py-4" style="font-size:13px;color:var(--text-secondary);">
                <i class="bi bi-kanban" style="font-size:32px;color:#94a3b8;display:block;margin-bottom:8px;"></i>
                No projects yet
            </div>
            @endforelse
        </div>

        {{-- Chats Under Review --}}
        <div class="dash-section-card">
            <div class="dash-section-header">
                <div class="d-flex align-items-center">
                    <div class="section-icon" style="background:rgba(234,88,12,0.1);">
                        <i class="bi bi-chat-left-quote" style="color:#ea580c;"></i>
                    </div>
                    <span>Chats Under Review</span>
                    @if($underReviewTasks->isNotEmpty())
                        <span class="ms-2 badge" style="background:rgba(234,88,12,0.12);color:#ea580c;border-radius:20px;font-size:11px;padding:3px 8px;font-weight:700;">{{ $underReviewTasks->count() }}</span>
                    @endif
                </div>
                <a href="{{ route('chat.index') }}?filter=review" class="btn btn-sm"
                    style="background:rgba(99,102,241,0.08);color:#6366f1;border:1px solid rgba(99,102,241,0.2);border-radius:8px;font-size:12px;font-weight:600;">
                    View All
                </a>
            </div>

            @forelse($underReviewTasks as $task)
            @php
                $project = $task->project;
                $assignee = $task->assignee;
                $fallbackColors = ['#6366f1','#2563eb','#16a34a','#ca8a04','#ef4444','#0891b2','#7c3aed'];
                $fallbackBgs   = ['rgba(99,102,241,0.12)','rgba(37,99,235,0.12)','rgba(22,163,74,0.12)','rgba(202,138,4,0.12)','rgba(239,68,68,0.12)','rgba(6,182,212,0.12)','rgba(124,58,237,0.12)'];
                $fi = (($project ? $project->id : $task->id) - 1) % 7;
            @endphp
            <a href="{{ route('chat.index') }}?select_task={{ $task->id }}" class="project-row d-flex align-items-center justify-content-between">
                {{-- Project Logo Section (Matching top Recent Projects design) --}}
                @if($project && $project->logo_path)
                    <img src="{{ asset('storage/' . $project->logo_path) }}" alt="{{ $project->name }}"
                        class="project-logo-thumb">
                @else
                    <div class="project-logo-thumb-fallback" style="background:{{ $fallbackBgs[$fi] }}; border: 1px solid rgba(0,0,0,0.05); flex-shrink:0;">
                        <span style="font-size:16px; font-weight:800; color:{{ $fallbackColors[$fi] }};">
                            {{ strtoupper(substr($project ? $project->name : $task->title, 0, 1)) }}
                        </span>
                    </div>
                @endif

                {{-- Task and Assignee details --}}
                <div class="min-w-0 flex-grow-1 ms-3">
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $task->title }}">{{ $task->title }}</div>
                    <div style="font-size:11.5px;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:6px;">
                        <span>{{ $project ? $project->name : 'No Project' }}</span>
                        <span>•</span>
                        <img src="{{ $assignee ? $assignee->avatar_url : asset('assets/images/users/avatar-placeholder.png') }}" class="rounded-circle border" style="width:16px;height:16px;object-fit:cover;border-color:rgba(0,0,0,0.08) !important;">
                        <span>{{ $assignee ? $assignee->name : 'Unassigned' }}</span>
                    </div>
                </div>

                {{-- Right Align Action --}}
                <div class="text-end flex-shrink-0" style="padding-right: 18px;">
                    <span style="background:rgba(234,88,12,0.12);color:#ea580c;border-radius:20px;font-size:10.5px;font-weight:700;padding:4px 12px;white-space:nowrap;transition:background 0.2s ease;">
                        Review Chat <i class="bi bi-chevron-right ms-1"></i>
                    </span>
                </div>
            </a>
            @empty
            <div class="text-center py-4" style="color:var(--text-secondary);">
                <div style="width:44px;height:44px;background:rgba(16,185,129,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                    <i class="bi bi-check-circle-fill" style="font-size:22px;color:#10b981;"></i>
                </div>
                <div style="font-size:13.5px;font-weight:700;color:#10b981;">All reviews completed!</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">No tasks pending review</div>
            </div>
            @endforelse
        </div>

    </div>
</div>

{{-- ===== CURRENT WORKS UPDATE MODAL ===== --}}
<div class="modal fade" id="currentWorksUpdateModal" tabindex="-1" aria-labelledby="currentWorksUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-3" style="background:linear-gradient(135deg,#0f172a,#1e1b4b); color: #fff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-20 text-success p-2 rounded-3">
                        <i class="bi bi-broadcast text-success fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="currentWorksUpdateModalLabel" style="color:#fff;">Current Works Update</h5>
                        <p class="text-muted fs-8 mb-0">Live tracking status of all team members</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    @forelse($employees as $emp)
                        @php
                            $session = $emp->todayWorkSession;
                            $activeLog = $session?->activeTaskLog;
                            
                            $status = 'not_started';
                            if ($session) {
                                if ($session->status === 'ended') {
                                    $status = 'completed';
                                } elseif ($activeLog) {
                                    $status = 'working';
                                } else {
                                    $status = ($session->started_at && $session->started_at->diffInMinutes(now()) > 15) ? 'idle' : 'working';
                                }
                            }
                            
                            $statusConfig = [
                                'working' => [
                                    'badge' => 'bg-success bg-opacity-10 text-success border-success border-opacity-20',
                                    'label' => 'Working',
                                    'dot' => 'bg-success',
                                ],
                                'idle' => [
                                    'badge' => 'bg-warning bg-opacity-10 text-warning border-warning border-opacity-20',
                                    'label' => 'Idle',
                                    'dot' => 'bg-warning',
                                ],
                                'completed' => [
                                    'badge' => 'bg-primary bg-opacity-10 text-primary border-primary border-opacity-20',
                                    'label' => 'Completed',
                                    'dot' => 'bg-primary',
                                ],
                                'not_started' => [
                                    'badge' => 'bg-secondary bg-opacity-10 text-secondary border-secondary border-opacity-20',
                                    'label' => 'Not Started',
                                    'dot' => 'bg-secondary',
                                ],
                            ];
                            
                            $config = $statusConfig[$status] ?? $statusConfig['not_started'];
                        @endphp
                        
                        <div class="col-12">
                            <div class="card border border-light-subtle shadow-xs">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative">
                                                <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}" class="avatar-circle rounded-circle border border-2 border-light" style="width: 44px; height: 44px; object-fit: cover;">
                                                <span class="position-absolute bottom-0 end-0 rounded-circle {{ $config['dot'] }}" style="width: 12px; height: 12px; border: 2px solid white; transform: translate(2px, 2px);"></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $emp->name }}</h6>
                                                <small class="text-muted fs-8">{{ $emp->role?->name ?? 'Employee' }} • {{ $emp->employee?->department?->name ?? 'No Dept' }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $config['badge'] }} border px-3 py-1.5 fs-8 fw-semibold rounded-pill">
                                                {{ $config['label'] }}
                                            </span>
                                            @if($session && !$emp->isSuperAdmin() && !$emp->isAdmin())
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-3 py-1.5 fs-8 fw-semibold rounded-pill">
                                                    {{ $session->total_hours }} worked
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        @if($status === 'working' && $activeLog)
                                            @php
                                                $logDiff = max(0, now()->timestamp - $activeLog->started_at->timestamp);
                                            @endphp
                                            <div class="p-3 bg-success bg-opacity-5 border border-success border-opacity-10 rounded-3">
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <i class="bi bi-play-circle-fill text-success fs-6"></i>
                                                            <span class="fw-bold text-success fs-7 text-truncate d-inline-block" style="max-width: 100%;">{{ $activeLog->task->title }}</span>
                                                        </div>
                                                        <div class="text-muted fs-8">
                                                            Project: <strong class="fw-semibold">{{ $activeLog->task->project->name ?? 'No Project' }}</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="text-sm-end flex-shrink-0">
                                                        <div class="running-timer-ticker font-monospace fw-bold text-success fs-6 bg-success bg-opacity-10 border border-success border-opacity-20 rounded px-2.5 py-1" data-start="{{ $activeLog->started_at->timestamp }}">
                                                            {{ sprintf('%02d:%02d:%02d', ($logDiff/3600), ($logDiff/60)%60, $logDiff%60) }}
                                                        </div>
                                                        <div class="text-muted fs-8 mt-1">Started: {{ $activeLog->started_at->format('h:i A') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($status === 'idle')
                                            <div class="p-3 bg-warning bg-opacity-5 border border-warning border-opacity-10 rounded-3 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-warning fs-7 fw-semibold">
                                                    <i class="bi bi-exclamation-circle-fill"></i>
                                                    <span>Active Session but not tracking a task</span>
                                                </div>
                                            </div>
                                        @elseif($status === 'completed')
                                            <div class="p-3 bg-primary bg-opacity-5 border border-primary border-opacity-10 rounded-3 text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-primary fs-7 fw-semibold">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    <span>Completed shift at {{ $session->ended_at?->format('h:i A') ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 rounded-3 text-center" style="background: var(--body-bg); border: 1px solid var(--border-color);">
                                                <div class="d-flex align-items-center justify-content-center gap-2 text-muted fs-7">
                                                    <i class="bi bi-moon-fill text-secondary"></i>
                                                    <span>Not working yet today</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-people fs-1"></i>
                            <p class="mt-2 mb-0">No employees registered in the system.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@if(isset($showProjectsModal) && $showProjectsModal)
<div class="modal fade" id="checkProjectsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="checkProjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-folder2-open" style="font-size: 28px;"></i>
                </div>
                <h5 class="fw-bold mb-2 text-dark">Check All Projects</h5>
                <p class="text-secondary fs-7 px-3 mb-4">Would you like to visit the project previews page to view the live status of all active projects now?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 500;">No, Go to Dashboard</button>
                    <a href="{{ route('projects.previews') }}" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500;">Yes, Check Previews</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const myModal = new bootstrap.Modal(document.getElementById('checkProjectsModal'));
        myModal.show();
    });
</script>
@endpush
@endif

@endsection

@push('scripts')
<script>
    function pad(num) {
        return ("0" + num).slice(-2);
    }
    document.addEventListener('DOMContentLoaded', () => {
        const tickers = document.querySelectorAll('.running-timer-ticker');
        tickers.forEach(ticker => {
            const start = parseInt(ticker.getAttribute('data-start'));
            setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const diff = Math.max(0, now - start);
                
                const hrs = Math.floor(diff / 3600);
                const mins = Math.floor((diff % 3600) / 60);
                const secs = Math.floor(diff % 60);
                
                ticker.textContent = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
            }, 1000);
        });
    });
</script>
@endpush