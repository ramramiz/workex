@extends('layouts.app', [
    'noSidebar' => request()->has('user_id'),
    'noHeader' => request()->has('user_id')
])
{{-- CC field added to compose box --}}

@section('title', 'Mailbox')
@section('page-title', 'Mailbox')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mailbox</li>
@endsection

@push('styles')
<style>
/* ===================================================
   GMAIL-STYLE MAILBOX — Full-Height Fixed Layout
   =================================================== */

/* Force main content to fill viewport exactly, no padding bottom */
#main-content {
    padding: 0 !important;
    display: flex;
    flex-direction: column;
    height: {{ request()->has('user_id') ? '100vh' : 'calc(100vh - var(--topnav-height))' }};
    overflow: hidden;
}

.mailbox-page-wrap {
    display: flex;
    flex: 1;
    height: 100%;
    overflow: hidden;
    background: #f6f8fc;
    font-family: 'Google Sans', 'Roboto', Arial, sans-serif;
}

/* ---- SIDEBAR ---- */
.mb-sidebar {
    width: 240px;
    min-width: 240px;
    display: flex;
    flex-direction: column;
    padding: 8px 0;
    background: #f6f8fc;
    overflow-y: auto;
}

.mb-compose-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 8px 12px 16px;
    padding: 16px 22px;
    background: #c2e7ff;
    color: #001d35;
    border: none;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(60,64,67,.3), 0 4px 8px 3px rgba(60,64,67,.15);
    transition: background .18s, box-shadow .18s;
    white-space: nowrap;
}
.mb-compose-btn:hover {
    background: #aed9f5;
    box-shadow: 0 2px 6px rgba(60,64,67,.3), 0 6px 10px 4px rgba(60,64,67,.15);
}

