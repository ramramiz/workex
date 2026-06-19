@extends('layouts.app')
@section('title', 'Live Status Board')
@section('page-title', 'Live Status Board')

@push('styles')
<style>
    /* Premium live board styling */
    .status-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03), 0 2px 4px -1px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .status-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0,0,0,0.08);
        border-color: var(--primary);
    }
    
    /* Glowing Avatar Ring */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        padding: 4px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .avatar-wrapper.status-working {
        background: linear-gradient(135deg, #10b981, #34d399);
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.35);
        animation: glow-working 2s infinite alternate;
    }
    
    .avatar-wrapper.status-idle {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        box-shadow: 0 0 12px rgba(245, 158, 11, 0.3);
    }
    
    .avatar-wrapper.status-completed {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        box-shadow: 0 0 12px rgba(59, 130, 246, 0.25);
    }
    
    .avatar-wrapper.status-not_started {
        background: linear-gradient(135deg, #94a3b8, #cbd5e1);
    }
    
    @keyframes glow-working {
        0% { box-shadow: 0 0 6px rgba(16, 185, 129, 0.2); }
        100% { box-shadow: 0 0 14px rgba(16, 185, 129, 0.5); }
    }
    
    .avatar-img {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--card-bg);
        background: var(--body-bg);
    }
    
    /* Expanded Area Styling */
    .expandable-panel {
        border-top: 1px dashed var(--border-color);
        margin-top: 14px;
        padding-top: 14px;
        transition: all 0.3s ease;
    }
    
    .section-header {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-secondary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .activity-item {
        padding: 10px 12px;
        margin-bottom: 8px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.4;
        transition: all 0.2s ease;
    }
    
    .activity-item.doing-now {
        background: rgba(16, 185, 129, 0.06);
        border-left: 4px solid #10b981;
    }
    [data-bs-theme="dark"] .activity-item.doing-now {
        background: rgba(16, 185, 129, 0.12);
    }
    
    .activity-item.completed-task {
        background: var(--body-bg);
        border-left: 4px solid var(--primary);
    }
    
    .activity-item.completed-call {
        background: var(--body-bg);
        border-left: 4px solid #06b6d4;
    }
    
    .activity-item.completed-session {
        background: var(--body-bg);
        border-left: 4px solid #8b5cf6;
    }
    
    /* Toggle Arrow Animation */
    .toggle-arrow-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: var(--text-secondary);
        transition: color 0.2s, transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .toggle-arrow-btn:hover {
        color: var(--primary);
    }
    .toggle-arrow-btn.rotated {
        transform: rotate(180deg);
    }
    .text-purple {
        color: #8b5cf6 !important;
    }
</style>
@endpush

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
        <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()">
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

function toggleEmployeeDetails(employeeId, btn) {
    const container = document.getElementById(`employee-details-${employeeId}`);
    if (!container) return;
    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.classList.add('rotated');
        window.expandedEmployees.add(employeeId);
    } else {
        container.style.display = 'none';
        btn.classList.remove('rotated');
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
    fetch('/live-status/data?_t=' + Date.now(), { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
        .then(r => r.json())
        .then(data => {
            const grid = document.getElementById('status-grid');
            document.getElementById('loading-state')?.remove();
            const working = data.employees.filter(e => e.status === 'working').length;
            document.getElementById('count-num').textContent = working;
            document.getElementById('last-updated').textContent = 'Last updated: ' + data.updated_at;

            const dark = isDark();
            const cardBorder = dark ? '#1e293b' : '#e2e8f0';
            const textPrimary   = dark ? '#f8fafc' : '#0f172a';
            const textSecondary = dark ? '#94a3b8' : '#64748b';
            const dottedBorder = dark ? '#334155' : '#e2e8f0';

            grid.innerHTML = data.employees.map(e => {
                const isExpanded = window.expandedEmployees.has(e.id);
                const displayStyle = isExpanded ? 'block' : 'none';
                const statusStyle = getStatusStyle(e.status);
                
                const hasActivity = (e.working_tasks && e.working_tasks.length > 0) || (e.completed_work && e.completed_work.length > 0);
                
                const arrowButton = hasActivity ? `
                    <button class="toggle-arrow-btn ${isExpanded ? 'rotated' : ''}" 
                            onclick="toggleEmployeeDetails(${e.id}, this)" 
                            title="Toggle Details">
                        <i class="bi bi-chevron-down" style="font-size: 16px;"></i>
                    </button>
                ` : '';

                return `
                <div class="col-md-6 col-xl-4">
                    <div class="status-card p-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="avatar-wrapper status-${e.status}">
                                <img src="${e.avatar}" class="avatar-img" alt="">
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="min-width-0">
                                        <div style="font-size:16px;font-weight:700;color:${textPrimary};" class="text-truncate">${e.name}</div>
                                        <div style="font-size:12px;color:${textSecondary};" class="text-truncate">${e.role ?? ''} ${e.department ? '· '+e.department : ''}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span style="background:${statusStyle.bg};color:${statusStyle.color};font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;">
                                            ${e.status==='working'?'<span class=\'status-dot working\' style=\'margin-right:0;\'></span>':''}${LABELS[e.status]}
                                        </span>
                                        ${arrowButton}
                                    </div>
                                </div>
                                
                                <div class="mt-3" style="font-size:13px;color:${textPrimary};">
                                    ${e.current_task
                                        ? e.calls_count !== null
                                            ? `<div class="d-flex align-items-center gap-1 text-truncate"><i class="bi bi-telephone-fill text-success"></i><strong class="text-truncate">${e.current_task}</strong></div><div style="font-size:11px;color:${textSecondary};" class="ms-4">${e.current_project ?? ''}</div>`
                                            : e.current_task_id
                                                ? `<div class="d-flex align-items-center gap-1 text-truncate"><i class="bi bi-stopwatch-fill text-success"></i><strong class="text-truncate"><a href="/chat?select_task=${e.current_task_id}" target="_blank" class="text-decoration-none text-success" title="Go to Task Chat">${e.current_task} <i class="bi bi-chat-dots ms-1" style="font-size: 10px;"></i></a></strong></div><div style="font-size:11px;color:${textSecondary};" class="ms-4">${e.current_project ?? ''}</div>`
                                                : `<div class="d-flex align-items-center gap-1 text-truncate"><i class="bi bi-stopwatch-fill text-success"></i><strong class="text-truncate">${e.current_task}</strong></div><div style="font-size:11px;color:${textSecondary};" class="ms-4">${e.current_project ?? ''}</div>`
                                        : e.status==='not_started'
                                            ? `<i class="bi bi-moon-stars me-1" style="color:${textSecondary};"></i><span style="color:${textSecondary};">Day not started</span>`
                                            : e.status==='completed'
                                                ? '<i class="bi bi-check2-all text-primary me-1"></i>Work day completed'
                                                : '<i class="bi bi-cup-hot text-warning me-1"></i>No active task (Idle)'
                                    }
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="font-size:12px;color:${textSecondary}; border-top: 1px solid ${dottedBorder};">
                                    ${e.started_at ? `<span><i class="bi bi-box-arrow-in-right me-1"></i>Started ${e.started_at}</span>` : '<span>Not active</span>'}
                                    <div class="d-flex gap-2 align-items-center">
                                        ${e.calls_count !== null && e.calls_count !== undefined
                                            ? `<span style="font-size:11px;font-weight:600;background:${dark?'rgba(6,182,212,0.1)':'#e0f7fa'};color:${dark?'#22d3ee':'#00838f'};border:1px solid ${dark?'rgba(6,182,212,0.2)':'#b2ebf2'};border-radius:6px;padding:2px 8px;"><i class="bi bi-telephone me-1"></i>${e.calls_count} ${e.calls_count === 1 ? 'call' : 'calls'}</span>`
                                            : ''
                                        }
                                        <span style="font-weight:600;color:${textPrimary};"><i class="bi bi-hourglass-split me-1"></i>${e.total_hours}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collapsible Panel containing Doing Now and Completed Today -->
                        <div class="expandable-panel" id="employee-details-${e.id}" style="display: ${displayStyle};">
                            ${e.working_tasks && e.working_tasks.length > 0 ? `
                                <div class="section-header">
                                    <i class="bi bi-activity text-success"></i> Doing Now
                                </div>
                                <div class="working-tasks-container mb-3">
                                    ${e.working_tasks.map(wt => `
                                        <div class="activity-item doing-now">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <strong style="color:${textPrimary};" class="text-truncate">
                                                    ${wt.task_id
                                                        ? `<a href="/chat?select_task=${wt.task_id}" target="_blank" class="text-decoration-none fw-bold" style="color:${textPrimary};" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='${textPrimary}'" title="Go to Task Chat">
                                                            ${wt.task_title} <i class="bi bi-chat-dots ms-1 text-success" style="font-size: 10px;"></i>
                                                           </a>`
                                                        : wt.task_title
                                                    }
                                                </strong>
                                                <span class="live-task-ticker font-monospace text-success fw-bold" data-start="${wt.task_start}">(${wt.task_time || '00:00'})</span>
                                            </div>
                                            <div style="font-size: 11px; color:${textSecondary};">${wt.project_name}</div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}

                            ${e.completed_work && e.completed_work.length > 0 ? `
                                <div class="section-header">
                                    <i class="bi bi-check2-circle text-primary"></i> Completed Today
                                </div>
                                <div class="completed-tasks-container">
                                    ${e.completed_work.map(cw => {
                                        if (cw.type === 'room_summary') {
                                            const itemClass = 'completed-session';
                                            let icon = '<i class="bi bi-door-open-fill text-purple me-1"></i>';
                                            let titleColor = '#8b5cf6';
                                            let borderLeftColor = '#8b5cf6';
                                            if (cw.room_id === 'followups') {
                                                icon = '<i class="bi bi-bell-fill text-danger me-1"></i>';
                                                titleColor = '#dc3545';
                                                borderLeftColor = '#dc3545';
                                            }
                                            return `
                                                <div class="activity-item ${itemClass}" style="border-left: 4px solid ${borderLeftColor};">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="min-width-0 text-truncate">
                                                            ${icon}
                                                            <a href="${cw.url}" class="fw-bold text-decoration-none" style="color: ${titleColor};" title="Click to view call details">
                                                                ${cw.title} <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
                                                            </a>
                                                        </span>
                                                        <span style="font-size: 11px; font-weight: 600;" class="text-secondary">Summary</span>
                                                    </div>
                                                    <div class="d-flex gap-3 mt-2" style="font-size: 12.5px;">
                                                        <span class="text-secondary"><i class="bi bi-telephone-outbound-fill text-info me-1"></i>Called: <strong style="color: ${textPrimary};">${cw.called_count}</strong></span>
                                                        <span class="text-secondary"><i class="bi bi-heart-fill text-danger me-1"></i>Interested: <strong style="color: ${textPrimary};">${cw.interested_count}</strong></span>
                                                    </div>
                                                </div>
                                            `;
                                        }

                                        let itemClass = 'completed-task';
                                        let icon = '<i class="bi bi-check2-all text-primary me-1"></i>';
                                        if (cw.type === 'call') {
                                            itemClass = 'completed-call';
                                            icon = '<i class="bi bi-telephone text-info me-1"></i>';
                                        } else if (cw.type === 'room_session') {
                                            itemClass = 'completed-session';
                                            icon = '<i class="bi bi-door-open text-purple me-1"></i>';
                                        }
                                        
                                        const titleDisplay = (cw.type === 'task' && cw.task_id)
                                            ? `<a href="/chat?select_task=${cw.task_id}" target="_blank" class="text-decoration-none" style="font-weight:600; color:${textPrimary};" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='${textPrimary}'" title="Go to Task Chat">
                                                ${icon}${cw.title} <i class="bi bi-chat-dots ms-1 text-success" style="font-size: 10px;"></i>
                                               </a>`
                                            : `<span style="font-weight:600; color:${textPrimary};" class="min-width-0">${icon}${cw.title}</span>`;
                                        
                                        return `
                                            <div class="activity-item ${itemClass}">
                                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                                    <span class="min-width-0">${titleDisplay}</span>
                                                    <span class="text-muted font-monospace fw-semibold flex-shrink-0" style="font-size: 11px;">${cw.ended_at} (${cw.duration})</span>
                                                </div>
                                                <div class="text-secondary" style="font-size: 11.5px; font-style: italic; white-space: pre-wrap;">
                                                    ${cw.note}
                                                </div>
                                            </div>
                                        `;
                                    }).join('')}
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
