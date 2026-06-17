@extends('layouts.app')
@section('title', 'Live Status Board')
@section('page-title', 'Live Status Board')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            <span class="status-dot working"></span> Live Status Board
        </h1>
        <p class="page-subtitle" id="last-updated">Updating every 30 seconds...</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success-subtle text-success px-3 py-2" id="working-count">
            <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>
            <span id="count-num">—</span> Working
        </span>
        <button class="btn btn-outline-secondary btn-sm" onclick="fetchStatus()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
</div>

<div class="row g-3" id="status-grid">
    <!-- Loading spinner -->
    <div class="col-12 text-center py-5" id="loading-state">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-2 text-muted">Loading live status...</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Detect current theme
function isDark() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark';
}

// Status colors — theme-aware
function getStatusStyle(status) {
    const dark = isDark();
    const configs = {
        working:     { bg: dark ? 'rgba(16,185,129,0.18)'  : '#d1fae5', color: dark ? '#34d399' : '#065f46' },
        idle:        { bg: dark ? 'rgba(245,158,11,0.18)'  : '#fef3c7', color: dark ? '#fbbf24' : '#92400e' },
        completed:   { bg: dark ? 'rgba(37,99,235,0.18)'   : '#dbeafe', color: dark ? '#60a5fa' : '#1e40af' },
        not_started: { bg: dark ? 'rgba(148,163,184,0.12)' : '#f1f5f9', color: dark ? '#94a3b8' : '#64748b' },
    };
    return configs[status] || configs.not_started;
}

const LABELS = { working: 'Working', idle: 'Idle', completed: 'Day Done', not_started: 'Not Started' };

window.expandedEmployees = window.expandedEmployees || new Set();

function pad(num) {
    return ("0" + num).slice(-2);
}

function toggleWorkingTasks(employeeId, btn) {
    const container = document.getElementById(`working-tasks-${employeeId}`);
    const icon = btn.querySelector('i');
    if (container.style.display === 'none') {
        container.style.display = 'block';
        icon.className = 'bi bi-chevron-up';
        window.expandedEmployees.add(employeeId);
    } else {
        container.style.display = 'none';
        icon.className = 'bi bi-chevron-down';
        window.expandedEmployees.delete(employeeId);
    }
}

// Restart live tickers after DOM change
function startTickers() {
    document.querySelectorAll('.live-task-ticker').forEach(el => {
        // Avoid double-binding
        if (el._tickerStarted) return;
        el._tickerStarted = true;
        const start = parseInt(el.getAttribute('data-start'));
        if (!start) return;
        setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            const diff = Math.max(0, now - start);
            const hrs  = Math.floor(diff / 3600);
            const mins = Math.floor((diff % 3600) / 60);
            const secs = Math.floor(diff % 60);
            el.textContent = `(${pad(hrs)}:${pad(mins)}:${pad(secs)})`;
        }, 1000);
    });
}