.mb-nav-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 7px 12px 7px 24px;
    border-radius: 0 20px 20px 0;
    margin-right: 12px;
    color: #444746;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: background .15s;
}
.mb-nav-item:hover { background: #e8eaf0; color: #1f1f1f; }
.mb-nav-item.active { background: #d3e3fd; color: #041e49; font-weight: 700; }
.mb-nav-item i { font-size: 16px; }

.mb-nav-divider {
    margin: 12px 16px;
    border: none;
    border-top: 1px solid #e0e0e0;
}
.mb-nav-section-label {
    padding: 6px 24px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #757575;
}

/* ---- MAIN PANEL ---- */
.mb-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 16px;
    margin: 8px 8px 8px {{ request()->has('user_id') ? '8px' : '0' }};
    box-shadow: 0 1px 2px rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
    overflow: hidden;
    min-width: 0;
}

/* ---- TOOLBAR (shared) ---- */
.mb-toolbar {
    height: 48px;
    min-height: 48px;
    display: flex;
    align-items: center;
    padding: 0 8px 0 16px;
    border-bottom: 1px solid #e0e0e0;
    background: #fff;
    flex-shrink: 0;
    gap: 4px;
}
.mb-toolbar-spacer { flex: 1; }
.mb-toolbar-divider {
    width: 1px;
    height: 24px;
    background: #e0e0e0;
    margin: 0 4px;
}

.mb-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: none;
    color: #5f6368;
    font-size: 15px;
    cursor: pointer;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.mb-icon-btn:hover { background: #f1f3f4; color: #202124; }
.mb-icon-btn:disabled { opacity: .45; cursor: default; }

/* ---- SEARCH BAR ---- */
.mb-search-wrap {
    flex: 1;
    max-width: 640px;
}
.mb-search-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #eaf1fb;
    border-radius: 24px;
    padding: 0 16px;
    height: 36px;
    transition: background .15s, box-shadow .15s;
}
.mb-search-bar:focus-within {
    background: #fff;
    box-shadow: 0 1px 3px rgba(65,69,73,.3), 0 1px 3px 1px rgba(65,69,73,.15);
}
.mb-search-bar input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    color: #202124;
    width: 100%;
}
.mb-search-bar i { color: #5f6368; font-size: 15px; }

/* ---- MESSAGE LIST ---- */
.mb-list-scroll {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.mb-row {
    display: flex;
    align-items: center;
    height: 40px;
    padding: 0 16px;
    border-bottom: 1px solid #e0e0e0;
    cursor: pointer;
    font-size: 14px;
    color: #202124;
    position: relative;
    transition: box-shadow .12s, background .1s;
    background: #f1f3f4; /* Grey background for read emails */
}
.mb-row.unread {
    background: #ffffff; /* White background for unread emails */
    font-weight: 600;
}
.mb-row:hover {
    box-shadow: inset 1px 0 0 #dadce0, inset -1px 0 0 #dadce0, 0 1px 2px rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
    z-index: 1;
    background: #e8eaed; /* Hover for grey read rows */
}
.mb-row.unread:hover {
    background: #f8f9fa; /* Hover for white unread rows */
}

.mb-row-checks {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: 12px;
    flex-shrink: 0;
}
.mb-star {
    font-size: 14px;
    color: #c5c5c5;
    cursor: pointer;
    transition: color .15s;
}
.mb-star:hover { color: #5f6368; }
.mb-star.starred { color: #f4b400; }

.mb-sender {
    width: 170px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 12px;
}
.mb-row.unread .mb-sender { font-weight: 700; }

.mb-content {
    flex: 1;
    display: flex;
    align-items: center;
    min-width: 0;
    gap: 6px;
}
.mb-subject {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 38%;
    flex-shrink: 0;
}
.mb-snippet {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #5f6368;
    font-weight: 400;
    font-size: 13px;
}

.mb-date {
    width: 76px;
    text-align: right;
    font-size: 12px;
    color: #5f6368;
    flex-shrink: 0;
}
.mb-row.unread .mb-date { font-weight: 700; color: #1a0dab; }

.mb-row-hover-actions {
    display: none;
    position: absolute;
    right: 16px;
    top: 0; bottom: 0;
    align-items: center;
    gap: 4px;
    background: linear-gradient(to right, transparent, #f7f9fa 20px);
    padding-left: 24px;
}
.mb-row:hover .mb-row-hover-actions { display: flex; }

/* ---- DETAIL VIEW ---- */
.mb-detail {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
    height: 100%;
}

.mb-detail-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f3f4;
    flex-shrink: 0;
}
.mb-detail-subject {
    font-size: 22px;
    font-weight: 400;
    color: #202124;
    margin: 0 0 14px 0;
    line-height: 1.3;
}
.mb-detail-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.mb-detail-sender-row {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.mb-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e8eaed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #5f6368;
    font-size: 16px;
    flex-shrink: 0;
}
.mb-detail-sender-info { min-width: 0; }
.mb-detail-sender-name { font-weight: 600; font-size: 14px; color: #202124; }
.mb-detail-sender-email { font-size: 12px; color: #5f6368; }
.mb-detail-time { text-align: right; flex-shrink: 0; }
.mb-detail-time .date-full { font-size: 13px; color: #5f6368; }
.mb-detail-time .date-human { font-size: 12px; color: #9aa0a6; margin-top: 2px; }

.mb-detail-body-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px 40px;
    min-height: 0;
}
.mb-detail-body {
    font-size: 14px;
    line-height: 1.65;
    color: #202124;
    word-break: break-word;
}

/* Attachments */
.mb-attachments { margin-top: 28px; padding-top: 16px; border-top: 1px solid #f1f3f4; }
.mb-attach-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #f8f9fa;
    color: #3c4043;
    font-size: 13px;
    text-decoration: none;
    transition: background .15s;
}
.mb-attach-chip:hover { background: #e8eaed; }

/* ---- INLINE REPLY SECTION ---- */
.mb-quick-actions {
    flex-shrink: 0;
    padding: 0 28px 24px;
    display: flex;
    gap: 12px;
}
.mb-quick-actions .btn {
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    padding: 8px 22px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ===================================================
   FLOATING COMPOSE BOX (Gmail style)
   =================================================== */
.gm-compose {
    position: fixed;
    bottom: 0;
    right: 80px;
    width: 540px;
    height: 496px;
    background: #fff;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 8px 28px rgba(0,0,0,.22), 0 2px 8px rgba(0,0,0,.1);
    display: none;
    flex-direction: column;
    z-index: 1055;
    border: 1px solid #dadce0;
    border-bottom: none;
    transition: height .18s ease, width .18s ease;
    font-family: 'Google Sans', Roboto, Arial, sans-serif;
}
.gm-compose.is-minimized {
    height: 40px !important;
}
.gm-compose.is-maximized {
    width: 70vw !important;
    height: 80vh !important;
    right: 15% !important;
}

.gm-compose-header {
    height: 40px;
    min-height: 40px;
    background: #404040;
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: center;
    padding: 0 12px;
    cursor: pointer;
    gap: 8px;
    flex-shrink: 0;
}
.gm-compose-title {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gm-compose-ctrl {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: none;
    background: none;
    color: #d0d0d0;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.gm-compose-ctrl:hover { background: rgba(255,255,255,.15); color: #fff; }

/* Form fields */
.gm-compose-fields {
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #f1f3f4;
}
.gm-field-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f1f3f4;
    padding: 0 12px;
}
.gm-field-row:last-child { border-bottom: none; }
.gm-field-label { font-size: 13px; color: #5f6368; width: 38px; flex-shrink: 0; }
.gm-field-row input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 14px;
    color: #202124;
    padding: 9px 0;
    background: transparent;
}

/* Body */
.gm-body-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.gm-body-wrap textarea {
    flex: 1;
    border: none;
    outline: none;
    resize: none;
    font-size: 14px;
    line-height: 1.5;
    color: #202124;
    padding: 12px 14px;
    background: transparent;
    overflow-y: auto;
}

/* Attachment bar */
.gm-attach-bar {
    display: none;
    align-items: center;
    justify-content: space-between;
    padding: 4px 14px;
    background: #f8f9fa;
    border-top: 1px solid #f1f3f4;
    font-size: 12px;
    color: #5f6368;
}
.gm-attach-bar.visible { display: flex; }

/* AI button (floating inside body) */
.gm-ai-btn {
    position: absolute;
    bottom: 10px;
    right: 14px;
    background: #e8f0fe;
    color: #1a73e8;
    border: none;
    border-radius: 16px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    display: none;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
    transition: background .15s;
    z-index: 5;
}
.gm-ai-btn:hover { background: #d2e3fc; }

/* Compose footer */
.gm-compose-footer {
    height: 56px;
    min-height: 56px;
    display: flex;
    align-items: center;
    padding: 0 12px;
    border-top: 1px solid #f1f3f4;
    gap: 8px;
    flex-shrink: 0;
}
.gm-send-btn {
    background: #0b57d0;
    color: #fff;
    border: none;
    border-radius: 18px;
    padding: 8px 22px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, box-shadow .15s;
    white-space: nowrap;
}
.gm-send-btn:hover { background: #0842a0; }
.gm-send-btn:disabled { opacity: .65; cursor: default; }

.gm-compose-tool {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: none;
    color: #5f6368;
    font-size: 16px;
    cursor: pointer;
    transition: background .15s;
}
.gm-compose-tool:hover { background: #f1f3f4; color: #202124; }

.gm-compose-footer-spacer { flex: 1; }
.gm-discard-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: none;
    color: #5f6368;
    font-size: 16px;
    cursor: pointer;
    transition: background .15s;
}
.gm-discard-btn:hover { background: #fce8e6; color: #c5221f; }

/* ===================================================
   STATES / LOADING
   =================================================== */
.mb-loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #5f6368;
    font-size: 13px;
    gap: 10px;
}
.mb-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 280px;
    color: #9aa0a6;
    font-size: 13px;
    gap: 8px;
}
.mb-empty-state i { font-size: 40px; }
.mb-error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 280px;
    color: #c5221f;
    font-size: 13px;
    gap: 8px;
    text-align: center;
    padding: 24px;
}
.mb-error-state i { font-size: 40px; }

/* ===================================================
   SETTINGS MODAL tweaks
   =================================================== */
#personalMailboxSettingsModal .modal-content { border-radius: 16px; }

/* Spin animation for fetch button icon */
@keyframes spin { to { transform: rotate(360deg); } }
.spin-anim { display: inline-block; animation: spin .8s linear infinite; }
</style>
@endpush

@section('content')

<div class="mailbox-page-wrap">

    <!-- ======================== SIDEBAR ======================== -->
    <div class="mb-sidebar">
        <button class="mb-compose-btn" id="btn-compose-open">
            <i class="bi bi-pencil-fill"></i> Compose
        </button>

        <a href="javascript:void(0)" onclick="selectFolder('inbox')"
           class="mb-nav-item {{ $folder === 'inbox' ? 'active' : '' }}" id="menu-inbox">
            <i class="bi bi-inbox-fill"></i> Inbox
        </a>
        <a href="javascript:void(0)" onclick="selectFolder('sent')"
           class="mb-nav-item {{ $folder === 'sent' ? 'active' : '' }}" id="menu-sent">
            <i class="bi bi-send-fill"></i> Sent
        </a>
        <a href="javascript:void(0)" onclick="selectFolder('trash')"
           class="mb-nav-item {{ $folder === 'trash' ? 'active' : '' }}" id="menu-trash">
            <i class="bi bi-trash3-fill"></i> Trash
        </a>

        <hr class="mb-nav-divider">
        <div class="d-flex align-items-center justify-content-between ps-4 pe-3">
            <span class="mb-nav-section-label" style="padding:0">Configuration</span>
            <button class="mb-icon-btn" data-bs-toggle="modal" data-bs-target="#personalMailboxSettingsModal" title="Mail Settings">
                <i class="bi bi-gear-fill"></i>
            </button>
        </div>
    </div>

    <!-- ======================== MAIN PANEL ======================== -->
    <div class="mb-main">

        <!-- === LIST VIEW === -->
        <div id="view-list" class="d-flex flex-column h-100">

            <!-- Toolbar -->
            <div class="mb-toolbar">
                <input type="checkbox" class="form-check-input m-0" id="select-all-cb" title="Select all">
                <button class="mb-icon-btn d-none" id="btn-bulk-delete" title="Delete selected"><i class="bi bi-trash3"></i></button>

                <!-- Fetch Now button -->
                <button id="btn-fetch-now" title="Check for new emails"
                    style="display:inline-flex;align-items:center;gap:7px;padding:5px 16px;background:#e8f0fe;color:#1a73e8;border:none;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s,box-shadow .15s;white-space:nowrap;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,.1);">
                    <i class="bi bi-arrow-clockwise" id="fetch-icon"></i>
                    <span id="fetch-label">Fetch Now</span>
                    <span id="fetch-badge" style="display:none;background:#d93025;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:700;"></span>
                </button>
                <span id="fetch-last-sync" style="font-size:11px;color:#9aa0a6;margin-left:4px;white-space:nowrap;"></span>

                <div class="mb-toolbar-spacer">
                    <div style="max-width:620px; margin: 0 auto;">
                        <div class="mb-search-bar">
                            <i class="bi bi-search"></i>
                            <input type="text" id="search-input" placeholder="Search mail">
                        </div>
                    </div>
                </div>

                <button class="mb-icon-btn" data-bs-toggle="modal" data-bs-target="#personalMailboxSettingsModal" title="Settings">
                    <i class="bi bi-gear"></i>
                </button>

                <div class="dropdown ms-2">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border-0 bg-transparent px-2" data-bs-toggle="dropdown" id="mailbox-user-dropdown" title="Mailbox Owner Details" style="border-radius: 20px; height: 34px;">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                        <span class="d-none d-md-inline-block text-truncate fw-medium text-secondary" style="max-width: 120px; font-size: 13px;">
                            {{ $user->name }}
                        </span>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 11px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="min-width: 240px; border-radius: 12px; z-index: 1050;">
                        <li class="px-3 py-2">
                            <div class="text-muted mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;">Viewing Mailbox of</div>
                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $user->name }}</div>
                            <div class="text-secondary font-monospace" style="font-size: 12px;">{{ $user->mailbox_imap_username ?: $user->email }}</div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Message rows -->
            <div class="mb-list-scroll" id="msg-list">
                <div class="mb-loading-state">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span>Loading...</span>
                </div>
            </div>
        </div>

        <!-- === DETAIL VIEW (hidden initially) === -->
        <div id="view-detail" class="d-none flex-column h-100">

            <!-- Detail Toolbar -->
            <div class="mb-toolbar">
                <button class="mb-icon-btn" id="btn-back" title="Back"><i class="bi bi-arrow-left"></i></button>
                <div class="mb-toolbar-divider"></div>
                <button class="mb-icon-btn" title="Archive"><i class="bi bi-archive"></i></button>
                <button class="mb-icon-btn" title="Spam"><i class="bi bi-exclamation-octagon"></i></button>
                <button class="mb-icon-btn" id="btn-delete" title="Delete"><i class="bi bi-trash3"></i></button>
                <div class="mb-toolbar-divider"></div>
                <button class="mb-icon-btn" title="Mark unread"><i class="bi bi-envelope"></i></button>
                <button class="mb-icon-btn" title="Snooze"><i class="bi bi-clock"></i></button>
                <button class="mb-icon-btn" title="Add to Tasks"><i class="bi bi-check2-circle"></i></button>
                <div class="mb-toolbar-divider"></div>
                <button class="mb-icon-btn" title="Move to"><i class="bi bi-folder-symlink"></i></button>
                <button class="mb-icon-btn" title="Labels"><i class="bi bi-tag"></i></button>
                <button class="mb-icon-btn" title="More"><i class="bi bi-three-dots-vertical"></i></button>
                <div class="mb-toolbar-spacer"></div>
                <button class="mb-icon-btn" id="btn-reply-toolbar" title="Reply"><i class="bi bi-reply-fill"></i></button>
                <button class="mb-icon-btn" id="btn-forward-toolbar" title="Forward"><i class="bi bi-forward-fill"></i></button>

                <div class="dropdown ms-2">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border-0 bg-transparent px-2" data-bs-toggle="dropdown" id="mailbox-user-dropdown-detail" title="Mailbox Owner Details" style="border-radius: 20px; height: 34px;">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                        <span class="d-none d-md-inline-block text-truncate fw-medium text-secondary" style="max-width: 120px; font-size: 13px;">
                            {{ $user->name }}
                        </span>
                        <i class="bi bi-chevron-down text-muted" style="font-size: 11px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="min-width: 240px; border-radius: 12px; z-index: 1050;">
                        <li class="px-3 py-2">
                            <div class="text-muted mb-1" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;">Viewing Mailbox of</div>
                            <div class="fw-bold text-dark" style="font-size: 13px;">{{ $user->name }}</div>
                            <div class="text-secondary font-monospace" style="font-size: 12px;">{{ $user->mailbox_imap_username ?: $user->email }}</div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Subject + Sender meta -->
            <div class="mb-detail-header">
                <h4 class="mb-detail-subject" id="d-subject">Loading…</h4>
                <div class="mb-detail-meta">
                    <div class="mb-detail-sender-row">
                        <div class="mb-avatar" id="d-avatar">?</div>
                        <div class="mb-detail-sender-info">
                            <div class="mb-detail-sender-name" id="d-sender-name">—</div>
                            <div class="mb-detail-sender-email" id="d-sender-email"></div>
                        </div>
                    </div>
                    <div class="mb-detail-time">
                        <div class="date-full" id="d-time"></div>
                        <div class="date-human" id="d-time-human"></div>
                    </div>
                </div>
            </div>

            <!-- Scrollable body -->
            <div class="mb-detail-body-scroll">
                <div class="mb-detail-body" id="d-body">
                    <div class="mb-loading-state"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>

                <!-- Attachments -->
                <div id="d-attachments" class="mb-attachments d-none">
                    <div class="text-muted fw-semibold mb-2" style="font-size:12px;"><i class="bi bi-paperclip me-1"></i>Attachments</div>
                    <div id="d-attach-list" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Inline reply / forward buttons at the bottom -->
            <div class="mb-quick-actions" id="d-quick-actions">
                <button type="button" class="btn btn-outline-secondary" id="btn-reply-inline">
                    <i class="bi bi-reply-fill"></i> Reply
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btn-forward-inline">
                    <i class="bi bi-forward-fill"></i> Forward
                </button>
            </div>

        </div>
        <!-- /detail view -->

    </div>
    <!-- /mb-main -->
</div>
<!-- /mailbox-page-wrap -->


<!-- ======================== FLOATING COMPOSE BOX ======================== -->
<div class="gm-compose" id="gm-compose">

    <!-- Header bar -->
    <div class="gm-compose-header" id="gm-compose-header">
        <span class="gm-compose-title" id="gm-compose-title">New Message</span>
        <button class="gm-compose-ctrl" id="btn-cm-minimize" title="Minimize"><i class="bi bi-dash-lg"></i></button>
        <button class="gm-compose-ctrl" id="btn-cm-expand" title="Maximize/Restore"><i class="bi bi-arrows-angle-expand" id="icon-cm-expand"></i></button>
        <button class="gm-compose-ctrl" id="btn-cm-close" title="Close"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Form -->
    <form id="compose-form" method="POST" action="{{ route('mailbox.store') }}" enctype="multipart/form-data"
          class="d-flex flex-column" style="flex:1; overflow:hidden; min-height:0;">
        @csrf

        <div class="gm-compose-fields">
            <div class="gm-field-row">
                <span class="gm-field-label">To</span>
                <input type="email" name="to_email" id="cm-to" required autocomplete="off">
                <button type="button" id="cm-cc-toggle" title="Add Cc" style="flex-shrink:0;background:none;border:none;color:#5f6368;font-size:12px;font-weight:500;padding:4px 8px;border-radius:4px;cursor:pointer;letter-spacing:.02em;transition:color .15s;white-space:nowrap;">Cc</button>
            </div>
            <div class="gm-field-row" id="cm-cc-row" style="display:none;">
                <span class="gm-field-label">Cc</span>
                <input type="email" name="cc_email" id="cm-cc" autocomplete="off" placeholder="Cc recipients (comma-separated)" style="width:100%;">
                <button type="button" id="cm-cc-remove" title="Remove Cc" style="flex-shrink:0;background:none;border:none;color:#9aa0a6;font-size:18px;line-height:1;padding:4px 6px;cursor:pointer;">&times;</button>
            </div>
            <div class="gm-field-row">
                <input type="text" name="subject" id="cm-subject" placeholder="Subject" required autocomplete="off">
            </div>
        </div>

        <div class="gm-body-wrap">
            <textarea name="body" id="cm-body" placeholder="Compose email…" required></textarea>
            <button type="button" class="gm-ai-btn" id="cm-ai-btn">
                <i class="bi bi-stars"></i> AI Correct
            </button>
        </div>

        <!-- Attachment name indicator -->
        <div class="gm-attach-bar" id="cm-attach-bar">
            <span><i class="bi bi-paperclip me-1"></i><span id="cm-attach-name">file</span></span>
            <button type="button" id="cm-remove-attach" style="background:none;border:none;color:#c5221f;cursor:pointer;font-size:16px;line-height:1;"><i class="bi bi-x"></i></button>
        </div>

        <!-- Hidden file input -->
        <input type="file" name="attachment" id="cm-file" class="d-none"
               accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.rar">

        <div class="gm-compose-footer">
            <button type="submit" class="gm-send-btn" id="cm-send-btn">Send</button>

            <button type="button" class="gm-compose-tool" id="cm-attach-btn" title="Attach file"><i class="bi bi-paperclip"></i></button>
            <button type="button" class="gm-compose-tool" title="Formatting"><i class="bi bi-type"></i></button>
            <button type="button" class="gm-compose-tool" title="Emoji"><i class="bi bi-emoji-smile"></i></button>
            <button type="button" class="gm-compose-tool" title="Insert link"><i class="bi bi-link-45deg"></i></button>

            <div class="gm-compose-footer-spacer"></div>
            <button type="button" class="gm-discard-btn" id="cm-discard-btn" title="Discard draft"><i class="bi bi-trash3"></i></button>
        </div>

    </form>
</div>


<!-- ======================== SETTINGS MODAL ======================== -->
<div class="modal fade" id="personalMailboxSettingsModal" tabindex="-1" aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Mailbox Server Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="settings-form" method="POST" action="{{ route('mailbox.settings.save') }}">
                @csrf
                @php $u = $user; @endphp
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="mailbox_imap_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="mailbox_imap_enabled" value="1"
                                   id="imap-enabled" {{ $u->mailbox_imap_enabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold ms-2" for="imap-enabled">Enable Domain Mailbox</label>
                        </div>
                        <div class="form-text text-muted">Connect your custom domain email via IMAP/SMTP.</div>
                    </div>

                    <div class="{{ $u->mailbox_imap_enabled ? '' : 'd-none' }}" id="cfg-fields">
                        <!-- IMAP -->
                        <div class="border rounded p-3 mb-3 bg-light-subtle">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-download me-1"></i> Incoming Mail (IMAP)</h6>
                            <div class="row g-2">
                                <div class="col-md-8"><label class="form-label fw-semibold">IMAP Host</label>
                                    <input type="text" name="mailbox_imap_host" id="imap-host" class="form-control" value="{{ $u->mailbox_imap_host }}" placeholder="imap.domain.com">
                                </div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Port</label>
                                    <input type="text" name="mailbox_imap_port" id="imap-port" class="form-control" value="{{ $u->mailbox_imap_port ?: '993' }}" placeholder="993">
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Encryption</label>
                                    <select name="mailbox_imap_encryption" id="imap-enc" class="form-select">
                                        <option value="ssl" {{ $u->mailbox_imap_encryption==='ssl'?'selected':'' }}>SSL (Port 993)</option>
                                        <option value="tls" {{ $u->mailbox_imap_encryption==='tls'?'selected':'' }}>TLS (Port 143)</option>
                                        <option value="none" {{ $u->mailbox_imap_encryption==='none'?'selected':'' }}>None</option>
                                    </select>
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Username / Email</label>
                                    <input type="email" name="mailbox_imap_username" id="imap-user" class="form-control" value="{{ $u->mailbox_imap_username }}" placeholder="you@domain.com">
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Password</label>
                                    <input type="password" name="mailbox_imap_password" id="imap-pass" class="form-control" placeholder="Leave blank to keep existing">
                                </div>
                            </div>
                        </div>
                        <!-- SMTP -->
                        <div class="border rounded p-3 bg-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-primary m-0"><i class="bi bi-upload me-1"></i> Outgoing Mail (SMTP)</h6>
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" id="sync-smtp" checked>
                                    <label class="form-check-label text-muted fw-semibold" for="sync-smtp" style="font-size:13px;">Same as IMAP</label>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-8"><label class="form-label fw-semibold">SMTP Host</label>
                                    <input type="text" name="mailbox_smtp_host" id="smtp-host" class="form-control" value="{{ $u->mailbox_smtp_host ?: $u->mailbox_imap_host }}" placeholder="smtp.domain.com">
                                </div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Port</label>
                                    <input type="text" name="mailbox_smtp_port" id="smtp-port" class="form-control" value="{{ $u->mailbox_smtp_port ?: '465' }}" placeholder="465">
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Encryption</label>
                                    <select name="mailbox_smtp_encryption" id="smtp-enc" class="form-select">
                                        <option value="ssl" {{ $u->mailbox_smtp_encryption==='ssl'?'selected':'' }}>SSL (Port 465)</option>
                                        <option value="tls" {{ $u->mailbox_smtp_encryption==='tls'?'selected':'' }}>TLS (Port 587)</option>
                                        <option value="none" {{ $u->mailbox_smtp_encryption==='none'?'selected':'' }}>None</option>
                                    </select>
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Username</label>
                                    <input type="email" name="mailbox_smtp_username" id="smtp-user" class="form-control" value="{{ $u->mailbox_smtp_username ?: $u->mailbox_imap_username }}" placeholder="you@domain.com">
                                </div>
                                <div class="col-12"><label class="form-label fw-semibold">Password</label>
                                    <input type="password" name="mailbox_smtp_password" id="smtp-pass" class="form-control" placeholder="Leave blank to keep existing">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btn-save-settings">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* =============================================
   MAILBOX JS  —  Gmail-style UX
   ============================================= */
const CSRF   = '{{ csrf_token() }}';
let currentFolder = '{{ $folder }}';
let activeUid    = null;
let activeData   = null;
let settingsModal = null;
const activeMailboxUserId = '{{ $user->id }}';

function selectFolder(folder) {
    currentFolder = folder;
    loadFolder(folder);
}

/* ---- helpers ---- */
function formatLocalTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + 
           date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
}

function stripHtml(html) {
    if (!html) return '';
    try {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        return (doc.body.textContent || '').trim();
    } catch { return html.replace(/<[^>]*>/g, ''); }
}

function showAlert(msg, type = 'danger') {
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 start-50 translate-middle-x mb-4`;
    div.style.cssText = 'z-index:9999;min-width:300px;max-width:520px;';
    div.innerHTML = msg + `<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(div);
    setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(div).close(); } catch {} }, 5000);
}

/* ============ COMPOSE BOX ============ */
const compose    = document.getElementById('gm-compose');
const cmTitle    = document.getElementById('gm-compose-title');
const cmTo       = document.getElementById('cm-to');
const cmCc       = document.getElementById('cm-cc');
const cmCcRow    = document.getElementById('cm-cc-row');
const cmSubject  = document.getElementById('cm-subject');
const cmBody     = document.getElementById('cm-body');
const cmAiBtn    = document.getElementById('cm-ai-btn');
const cmAttachBar = document.getElementById('cm-attach-bar');
const cmAttachName = document.getElementById('cm-attach-name');
const expandIcon = document.getElementById('icon-cm-expand');

function composeOpen(title = 'New Message', to = '', subject = '', body = '') {
    cmTitle.textContent = title;
    cmTo.value      = to;
    cmSubject.value = subject;
    cmBody.value    = body;
    compose.style.display = 'flex';
    compose.classList.remove('is-minimized', 'is-maximized');
    if (!to) setTimeout(() => cmTo.focus(), 80);
    else     setTimeout(() => { cmBody.focus(); cmBody.setSelectionRange(0,0); }, 80);
    syncAiBtn();
}
function composeClose() {
    compose.style.display = 'none';
    document.getElementById('compose-form').reset();
    cmAttachBar.classList.remove('visible');
    cmAiBtn.style.display = 'none';
    // Hide CC row and clear it
    cmCcRow.style.display = 'none';
    cmCc.value = '';
}
function composeMinimize() {
    compose.classList.toggle('is-minimized');
}
function composeMaximize() {
    compose.classList.toggle('is-maximized');
    if (compose.classList.contains('is-maximized')) {
        expandIcon.className = 'bi bi-arrows-angle-contract';
    } else {
        expandIcon.className = 'bi bi-arrows-angle-expand';
    }
}
function syncAiBtn() {
    cmAiBtn.style.display = cmBody.value.trim().length > 10 ? 'inline-flex' : 'none';
}

/* Compose open from sidebar button */
document.getElementById('btn-compose-open').addEventListener('click', () => composeOpen());

/* Compose controls */
document.getElementById('btn-cm-close').addEventListener('click', e => { e.stopPropagation(); composeClose(); });
document.getElementById('btn-cm-minimize').addEventListener('click', e => { e.stopPropagation(); composeMinimize(); });
document.getElementById('btn-cm-expand').addEventListener('click', e => { e.stopPropagation(); composeMaximize(); });
document.getElementById('gm-compose-header').addEventListener('click', composeMinimize);

/* Discard */
document.getElementById('cm-discard-btn').addEventListener('click', () => {
    if (confirm('Discard this draft?')) composeClose();
});

/* CC toggle */
document.getElementById('cm-cc-toggle').addEventListener('click', e => {
    e.stopPropagation();
    cmCcRow.style.display = 'flex';
    cmCc.focus();
});
document.getElementById('cm-cc-remove').addEventListener('click', e => {
    e.stopPropagation();
    cmCcRow.style.display = 'none';
    cmCc.value = '';
});

/* Attach file */
document.getElementById('cm-attach-btn').addEventListener('click', () => document.getElementById('cm-file').click());
document.getElementById('cm-file').addEventListener('change', function() {
    if (this.files[0]) {
        const f = this.files[0];
        cmAttachName.textContent = `${f.name} (${(f.size/1024/1024).toFixed(2)} MB)`;
        cmAttachBar.classList.add('visible');
    }
});
document.getElementById('cm-remove-attach').addEventListener('click', () => {
    document.getElementById('cm-file').value = '';
    cmAttachBar.classList.remove('visible');
});

/* AI correct */
cmBody.addEventListener('input', syncAiBtn);
cmAiBtn.addEventListener('click', async () => {
    const text = cmBody.value;
    cmAiBtn.disabled = true;
    cmAiBtn.innerHTML = `<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;border-width:2px;"></span> Correcting…`;
    try {
        const r = await fetch('{{ route("ai.correct") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ text })
        });
        const d = await r.json();
        if (d.success && d.corrected) { cmBody.value = d.corrected; }
        else showAlert(d.error || d.message || 'AI correction failed.');
    } catch { showAlert('AI correction failed.'); }
    cmAiBtn.disabled = false;
    cmAiBtn.innerHTML = `<i class="bi bi-stars"></i> AI Correct`;
    syncAiBtn();
});

/* Compose send */
document.getElementById('compose-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('cm-send-btn');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm" style="width:12px;height:12px;border-width:2px;"></span> Sending…`;
    try {
        const formData = new FormData(this);
        formData.append('user_id', activeMailboxUserId);
        const r = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await r.json();
        if (d.success) {
            composeClose();
            showAlert('<i class="bi bi-check-circle-fill me-2"></i>Mail sent successfully!', 'success');
            loadFolder(currentFolder);
        } else {
            showAlert(d.error || d.message || 'Failed to send mail.');
        }
    } catch { showAlert('Failed to send mail. Check your SMTP settings.'); }
    btn.disabled = false;
    btn.innerHTML = 'Send';
});


/* ============ MAIL LIST ============ */

/* Render a list of message objects into #msg-list */
function renderMessages(messages, folder) {
    const list = document.getElementById('msg-list');
    if (!messages || !messages.length) {
        list.innerHTML = `<div class="mb-empty-state"><i class="bi bi-envelope-open"></i><span>No emails in this folder.</span></div>`;
        return;
    }
    // Sort descending by timestamp (latest messages first)
    messages.sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0));

    list.innerHTML = messages.map(m => {
        const displayName = folder === 'sent' 
            ? 'To: ' + (m.to_name && m.to_name !== m.to_email ? m.to_name : (m.to_email || 'No Recipient')) 
            : (m.sender_name && m.sender_name !== m.sender_email ? m.sender_name : (m.sender_email || ''));
        const senderClean = displayName.replace(/"/g, '');
        const searchSender = folder === 'sent' 
            ? (m.to_name || m.to_email || '') 
            : (m.sender_name || m.sender_email || '');
        const subjectSafe = (m.subject || '').replace(/"/g, '&quot;');
        const unreadClass = m.is_seen ? '' : 'unread';
        return `
        <div class="mb-row ${unreadClass}" data-uid="${m.uid}" data-sender="${searchSender.toLowerCase()}" data-subject="${(m.subject||'').toLowerCase()}" onclick="handleRowClick(event,${m.uid},'${folder}')">
            <div class="mb-row-checks" onclick="event.stopPropagation()">
                <input type="checkbox" class="form-check-input mb-cb" data-uid="${m.uid}" onchange="syncBulkBtn()">
                <i class="bi bi-star mb-star" onclick="toggleStar(this)"></i>
            </div>
            <div class="mb-sender">${senderClean}</div>
            <div class="mb-content">
                <span class="mb-subject">${m.subject}</span>
                <span class="mb-snippet">${m.snippet ? '— ' + m.snippet : ''}</span>
            </div>
            ${m.has_attachment ? '<i class="bi bi-paperclip text-muted me-2" style="font-size:13px;"></i>' : ''}
            <div class="mb-date" title="${formatLocalTime(m.timestamp)}">${m.created_at_human}</div>
            <div class="mb-row-hover-actions" onclick="event.stopPropagation()">
                <button class="mb-icon-btn" onclick="deleteMail(${m.uid},'${folder}')" title="Delete"><i class="bi bi-trash3"></i></button>
            </div>
        </div>`;
    }).join('');
    document.getElementById('select-all-cb').checked = false;
}

/* Update last-synced label */
function updateSyncLabel() {
    const now = new Date();
    const t = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('fetch-last-sync').textContent = `Synced at ${t}`;
}

/* loadFolder — serves from cache INSTANTLY, no IMAP */
function loadFolder(folder) {
    /* highlight active nav */
    document.querySelectorAll('.mb-nav-item').forEach(el => el.classList.remove('active'));
    const navEl = document.getElementById(`menu-${folder}`);
    if (navEl) navEl.classList.add('active');

    /* switch to list pane */
    document.getElementById('view-detail').classList.remove('d-flex');
    document.getElementById('view-detail').classList.add('d-none');
    document.getElementById('view-list').classList.remove('d-none');

    const list = document.getElementById('msg-list');
    list.innerHTML = `<div class="mb-loading-state"><div class="spinner-border spinner-border-sm text-primary"></div><span>Loading…</span></div>`;

    fetch(`{{ route('mailbox.official.index') }}?folder=${folder}&user_id=${activeMailboxUserId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                const err = data.error || data.message || '';
                if (err.includes('not enabled')) {
                    list.innerHTML = `
                        <div class="mb-empty-state">
                            <i class="bi bi-gear-wide-connected text-secondary"></i>
                            <span>Domain Mailbox is not enabled.</span>
                            <button class="btn btn-primary btn-sm px-4" style="border-radius:18px;" data-bs-toggle="modal" data-bs-target="#personalMailboxSettingsModal">Configure Settings</button>
                        </div>`;
                } else {
                    list.innerHTML = `<div class="mb-error-state"><i class="bi bi-exclamation-circle"></i><span>${err || 'Failed to load emails.'}</span></div>`;
                }
                return;
            }
            renderMessages(data.messages, folder);
            if (data.from_cache === false) {
                // First load — update sync label
                updateSyncLabel();
            } else {
                // From cache — show "cached" hint
                const el = document.getElementById('fetch-last-sync');
                if (!el.textContent) el.textContent = 'Cached';
            }
        })
        .catch(() => {
            list.innerHTML = `<div class="mb-error-state"><i class="bi bi-exclamation-circle"></i><span>Failed to connect to server.</span></div>`;
        });
}

/* fetchNewMail — hits IMAP, merges, shows new badge */
async function fetchNewMail(folder) {
    const btn      = document.getElementById('btn-fetch-now');
    const icon     = document.getElementById('fetch-icon');
    const label    = document.getElementById('fetch-label');
    const badge    = document.getElementById('fetch-badge');

    btn.disabled = true;
    icon.className = 'bi bi-arrow-clockwise spin-anim';
    label.textContent = 'Checking…';
    badge.style.display = 'none';

    try {
        const r = await fetch('{{ route("mailbox.fetch-new") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `folder=${encodeURIComponent(folder)}&user_id=${activeMailboxUserId}`
        });
        const data = await r.json();

        if (data.success) {
            renderMessages(data.messages, folder);
            updateSyncLabel();
            const n = data.new_count || 0;
            if (n > 0) {
                badge.textContent = `+${n} new`;
                badge.style.display = 'inline';
                showAlert(`<i class="bi bi-envelope-fill me-2"></i>${n} new email${n > 1 ? 's' : ''} received!`, 'success');
                // Auto-hide badge after 8s
                setTimeout(() => { badge.style.display = 'none'; }, 8000);
            } else {
                showAlert('<i class="bi bi-check-circle me-2"></i>Inbox is up to date.', 'info');
            }
        } else {
            showAlert(data.error || 'Failed to fetch emails.');
        }
    } catch {
        showAlert('Failed to connect to mail server. Check your IMAP settings.');
    }

    btn.disabled = false;
    icon.className = 'bi bi-arrow-clockwise';
    label.textContent = 'Fetch Now';
}

function handleRowClick(e, uid, folder) {
    if (e.target.closest('.mb-row-checks') || e.target.closest('.mb-row-hover-actions')) return;
    loadMail(uid, folder);
}
function toggleStar(el) {
    el.classList.toggle('bi-star');
    el.classList.toggle('bi-star-fill');
    el.classList.toggle('starred');
}
function syncBulkBtn() {
    const anyChecked = document.querySelectorAll('.mb-cb:checked').length > 0;
    document.getElementById('btn-bulk-delete').classList.toggle('d-none', !anyChecked);
}

document.getElementById('select-all-cb').addEventListener('change', function() {
    document.querySelectorAll('.mb-cb').forEach(cb => cb.checked = this.checked);
    syncBulkBtn();
});
document.getElementById('btn-fetch-now').addEventListener('click', () => fetchNewMail(currentFolder));

document.getElementById('search-input').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.mb-row').forEach(row => {
        const match = (row.dataset.sender||'').includes(q) || (row.dataset.subject||'').includes(q);
        row.classList.toggle('d-none', !match);
    });
});


/* ============ MAIL DETAIL ============ */
function loadMail(uid, folder) {
    if (activeUid === uid) return;
    activeUid = uid;

    /* Update row in list view to be read immediately */
    const rowEl = document.querySelector(`.mb-row[data-uid="${uid}"]`);
    if (rowEl) {
        rowEl.classList.remove('unread');
    }

    /* switch panes */
    document.getElementById('view-list').classList.add('d-none');
    const detail = document.getElementById('view-detail');
    detail.classList.remove('d-none');
    detail.classList.add('d-flex');

    /* reset state */
    document.getElementById('d-subject').textContent = 'Loading…';
    document.getElementById('d-sender-name').textContent = '—';
    document.getElementById('d-sender-email').textContent = '';
    document.getElementById('d-time').textContent = '';
    document.getElementById('d-time-human').textContent = '';
    document.getElementById('d-body').innerHTML = `<div class="mb-loading-state"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;
    document.getElementById('d-attachments').classList.add('d-none');

    fetch(`{{ url('mailbox/official') }}/${uid}?folder=${folder}&user_id=${activeMailboxUserId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('d-body').innerHTML = `<div class="mb-error-state"><i class="bi bi-exclamation-triangle"></i><span>${data.error||data.message||'Error loading message.'}</span></div>`;
                return;
            }
            activeData = data;

            document.getElementById('d-subject').textContent = data.subject || '(No Subject)';
            let displayName = data.sender_name;
            let displayEmail = data.sender_email;
            if (folder === 'sent') {
                if (data.to_name && data.to_name !== data.to_email) {
                    displayName = 'To: ' + data.to_name;
                    displayEmail = data.to_email;
                } else {
                    displayName = 'To: ' + (data.to_email || 'No Recipient');
                    displayEmail = '';
                }
            } else {
                if (data.sender_name && data.sender_name === data.sender_email) {
                    displayName = data.sender_email;
                    displayEmail = '';
                }
            }
            document.getElementById('d-sender-name').textContent = displayName;
            document.getElementById('d-sender-email').textContent = displayEmail ? `<${displayEmail}>` : '';
            document.getElementById('d-time').textContent = formatLocalTime(data.timestamp);
            document.getElementById('d-time-human').textContent = data.created_at_human;
            const avatarChar = (displayName.replace(/^To:\s*/i, '') || '?').trim().charAt(0).toUpperCase();
            document.getElementById('d-avatar').textContent = avatarChar;

            const bodyEl = document.getElementById('d-body');
            if (data.is_html) {
                bodyEl.innerHTML = data.body;
                bodyEl.style.whiteSpace = 'normal';
            } else {
                bodyEl.textContent = data.body;
                bodyEl.style.whiteSpace = 'pre-wrap';
            }

            /* Attachments */
            if (data.attachments && data.attachments.length) {
                document.getElementById('d-attach-list').innerHTML = data.attachments.map(a =>
                    `<a href="${a.url}" class="mb-attach-chip" download><i class="bi bi-file-earmark-arrow-down text-primary"></i><span>${a.name}</span></a>`
                ).join('');
                document.getElementById('d-attachments').classList.remove('d-none');
            }

            /* wire delete */
            document.getElementById('btn-delete').onclick = () => deleteMail(uid, folder, true);
        })
        .catch(() => {
            document.getElementById('d-body').innerHTML = `<div class="mb-error-state"><i class="bi bi-exclamation-triangle"></i><span>Error loading message. Please try again.</span></div>`;
        });
}

/* Back button */
document.getElementById('btn-back').addEventListener('click', () => {
    document.getElementById('view-detail').classList.remove('d-flex');
    document.getElementById('view-detail').classList.add('d-none');
    document.getElementById('view-list').classList.remove('d-none');
    activeUid = null;
    activeData = null;
});

/* Delete */
function deleteMail(uid, folder, fromDetail = false) {
    const msg = folder === 'trash' ? 'Permanently delete this email?' : 'Move to Trash?';
    if (!confirm(msg)) return;
    fetch(`{{ url('mailbox/official') }}/${uid}?folder=${folder}&user_id=${activeMailboxUserId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showAlert('<i class="bi bi-check-circle-fill me-2"></i>Deleted.', 'success');
            if (fromDetail) {
                document.getElementById('btn-back').click();
            }
            loadFolder(folder);
        } else {
            showAlert(d.error || d.message || 'Delete failed.');
        }
    })
    .catch(() => showAlert('Delete failed.'));
}


/* ============ REPLY / FORWARD ============ */
function doReply() {
    if (!activeData) return;
    let body = activeData.body || '';
    if (activeData.is_html) body = stripHtml(body);
    const lines = body.split(/\r?\n/).map(l => '> ' + l).join('\n');
    const quoted = `\n\n\n——————————————————————\nOn ${activeData.created_at_formatted}, ${activeData.sender_name} wrote:\n${lines}`;

    let subject = activeData.subject || '';
    if (!subject.toLowerCase().startsWith('re:')) subject = 'Re: ' + subject;

    composeOpen('Reply', activeData.sender_email, subject, quoted);
}

function doForward() {
    if (!activeData) return;
    let body = activeData.body || '';
    if (activeData.is_html) body = stripHtml(body);
    const lines = body.split(/\r?\n/).map(l => '> ' + l).join('\n');
    const fwdHeader = `\n\n——————————————————————\nForwarded message\nFrom: ${activeData.sender_name} <${activeData.sender_email}>\nDate: ${activeData.created_at_formatted}\nSubject: ${activeData.subject}\n\n${lines}`;

    let subject = activeData.subject || '';
    if (!subject.toLowerCase().startsWith('fwd:')) subject = 'Fwd: ' + subject;

    composeOpen('Forward', '', subject, fwdHeader);
}

document.getElementById('btn-reply-toolbar').addEventListener('click', doReply);
document.getElementById('btn-forward-toolbar').addEventListener('click', doForward);
document.getElementById('btn-reply-inline').addEventListener('click', doReply);
document.getElementById('btn-forward-inline').addEventListener('click', doForward);


/* ============ SETTINGS ============ */
settingsModal = new bootstrap.Modal(document.getElementById('personalMailboxSettingsModal'));

document.getElementById('imap-enabled').addEventListener('change', function() {
    document.getElementById('cfg-fields').classList.toggle('d-none', !this.checked);
});

/* Sync SMTP = IMAP */
const syncCb   = document.getElementById('sync-smtp');
const imapHost = document.getElementById('imap-host');
const imapUser = document.getElementById('imap-user');
const imapPass = document.getElementById('imap-pass');
const smtpHost = document.getElementById('smtp-host');
const smtpUser = document.getElementById('smtp-user');
const smtpPass = document.getElementById('smtp-pass');

function syncSmtp() {
    const on = syncCb.checked;
    smtpHost.readOnly = on; if (on) smtpHost.value = imapHost.value;
    smtpUser.readOnly = on; if (on) smtpUser.value = imapUser.value;
    smtpPass.readOnly = on; if (on) smtpPass.value = imapPass.value;
}
syncCb.addEventListener('change', syncSmtp);
[imapHost, imapUser, imapPass].forEach(el => el.addEventListener('input', syncSmtp));
syncSmtp();

document.getElementById('settings-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    syncSmtp();
    const btn = document.getElementById('btn-save-settings');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Saving…`;
    try {
        const formData = new FormData(this);
        formData.append('user_id', activeMailboxUserId);
        const r = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const d = await r.json();
        if (d.success) {
            settingsModal.hide();
            showAlert('<i class="bi bi-check-circle-fill me-2"></i>Settings saved!', 'success');
            loadFolder(currentFolder);
        } else {
            showAlert(d.error || d.message || 'Failed to save settings.');
        }
    } catch { showAlert('Error saving settings.'); }
    btn.disabled = false;
    btn.innerHTML = 'Save Settings';
});


/* ============ INITIAL LOAD ============ */
document.addEventListener('DOMContentLoaded', () => {
    loadFolder(currentFolder);
});
</script>
@endpush
