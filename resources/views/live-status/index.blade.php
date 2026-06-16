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
const COLORS = { working: '#d1fae5', idle: '#fef3c7', completed: '#dbeafe', not_started: '#f1f5f9' };
const TEXT   = { working: '#065f46', idle: '#92400e', completed: '#1e40af', not_started: '#64748b' };
const LABELS = { working: 'Working', idle: 'Idle', completed: 'Day Done', not_started: 'Not Started' };

function pad(num) {
    return ("0" + num).slice(-2);
}

// Master live ticker
setInterval(() => {
    const now = Math.floor(Date.now() / 1000);
    document.querySelectorAll('.live-task-ticker').forEach(el => {
        const start = parseInt(el.getAttribute('data-start'));
        if (!start) return;
        const diff = Math.max(0, now - start);
        const hrs = Math.floor(diff / 3600);
        const mins = Math.floor((diff % 3600) / 60);
        const secs = Math.floor(diff % 60);
        el.textContent = `(${pad(hrs)}:${pad(mins)}:${pad(secs)})`;
    });
}, 1000);

function fetchStatus() {
    fetch('/live-status/data', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
        .then(r => r.json())
        .then(data => {
            const grid = document.getElementById('status-grid');
            document.getElementById('loading-state')?.remove();
            const working = data.employees.filter(e => e.status === 'working').length;
            document.getElementById('count-num').textContent = working;
            document.getElementById('last-updated').textContent = 'Last updated: ' + data.updated_at;

            grid.innerHTML = data.employees.map(e => `
                <div class="col-md-6 col-xl-4">
                    <div class="stat-card d-flex gap-3 align-items-start" style="border-left:4px solid ${e.status==='working'?'#10b981':e.status==='idle'?'#f59e0b':'#cbd5e1'}">
                        <img src="${e.avatar}" class="rounded-circle" width="48" height="48" alt="">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div style="font-size:15px;font-weight:600;">${e.name}</div>
                                    <div style="font-size:12px;color:#94a3b8;">${e.role ?? ''} ${e.department ? '· '+e.department : ''}</div>
                                </div>
                                <span style="background:${COLORS[e.status]};color:${TEXT[e.status]};font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;">
                                    ${e.status==='working'?'<span class=\'status-dot working\' style=\'margin-right:4px;\'></span>':''}${LABELS[e.status]}
                                </span>
                            </div>
                            <div class="mt-2" style="font-size:13px;">
                                ${e.current_task
                                    ? `<i class="bi bi-play-circle-fill text-success me-1"></i><strong>${e.current_task} <span class="live-task-ticker font-monospace text-success" data-start="${e.current_task_start}">(${e.current_task_time || '00:00'})</span></strong><div style="font-size:11px;color:#94a3b8;">${e.current_project ?? ''}</div>`
                                    : e.status==='not_started'
                                        ? '<i class="bi bi-clock text-muted me-1"></i>Day not started'
                                        : e.status==='completed'
                                            ? '<i class="bi bi-check2-all text-primary me-1"></i>Work day ended'
                                            : '<i class="bi bi-pause-circle text-warning me-1"></i>No active task'
                                }
                            </div>
                            <div class="d-flex justify-content-between mt-2" style="font-size:12px;color:#94a3b8;">
                                ${e.started_at ? `<span><i class="bi bi-play me-1"></i>Started ${e.started_at}</span>` : '<span></span>'}
                                <div class="d-flex gap-2 align-items-center">
                                    ${e.calls_count !== null && e.calls_count !== undefined
                                        ? `<span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-telephone me-1"></i>${e.calls_count} ${e.calls_count === 1 ? 'call' : 'calls'}</span>`
                                        : ''
                                    }
                                    <span style="font-weight:600;color:#0f172a;">${e.total_hours} worked</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => console.error('Status fetch error:', err));
}

fetchStatus();
setInterval(fetchStatus, 30000);
</script>
@endpush