function fetchStatus() {
    fetch('/live-status/data', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(r => r.json())
        .then(data => {
            const grid = document.getElementById('status-grid');
            document.getElementById('loading-state')?.remove();
            const working = data.employees.filter(e => e.status === 'working').length;
            document.getElementById('count-num').textContent = working;
            document.getElementById('last-updated').textContent = 'Last updated: ' + data.updated_at;

            const dark = isDark();
            const cardBg    = dark ? '#111c2a' : '#ffffff';
            const cardBorder = dark ? '#1e293b' : '#e2e8f0';
            const textPrimary   = dark ? '#f8fafc' : '#0f172a';
            const textSecondary = dark ? '#94a3b8' : '#94a3b8';
            const taskRowBg = dark ? '#1e293b' : '#f0fdf4';
            const taskRowBorder = dark ? '#334155' : '#bbf7d0';
            const taskTitleColor = dark ? '#f8fafc' : '#0f172a';
            const dottedBorder = dark ? '#334155' : '#e2e8f0';

            grid.innerHTML = data.employees.map(e => {
                const isExpanded = window.expandedEmployees.has(e.id);
                const displayStyle = isExpanded ? 'block' : 'none';
                const iconClass = isExpanded ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                const statusStyle = getStatusStyle(e.status);
                const borderAccent = e.status === 'working' ? '#10b981' : e.status === 'idle' ? '#f59e0b' : '#cbd5e1';

                return `
                <div class="col-md-6 col-xl-4">
                    <div class="stat-card d-flex gap-3 align-items-start" style="border-left:4px solid ${borderAccent};">
                        <img src="${e.avatar}" class="rounded-circle" width="48" height="48" alt="">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div style="font-size:15px;font-weight:600;color:${textPrimary};">${e.name}</div>
                                    <div style="font-size:12px;color:${textSecondary};">${e.role ?? ''} ${e.department ? '· '+e.department : ''}</div>
                                </div>
                                <span style="background:${statusStyle.bg};color:${statusStyle.color};font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;">
                                    ${e.status==='working'?'<span class=\'status-dot working\' style=\'margin-right:4px;\'></span>':''}${LABELS[e.status]}
                                </span>
                            </div>
                            <div class="mt-2" style="font-size:13px;color:${textPrimary};">
                                ${e.current_task
                                    ? e.calls_count !== null
                                        ? `<i class="bi bi-play-circle-fill text-success me-1"></i><strong>${e.current_task} <span class="live-task-ticker font-monospace text-success" data-start="${e.current_task_start}">(${e.current_task_time || '00:00'})</span></strong><div style="font-size:11px;color:${textSecondary};">${e.current_project ?? ''}</div>`
                                        : `<i class="bi bi-stopwatch-fill text-success me-1"></i><strong>Timer: <span class="live-task-ticker font-monospace text-success" data-start="${e.current_task_start}">(${e.current_task_time || '00:00'})</span></strong>`
                                    : e.status==='not_started'
                                        ? `<i class="bi bi-clock me-1" style="color:${textSecondary};"></i><span style="color:${textSecondary};">Day not started</span>`
                                        : e.status==='completed'
                                            ? '<i class="bi bi-check2-all text-primary me-1"></i>Work day ended'
                                            : '<i class="bi bi-pause-circle text-warning me-1"></i>No active task'
                                }
                            </div>
                            <div class="d-flex justify-content-between mt-2" style="font-size:12px;color:${textSecondary};">
                                ${e.started_at ? `<span><i class="bi bi-play me-1"></i>Started ${e.started_at}</span>` : '<span></span>'}
                                <div class="d-flex gap-2 align-items-center">
                                    ${e.calls_count !== null && e.calls_count !== undefined
                                        ? `<span style="font-size:11px;font-weight:600;background:${dark?'#1e293b':'#f1f5f9'};color:${textSecondary};border:1px solid ${cardBorder};border-radius:6px;padding:2px 8px;"><i class="bi bi-telephone me-1"></i>${e.calls_count} ${e.calls_count === 1 ? 'call' : 'calls'}</span>`
                                        : ''
                                    }
                                    <span style="font-weight:600;color:${textPrimary};">${e.total_hours} worked</span>
                                </div>
                            </div>

                            ${e.status === 'working' && e.working_tasks && e.working_tasks.length > 0 ? `
                                <div style="border-top: 1px dashed ${dottedBorder}; margin-top: 10px; padding-top: 8px;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span style="font-size: 11.5px; font-weight: 500; color:${textSecondary};">Active Tasks (${e.working_tasks.length})</span>
                                        <button class="btn btn-link p-0 d-flex align-items-center justify-content-center" 
                                                onclick="toggleWorkingTasks(${e.id}, this)" 
                                                style="width: 24px; height: 24px; border-radius: 50%; background: ${dark?'rgba(34,197,94,0.15)':'#f0fdf4'}; border: 1px solid ${dark?'#166534':'#bbf7d0'}; color: #22c55e; text-decoration: none;">
                                            <i class="${iconClass}" style="font-size: 12px; transition: transform 0.2s ease;"></i>
                                        </button>
                                    </div>
                                    <div class="working-tasks-container mt-2" id="working-tasks-${e.id}" style="display: ${displayStyle};">
                                        ${e.working_tasks.map(wt => `
                                            <div style="padding:8px 10px;margin-bottom:6px;border-radius:8px;background:${taskRowBg};border-left:3px solid #22c55e;font-size:12.5px;">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <strong style="color:${taskTitleColor};">${wt.task_title}</strong>
                                                    <span class="live-task-ticker font-monospace text-success fw-bold" data-start="${wt.task_start}">(${wt.task_time || '00:00'})</span>
                                                </div>
                                                <div style="font-size: 11px; color:${textSecondary};">${wt.project_name}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            // Restart tickers for newly created DOM elements
            startTickers();
        })
        .catch(err => console.error('Status fetch error:', err));
}

fetchStatus();
setInterval(fetchStatus, 30000);

// Re-render cards when theme changes to update colors
const themeObserver = new MutationObserver(() => fetchStatus());
themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
</script>
@endpush
