@extends('layouts.app')

@section('title', 'Chat Workspace')
@section('page-title', 'Chat Workspace')

@section('breadcrumb')
    <li class="breadcrumb-item active">Chat Workspace</li>
@endsection

@push('styles')
<style>
    .chat-layout {
        display: flex;
        height: calc(100vh - var(--topnav-height) - 48px);
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    .chat-sidebar {
        width: 350px;
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        background: #ffffff;
        flex-shrink: 0;
    }
    .chat-sidebar-search {
        padding: 12px 16px;
        background: #ffffff;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .chat-sidebar-search .input-group {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .chat-sidebar-search .input-group:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
    .chat-sidebar-search .form-control {
        background-color: transparent;
        border: none;
        box-shadow: none;
        font-size: 13.5px;
        padding: 8px 12px;
    }
    .chat-sidebar-search .input-group-text {
        background-color: transparent;
        border: none;
        color: #64748b;
        padding-left: 12px;
    }
    .chat-task-list {
        flex: 1;
        overflow-y: auto;
        padding: 0;
    }
    .chat-task-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.15s ease;
        border-bottom: 1px solid #f8fafc;
        border-left: 4px solid transparent;
        text-decoration: none !important;
        color: inherit !important;
    }
    .chat-task-item:hover {
        background: #f8fafc;
    }
    .chat-task-item.active {
        background: #f0f7ff;
        border-left: 4px solid var(--primary);
    }
    .chat-task-item .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .chat-task-item .task-info {
        flex: 1;
        min-width: 0;
    }
    .chat-task-item .task-title {
        font-size: 14.5px;
        font-weight: 600;
        color: #111b21;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-task-item .task-project {
        font-size: 12px;
        color: #667781;
        font-weight: 500;
    }
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #efeae2;
        min-width: 0;
        position: relative;
    }
    .chat-main-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        text-align: center;
        padding: 40px;
        background: #f8fafc;
        color: #64748b;
    }
    .chat-header {
        min-height: 72px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #ffffff;
        flex-shrink: 0;
    }
    .chat-header-info {
        min-width: 0;
    }
    .chat-header-title {
        font-size: 15px;
        font-weight: 600;
        color: #111b21;
        margin-bottom: 0px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-header-subtitle {
        font-size: 12px;
        color: #667781;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }
    .chat-body.chat-container {
        background-color: #efeae2;
        background-image: radial-gradient(rgba(0,0,0,0.08) 1px, transparent 1px);
        background-size: 20px 20px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
    }
    .chat-body-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-width: 900px;
        margin: 0 auto;
    }

    /* WhatsApp Chat Row and Bubble Styles */
    .chat-row {
        display: flex;
        width: 100%;
        align-items: flex-end;
        gap: 8px;
        margin-bottom: 12px;
    }
    .chat-row.sent {
        justify-content: flex-end;
    }
    .chat-row.received {
        justify-content: flex-start;
    }
    .chat-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 2px;
        flex-shrink: 0;
    }
    .chat-bubble {
        max-width: 75%;
        min-width: 95px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        font-size: 13.5px;
        line-height: 1.45;
        padding: 8px 12px 22px 12px;
    }
    .chat-row.sent .chat-bubble {
        background-color: #d9fdd3;
        color: #111b21;
        border-radius: 14px 14px 0px 14px;
        border: 1px solid #c7ebc3;
    }
    .chat-row.received .chat-bubble {
        background-color: #ffffff;
        color: #111b21;
        border-radius: 14px 14px 14px 0px;
        border: 1px solid #e2e8f0;
    }
    .chat-sender {
        font-weight: 700;
        font-size: 11px;
        margin-bottom: 3px;
        display: block;
    }
    .chat-row.received .chat-sender {
        color: #0284c7;
    }
    .chat-row.sent .chat-sender {
        color: #16a34a;
    }
    .chat-meta {
        position: absolute;
        bottom: 3px;
        right: 8px;
        font-size: 9px;
        color: #667781;
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
    }
    .chat-row.sent .chat-meta i {
        color: #53bdeb; /* WhatsApp blue ticks */
        font-size: 11px;
    }

    /* Time Log Bubble Details */
    .time-log-box {
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 4px;
        font-size: 12.5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .chat-row.sent .time-log-box {
        background-color: rgba(255, 255, 255, 0.6);
        border-left: 4px solid #10b981;
    }
    .chat-row.received .time-log-box {
        background-color: #f8fafc;
        border-left: 4px solid #6366f1;
    }
    .time-log-header {
        font-weight: 700;
        font-size: 12px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .chat-row.sent .time-log-header {
        color: #10b981;
    }
    .chat-row.received .time-log-header {
        color: #6366f1;
    }
    .time-log-grid {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 4px 10px;
        line-height: 1.3;
    }
    .time-log-label {
        color: #64748b;
        font-weight: 500;
        font-size: 11.5px;
    }
    .time-log-value {
        font-weight: 600;
        color: #1e293b;
    }
    .time-log-note-section {
        margin-top: 8px;
        border-top: 1px dashed rgba(0, 0, 0, 0.08);
        padding-top: 8px;
    }
    .time-log-note-title {
        font-weight: 600;
        font-size: 11px;
        color: #64748b;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .time-log-note-content {
        font-style: italic;
        color: #334155;
        white-space: pre-wrap;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-active 1.5s infinite;
    }
    .pulse-dot-green {
        width: 8px;
        height: 8px;
        background-color: #22c55e;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-active-green 1.5s infinite;
    }

    /* WhatsApp Bottom Input Bar Styles */
    .whatsapp-input-bar {
        background-color: #f0f2f5;
        border-top: 1px solid #e2e8f0;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        flex-shrink: 0;
    }
    .whatsapp-input-container {
        flex: 1;
        background-color: #ffffff;
        border-radius: 24px;
        padding: 6px 16px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }
    .whatsapp-input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        padding: 6px 0;
        resize: none;
        max-height: 100px;
        line-height: 1.4;
        color: #111b21;
    }
    .whatsapp-input::placeholder {
        color: #8696a0;
    }
    .whatsapp-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #00a884; /* WhatsApp green */
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
        padding: 0;
        flex-shrink: 0;
    }
    .whatsapp-send-btn:hover {
        background-color: #008f72;
        transform: scale(1.05);
    }
    .whatsapp-send-btn:active {
        transform: scale(0.95);
    }

    @keyframes pulse-active {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    @keyframes pulse-active-green {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Mention Dropdown Styles */
    .mention-dropdown {
        position: absolute;
        bottom: 58px;
        left: 16px;
        z-index: 1000;
        width: 280px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.15);
        max-height: 220px;
        overflow-y: auto;
        display: none;
    }
    .mention-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }
    .mention-item:last-child {
        border-bottom: none;
    }
    .mention-item:hover, .mention-item.active {
        background-color: #f1f5f9;
    }
    .mention-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
    }
    .mention-info {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
    }
    .mention-name {
        font-weight: 600;
        color: #0f172a;
    }
    .mention-email {
        font-size: 11px;
        color: #64748b;
    }
    .color-btn.active {
        outline: 2px solid #000000;
        outline-offset: 2px;
    }

    /* Premium Button & Dropdown Styles for Chat Header */
    .chat-header .btn {
        height: 36px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid transparent;
    }
    .chat-header .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .chat-header .btn:active {
        transform: translateY(0);
    }
    .chat-header .btn-success {
        background-color: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }
    .chat-header .btn-success:hover {
        background-color: #d1fae5;
        border-color: #6ee7b7;
        color: #047857;
    }
    .chat-header .btn-danger {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }
    .chat-header .btn-danger:hover {
        background-color: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }
    .chat-header .btn-primary {
        background-color: #eff6ff;
        border-color: #bfdbfe;
        color: #1e40af;
    }
    .chat-header .btn-primary:hover {
        background-color: #dbeafe;
        border-color: #93c5fd;
        color: #1d4ed8;
    }
    .chat-header .btn-outline-secondary {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #475569;
    }
    .chat-header .btn-outline-secondary:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #334155;
    }

    /* Status Dropdown Colors */
    .chat-header select.form-select {
        height: 36px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        padding: 0 32px 0 12px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        background-position: right 10px center;
    }
    .chat-header select.form-select.status-select-pending {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    .chat-header select.form-select.status-select-in_progress {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border-color: #fde68a !important;
    }
    .chat-header select.form-select.status-select-review {
        background-color: #e0f2fe !important;
        color: #075985 !important;
        border-color: #bae6fd !important;
    }
    .chat-header select.form-select.status-select-rework {
        background-color: #fef2f2 !important;
        color: #991b1b !important;
        border-color: #fecaca !important;
    }
    .chat-header select.form-select.status-select-completed {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border-color: #a7f3d0 !important;
    }
    .chat-header select.form-select.status-select-cancelled {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
        border-color: #d1d5db !important;
    }
    #chatTaskStatusSelect option {
        background-color: #ffffff !important;
        color: #212529 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="chat-layout">
        
        <!-- Left Sidebar: Tasks List -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-search">
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="task-search-input" class="form-control" placeholder="Search tasks...">
                </div>
            </div>
            <div class="chat-task-list" id="chat-tasks-container">
                @forelse($tasks as $t)
                    <a href="javascript:void(0);" 
                       class="chat-task-item" 
                       id="chat-task-item-{{ $t->id }}" 
                       data-task-id="{{ $t->id }}" 
                       data-title="{{ strtolower($t->title) }}" 
                       data-project="{{ strtolower($t->project->name ?? '') }}"
                       onclick="selectTask({{ $t->id }})">
                        <div class="position-relative" style="margin-left: 5px;">
                            @if($t->assignee)
                                <img src="{{ $t->assignee->avatar_url }}" alt="" class="avatar-circle">
                            @else
                                <img src="https://ui-avatars.com/api/?name=Unassigned&background=cbd5e1&color=64748b" alt="" class="avatar-circle">
                            @endif
                            @php
                                $badgeClass = $t->priority_badge;
                            @endphp
                            <span class="position-absolute top-0 start-0 badge bg-{{ $badgeClass }} {{ $badgeClass === 'warning' ? 'text-dark' : 'text-white' }} rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; border: 2px solid var(--card-bg); font-size: 8px; font-weight: 800; transform: translate(-30%, -30%); z-index: 10;" title="Priority: {{ ucfirst($t->priority) }}">
                                {{ strtoupper(substr($t->priority, 0, 1)) }}
                            </span>
                            @if(str_starts_with(strtolower($t->title), 'bug:'))
                                <span class="position-absolute bottom-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; border: 2px solid var(--card-bg); font-size: 10px;" title="Bug">
                                    <i class="bi bi-bug-fill"></i>
                                </span>
                            @endif
                        </div>
                        <div class="task-info">
                            <div class="d-flex justify-content-between align-items-baseline">
                                <div class="task-title" title="{{ $t->title }}">
                                    @if(str_starts_with(strtolower($t->title), 'bug:'))
                                        <i class="bi bi-bug-fill text-danger me-1"></i>
                                    @endif
                                    {{ $t->title }}
                                </div>
                                <span class="text-muted flex-shrink-0 ms-2" style="font-size: 10px;">{{ $t->updated_at->diffForHumans(null, true) }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <div class="text-truncate fs-8 d-flex align-items-center gap-1 flex-wrap" style="color: #667781;">
                                    <span class="task-project" style="color: var(--primary);">{{ $t->project->name ?? 'No Project' }}</span>
                                    <span>-</span>
                                    <span class="text-secondary" style="font-size: 11px;">{{ $t->assignee->name ?? 'Unassigned' }}</span>
                                    <span>-</span>
                                    @if($t->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8px; padding: 1px 3px;">Completed</span>
                                    @elseif($t->status === 'in_progress')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 8px; padding: 1px 3px;">In Progress</span>
                                    @elseif($t->status === 'review')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 8px; padding: 1px 3px;">Review</span>
                                    @elseif($t->status === 'rework')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 8px; padding: 1px 3px;">Rework</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 8px; padding: 1px 3px;">Pending</span>
                                    @endif

                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isTeamLeader())
                                        @if($t->timeLogs->isNotEmpty())
                                            <span class="badge bg-success text-white border border-success d-inline-flex align-items-center gap-1 sidebar-working-badge" data-task-id="{{ $t->id }}" style="font-size: 8px; padding: 1px 3px; font-weight: 600;">
                                                <span class="pulse-dot-green"></span> working
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                @php
                                    $unreadCommentsCount = $t->comments->filter(function($comment) {
                                        return $comment->user_id !== auth()->id() && !$comment->views->contains('user_id', auth()->id());
                                    })->count();
                                @endphp
                                @if($unreadCommentsCount > 0)
                                    <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 9px; padding: 0; line-height: 1; flex-shrink: 0;" id="unread-badge-{{ $t->id }}">
                                        {{ $unreadCommentsCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 text-muted fs-7">
                        <i class="bi bi-check2-square fs-3"></i>
                        <div class="mt-2">No tasks available for chat.</div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Pane: Active Chat Window -->
        <div class="chat-main" id="chat-main-window">
            
            <!-- Placeholder when no task is selected -->
            <div class="chat-main-placeholder" id="chat-no-task-placeholder">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-chat-fill fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Unified Task Chat Workspace</h4>
                <p class="fs-7 text-muted" style="max-width: 360px;">Select a task from the list on the left to start discussion or view work updates.</p>
            </div>

            <!-- Chat Content (initially hidden) -->
            <div class="d-none flex-column h-100" id="chat-content-container">
                <!-- Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <h6 class="chat-header-title mb-0" id="chat-active-title">Task Title</h6>
                        <div class="chat-header-subtitle d-flex align-items-center gap-1 flex-wrap">
                            <span id="chat-active-project" class="text-primary fw-medium">Project Name</span>
                            <span class="text-muted mx-1">•</span>
                            <span class="text-muted">Assignee:</span>
                            <img src="" id="chat-active-avatar" class="avatar-circle ms-1" style="width: 18px; height: 18px; border-radius: 50%; object-fit: cover;">
                            <span id="chat-active-assignee" class="fw-semibold text-dark fs-8">Assignee Name</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Dynamic task action buttons container -->
                        <div id="chat-header-actions" class="d-flex align-items-center gap-1"></div>

                        <a href="#" id="chat-active-details-link" class="btn btn-outline-secondary btn-sm" target="_blank" title="View Task Details">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Details
                        </a>
                    </div>
                </div>

                <!-- Chat Body (Messages) -->
                <div class="chat-body chat-container p-4">
                    <div class="chat-body-container" id="chat-messages-container">
                        <!-- Rendered via AJAX -->
                    </div>
                </div>

                <!-- Input box -->
                <form id="chat-form" method="POST" action="" class="whatsapp-input-bar" enctype="multipart/form-data">
                    @csrf
                    <!-- Mention Dropdown -->
                    <div id="mention-list" class="mention-dropdown"></div>

                    <!-- Hidden input for base64 canvas image data -->
                    <input type="hidden" name="image_data" id="chat-image-data">

                    <!-- Attachment trigger -->
                    <label for="chat-image-input" class="btn btn-link text-muted p-0 m-0 d-flex align-items-center justify-content-center" style="font-size: 20px; width: 36px; height: 36px; cursor: pointer;" title="Attach Image">
                        <i class="bi bi-paperclip"></i>
                    </label>
                    <input type="file" id="chat-image-input" accept="image/*" style="display: none;">

                    <div class="whatsapp-input-container">
                        <textarea name="comment" id="whatsapp-comment-input" class="whatsapp-input" placeholder="Type a message..." rows="1" autocomplete="off"></textarea>
                    </div>
                    <button type="submit" class="whatsapp-send-btn">
                        <i class="bi bi-send-fill" style="margin-left: 2px;"></i>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>

<!-- Annotation Markup Modal -->
<div class="modal fade" id="imageMarkupModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="imageMarkupModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="imageMarkupModalLabel">Annotate Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="markup-modal-close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 bg-light p-2 rounded-3 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark active" id="tool-pencil" title="Pencil Tool">
                        <i class="bi bi-pencil-fill me-1"></i> Pencil
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="tool-circle" title="Circle Tool">
                        <i class="bi bi-circle me-1"></i> Circle
                    </button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn active" data-color="#ef4444" style="width: 24px; height: 24px; background-color: #ef4444;" title="Red"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#3b82f6" style="width: 24px; height: 24px; background-color: #3b82f6;" title="Blue"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#22c55e" style="width: 24px; height: 24px; background-color: #22c55e;" title="Green"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#eab308" style="width: 24px; height: 24px; background-color: #eab308;" title="Yellow"></button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="btn-clear-canvas">
                        <i class="bi bi-trash3-fill me-1"></i> Clear
                    </button>
                </div>
                <div class="d-flex justify-content-center align-items-center border rounded-3 bg-dark overflow-auto p-2" style="max-height: 400px; min-height: 250px;">
                    <canvas id="markup-canvas" style="cursor: crosshair; display: block; max-width: 100%; height: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></canvas>
                </div>
                <div class="mt-3 text-start">
                    <label class="form-label fs-7 fw-semibold text-dark">Add message with image (optional)</label>
                    <input type="text" id="markup-comment-input" class="form-control form-control-sm" placeholder="Type a comment...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4" id="btn-send-markup">
                    <i class="bi bi-send-fill me-1"></i> Send Image
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 justify-content-end mb-2">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="image-viewer-img" src="" alt="" class="img-fluid rounded-3" style="max-height: 80vh; box-shadow: 0 4px 24px rgba(0,0,0,0.5);">
            </div>
        </div>
    </div>
</div>

<!-- Comment Info Modal -->
<div class="modal fade" id="commentInfoModal" tabindex="-1" aria-labelledby="commentInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="commentInfoModalLabel">Message Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="text-muted mb-3 fs-7">People who viewed this message:</div>
                <div id="comment-viewers-list" class="d-flex flex-column gap-2">
                    <!-- Dynamic List of Viewers -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Task Modal -->
<div class="modal fade" id="endTaskModal" tabindex="-1" aria-labelledby="endTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog text-start">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title" id="endTaskModalLabel">End Work - Record Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="endTaskModalForm" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Task Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm fw-semibold" required id="endTaskStatusSelect">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="rework">Rework</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold">Work Done Description <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="3" required placeholder="Describe what progress you made during this log..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Complete Time Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Completion Modal -->
<div class="modal fade" id="taskCompletionModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="taskCompletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="taskCompletionModalLabel">Submit Task Completion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="taskCompletionModalForm" action="">
                @csrf
                <div class="modal-body text-start pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Description of Work Done <span class="text-danger">*</span></label>
                        <textarea name="completed_description" class="form-control" rows="4" required placeholder="Describe what you completed, any details of the changes made, etc."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Page Link / Test URL <span class="text-danger">*</span></label>
                        <input type="url" name="completed_link" class="form-control" required placeholder="https://example.com/test-page">
                        <div class="form-text text-muted fs-8">Provide the exact link where this change/feature can be verified.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Submit Work</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeTaskId = null;
    let latestFeedTime = null;
    let pollInterval = null;
    let activeStoreUrl = '';
    const isLeaderOrAbove = {{ (auth()->user()->isSuperAdmin() || auth()->user()->isTeamLeader()) ? 'true' : 'false' }};

    function openEndTaskModalChat(logId, currentStatus) {
        document.getElementById('endTaskModalForm').action = `{{ url('work-timer/end-task') }}/${logId}`;
        const statusSelect = document.querySelector('#endTaskModal #endTaskStatusSelect');
        if (statusSelect) {
            statusSelect.value = currentStatus;
        }
        const modal = new bootstrap.Modal(document.getElementById('endTaskModal'));
        modal.show();
    }

    function openTaskCompletionModalChat(taskId) {
        document.getElementById('taskCompletionModalForm').action = `{{ url('tasks') }}/${taskId}/submit-completion`;
        const modal = new bootstrap.Modal(document.getElementById('taskCompletionModal'));
        modal.show();
    }

    function updateTaskStatusChat(taskId, status) {
        fetch(`{{ url('tasks') }}/${taskId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedTaskId = localStorage.getItem('active_chat_task_id');
        if (savedTaskId) {
            const el = document.getElementById(`chat-task-item-${savedTaskId}`);
            if (el) {
                setTimeout(() => {
                    selectTask(parseInt(savedTaskId));
                }, 100);
            }
        }
    });

    // Search filter
    document.getElementById('task-search-input').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const items = document.querySelectorAll('.chat-task-item');
        items.forEach(item => {
            const title = item.dataset.title;
            const project = item.dataset.project;
            if (title.includes(query) || project.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    });

    function selectTask(taskId) {
        if (activeTaskId === taskId) return;
        
        // Clear old interval
        if (pollInterval) {
            clearInterval(pollInterval);
        }

        activeTaskId = taskId;

        // Highlight active task item
        document.querySelectorAll('.chat-task-item').forEach(item => {
            item.classList.remove('active');
        });
        document.getElementById(`chat-task-item-${taskId}`).classList.add('active');

        // Show window, hide placeholder
        document.getElementById('chat-no-task-placeholder').classList.add('d-none');
        document.getElementById('chat-content-container').classList.remove('d-none');
        document.getElementById('chat-content-container').classList.add('d-flex');

        // Show loading spinner
        const messagesContainer = document.getElementById('chat-messages-container');
        messagesContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Load details via AJAX
        fetch(`{{ url('chat/tasks') }}/${taskId}`)
            .then(response => response.json())
            .then(data => {
                // Populate headers
                let titleHtml = '';
                if (data.task_title.toLowerCase().startsWith('bug:')) {
                    titleHtml = `<i class="bi bi-bug-fill text-danger me-1"></i>${data.task_title}`;
                } else {
                    titleHtml = data.task_title;
                }

                if (isLeaderOrAbove && data.is_working) {
                    titleHtml += ` <span class="badge bg-success text-white border border-success ms-2 d-inline-flex align-items-center gap-1" style="font-size: 11px; padding: 2px 6px; font-weight: 600;"><span class="pulse-dot-green"></span> working</span>`;
                }
                document.getElementById('chat-active-title').innerHTML = titleHtml;

                // Sync sidebar working badge state
                const sidebarItem = document.getElementById(`chat-task-item-${taskId}`);
                if (sidebarItem && isLeaderOrAbove) {
                    const badgeContainer = sidebarItem.querySelector('.text-truncate');
                    if (badgeContainer) {
                        let existingBadge = badgeContainer.querySelector('.sidebar-working-badge');
                        if (data.is_working) {
                            if (!existingBadge) {
                                const badgeSpan = document.createElement('span');
                                badgeSpan.className = 'badge bg-success text-white border border-success d-inline-flex align-items-center gap-1 sidebar-working-badge';
                                badgeSpan.dataset.taskId = taskId;
                                badgeSpan.style.fontSize = '8px';
                                badgeSpan.style.padding = '1px 3px';
                                badgeSpan.style.fontWeight = '600';
                                badgeSpan.innerHTML = '<span class="pulse-dot-green"></span> working';
                                badgeContainer.appendChild(badgeSpan);
                            }
                        } else {
                            if (existingBadge) {
                                existingBadge.remove();
                            }
                        }
                    }
                }
                document.getElementById('chat-active-project').textContent = data.project_name;
                document.getElementById('chat-active-assignee').textContent = data.assignee_name;
                document.getElementById('chat-active-avatar').src = data.assignee_avatar;
                document.getElementById('chat-active-details-link').href = data.task_url;
                
                // Form action
                document.getElementById('chat-form').action = data.store_url;
                activeStoreUrl = data.store_url;

                // Save selected taskId to localStorage
                localStorage.setItem('active_chat_task_id', taskId);

                // Build header actions
                let actionsHtml = '';

                if (data.active_log_id) {
                    actionsHtml += `
                        <button type="button" class="btn btn-danger btn-sm me-1" onclick="openEndTaskModalChat(${data.active_log_id}, '${data.status}')" ${data.is_buttons_disabled ? 'disabled' : ''}>
                            <i class="bi bi-stop-fill me-1"></i> End Work
                        </button>
                    `;
                } else {
                    actionsHtml += `
                        <form method="POST" action="{{ url('work-timer/start-task') }}/${taskId}" class="d-inline me-1">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                <i class="bi bi-play-fill me-1"></i> Start Work
                            </button>
                        </form>
                        <button type="button" class="btn btn-primary btn-sm me-1" onclick="openTaskCompletionModalChat(${taskId})" ${data.is_buttons_disabled ? 'disabled' : ''}>
                            <i class="bi bi-check2-circle me-1"></i> Complete
                        </button>
                    `;
                }

                document.getElementById('chat-header-actions').innerHTML = actionsHtml;

                // Remove unread count badge in sidebar
                const badge = document.getElementById(`unread-badge-${taskId}`);
                if (badge) {
                    badge.remove();
                }

                // Load messages
                if (data.html.trim() === '') {
                    messagesContainer.innerHTML = `
                        <div class="text-center py-5 text-muted" id="no-chat-messages">
                            <i class="bi bi-chat-text" style="font-size: 32px;"></i>
                            <div class="mt-2 fs-7">No messages or time logs yet. Start the discussion!</div>
                        </div>
                    `;
                } else {
                    messagesContainer.innerHTML = data.html;
                }

                // Scroll to bottom
                const chatBody = document.querySelector('.chat-body');
                chatBody.scrollTop = chatBody.scrollHeight;

                latestFeedTime = data.latest_time;

                // Focus input
                document.getElementById('whatsapp-comment-input').focus();

                // Start Polling for this task
                pollInterval = setInterval(pollChatUpdates, 5000);
            })
            .catch(error => {
                console.error('Error loading task chat:', error);
                messagesContainer.innerHTML = `<div class="text-center text-danger py-5">Error loading chat history. Please try again.</div>`;
            });
    }

    const pollChatUpdates = () => {
        if (!activeTaskId || !latestFeedTime) return;

        const url = `{{ url('tasks') }}/${activeTaskId}/feed-updates?since=${encodeURIComponent(latestFeedTime)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.has_updates) {
                    latestFeedTime = data.latest_time;
                    
                    const messagesContainer = document.getElementById('chat-messages-container');
                    const chatBody = document.querySelector('.chat-body');
                    if (messagesContainer && chatBody) {
                        const noMessagesEl = document.getElementById('no-chat-messages');
                        if (noMessagesEl) {
                            noMessagesEl.remove();
                        }
                        
                        const isNearBottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop < 100;
                        
                        messagesContainer.insertAdjacentHTML('beforeend', data.html);
                        
                        if (isNearBottom) {
                            chatBody.scrollTop = chatBody.scrollHeight;
                        }
                    }
                    
                    if (data.play_sound && typeof window.playNotificationSound === 'function') {
                        window.playNotificationSound();
                    }
                }
            })
            .catch(error => console.error('Error polling chat updates:', error));
    };

    // AJAX Form submission
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const textarea = document.getElementById('whatsapp-comment-input');
        const comment = textarea.value.trim();
        const imageData = document.getElementById('chat-image-data').value;

        if (!comment && !imageData) return;

        const submitBtn = this.querySelector('.whatsapp-send-btn');
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch(activeStoreUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            textarea.value = '';
            textarea.style.height = 'auto';
            document.getElementById('chat-image-data').value = '';
            
            // Trigger an immediate check for updates
            pollChatUpdates();
        })
        .catch(error => {
            submitBtn.disabled = false;
            console.error('Error posting comment:', error);
        });
    });

    // Auto-resize textarea and submit on Enter
    const commentInput = document.getElementById('whatsapp-comment-input');
    if (commentInput) {
        commentInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        commentInput.addEventListener('keydown', function(e) {
            const mentionList = document.getElementById('mention-list');
            if (mentionList && mentionList.style.display === 'block') {
                return;
            }

            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0) {
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // Modal viewers logic
    const commentInfoModal = document.getElementById('commentInfoModal');
    if (commentInfoModal) {
        commentInfoModal.addEventListener('show.bs.modal', function(event) {
            const triggerButton = event.relatedTarget;
            const viewers = JSON.parse(triggerButton.getAttribute('data-viewers') || '[]');
            const listContainer = document.getElementById('comment-viewers-list');
            
            listContainer.innerHTML = '';
            
            if (viewers.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-eye-slash fs-2 mb-2 d-block"></i>
                        <span class="fs-7">No one has viewed this message yet.</span>
                    </div>
                `;
            } else {
                viewers.forEach(v => {
                    const item = document.createElement('div');
                    item.className = 'd-flex align-items-center justify-content-between py-2 border-bottom border-light';
                    item.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <img src="${v.avatar_url}" class="avatar-circle" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <span class="fw-semibold text-dark fs-7">${v.name}</span>
                        </div>
                        <span class="text-muted fs-8">${v.viewed_at}</span>
                    `;
                    listContainer.appendChild(item);
                });
            }
        });
    }

    const imageViewerModal = document.getElementById('imageViewerModal');
    if (imageViewerModal) {
        imageViewerModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const src = trigger.getAttribute('data-src');
            document.getElementById('image-viewer-img').setAttribute('src', src);
        });
    }

    // @mention list functionality
    const staffList = {!! json_encode(\App\Models\User::where('status', 'active')->get()->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'avatar_url' => $u->avatar_url
        ];
    })) !!};

    const mentionList = document.getElementById('mention-list');
    let selectedIndex = 0;
    let filteredStaff = [];
    let mentionStartIndex = -1;

    if (commentInput && mentionList) {
        commentInput.addEventListener('input', function(e) {
            const text = this.value;
            const cursorPosition = this.selectionStart;
            
            const lastAtIndex = text.lastIndexOf('@', cursorPosition - 1);
            
            if (lastAtIndex !== -1) {
                const textBetween = text.substring(lastAtIndex + 1, cursorPosition);
                
                if (!textBetween.includes(' ') && !textBetween.includes('\n')) {
                    mentionStartIndex = lastAtIndex;
                    showMentions(textBetween);
                    return;
                }
            }
            
            hideMentions();
        });

        commentInput.addEventListener('keydown', function(e) {
            if (mentionList.style.display === 'block') {
                const items = mentionList.querySelectorAll('.mention-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'Enter' || e.key === 'Tab') {
                    if (items.length > 0) {
                        e.preventDefault();
                        selectMention(items[selectedIndex].dataset.name);
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    hideMentions();
                }
            }
        });
    }

    function showMentions(query) {
        filteredStaff = staffList.filter(u => 
            u.name.toLowerCase().includes(query.toLowerCase()) || 
            u.email.toLowerCase().includes(query.toLowerCase())
        );

        if (filteredStaff.length === 0) {
            hideMentions();
            return;
        }

        mentionList.innerHTML = '';
        selectedIndex = 0;

        filteredStaff.forEach((u, index) => {
            const div = document.createElement('div');
            div.className = `mention-item ${index === 0 ? 'active' : ''}`;
            div.dataset.name = u.name;
            div.dataset.index = index;
            div.innerHTML = `
                <img src="${u.avatar_url}" class="mention-avatar" alt="">
                <div class="mention-info">
                    <span class="mention-name">${u.name}</span>
                    <span class="mention-email">${u.email}</span>
                </div>
            `;
            
            div.addEventListener('click', function() {
                selectMention(this.dataset.name);
            });
            
            mentionList.appendChild(div);
        });

        mentionList.style.display = 'block';
    }

    function updateActiveItem(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    function selectMention(name) {
        const text = commentInput.value;
        const cursorPosition = commentInput.selectionStart;
        
        const beforeMention = text.substring(0, mentionStartIndex);
        const afterMention = text.substring(cursorPosition);
        
        commentInput.value = beforeMention + '@' + name + ' ' + afterMention;
        
        const newCursorPos = mentionStartIndex + name.length + 2;
        commentInput.setSelectionRange(newCursorPos, newCursorPos);
        commentInput.focus();
        
        hideMentions();
        
        commentInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function hideMentions() {
        mentionList.style.display = 'none';
        filteredStaff = [];
        mentionStartIndex = -1;
    }

    // Image annotation logic
    const chatImageInput = document.getElementById('chat-image-input');
    const canvas = document.getElementById('markup-canvas');
    const ctx = canvas ? canvas.getContext('2d') : null;
    
    let drawing = false;
    let currentTool = 'pencil';
    let currentColor = '#ef4444';
    let currentLineWidth = 3;
    let startX = 0;
    let startY = 0;
    let savedImageData = null;
    let loadedImg = null;

    if (chatImageInput && canvas && ctx) {
        chatImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    loadedImg = new Image();
                    loadedImg.onload = function() {
                        const maxDimension = 600;
                        let width = loadedImg.width;
                        let height = loadedImg.height;
                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;

                        ctx.drawImage(loadedImg, 0, 0, width, height);

                        const markupModalEl = document.getElementById('imageMarkupModal');
                        const modal = new bootstrap.Modal(markupModalEl);
                        modal.show();
                    };
                    loadedImg.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            drawing = true;
            savedImageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const coords = getCoordinates(e);
            startX = coords.x;
            startY = coords.y;

            if (currentTool === 'pencil') {
                ctx.beginPath();
                ctx.moveTo(startX, startY);
                ctx.strokeStyle = currentColor;
                ctx.lineWidth = currentLineWidth;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
            }
        }

        function draw(e) {
            if (!drawing) return;
            if (e.touches) e.preventDefault();

            const coords = getCoordinates(e);
            const x = coords.x;
            const y = coords.y;

            if (currentTool === 'pencil') {
                ctx.lineTo(x, y);
                ctx.stroke();
            } else if (currentTool === 'circle') {
                ctx.putImageData(savedImageData, 0, 0);
                const radius = Math.sqrt(Math.pow(x - startX, 2) + Math.pow(y - startY, 2));
                ctx.beginPath();
                ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
                ctx.strokeStyle = currentColor;
                ctx.lineWidth = currentLineWidth;
                ctx.stroke();
            }
        }

        function stopDrawing() {
            if (drawing) {
                drawing = false;
                if (currentTool === 'pencil') {
                    ctx.closePath();
                }
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('tool-pencil').addEventListener('click', function() {
            currentTool = 'pencil';
            this.classList.add('active');
            document.getElementById('tool-circle').classList.remove('active');
        });

        document.getElementById('tool-circle').addEventListener('click', function() {
            currentTool = 'circle';
            this.classList.add('active');
            document.getElementById('tool-pencil').classList.remove('active');
        });

        const colorBtns = document.querySelectorAll('.color-btn');
        colorBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                colorBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentColor = this.getAttribute('data-color');
            });
        });

        document.getElementById('btn-clear-canvas').addEventListener('click', function() {
            if (loadedImg) {
                ctx.drawImage(loadedImg, 0, 0, canvas.width, canvas.height);
            }
        });

        document.getElementById('btn-send-markup').addEventListener('click', function() {
            const base64 = canvas.toDataURL('image/jpeg', 0.85);
            document.getElementById('chat-image-data').value = base64;

            const markupComment = document.getElementById('markup-comment-input').value;
            document.getElementById('whatsapp-comment-input').value = markupComment;

            const modalEl = document.getElementById('imageMarkupModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            chatImageInput.value = '';

            document.getElementById('chat-form').dispatchEvent(new Event('submit'));
        });

        document.getElementById('imageMarkupModal').addEventListener('hidden.bs.modal', function () {
            chatImageInput.value = '';
            document.getElementById('markup-comment-input').value = '';
        });
    }
</script>
@endpush
