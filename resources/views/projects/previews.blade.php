@extends('layouts.app')

@section('title', 'Project Live Previews')
@section('page-title', 'Project Live Previews')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Project Previews</li>
@endsection

@push('styles')
<style>
    .live-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .live-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08);
        border-color: rgba(99, 102, 241, 0.25);
    }
    
    .mock-browser-header {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        flex-shrink: 0;
    }
    
    .browser-dots {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    
    .browser-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .browser-dot.red { background: #ef4444; }
    .browser-dot.yellow { background: #f59e0b; }
    .browser-dot.green { background: #10b981; }
    
    .browser-address-bar {
        flex: 1;
        margin: 0 12px;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 11px;
        color: var(--text-secondary);
        padding: 3px 12px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: var(--bs-font-monospace);
    }
    
    .browser-external-link {
        color: var(--text-secondary);
        transition: color 0.15s;
        font-size: 13px;
        text-decoration: none;
    }
    
    .browser-external-link:hover {
        color: var(--bs-primary);
    }
    
    .browser-content {
        height: 240px;
        background: #fff;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid var(--border-color);
        flex-shrink: 0;
    }
    
    .browser-iframe {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
    }
    
    .project-info-footer {
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .project-card-title {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 4px;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .avatar-circle {
        border-radius: 50%;
        object-fit: cover;
    }

    [data-bs-theme="dark"] .mock-browser-header {
        background: #1e293b;
    }
    
    [data-bs-theme="dark"] .live-card:hover {
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
    }
    
    .preview-warning-state {
        height: 100%;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px;
        text-align: center;
    }
    
    [data-bs-theme="dark"] .preview-warning-state {
        background: #0f172a;
    }

    .preview-filter-btn {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .preview-filter-btn:hover {
        color: var(--text-primary);
        border-color: rgba(99, 102, 241, 0.4);
        background: rgba(99, 102, 241, 0.02);
    }
    
    .preview-filter-btn.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    
    .preview-filter-btn .count {
        font-size: 11px;
        font-weight: 700;
        background: var(--border-color);
        color: var(--text-secondary);
        padding: 1px 6px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    
    .preview-filter-btn.active .count {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4" id="previews-filter-bar">
    <button class="preview-filter-btn active" id="pill-all" onclick="filterPreviews('all')">
        All Projects <span class="count" id="count-all">{{ count($projects) }}</span>
    </button>
    @php
        $restrictedCount = $projects->filter(function($p) {
            return isset($p->iframe_status) && !$p->iframe_status['embeddable'];
        })->count();
    @endphp
    <button class="preview-filter-btn" id="pill-restricted" onclick="filterPreviews('restricted')">
        Restricted / No Preview <span class="count" id="count-restricted">{{ $restrictedCount }}</span>
    </button>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="previews-grid">
    @forelse($projects as $p)
        @php
            $isRestricted = isset($p->iframe_status) && !$p->iframe_status['embeddable'];
        @endphp
        <div class="col project-preview-col" data-restricted="{{ $isRestricted ? 'true' : 'false' }}">
            <div class="live-card">
                <!-- Mock Browser Header -->
                <div class="mock-browser-header">
                    <div class="browser-dots">
                        <span class="browser-dot red"></span>
                        <span class="browser-dot yellow"></span>
                        <span class="browser-dot green"></span>
                    </div>
                    <div class="browser-address-bar">
                        {{ parse_url($p->url, PHP_URL_HOST) ?? $p->url }}
                        @if($isRestricted)
                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Embedding restricted: {{ $p->iframe_status['reason'] }}"></i>
                        @endif
                    </div>
                    <a href="{{ $p->url }}" target="_blank" class="browser-external-link" title="Open live site">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
                
                <!-- Browser Content (Live Iframe or Warning State) -->
                <div class="browser-content">
                    @if($isRestricted)
                        <div class="preview-warning-state">
                            <i class="bi bi-exclamation-triangle-fill text-warning mb-2" style="font-size: 28px;"></i>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">Preview Restricted</h6>
                            <p class="text-muted mb-0" style="font-size: 11px;">{{ $p->iframe_status['reason'] }}</p>
                        </div>
                    @else
                        <iframe src="{{ $p->url }}" class="browser-iframe" loading="lazy"></iframe>
                    @endif
                </div>
                
                <!-- Project Info Footer -->
                <div class="project-info-footer">
                    <div>
                        <div class="d-flex align-items-center justify-content-end mb-2">
                            @php
                                $badgeClass = match($p->status) {
                                    'completed', 'delivered', 'completed_started_amc' => 'success',
                                    'planning', 'design', 'development', 'testing', 'client_review' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }} fs-8 fw-bold">{{ ucwords(str_replace('_', ' ', $p->status)) }}</span>
                        </div>
                        <h5 class="project-card-title" title="{{ $p->name }}">{{ $p->name }}</h5>
                        @if($p->client)
                            <div class="text-muted fs-8 mt-1" style="font-weight: 500;">
                                <i class="bi bi-person me-1 text-primary"></i>Client: <span class="fw-semibold text-dark">{{ $p->client->company_name }}</span>
                            </div>
                        @endif
                    </div>
                    
                    @if($p->teamLeader)
                        <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top">
                            <img src="{{ $p->teamLeader->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                            <div class="fs-8 text-muted">
                                Lead: <span class="fw-semibold text-dark">{{ $p->teamLeader->name }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-folder-x display-1 mb-3"></i>
            <h4 class="fw-bold">No active preview projects</h4>
            <p class="fs-7 max-w-xs mx-auto">Active projects with URLs will appear here as live cards.</p>
        </div>
    @endforelse
</div>

<div class="text-center py-5 text-muted" id="empty-state" style="display: none;">
    <i class="bi bi-folder-x display-1 mb-3"></i>
    <h4 class="fw-bold" id="empty-state-title">No active preview projects</h4>
    <p class="fs-7 max-w-xs mx-auto" id="empty-state-desc">Active projects with URLs will appear here as live cards.</p>
</div>
@endsection

@push('scripts')
<script>
    function filterPreviews(type) {
        document.querySelectorAll('.preview-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const allCols = document.querySelectorAll('.project-preview-col');
        const emptyState = document.getElementById('empty-state');
        const previewsGrid = document.getElementById('previews-grid');
        
        if (type === 'all') {
            document.getElementById('pill-all').classList.add('active');
            allCols.forEach(el => {
                el.style.display = 'block';
            });
            
            if (allCols.length === 0) {
                if (previewsGrid) previewsGrid.style.display = 'none';
                if (emptyState) {
                    emptyState.style.display = 'block';
                    document.getElementById('empty-state-title').textContent = 'No active preview projects';
                    document.getElementById('empty-state-desc').textContent = 'Active projects with URLs will appear here as live cards.';
                }
            } else {
                if (previewsGrid) previewsGrid.style.display = 'flex';
                if (emptyState) emptyState.style.display = 'none';
            }
        } else if (type === 'restricted') {
            document.getElementById('pill-restricted').classList.add('active');
            let visibleCount = 0;
            
            allCols.forEach(el => {
                if (el.getAttribute('data-restricted') === 'true') {
                    el.style.display = 'block';
                    visibleCount++;
                } else {
                    el.style.display = 'none';
                }
            });
            
            if (visibleCount === 0) {
                if (previewsGrid) previewsGrid.style.display = 'none';
                if (emptyState) {
                    emptyState.style.display = 'block';
                    document.getElementById('empty-state-title').textContent = 'No restricted previews';
                    document.getElementById('empty-state-desc').textContent = 'All active projects have working previews.';
                }
            } else {
                if (previewsGrid) previewsGrid.style.display = 'flex';
                if (emptyState) emptyState.style.display = 'none';
            }
        }
    }
</script>
@endpush
