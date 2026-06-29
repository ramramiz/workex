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
        border: 1px solid var(--boarder-color);
        position: relative;
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
    .chat-filter-btn {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }
    .chat-filter-btn:hover {
        background: #e2e8f0;
        color: #334155;
    }
    .chat-filter-btn.active {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }
    [data-bs-theme="dark"] .chat-filter-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    [data-bs-theme="dark"] .chat-filter-btn:hover {
        background: #334155;
        color: #f1f5f9;
    }
    [data-bs-theme="dark"] .chat-filter-btn.active {
        background: #f8fafc;
        border-color: #f8fafc;
        color: #0f172a;
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
    .min-width-0 {
        min-width: 0 !important;
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
    .chat-header select.form-select.status-select-rejected {
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

    @media (max-width: 767.98px) {
        .chat-sidebar {
            width: 100% !important;
            border-right: none !important;
        }
        .chat-main {
            width: 100% !important;
            flex: none !important;
        }
        
        /* If no chat is active, hide main window */
        .chat-layout:not(.chat-show-main) .chat-sidebar {
            display: flex !important;
        }
        .chat-layout:not(.chat-show-main) .chat-main {
            display: none !important;
        }

        /* If chat is active, hide sidebar and show main window */
        .chat-layout.chat-show-main .chat-sidebar {
            display: none !important;
        }
        .chat-layout.chat-show-main .chat-main {
            display: flex !important;
        }
        
        /* Adjust layout spacing for mobile */
        .chat-layout {
            height: calc(100vh - var(--topnav-height) - 16px);
            border-radius: 0;
            border: none;
        }
    }

    .chat-header-desktop-actions {
        display: flex !important;
        align-items: center;
        gap: 8px;
    }
    .chat-header-mobile-actions {
        display: none !important;
    }

    @media (max-width: 767.98px) {
        .chat-header-desktop-actions {
            display: none !important;
        }
        .chat-header-mobile-actions {
            display: block !important;
        }
    }

    .hover-bg-light-circle {
        transition: background-color 0.2s ease;
    }
    .hover-bg-light-circle:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }
    .chat-header-title a {
        color: #111b21 !important;
        text-decoration: none;
    }
    .chat-header-title a:hover {
        color: var(--primary) !important;
        text-decoration: underline !important;
    }

    /* WhatsApp Task Info Sidebar Styles */
    .chat-info-sidebar {
        width: 320px;
        border-left: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        background: #ffffff;
        flex-shrink: 0;
        height: 100%;
        animation: slide-in-info 0.2s ease-out;
    }
    .chat-info-header {
        height: 72px;
        flex-shrink: 0;
    }
    .chat-info-body {
        flex: 1;
        overflow-y: auto;
    }
    .uppercase-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    @keyframes slide-in-info {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    @media (max-width: 767.98px) {
        .chat-info-sidebar {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 100% !important;
            height: 100% !important;
            z-index: 1020;
            border-left: none !important;
        }
    }

    /* Unified Chat Switcher Styling */
    .chat-tabs {
        display: flex;
        padding: 10px 16px;
        background: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        gap: 10px;
        flex-shrink: 0;
    }
    .chat-tab-btn {
        flex: 1;
        border: none;
        background: transparent;
        padding: 8px 12px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-secondary);
        border-radius: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .chat-tab-btn:hover {
        background: rgba(99, 102, 241, 0.05);
        color: var(--primary);
    }
    .chat-tab-btn.active {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
    }
    .chat-user-item {
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
    .chat-user-item:hover {
        background: #f8fafc;
    }
    .chat-user-item.active {
        background: #f0f7ff;
        border-left: 4px solid var(--primary);
    }
    /* Dark Mode support for direct user items */
    [data-bs-theme="dark"] .chat-user-item {
        border-bottom-color: var(--border-color);
    }
    [data-bs-theme="dark"] .chat-user-item:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    [data-bs-theme="dark"] .chat-user-item.active {
        background: rgba(99, 102, 241, 0.15);
    }
    /* WhatsApp "New Chat" styling rules */
    #sidebar-new-chat-view {
        background-color: #ffffff;
    }
    #sidebar-new-chat-view .chat-sidebar-header {
        background-color: #008069 !important; /* WhatsApp Green */
        color: #ffffff !important;
        padding: 18px 24px !important;
        min-height: 64px;
        display: flex;
        align-items: center;
        border-bottom: none !important;
    }
    #sidebar-new-chat-view .chat-sidebar-header h5 {
        color: #ffffff !important;
        font-weight: 600;
        font-size: 17px;
        margin-left: 12px;
    }
    #sidebar-new-chat-view .chat-sidebar-header button {
        color: #ffffff !important;
        transition: transform 0.2s ease;
    }
    #sidebar-new-chat-view .chat-sidebar-header button:hover {
        transform: scale(1.1);
    }
    #sidebar-new-chat-view .chat-sidebar-search {
        padding: 10px 16px !important;
        background-color: #ffffff !important;
        border-bottom: none !important;
    }
    #sidebar-new-chat-view .chat-sidebar-search .input-group {
        background-color: #f0f2f5 !important;
        border-radius: 20px !important;
        border: 1px solid transparent !important;
        padding: 2px 14px !important;
        transition: all 0.2s ease;
    }
    #sidebar-new-chat-view .chat-sidebar-search .input-group:focus-within {
        background-color: #ffffff !important;
        border-color: #00a884 !important;
        box-shadow: 0 0 0 1px #00a884 !important;
    }
    #sidebar-new-chat-view .chat-sidebar-search .form-control {
        font-size: 14px !important;
        color: #111b21 !important;
        padding: 6px 8px !important;
        background-color: transparent !important;
        border: none !important;
    }
    #sidebar-new-chat-view .chat-sidebar-search .input-group-text {
        background-color: transparent !important;
        border: none !important;
        color: #667781 !important;
        padding-left: 0 !important;
    }
    #sidebar-new-chat-view .chat-task-item {
        padding: 12px 24px !important;
        border-bottom: none !important;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: background-color 0.15s ease;
    }
    #sidebar-new-chat-view .chat-task-item:hover {
        background-color: #f0f2f5 !important;
    }
    #sidebar-new-chat-view .new-chat-contact-item {
        padding: 10px 24px !important;
        border-bottom: none !important;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: background-color 0.15s ease;
    }
    #sidebar-new-chat-view .new-chat-contact-item:hover {
        background-color: #f0f2f5 !important;
    }
    #sidebar-new-chat-view .new-chat-green-circle {
        background-color: #00a884 !important; /* WhatsApp Green circle */
        color: #ffffff !important;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    #sidebar-new-chat-view .new-chat-green-circle i {
        color: #ffffff !important;
        font-size: 18px;
        line-height: 1;
    }
    #sidebar-new-chat-view .new-chat-section-header {
        background-color: #ffffff !important;
        padding: 18px 24px 8px 24px !important;
        color: #008069 !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
    }
    #sidebar-new-chat-view .fw-bold.text-dark {
        font-size: 14.5px !important;
        font-weight: 500 !important;
        color: #111b21 !important;
    }
    #sidebar-new-chat-view .new-chat-contact-item .fw-semibold {
        font-size: 14.5px !important;
        font-weight: 500 !important;
        color: #111b21 !important;
    }
    #sidebar-new-chat-view .new-chat-contact-item .text-muted {
        font-size: 13px !important;
        color: #667781 !important;
    }
    /* Dark Mode overrides for WhatsApp style view */
    [data-bs-theme="dark"] #sidebar-new-chat-view {
        background-color: #111b21 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-header {
        background-color: #202c33 !important;
        border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-search {
        background-color: #111b21 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-search .input-group {
        background-color: #202c33 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-search .form-control {
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-search .input-group:focus-within {
        background-color: #202c33 !important;
        border-color: #00a884 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-sidebar-search .input-group-text {
        color: #aebac1 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .chat-task-item:hover,
    [data-bs-theme="dark"] #sidebar-new-chat-view .new-chat-contact-item:hover {
        background-color: #202c33 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .fw-bold.text-dark,
    [data-bs-theme="dark"] #sidebar-new-chat-view .new-chat-contact-item .fw-semibold {
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .new-chat-contact-item .text-muted {
        color: #8696a0 !important;
    }
    [data-bs-theme="dark"] #sidebar-new-chat-view .new-chat-section-header {
        background-color: #111b21 !important;
        color: #00a884 !important;
    }

    /* Custom styles for inline creation forms in chat main pane */
    #chat-create-project-container .card, #chat-create-task-container .card, #chat-create-bug-container .card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    #chat-create-project-container .form-control, 
    #chat-create-project-container .form-select,
    #chat-create-task-container .form-control,
    #chat-create-task-container .form-select,
    #chat-create-bug-container .form-control,
    #chat-create-bug-container .form-select {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }
    #chat-create-project-container .form-control:focus, 
    #chat-create-project-container .form-select:focus,
    #chat-create-task-container .form-control:focus,
    #chat-create-task-container .form-select:focus,
    #chat-create-bug-container .form-control:focus,
    #chat-create-bug-container .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    #chat-create-project-container .form-label,
    #chat-create-task-container .form-label,
    #chat-create-bug-container .form-label {
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
    }
    [data-bs-theme="dark"] #chat-create-project-container .card,
    [data-bs-theme="dark"] #chat-create-task-container .card,
    [data-bs-theme="dark"] #chat-create-bug-container .card {
        background-color: #222e35 !important;
        border-color: #2a3942 !important;
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] #chat-create-project-container .form-control, 
    [data-bs-theme="dark"] #chat-create-project-container .form-select,
    [data-bs-theme="dark"] #chat-create-task-container .form-control,
    [data-bs-theme="dark"] #chat-create-task-container .form-select,
    [data-bs-theme="dark"] #chat-create-bug-container .form-control,
    [data-bs-theme="dark"] #chat-create-bug-container .form-select {
        background-color: #2a3942 !important;
        border-color: #3b4a54 !important;
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] #chat-create-project-container .form-label,
    [data-bs-theme="dark"] #chat-create-task-container .form-label,
    [data-bs-theme="dark"] #chat-create-bug-container .form-label {
        color: #8696a0 !important;
    }

    /* WhatsApp Web Placeholder styling */
    .chat-main-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        height: 100%;
        background-color: #efeae2;
        transition: background-color 0.2s ease;
    }
    [data-bs-theme="dark"] .chat-main-placeholder {
        background-color: #222e35 !important;
    }
    .whatsapp-placeholder-btn-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: transform 0.2s ease;
    }
    .whatsapp-placeholder-btn-container:hover {
        transform: scale(1.08);
    }
    .whatsapp-circle-btn {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background-color: #e9edef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        transition: background-color 0.2s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .whatsapp-circle-btn i {
        color: #54656f !important;
    }
    .whatsapp-placeholder-btn-container:hover .whatsapp-circle-btn {
        background-color: #d1d7db;
    }
    .whatsapp-btn-label {
        font-size: 13px;
        color: #667781;
        font-weight: 500;
        transition: color 0.2s ease;
    }
    .whatsapp-placeholder-btn-container:hover .whatsapp-btn-label {
        color: #111b21;
    }
    
    /* Ask Meta AI custom gradient style */
    .whatsapp-circle-btn.ask-meta-ai-btn {
        background: linear-gradient(135deg, #a855f7, #6366f1) !important;
        box-shadow: 0 4px 10px rgba(168, 85, 247, 0.25);
    }
    .whatsapp-circle-btn.ask-meta-ai-btn i {
        color: #ffffff !important;
    }
    .whatsapp-placeholder-btn-container:hover .whatsapp-circle-btn.ask-meta-ai-btn {
        opacity: 0.95;
    }

    /* Dark Mode overrides for buttons */
    [data-bs-theme="dark"] .whatsapp-circle-btn {
        background-color: #2a3942;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    [data-bs-theme="dark"] .whatsapp-circle-btn i {
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] .whatsapp-placeholder-btn-container:hover .whatsapp-circle-btn {
        background-color: #3b4a54;
    }
    [data-bs-theme="dark"] .whatsapp-btn-label {
        color: #8696a0;
    }
    [data-bs-theme="dark"] .whatsapp-placeholder-btn-container:hover .whatsapp-btn-label {
        color: #e9edef;
    }
    
    /* Dark Mode overrides for employee fallback placeholder */
    [data-bs-theme="dark"] .chat-main-placeholder h4 {
        color: #e9edef !important;
    }
    [data-bs-theme="dark"] .chat-main-placeholder p {
        color: #8696a0 !important;
    }
    [data-bs-theme="dark"] .whatsapp-circle-btn-disabled {
        background-color: #2a3942 !important;
    }
    [data-bs-theme="dark"] .whatsapp-circle-btn-disabled i {
        color: #e9edef !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="chat-layout">
        
        <!-- Left Sidebar: Unified Tasks & Direct Messages List -->
        <div class="chat-sidebar">
            <!-- MAIN VIEW (Chats list) -->
            <div id="sidebar-main-view" class="d-flex flex-column h-100 w-100">
                <!-- Sidebar Header -->
                <div class="chat-sidebar-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                    <h4 class="fw-bold mb-0 text-dark">Chats</h4>
                    <button class="btn btn-link text-muted p-1 hover-bg-light-circle" type="button" onclick="showNewChatView()" title="New Chat" style="border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; text-decoration: none; border: none; background: transparent;">
                        <i class="bi bi-plus-square-fill fs-5 text-primary"></i>
                    </button>
                </div>

                <!-- Sidebar Search -->
                <div class="chat-sidebar-search">
                    <div class="input-group">
                        <span class="input-group-text text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="task-search-input" class="form-control" placeholder="Search chats...">
                    </div>
                </div>

                <!-- Filters -->
                <div class="chat-sidebar-filters px-3 pb-2 d-flex align-items-center gap-2 flex-wrap" style="border-bottom: 1px solid rgba(0,0,0,0.05); margin-bottom: 8px;">
                    <button class="chat-filter-btn active" data-filter="all" onclick="filterChats('all')">All</button>
                    <button class="chat-filter-btn" data-filter="unread" onclick="filterChats('unread')">Unread</button>
                    <button class="chat-filter-btn" data-filter="bugs" onclick="filterChats('bugs')">Bugs</button>
                    <button class="chat-filter-btn" data-filter="review" onclick="filterChats('review')">Review</button>
                </div>

                <!-- Unified Chat List -->
                <div class="chat-task-list" id="chat-unified-container">
                    @forelse($unifiedItems as $item)
                        @if($item->type === 'task')
                            @php $t = $item->task; @endphp
                            <a href="javascript:void(0);" 
                               class="chat-task-item unified-chat-item" 
                               id="chat-task-item-{{ $t->id }}" 
                               data-chat-type="task"
                               data-task-id="{{ $t->id }}" 
                               data-title="{{ strtolower($t->title) }}" 
                               data-project="{{ strtolower($t->project->name ?? '') }}"
                               data-unread-count="{{ $item->unread_count }}"
                               data-is-bug="{{ $item->is_bug ? '1' : '0' }}"
                               data-priority="{{ $t->priority }}"
                               data-status="{{ $t->status }}"
                               onclick="selectTask({{ $t->id }})">
                                <div class="position-relative" style="margin-left: 5px;">
                                    <img src="{{ $t->avatar_url }}" alt="" class="avatar-circle">
                                    @php
                                        $badgeClass = $t->priority_badge;
                                    @endphp
                                    <span class="position-absolute top-0 start-0 badge bg-{{ $badgeClass }} {{ $badgeClass === 'warning' ? 'text-dark' : 'text-white' }} rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; border: 2px solid var(--card-bg); font-size: 8px; font-weight: 800; transform: translate(-30%, -30%); z-index: 10;" title="Priority: {{ ucfirst($t->priority) }}">
                                        {{ strtoupper(substr($t->priority, 0, 1)) }}
                                    </span>
                                    @if($t->status === 'review')
                                        @if(auth()->user()->isAdminOrAbove())
                                            <span class="position-absolute bottom-0 end-0 bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow-sm border" style="width: 18px; height: 18px; border-color: var(--card-bg) !important; font-size: 10px; z-index: 11;" title="Action Required: Review & Approve">
                                                <i class="bi bi-exclamation-circle-fill"></i>
                                            </span>
                                        @else
                                            <span class="position-absolute bottom-0 end-0 bg-info text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm border" style="width: 18px; height: 18px; border-color: var(--card-bg) !important; font-size: 9px; z-index: 11;" title="Pending Review Approval">
                                                <i class="bi bi-hourglass-split"></i>
                                            </span>
                                        @endif
                                    @elseif(str_starts_with(strtolower($t->title), 'bug:'))
                                        <span class="position-absolute bottom-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; border: 2px solid var(--card-bg); font-size: 10px;" title="Bug">
                                            <i class="bi bi-bug-fill"></i>
                                        </span>
                                    @elseif(str_starts_with(strtolower($t->title), 'room calling:'))
                                        <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 18px; height: 18px; border: 2px solid var(--card-bg); font-size: 10px;" title="Room Calling">
                                            <i class="bi bi-telephone-fill" style="font-size: 9px;"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="task-info">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <div class="task-title" title="{{ $t->title }}">
                                            @if(str_starts_with(strtolower($t->title), 'bug:'))
                                                <i class="bi bi-bug-fill text-danger me-1"></i>
                                            @elseif(str_starts_with(strtolower($t->title), 'room calling:'))
                                                <i class="bi bi-telephone-fill text-success me-1"></i>
                                            @endif
                                            {{ $t->title }}
                                        </div>
                                        <span class="text-muted flex-shrink-0 ms-2" style="font-size: 10px;">{{ $t->updated_at->diffForHumans(null, true) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-1" id="badge-container-{{ $t->id }}">
                                        <div class="text-truncate fs-8 d-flex align-items-center gap-1 flex-wrap" style="color: #667781; max-width: 80%;">
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
                                            @elseif($t->status === 'rejected')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 8px; padding: 1px 3px;">Rejected</span>
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
                                        @if($item->unread_count > 0)
                                            <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 18px; height: 18px; font-size: 9px; padding: 0; line-height: 1; flex-shrink: 0;" id="unread-badge-{{ $t->id }}">
                                                {{ $item->unread_count }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @else
                            @php $u = $item->user; @endphp
                            <a href="javascript:void(0);" 
                               class="chat-user-item contact-item unified-chat-item" 
                               id="contact-{{ $u->id }}" 
                               data-chat-type="direct"
                               data-id="{{ $u->id }}" 
                               data-name="{{ strtolower($u->name) }}" 
                               data-role="{{ strtolower($u->role?->name ?? 'Staff') }}"
                               data-avatar="{{ $u->avatar_url }}"
                               data-unread-count="{{ $item->unread_count }}"
                               data-is-bug="0"
                               data-priority="low"
                               onclick="selectDirectUser({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->employee->designation->name ?? ($u->role?->name ?? 'Staff')) }}', '{{ $u->avatar_url }}')">
                                <div class="position-relative" style="margin-left: 5px;">
                                    <img src="{{ $u->avatar_url }}" alt="" class="avatar-circle" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                                    @if($u->is_working_today)
                                        <div class="online-dot position-absolute" title="Working Today" style="bottom: 2px; right: 2px; width: 10px; height: 10px; background: #10b981; border: 2px solid var(--card-bg); border-radius: 50%;"></div>
                                    @endif
                                </div>
                                <div class="user-info flex-grow-1 min-width-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="user-name fw-semibold text-dark fs-7 text-truncate" title="{{ $u->name }}">{{ $u->name }}</span>
                                        @if($u->last_message)
                                            <span class="text-muted flex-shrink-0 ms-2" style="font-size: 10px;">{{ $u->last_message->created_at->diffForHumans(null, true) }}</span>
                                        @endif
                                    </div>
                                    <div class="last-msg text-muted text-truncate mt-1" id="last-msg-{{ $u->id }}" style="font-size: 12px;">
                                        @if($u->last_message)
                                            {{ $u->last_message->message ?? '[Image]' }}
                                        @else
                                            No messages yet
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center {{ $item->unread_count > 0 ? '' : 'd-none' }}" style="width: 18px; height: 18px; font-size: 9px; padding: 0; line-height: 1; flex-shrink: 0;" id="badge-{{ $u->id }}">
                                    {{ $item->unread_count }}
                                </span>
                            </a>
                        @endif
                    @empty
                        <div class="text-center py-5 text-muted fs-7">
                            <i class="bi bi-chat-text fs-3"></i>
                            <div class="mt-2">No chats available.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- NEW CHAT VIEW -->
            <div id="sidebar-new-chat-view" class="d-none flex-column h-100 w-100">
                <!-- Header (Back arrow + New Chat title) -->
                <div class="chat-sidebar-header d-flex align-items-center px-3 py-3 border-bottom">
                    <button class="btn btn-link text-dark p-0 me-3" type="button" onclick="hideNewChatView()" title="Back" style="text-decoration: none; border: none; background: transparent;">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </button>
                    <h5 class="fw-bold mb-0 text-dark">New chat</h5>
                </div>

                <!-- Search box in New Chat view -->
                <div class="chat-sidebar-search">
                    <div class="input-group">
                        <span class="input-group-text text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="new-chat-search-input" class="form-control" placeholder="Search name or role...">
                    </div>
                </div>

                <!-- Scrollable Actions and Contacts List -->
                <div class="chat-task-list" style="flex: 1; overflow-y: auto;">
                    @if(auth()->user()->isLeaderOrAbove())
                        <!-- Option 1: Add new project -->
                        <a href="javascript:void(0);" onclick="showCreateProjectForm()" class="chat-task-item d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-dark" style="transition: all 0.15s ease;">
                            <div class="new-chat-green-circle" style="flex-shrink: 0;">
                                <i class="bi bi-folder-plus"></i>
                            </div>
                            <div class="fw-bold text-dark">Add new project</div>
                        </a>

                        <!-- Option 2: Add new task -->
                        <a href="javascript:void(0);" onclick="showCreateTaskForm()" class="chat-task-item d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-dark" style="transition: all 0.15s ease;">
                            <div class="new-chat-green-circle" style="flex-shrink: 0;">
                                <i class="bi bi-clipboard-plus"></i>
                            </div>
                            <div class="fw-bold text-dark">Add new task</div>
                        </a>

                        <!-- Option 3: Register bug -->
                        <a href="javascript:void(0);" onclick="showCreateBugForm()" class="chat-task-item d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-dark" style="transition: all 0.15s ease;">
                            <div class="new-chat-green-circle" style="flex-shrink: 0;">
                                <i class="bi bi-bug"></i>
                            </div>
                            <div class="fw-bold text-dark">Register bug</div>
                        </a>
                    @endif

                    <!-- Section header for employees list -->
                    <div class="new-chat-section-header">Contacts</div>

                    <!-- List of employees from DB to select and open DM chat -->
                    <div id="new-chat-contacts-container">
                        @foreach($employees as $u)
                            <a href="javascript:void(0);" 
                               class="chat-user-item contact-item new-chat-contact-item d-flex align-items-center gap-3 px-3 py-2 border-bottom text-decoration-none text-dark"
                               data-id="{{ $u->id }}"
                               data-name="{{ strtolower($u->name) }}"
                               data-role="{{ strtolower($u->employee->designation->name ?? ($u->role?->name ?? 'Staff')) }}"
                               data-avatar="{{ $u->avatar_url }}"
                               onclick="startDirectFromNewChat({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->employee->designation->name ?? ($u->role?->name ?? 'Staff')) }}', '{{ $u->avatar_url }}')">
                                <div class="position-relative" style="flex-shrink: 0;">
                                    <img src="{{ $u->avatar_url }}" alt="" class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    @if($u->is_working_today)
                                        <div class="online-dot position-absolute" title="Working Today" style="bottom: 1px; right: 1px; width: 10px; height: 10px; background: #10b981; border: 2px solid white; border-radius: 50%;"></div>
                                    @endif
                                </div>
                                <div class="min-width-0 flex-grow-1">
                                    <div class="fw-semibold text-dark text-truncate">{{ $u->name }}</div>
                                    <div class="text-muted fs-8 text-truncate">{{ $u->employee->designation->name ?? ($u->role?->name ?? 'Staff') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Pane: Active Chat Window -->
        <div class="chat-main" id="chat-main-window">
            
            <!-- Placeholder when no task is selected -->
            <div class="chat-main-placeholder" id="chat-no-task-placeholder">
                @if(auth()->user()->isLeaderOrAbove())
                    <div class="d-flex align-items-center justify-content-center gap-5">
                        <div class="whatsapp-placeholder-btn-container" onclick="showCreateProjectForm()">
                            <div class="whatsapp-circle-btn">
                                <i class="bi bi-folder-plus fs-4"></i>
                            </div>
                            <span class="whatsapp-btn-label">Add new project</span>
                        </div>
                        <div class="whatsapp-placeholder-btn-container" onclick="showCreateTaskForm()">
                            <div class="whatsapp-circle-btn">
                                <i class="bi bi-clipboard-plus fs-4"></i>
                            </div>
                            <span class="whatsapp-btn-label">Add new task</span>
                        </div>
                        <div class="whatsapp-placeholder-btn-container" onclick="showCreateBugForm()">
                            <div class="whatsapp-circle-btn ask-meta-ai-btn">
                                <i class="bi bi-bug fs-4"></i>
                            </div>
                            <span class="whatsapp-btn-label">Register bug</span>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="whatsapp-circle-btn-disabled" style="width: 80px; height: 80px; background: rgba(0,0,0,0.04); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="bi bi-chat-dots fs-1 text-muted"></i>
                        </div>
                        <h4 class="fw-semibold" style="color: #111b21;">WorkeX Chat</h4>
                        <p class="text-muted fs-7">Select a task or contact on the left to start collaborating.</p>
                    </div>
                @endif
            </div>

            <!-- Chat Content (initially hidden) -->
            <div class="d-none flex-column h-100" id="chat-content-container">
                <!-- Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center min-width-0">
                        <button type="button" class="btn btn-link text-dark p-0 me-3 d-md-none" onclick="goBackToChatList()" title="Back to chats">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>
                        <div class="position-relative me-3" id="chat-header-avatar-container" style="display: none;">
                            <img src="" id="chat-header-avatar" class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(0,0,0,0.08);">
                            <div id="chat-header-online-dot" class="position-absolute" style="bottom: 1px; right: 1px; width: 10px; height: 10px; background: #10b981; border: 2px solid white; border-radius: 50%; display: none;"></div>
                        </div>
                        <div class="chat-header-info">
                            <h6 class="chat-header-title mb-0">
                                <a href="javascript:void(0);" id="chat-active-title" onclick="toggleInfoSidebar(event)" class="text-decoration-none text-dark fw-bold" title="Click to view task details">Task Title</a>
                            </h6>
                            <div class="chat-header-subtitle d-flex align-items-center gap-1 flex-wrap">
                                <span id="chat-active-project" class="text-primary fw-medium">Project Name</span>
                                <span id="chat-active-separator" class="text-muted mx-1">•</span>
                                <span id="chat-active-assignee-label" class="text-muted">Assignee:</span>
                                <img src="" id="chat-active-avatar" class="avatar-circle ms-1" style="width: 18px; height: 18px; border-radius: 50%; object-fit: cover;">
                                <span id="chat-active-assignee" class="fw-semibold text-dark fs-8">Assignee Name</span>
                                <span id="chat-active-deadline" class="text-muted fs-8 ms-1"></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <!-- Desktop action buttons container -->
                        <div id="chat-header-desktop-actions" class="chat-header-desktop-actions me-2"></div>

                        <!-- Pinned Messages Dropdown -->
                        <div class="dropdown me-1" id="chat-header-pinned-dropdown" style="display: none;">
                            <button class="btn btn-link text-muted p-1 hover-bg-light-circle position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pinned Messages" style="border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; text-decoration: none;">
                                <i class="bi bi-pin-angle fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="chat-header-pinned-count" style="font-size: 9px; padding: 3px 6px; display: none;">0</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border border-light-subtle py-2" id="chat-header-pinned-list" style="border-radius: 12px; min-width: 250px; max-width: 320px; max-height: 350px; overflow-y: auto; font-size: 13px; z-index: 1060;">
                                <li class="dropdown-header text-muted fw-semibold">Pinned Messages</li>
                                <div id="chat-header-pinned-items-container">
                                    <li class="text-center py-3 text-muted fs-7">No pinned messages</li>
                                </div>
                            </ul>
                        </div>

                        <!-- Search Icon Toggle Button -->
                        <button class="btn btn-link text-muted p-1 hover-bg-light-circle" type="button" onclick="toggleChatSearch()" title="Search messages" style="border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; text-decoration: none;">
                            <i class="bi bi-search fs-5"></i>
                        </button>

                        <!-- Dropdown Menu for Task Actions (Mobile only) -->
                        <div class="dropdown chat-header-mobile-actions">
                            <button class="btn btn-link text-muted p-1 hover-bg-light-circle" type="button" id="chatHeaderDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; text-decoration: none;">
                                <i class="bi bi-chevron-down fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="chatHeaderDropdown" style="border-radius: 12px; min-width: 180px; padding: 6px; z-index: 1050;" id="chat-header-dropdown-menu">
                                <!-- Populated dynamically -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Chat Message Search Bar (WhatsApp Style) -->
                <div class="chat-search-bar d-none" id="chat-message-search-bar" style="background-color: #f0f2f5; border-bottom: 1px solid #e2e8f0; padding: 8px 16px; display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <div class="input-group input-group-sm" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #cbd5e1; padding: 2px 8px; flex: 1;">
                        <span class="input-group-text bg-transparent border-0 text-muted p-1" style="box-shadow: none;"><i class="bi bi-search"></i></span>
                        <input type="text" id="chat-message-search-input" class="form-control bg-transparent border-0 shadow-none p-1" placeholder="Search in this chat..." style="font-size: 13px; box-shadow: none !important;">
                    </div>
                    <button type="button" class="btn-close btn-close-sm shadow-none" onclick="toggleChatSearch()" style="font-size: 10px; box-shadow: none;"></button>
                </div>

                <!-- Chat Body (Messages) -->
                <div class="chat-body chat-container p-4" id="chat-body-wrap">
                    <div class="chat-body-container" id="chat-messages-container">
                        <!-- Rendered via AJAX -->
                    </div>
                </div>

                <!-- Task Detail Inline View (hidden by default, shown on header click) -->
                <div id="chat-task-detail-view" style="display:none; flex: 1; overflow-y:auto;
                     background:#f8fafc; padding:28px 24px;">
                    <!-- content injected by JS -->
                </div>

                <!-- Input box -->
                <form id="chat-form" method="POST" action="" class="whatsapp-input-bar" enctype="multipart/form-data">
                    @csrf
                    <!-- Mention Dropdown -->
                    <div id="mention-list" class="mention-dropdown"></div>

                    <!-- Hidden input for base64 canvas image data -->
                    <input type="hidden" name="image_data" id="chat-image-data">

                    <!-- Attachment trigger -->
                    <label for="chat-image-input" class="btn btn-link text-muted p-0 m-0 d-flex align-items-center justify-content-center" style="font-size: 20px; width: 36px; height: 36px; cursor: pointer;" title="Attach Image/PDF">
                        <i class="bi bi-paperclip"></i>
                    </label>
                    <input type="file" id="chat-image-input" name="document" accept="image/*,application/pdf" style="display: none;">

                    <div class="whatsapp-input-container flex-column align-items-start py-2">
                        <!-- Attachment Preview -->
                        <div id="chat-attachment-preview" class="d-none mb-2 position-relative" style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid #cbd5e1; overflow: visible; background-size: cover; background-position: center;">
                            <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        
                        <!-- Reply Preview -->
                        <div id="reply-preview-container" class="d-none w-100 p-2 mb-2 rounded border-start border-4 border-primary position-relative" style="font-size: 12px; max-height: 80px; overflow: hidden; background-color: rgba(0, 0, 0, 0.04);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-primary mb-1" id="reply-preview-sender">Sender</div>
                                    <div class="text-muted text-truncate" id="reply-preview-text" style="max-width: 85%;">Message text</div>
                                </div>
                                <button type="button" class="btn-close shadow-none p-1 position-absolute" style="top: 8px; right: 8px; font-size: 10px;" id="reply-preview-close" onclick="cancelReply()"></button>
                            </div>
                        </div>

                        <!-- Edit Preview -->
                        <div id="edit-preview-container" class="d-none w-100 p-2 mb-2 rounded border-start border-4 border-warning position-relative" style="font-size: 12px; max-height: 80px; overflow: hidden; background-color: rgba(255, 193, 7, 0.08);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-warning mb-1">Editing Message</div>
                                    <div class="text-muted text-truncate" id="edit-preview-text" style="max-width: 85%;">Original text</div>
                                </div>
                                <button type="button" class="btn-close shadow-none p-1 position-absolute" style="top: 8px; right: 8px; font-size: 10px;" id="edit-preview-close" onclick="cancelEdit()"></button>
                            </div>
                        </div>

                        <input type="hidden" name="parent_id" id="reply-parent-id">
                        <input type="hidden" id="edit-message-id">
                        <input type="hidden" id="edit-message-type">
                        <textarea name="comment" id="whatsapp-comment-input" class="whatsapp-input w-100" placeholder="Type a message..." rows="1" autocomplete="off"></textarea>
                    </div>
                    <button type="submit" class="whatsapp-send-btn">
                        <i class="bi bi-send-fill" style="margin-left: 2px;"></i>
                    </button>
                </form>
            </div>

            @if(auth()->user()->isLeaderOrAbove())
            <!-- Form 1: Create Project Container -->
            <div class="d-none flex-column h-100" id="chat-create-project-container" style="background: #efeae2;">
                <!-- Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center min-width-0">
                        <button type="button" class="btn btn-link text-dark p-0 me-3" onclick="cancelCreationForm()" title="Back">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>
                        <div class="chat-header-info">
                            <h6 class="chat-header-title mb-0 text-dark fw-bold">Create New Project</h6>
                            <div class="chat-header-subtitle">
                                <span class="text-muted">Set up a new project board in the system</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="chat-body chat-container p-4 overflow-y-auto">
                    <div class="card shadow-sm border-0 mx-auto" style="max-width: 650px; border-radius: 16px;">
                        <div class="card-body p-4">
                            <form id="chat-project-create-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. E-Commerce Development">
                                        <div class="invalid-feedback" id="project_name_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Project Type</label>
                                        <select name="project_type" id="project_type_select" class="form-select form-select-sm" required>
                                            <option value="" disabled selected>-- Choose Project Type --</option>
                                            <option value="new_type" class="text-primary fw-bold">+ Add New Type...</option>
                                            @php
                                                $defaultTypes = [
                                                    'web' => 'Web Application',
                                                    'mobile' => 'Mobile Application',
                                                    'desktop' => 'Desktop Software',
                                                    'other' => 'Other Services'
                                                ];
                                                $allTypes = [];
                                                foreach($defaultTypes as $val => $label) {
                                                    $allTypes[$val] = $label;
                                                }
                                                if (isset($projectTypes)) {
                                                    foreach($projectTypes as $type) {
                                                        if (!empty($type) && !isset($allTypes[$type])) {
                                                            $allTypes[$type] = ucwords(str_replace('_', ' ', $type));
                                                        }
                                                    }
                                                }
                                            @endphp
                                            @foreach($allTypes as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="project_project_type_error"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Project Description</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Provide a brief summary of project requirements..."></textarea>
                                        <div class="invalid-feedback" id="project_description_error"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Project Logo</label>
                                        <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                                        <div class="invalid-feedback" id="project_logo_error"></div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Select Client</label>
                                        <select name="client_id" id="project_client_select" class="form-select form-select-sm">
                                            <option value="">-- Choose Client --</option>
                                            <option value="">-- Internal Project --</option>
                                            @foreach($clients as $c)
                                                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                            @endforeach
                                            <option value="new_client" class="text-primary fw-bold">+ Add New Client...</option>
                                        </select>
                                        <div class="invalid-feedback" id="project_client_id_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Team Leader / Project Manager</label>
                                        <select name="team_leader_id" id="project_team_leader_select" class="form-select form-select-sm">
                                            <option value="">-- Choose Team Leader --</option>
                                            @foreach($teamLeaders as $tl)
                                                <option value="{{ $tl->id }}">{{ $tl->name }}</option>
                                            @endforeach
                                            <option value="new_team_leader" class="text-primary fw-bold">+ Add New Team Leader...</option>
                                        </select>
                                        <div class="invalid-feedback" id="project_team_leader_id_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Project Budget (₹)</label>
                                        <input type="number" step="0.01" name="budget" class="form-control form-control-sm" placeholder="0.00">
                                        <div class="invalid-feedback" id="project_budget_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Priority Level <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-select form-select-sm" required>
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                        <div class="invalid-feedback" id="project_priority_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Start Date</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                        <div class="invalid-feedback" id="project_start_date_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Deadline / Due Date</label>
                                        <input type="date" name="deadline" class="form-control form-control-sm">
                                        <div class="invalid-feedback" id="project_deadline_error"></div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Technologies Used <span class="text-muted">(Comma separated tags)</span></label>
                                        <input type="text" name="technologies" class="form-control form-control-sm" placeholder="e.g. PHP, Laravel, MySQL">
                                        <div class="invalid-feedback" id="project_technologies_error"></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="cancelCreationForm()">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-3" id="chat-project-submit-btn">Save Project</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form 2: Create Task Container -->
            <div class="d-none flex-column h-100" id="chat-create-task-container" style="background: #efeae2;">
                <!-- Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center min-width-0">
                        <button type="button" class="btn btn-link text-dark p-0 me-3" onclick="cancelCreationForm()" title="Back">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>
                        <div class="chat-header-info">
                            <h6 class="chat-header-title mb-0 text-dark fw-bold">Create New Task</h6>
                            <div class="chat-header-subtitle">
                                <span class="text-muted">Set up and assign a new task card</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="chat-body chat-container p-4 overflow-y-auto">
                    <div class="card shadow-sm border-0 mx-auto" style="max-width: 650px; border-radius: 16px;">
                        <div class="card-body p-4">
                            <form id="chat-task-create-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Task Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Design Landing Page Login Flow">
                                        <div class="invalid-feedback" id="task_title_error"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Task Description</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Detailed instructions for this task..."></textarea>
                                        <div class="invalid-feedback" id="task_description_error"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Do you want to add this task to any project?</label>
                                        <select name="project_id" class="form-select form-select-sm">
                                            <option value="">-- No, do not link to any project --</option>
                                            @foreach($projects as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="task_project_id_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Assignee / Developer <span class="text-danger">*</span></label>
                                        <select name="assigned_to" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Assignee --</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="task_assigned_to_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Priority Level <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-select form-select-sm" required>
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                        <div class="invalid-feedback" id="task_priority_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Task Deadline</label>
                                        <input type="date" name="deadline" class="form-control form-control-sm">
                                        <div class="invalid-feedback" id="task_deadline_error"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Estimated Hours</label>
                                        <input type="number" step="0.5" name="estimated_hours" class="form-control form-control-sm" placeholder="e.g. 8">
                                        <div class="invalid-feedback" id="task_estimated_hours_error"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Task Attachment <span class="text-muted">(Optional)</span></label>
                                        <input type="file" name="attachment" class="form-control form-control-sm" accept="image/*,application/pdf">
                                        <div class="invalid-feedback" id="task_attachment_error"></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="cancelCreationForm()">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-3" id="chat-task-submit-btn">Save Task</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form 3: Create Bug Container -->
            <div class="d-none flex-column h-100" id="chat-create-bug-container" style="background: #efeae2;">
                <!-- Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center min-width-0">
                        <button type="button" class="btn btn-link text-dark p-0 me-3" onclick="cancelCreationForm()" title="Back">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>
                        <div class="chat-header-info">
                            <h6 class="chat-header-title mb-0 text-dark fw-bold">Register New Bug</h6>
                            <div class="chat-header-subtitle">
                                <span class="text-muted">File an issue / bug board in the system</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="chat-body chat-container p-4 overflow-y-auto">
                    <div class="card shadow-sm border-0 mx-auto" style="max-width: 650px; border-radius: 16px;">
                        <div class="card-body p-4">
                            <form id="chat-bug-create-form" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Issue Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Login button fails on Firefox browser">
                                        <div class="invalid-feedback" id="bug_title_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Project Board <span class="text-danger">*</span></label>
                                        <select name="project_id" class="form-select form-select-sm" required>
                                            <option value="">-- Choose Project --</option>
                                            @foreach($projects as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="bug_project_id_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Assignee / Developer</label>
                                        <select name="assigned_to" class="form-select form-select-sm">
                                            <option value="">-- Choose Developer --</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="bug_assigned_to_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Priority Level <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-select form-select-sm" required>
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                        <div class="invalid-feedback" id="bug_priority_error"></div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold text-dark">Related Link / URL</label>
                                        <input type="text" name="link" class="form-control form-control-sm" placeholder="e.g. http://127.0.0.1:8000/some-page">
                                        <div class="invalid-feedback" id="bug_link_error"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Steps to Reproduce</label>
                                        <textarea name="steps_to_reproduce" class="form-control form-control-sm" rows="3" placeholder="1. Go to page X&#10;2. Click Z..."></textarea>
                                        <div class="invalid-feedback" id="bug_steps_to_reproduce_error"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Detailed Description <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control form-control-sm" rows="3" required placeholder="Provide context or details about the issue..."></textarea>
                                        <div class="invalid-feedback" id="bug_description_error"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">Screenshots / Reference Images <span class="text-muted fs-8">(Optional, Up to 3 images)</span></label>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <div class="border rounded p-2 text-center" style="background: #f8f9fa; [data-bs-theme='dark'] & { background: #2a3942; }">
                                                    <span class="fs-8 fw-semibold d-block mb-1">Img 1</span>
                                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-xs bug-chat-file-input" data-preview="bug-chat-preview-1" style="font-size: 10px;">
                                                    <div class="mt-2 d-none" id="bug-chat-preview-container-1">
                                                        <img id="bug-chat-preview-1" src="" class="img-fluid rounded border" style="max-height: 60px; object-fit: cover;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2 text-center" style="background: #f8f9fa; [data-bs-theme='dark'] & { background: #2a3942; }">
                                                    <span class="fs-8 fw-semibold d-block mb-1">Img 2</span>
                                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-xs bug-chat-file-input" data-preview="bug-chat-preview-2" style="font-size: 10px;">
                                                    <div class="mt-2 d-none" id="bug-chat-preview-container-2">
                                                        <img id="bug-chat-preview-2" src="" class="img-fluid rounded border" style="max-height: 60px; object-fit: cover;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="border rounded p-2 text-center" style="background: #f8f9fa; [data-bs-theme='dark'] & { background: #2a3942; }">
                                                    <span class="fs-8 fw-semibold d-block mb-1">Img 3</span>
                                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-xs bug-chat-file-input" data-preview="bug-chat-preview-3" style="font-size: 10px;">
                                                    <div class="mt-2 d-none" id="bug-chat-preview-container-3">
                                                        <img id="bug-chat-preview-3" src="" class="img-fluid rounded border" style="max-height: 60px; object-fit: cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Auto-filled metadata -->
                                    <input type="hidden" name="browser_info" id="bug_chat_browser_info">
                                    <input type="hidden" name="os_info" id="bug_chat_os_info">
                                </div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="cancelCreationForm()">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-3" id="chat-bug-submit-btn">File Bug</button>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
            @endif


        </div>

        <!-- Right Sidebar: Task Information Panel (WhatsApp style) -->
        <div class="chat-info-sidebar d-none" id="chat-info-sidebar">
            <div class="chat-info-header d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-link text-dark p-0 me-1" onclick="closeInfoSidebar()" title="Close details" style="border: none; background: transparent; text-decoration: none;">
                        <i class="bi bi-x-lg fs-5"></i>
                    </button>
                    <h6 class="mb-0 fw-bold">Task Info</h6>
                </div>
            </div>
            <div class="chat-info-body p-4 overflow-y-auto">
                <div class="text-center mb-4">
                    <img src="" id="info-task-avatar" class="avatar-circle mb-3" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
                    <h5 class="fw-bold text-dark mb-1" id="info-task-title">Task Title</h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-8" id="info-task-project">Project Name</span>
                </div>
                
                <hr class="my-4 border-light">
                
                <div class="mb-4">
                    <span class="fw-bold text-muted uppercase-title mb-2 d-block">Work Description</span>
                    <div class="p-3 bg-light rounded-3 text-dark fs-7" id="info-task-desc" style="white-space: pre-wrap; line-height: 1.5; min-height: 60px;">
                        Task Description goes here...
                    </div>
                </div>

                <hr class="my-4 border-light">

                <div class="mb-4">
                    <span class="fw-bold text-muted uppercase-title mb-3 d-block">Task Details</span>
                    <div class="d-flex flex-column gap-3 fs-7">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="fw-semibold" id="info-task-status">In Progress</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Priority:</span>
                            <span class="fw-semibold" id="info-task-priority">Medium</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Deadline:</span>
                            <span class="fw-semibold" id="info-task-deadline">Dec 31, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created At:</span>
                            <span class="fw-semibold text-end" id="info-task-created">Dec 31, 2026</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-light">

                <div>
                    <span class="fw-bold text-muted uppercase-title mb-3 d-block">People</span>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="" id="info-task-assignee-avatar" class="avatar-circle" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div class="fw-semibold text-dark fs-7" id="info-task-assignee-name">Assignee Name</div>
                            <div class="text-muted fs-8">Assignee</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <img src="" id="info-task-creator-avatar" class="avatar-circle" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div class="fw-semibold text-dark fs-7" id="info-task-creator-name">Creator Name</div>
                            <div class="text-muted fs-8">Creator</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-light">

                {{-- Attachments Section --}}
                <div id="info-task-attachments-section">
                    <span class="fw-bold text-muted uppercase-title mb-3 d-block">Attachments</span>
                    <div id="info-task-attachments-list">
                        <div class="text-muted fs-8 text-center py-3" id="info-task-no-attachments">
                            <i class="bi bi-paperclip" style="font-size:24px; opacity:.4;"></i>
                            <div class="mt-1">No attachments</div>
                        </div>
                    </div>
                </div>
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
                <h5 class="modal-title fw-bold text-dark" id="taskCompletionModalLabel">Submit for Review</h5>
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
                        <label class="form-label fw-semibold text-dark">Page Link / Test URL</label>
                        <input type="url" name="completed_link" class="form-control" placeholder="https://example.com/test-page">
                        <div class="form-text text-muted fs-8">Provide the exact link where this change/feature can be verified.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Submit for Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editTaskModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editTaskModalForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit-task-title-input" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Task Description</label>
                        <textarea name="description" id="edit-task-desc-input" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Project</label>
                        <select name="project_id" id="edit-task-project-select" class="form-select form-select-sm">
                            <option value="">-- No Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">Assignee <span class="text-danger">*</span></label>
                            <select name="assigned_to" id="edit-task-assignee-select" class="form-select form-select-sm" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="edit-task-priority-select" class="form-select form-select-sm" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-task-status-select" class="form-select form-select-sm" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="rework">Rework</option>
                                <option value="rejected">Rejected</option>
                                <option value="completed" class="completed-option d-none">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark">Deadline</label>
                            <input type="date" name="deadline" id="edit-task-deadline-input" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold text-dark">Estimated Hours</label>
                        <input type="number" step="0.5" name="estimated_hours" id="edit-task-est-input" class="form-control form-control-sm" min="0">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Tasks Modal -->
<div class="modal fade" id="employeeTasksModal" tabindex="-1" aria-labelledby="employeeTasksModalLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="employeeTasksModalLabel">Tasks Assigned to <span id="employee-tasks-name">Employee</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                <th>Task Title</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Deadline</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="employee-tasks-list">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="employee-tasks-empty" class="text-center py-4 text-muted d-none">
                    <i class="bi bi-card-checklist fs-2 mb-2 d-block"></i>
                    <span>No active tasks assigned to this employee.</span>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addClientModalLabel"><i class="bi bi-building me-2 text-primary"></i>Add New Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickClientForm">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Acme Corporation" required>
                        <div class="invalid-feedback" id="client_company_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="e.g. Robert Smith" required>
                        <div class="invalid-feedback" id="client_contact_person_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. robert@acme.com" required>
                        <div class="invalid-feedback" id="client_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-sm" placeholder="e.g. 9876543210">
                        <div class="invalid-feedback" id="client_phone_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveClientBtn">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Team Leader Modal -->
<div class="modal fade" id="addTeamLeaderModal" tabindex="-1" aria-labelledby="addTeamLeaderModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addTeamLeaderModalLabel"><i class="bi bi-people me-2 text-primary"></i>Add New Team Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickTeamLeaderForm">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. John Doe" required>
                        <div class="invalid-feedback" id="tl_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. john@company.com" required>
                        <div class="invalid-feedback" id="tl_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimum 8 characters" required>
                        <div class="invalid-feedback" id="tl_password_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Re-type password" required>
                        <div class="invalid-feedback" id="tl_password_confirmation_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveTLBtn">Save Team Leader</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Project Type Modal -->
<div class="modal fade" id="addProjectTypeModal" tabindex="-1" aria-labelledby="addProjectTypeModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addProjectTypeModalLabel"><i class="bi bi-tag me-2 text-primary"></i>Add Custom Project Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickProjectTypeForm">
                @csrf
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Project Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="new_project_type" id="new_project_type" class="form-control form-control-sm" placeholder="e.g. API Integration" required>
                        <div class="invalid-feedback" id="new_project_type_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveProjectTypeBtn">Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="leaveActionModal" tabindex="-1" aria-labelledby="leaveActionModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="leaveActionModalLabel">Approve Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leaveActionForm">
                <div class="modal-body pt-3">
                    <input type="hidden" id="leave_action_id">
                    <input type="hidden" id="leave_action_type">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark" id="leaveActionCommentLabel">Approval Comments</label>
                        <textarea id="leave_action_comment" class="form-control" rows="3" placeholder="Enter comments here..."></textarea>
                        <div class="invalid-feedback" id="leave_action_error"></div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="leaveActionConfirmBtn">Confirm</button>
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
    let lastCommentId = null;
    let lastTimelogId = null;
    let pollInterval = null;
    let activeStoreUrl = '';
    let currentTaskData = null;
    const isLeaderOrAbove = {{ (auth()->user()->isSuperAdmin() || auth()->user()->isTeamLeader()) ? 'true' : 'false' }};
    window.currentUserRole = "{{ auth()->user()->role->slug }}";

    // Unified Direct Chat variables
    let activeChatType = 'task'; // 'task' or 'direct'
    let activeDirectUserId = null;
    let lastDirectPolledAt = '{{ now()->toISOString() }}';
    let currentDirectUnreadCount = {{ \App\Models\DirectMessage::where('receiver_id', auth()->id())->whereNull('read_at')->count() }};
    let currentTaskUnreadCount = 0;

    // Switch tab dummy for compatibility
    function switchChatTab(tab) {}

    // Move thread to top of unified list (WhatsApp style)
    function moveThreadToTop(type, id, lastMessageText, timeText, unreadCount) {
        const container = document.getElementById('chat-unified-container');
        if (!container) return;
        
        let item = null;
        if (type === 'task') {
            item = document.getElementById(`chat-task-item-${id}`);
        } else {
            item = document.getElementById(`contact-${id}`);
        }
        if (!item) return;

        // Update last message preview for contacts
        if (type === 'direct') {
            const lastMsgDiv = document.getElementById(`last-msg-${id}`);
            if (lastMsgDiv) {
                lastMsgDiv.textContent = lastMessageText || '[Image]';
            }
        }

        // Update time text
        const timeSpan = item.querySelector('.text-muted.flex-shrink-0.ms-2');
        if (timeSpan && timeText) {
            timeSpan.textContent = timeText;
        }

        // Update unread badge
        if (unreadCount !== undefined) {
            if (type === 'task') {
                let badge = document.getElementById(`unread-badge-${id}`);
                const badgeContainer = document.getElementById(`badge-container-${id}`);
                if (badgeContainer) {
                    if (unreadCount > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.id = `unread-badge-${id}`;
                            badge.className = 'badge bg-success rounded-circle d-flex align-items-center justify-content-center';
                            badge.style.cssText = 'width: 18px; height: 18px; font-size: 9px; padding: 0; line-height: 1; flex-shrink: 0;';
                            badgeContainer.appendChild(badge);
                        }
                        badge.textContent = unreadCount;
                    } else {
                        if (badge) badge.remove();
                    }
                }
            } else {
                const badge = document.getElementById(`badge-${id}`);
                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount;
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                        badge.textContent = '0';
                    }
                }
            }
        }

        // Move to the top of the list
        container.insertBefore(item, container.firstChild);
    }

    // Direct Message User selection
    function selectDirectUser(userId, name, role, avatar) {
        hideCreationForms();
        if (activeDirectUserId === userId) return;
        
        window.activeDirectUserName = name;
        window.activeDirectUserAvatar = avatar;
        cancelReply();
        
        // Clear active task state
        activeTaskId = null;
        activeDirectUserId = userId;
        activeChatType = 'direct';
        
        // Hide task search inside messages
        const searchBar = document.getElementById('chat-message-search-bar');
        if (searchBar) {
            searchBar.classList.remove('d-flex');
            searchBar.classList.add('d-none');
        }
        const searchInput = document.getElementById('chat-message-search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Clear old poll intervals
        if (pollInterval) {
            clearInterval(pollInterval);
        }

        // Highlight active user item in sidebar
        document.querySelectorAll('.chat-user-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.chat-task-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeItem = document.getElementById(`contact-${userId}`);
        if (activeItem) activeItem.classList.add('active');

        // Trigger mobile view transition to chat messages pane
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.add('chat-show-main');
        }

        // Show window, hide placeholder
        document.getElementById('chat-no-task-placeholder').classList.add('d-none');
        document.getElementById('chat-content-container').classList.remove('d-none');
        document.getElementById('chat-content-container').classList.add('d-flex');

        // Clear task headers details and set designation
        const projectSpan = document.getElementById('chat-active-project');
        projectSpan.textContent = role;
        projectSpan.classList.remove('text-primary');
        projectSpan.classList.add('text-muted');
        
        document.getElementById('chat-active-separator').style.display = 'none';
        document.getElementById('chat-active-assignee-label').style.display = 'none';
        document.getElementById('chat-active-avatar').style.display = 'none';
        document.getElementById('chat-active-assignee').textContent = '';
        document.getElementById('chat-active-deadline').textContent = '';
        
        // Set direct chat avatar and online dot
        const avatarContainer = document.getElementById('chat-header-avatar-container');
        if (avatarContainer) {
            avatarContainer.style.display = 'block';
            document.getElementById('chat-header-avatar').src = avatar;
            
            // Check if user is online based on activeItem's online-dot in sidebar
            const activeItem = document.getElementById(`contact-${userId}`);
            const isOnline = activeItem && activeItem.querySelector('.online-dot') !== null;
            document.getElementById('chat-header-online-dot').style.display = isOnline ? 'block' : 'none';
        }
        
        // Set direct chat title
        const titleLink = document.getElementById('chat-active-title');
        titleLink.textContent = name;
        titleLink.removeAttribute('href');
        titleLink.onclick = function(e) {
            e.preventDefault();
            openEmployeeTasksModal(userId);
        };
        
        // Hide task action buttons in header
        document.getElementById('chat-header-dropdown-menu').innerHTML = '';
        document.getElementById('chat-header-desktop-actions').innerHTML = '';
        
        // Close info sidebar if open
        closeInfoSidebar();

        // Show loading spinner
        const messagesContainer = document.getElementById('chat-messages-container');
        messagesContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Set form send active URLs
        const form = document.getElementById('chat-form');
        form.action = `/direct-chat/messages/${userId}`;
        activeStoreUrl = `/direct-chat/messages/${userId}`;

        // Save selected direct userId to localStorage
        localStorage.setItem('active_chat_direct_user_id', userId);
        localStorage.removeItem('active_chat_task_id');

        // Load message history from Direct Message routes
        fetch(`/direct-chat/messages/${userId}?_t=${new Date().getTime()}`)
            .then(response => response.json())
            .then(data => {
                messagesContainer.innerHTML = '';
                if (data.success) {
                    lastDirectPolledAt = data.latest_time || new Date().toISOString();
                }
                if (data.success && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        appendDirectMessageHtml(msg);
                    });
                    groupChatMessagesByDate('chat-messages-container');
                    window.updatePinnedMessagesList();
                    
                    const chatBody = document.querySelector('.chat-body');
                    chatBody.scrollTop = chatBody.scrollHeight;
                } else {
                    messagesContainer.innerHTML = `
                        <div class="text-center py-5 text-muted" id="no-chat-messages">
                            <i class="bi bi-chat-text" style="font-size: 32px;"></i>
                            <div class="mt-2 fs-7">No messages yet. Send a message to start the conversation!</div>
                        </div>
                    `;
                }

                // Remove unread count badge in list
                const badge = document.getElementById(`badge-${userId}`);
                if (badge) {
                    badge.classList.add('d-none');
                    badge.textContent = '0';
                }

                // Focus input box
                document.getElementById('whatsapp-comment-input').focus();
            })
            .catch(error => {
                console.error('Error loading direct chat:', error);
                messagesContainer.innerHTML = `<div class="text-center text-danger py-5">Error loading chat history. Please try again.</div>`;
            });
    }

    function appendDirectMessageHtml(msg) {
        const messagesContainer = document.getElementById('chat-messages-container');
        
        const noMessagesEl = document.getElementById('no-chat-messages');
        if (noMessagesEl) {
            noMessagesEl.remove();
        }

        const msgRow = document.createElement('div');
        msgRow.className = `chat-row ${msg.is_sent ? 'sent' : 'received'} mb-3`;
        msgRow.id = `msg-row-${msg.id}`;
        msgRow.dataset.date = msg.date || '';
        msgRow.dataset.time = msg.time || '';

        let imgHtml = '';
        if (msg.image_url) {
            imgHtml = `<img src="${msg.image_url}" class="chat-image-preview rounded-3 mb-2" style="max-width: 250px; cursor: pointer;" onclick="openImageViewer('${msg.image_url}')">`;
        }

        let fileHtml = '';
        if (msg.file_url) {
            fileHtml = `
                <div class="chat-file-attachment p-2 mb-2 rounded border border-light-subtle bg-light d-flex align-items-center gap-2" style="max-width: 280px; font-size: 13px;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-4 flex-shrink-0"></i>
                    <div class="flex-grow-1 text-truncate" style="max-width: 180px;">
                        <a href="${msg.file_url}" target="_blank" class="text-decoration-none text-dark fw-semibold" title="${escapeHtml(msg.file_name)}">
                            ${escapeHtml(msg.file_name)}
                        </a>
                    </div>
                    <a href="${msg.file_url}" target="_blank" download="${escapeHtml(msg.file_name)}" class="btn btn-link text-muted p-1 ms-auto" title="Download">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            `;
        }

        let textHtml = '';
        if (msg.message) {
            textHtml = `<div class="chat-text">${formatMessageText(msg.message, msg)}</div>`;
        }

        let replyHtml = '';
        if (msg.reply_to_message) {
            let quoteText = '';
            if (msg.reply_to_message.message) {
                quoteText = escapeHtml(msg.reply_to_message.message);
            } else if (msg.reply_to_message.file_url) {
                quoteText = '[Document]';
            } else if (msg.reply_to_message.image_url) {
                quoteText = '[Image]';
            } else {
                quoteText = '[Attachment]';
            }
            replyHtml = `
                <div class="reply-quote-box p-2 mb-2 rounded border-start border-4 border-primary bg-light-subtle" style="font-size: 11px; opacity: 0.85; background-color: rgba(0, 0, 0, 0.03);">
                    <div class="fw-bold text-primary mb-1">${escapeHtml(msg.reply_to_message.sender_name)}</div>
                    <div class="text-truncate text-muted text-dark" style="max-width: 90%;">${quoteText}</div>
                </div>
            `;
        }

        const targetUser = window.activeDirectUserName || 'Recipient';
        const targetAvatar = window.activeDirectUserAvatar || '';
        const viewersData = JSON.stringify(msg.seen_by || []);

        const escapedMsgText = (msg.message || (msg.file_url ? '[Document]' : '[Image]')).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');
        const contactName = msg.is_sent ? 'You' : targetUser;

        const actionsHtml = `
            <div class="chat-bubble-actions dropdown">
                <button class="chat-bubble-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-${msg.is_sent ? 'start' : 'end'} shadow-sm border border-light-subtle py-1" style="font-size: 13px; z-index: 1050;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" onclick="replyToDirectMessage(${msg.id}, '${escapeHtml(contactName)}')">
                            <i class="bi bi-reply-fill text-muted"></i> Reply
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#commentInfoModal" data-viewers='${viewersData}' data-sent-at="${msg.formatted_time || msg.time}">
                            <i class="bi bi-info-circle text-muted"></i> Message Info
                        </a>
                    </li>
                    ${msg.is_sent && msg.is_editable ? `
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="javascript:void(0)" onclick="startEditMessage(${msg.id}, 'direct', \`${escapedMsgText}\`)">
                            <i class="bi bi-pencil text-muted"></i> Edit Message
                        </a>
                    </li>
                    ` : ''}
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-warning" href="javascript:void(0)" onclick="toggleMessageImportant(${msg.id}, 'direct')">
                            <i class="bi ${msg.is_important ? 'bi-star-fill text-warning' : 'bi-star text-muted'}"></i> ${msg.is_important ? 'Unstar Message' : 'Star Important'}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-1.5 text-danger" href="javascript:void(0)" onclick="toggleMessagePin(${msg.id}, 'direct')">
                            <i class="bi ${msg.is_pinned ? 'bi-pin-angle-fill text-danger' : 'bi-pin text-muted'}"></i> ${msg.is_pinned ? 'Unpin Message' : 'Pin Message'}
                        </a>
                    </li>
                </ul>
            </div>
        `;

        const starIconHtml = msg.is_important ? `<i class="bi bi-star-fill text-warning me-1" style="font-size: 10px;" title="Starred"></i>` : '';
        const pinIconHtml = msg.is_pinned ? `<i class="bi bi-pin-angle-fill text-danger me-1" style="font-size: 10px;" title="Pinned"></i>` : '';
        const editedHtml = msg.is_edited ? `<span class="text-muted me-1" style="font-size: 9px; font-style: italic;">(edited)</span>` : '';

        const metaHtml = `
            <div class="chat-meta d-flex align-items-center gap-1">
                ${starIconHtml}
                ${pinIconHtml}
                ${editedHtml}
                <span>${msg.time || msg.formatted_time}</span>
                ${msg.is_sent ? `<i class="bi ${msg.read_at ? 'bi-check2-all' : 'bi-check2'} ms-1"></i>` : ''}
            </div>
        `;

        if (msg.is_sent) {
            msgRow.innerHTML = `
                ${actionsHtml}
                <div class="chat-bubble">
                    ${replyHtml}
                    ${imgHtml}
                    ${fileHtml}
                    ${textHtml}
                    ${metaHtml}
                </div>
            `;
        } else {
            msgRow.innerHTML = `
                <div class="chat-bubble">
                    ${replyHtml}
                    ${imgHtml}
                    ${fileHtml}
                    ${textHtml}
                    ${metaHtml}
                </div>
                ${actionsHtml}
            `;
        }

        messagesContainer.appendChild(msgRow);
    }

    function openImageViewer(url) {
        const img = document.getElementById('image-viewer-img');
        if (img) {
            img.src = url;
            const modal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
            modal.show();
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatMessageText(text, msg) {
        if (!text) return '';
        
        let formatted = text;
        
        // Check if it's a leave request message
        if (formatted.includes('submitted a leave request') || formatted.includes('leave request for')) {
            // Extract leave ID
            const leaveIdMatch = formatted.match(/\/leaves\/(\d+)/);
            const leaveId = leaveIdMatch ? leaveIdMatch[1] : null;

            // Let's parse details
            let type = '';
            let duration = '';
            let reason = '';
            let extra = '';

            const typeMatch = formatted.match(/\*Type:\*\s*([^\n\*]+)/i) || formatted.match(/Type:\s*([^\n\*]+)/i);
            const durationMatch = formatted.match(/\*Duration:\*\s*([^\n\*]+)/i) || formatted.match(/Duration:\s*([^\n\*]+)/i);
            const reasonMatch = formatted.match(/\*Reason:\*\s*([^\n\*]+)/i) || formatted.match(/Reason:\s*([^\n\*]+)/i);
            const commentMatch = formatted.match(/\*Comment:\*\s*([^\n\*]+)/i) || formatted.match(/Comment:\s*([^\n\*]+)/i) || formatted.match(/\*Reason:\*\s*([^\n\*]+)/i);

            if (typeMatch) type = typeMatch[1].trim();
            if (durationMatch) duration = durationMatch[1].trim();
            if (reasonMatch) reason = reasonMatch[1].trim();
            if (commentMatch) extra = commentMatch[1].trim();

            // Render a beautiful, premium HTML card!
            let cardHtml = `
                <div class="card border border-light-subtle shadow-sm my-2 overflow-hidden" style="max-width: 320px; border-radius: 12px; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;">
                    <div class="card-header bg-light border-bottom border-light-subtle py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                <i class="bi bi-calendar-event" style="font-size: 11px;"></i>
                            </div>
                            <div class="fw-semibold text-dark fs-7" style="font-size: 13px;">Leave Request</div>
                        </div>
                    </div>
                    <div class="card-body p-3 text-dark fs-7" style="line-height: 1.4; font-size: 12px;">
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Type</span>
                            <span class="fw-semibold text-dark">${type || 'Leave'}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Duration</span>
                            <span class="fw-semibold text-dark">${duration || 'N/A'}</span>
                        </div>
                        ${reason ? `
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Reason</span>
                            <span class="text-dark">${reason}</span>
                        </div>
                        ` : ''}
                        ${extra && extra !== reason ? `
                        <div class="mb-2">
                            <span class="text-muted d-block" style="font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Comment/Reason</span>
                            <span class="text-dark">${extra}</span>
                        </div>
                        ` : ''}
            `;

            // If we have a leave ID and the current user is NOT the sender of the message, and has role permissions
            const canApproveReject = !msg.is_sent && (window.currentUserRole === 'super-admin' || window.currentUserRole === 'admin' || window.currentUserRole === 'hr' || window.currentUserRole === 'team-leader');
            
            if (leaveId) {
                const status = msg.leave_status || 'pending';
                if (status === 'approved') {
                    cardHtml += `
                        <div class="mt-3 pt-3 border-top border-light-subtle text-center">
                            <span class="badge bg-success py-1.5 px-3 w-100 fw-semibold text-white" style="font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                <i class="bi bi-check-circle-fill"></i> Approved
                            </span>
                        </div>
                    `;
                } else if (status === 'rejected') {
                    cardHtml += `
                        <div class="mt-3 pt-3 border-top border-light-subtle text-center">
                            <span class="badge bg-danger py-1.5 px-3 w-100 fw-semibold text-white" style="font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                <i class="bi bi-x-circle-fill"></i> Rejected
                            </span>
                        </div>
                    `;
                } else if (status === 'team_leader_approved') {
                    const isHrOrAdmin = (window.currentUserRole === 'super-admin' || window.currentUserRole === 'admin' || window.currentUserRole === 'hr');
                    if (canApproveReject && isHrOrAdmin) {
                        cardHtml += `
                            <div class="d-flex gap-2 mt-3 pt-3 border-top border-light-subtle">
                                <button type="button" class="btn btn-success btn-sm flex-grow-1 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" onclick="handleLeaveAction(${leaveId}, 'approve')" style="font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-sm flex-grow-1 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" onclick="handleLeaveAction(${leaveId}, 'reject')" style="font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </div>
                        `;
                    } else {
                        cardHtml += `
                            <div class="mt-3 pt-3 border-top border-light-subtle text-center">
                                <span class="badge bg-warning py-1.5 px-3 w-100 fw-semibold text-dark" style="font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                    <i class="bi bi-hourglass-split"></i> TL Approved
                                </span>
                            </div>
                        `;
                    }
                } else {
                    if (canApproveReject) {
                        cardHtml += `
                            <div class="d-flex gap-2 mt-3 pt-3 border-top border-light-subtle">
                                <button type="button" class="btn btn-success btn-sm flex-grow-1 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" onclick="handleLeaveAction(${leaveId}, 'approve')" style="font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-sm flex-grow-1 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" onclick="handleLeaveAction(${leaveId}, 'reject')" style="font-size: 11px; border-radius: 6px;">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </div>
                        `;
                    } else {
                        cardHtml += `
                            <div class="mt-3 pt-3 border-top border-light-subtle text-center">
                                <span class="badge bg-secondary py-1.5 px-3 w-100 fw-semibold text-white" style="font-size: 11px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                    <i class="bi bi-hourglass"></i> Pending Review
                                </span>
                            </div>
                        `;
                    }
                }
            }

            cardHtml += `
                    </div>
                </div>
            `;
            return cardHtml;
        }

        // Standard formatting
        let escaped = escapeHtml(formatted);
        escaped = escaped.replace(/\*([^*]+)\*/g, '<strong>$1</strong>');
        const urlRegex = /(https?:\/\/[^\s<]+)/g;
        escaped = escaped.replace(urlRegex, '<a href="$1" target="_blank" class="text-primary text-decoration-underline">$1</a>');
        return escaped.replace(/\n/g, '<br>');
    }

    window.handleLeaveAction = function(leaveId, action) {
        document.getElementById('leave_action_id').value = leaveId;
        document.getElementById('leave_action_type').value = action;
        
        const label = document.getElementById('leaveActionCommentLabel');
        const title = document.getElementById('leaveActionModalLabel');
        const btn = document.getElementById('leaveActionConfirmBtn');
        const textarea = document.getElementById('leave_action_comment');
        
        textarea.value = '';
        textarea.classList.remove('is-invalid');
        document.getElementById('leave_action_error').textContent = '';

        if (action === 'approve') {
            title.textContent = 'Approve Leave Request';
            label.textContent = 'Approval Comments (Optional)';
            textarea.placeholder = 'Enter any approval comments...';
            btn.className = 'btn btn-success btn-sm';
            btn.textContent = 'Approve';
            textarea.required = false;
        } else {
            title.textContent = 'Reject Leave Request';
            label.textContent = 'Rejection Reason (Required)';
            textarea.placeholder = 'Enter reason for rejection...';
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Reject';
            textarea.required = true;
        }

        const modalEl = document.getElementById('leaveActionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    };

    // Setup submit listener for leave action form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('leaveActionForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const leaveId = document.getElementById('leave_action_id').value;
                const action = document.getElementById('leave_action_type').value;
                const commentVal = document.getElementById('leave_action_comment').value.trim();
                const textarea = document.getElementById('leave_action_comment');
                const errorDiv = document.getElementById('leave_action_error');
                
                if (action === 'reject' && !commentVal) {
                    textarea.classList.add('is-invalid');
                    errorDiv.textContent = 'Rejection reason is required.';
                    return;
                }

                const token = "{{ csrf_token() }}";
                const role = window.currentUserRole;
                
                let url = '';
                let payload = {};
                
                if (action === 'approve') {
                    url = (role === 'team-leader') ? `/leaves/${leaveId}/approve-tl` : `/leaves/${leaveId}/approve-hr`;
                    payload = { comment: commentVal };
                } else {
                    url = `/leaves/${leaveId}/reject`;
                    payload = { reason: commentVal };
                }

                const confirmBtn = document.getElementById('leaveActionConfirmBtn');
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => {
                    if (res.redirected) {
                        window.location.reload();
                    } else {
                        return res.json().then(data => {
                            window.location.reload();
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("An error occurred. Please refresh and try again.");
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = 'Confirm';
                });
            });
        }
    });

    function updateMainSidebarBadge(directCount) {
        if (directCount !== undefined) {
            currentDirectUnreadCount = directCount;
        }
        const total = currentDirectUnreadCount + currentTaskUnreadCount;
        const mainBadge = document.getElementById('sidebar-chat-badge');
        if (mainBadge) {
            if (total > 0) {
                mainBadge.textContent = total;
                mainBadge.classList.remove('d-none');
            } else {
                mainBadge.classList.add('d-none');
            }
        }
    }

    function startGlobalDirectChatPolling() {
        setInterval(function() {
            let url = `/direct-chat/updates?since=${encodeURIComponent(lastDirectPolledAt)}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        lastDirectPolledAt = data.timestamp;

                        // 1. Update individual contact badges in the list and move to top
                        document.querySelectorAll('.chat-user-item').forEach(item => {
                            const userId = item.id.replace('contact-', '');
                            const badge = document.getElementById(`badge-${userId}`);
                            if (badge) {
                                const count = parseInt(data.unread_counts[userId]) || 0;
                                if (count > 0 && parseInt(userId) !== activeDirectUserId) {
                                    badge.textContent = count;
                                    badge.classList.remove('d-none');
                                    const lastMsgText = item.querySelector('.last-msg').textContent.trim();
                                    const timeText = item.querySelector('.text-muted.flex-shrink-0.ms-2')?.textContent?.trim() || '';
                                    moveThreadToTop('direct', userId, lastMsgText, timeText, count);
                                } else {
                                    if (parseInt(userId) !== activeDirectUserId) {
                                        badge.classList.add('d-none');
                                        badge.textContent = '0';
                                    }
                                }
                            }
                        });

                        // Move items for explicitly received new messages
                        data.new_messages.forEach(msg => {
                            const count = parseInt(data.unread_counts[msg.sender_id]) || 0;
                            moveThreadToTop('direct', msg.sender_id, msg.message, 'Just now', count);
                        });

                        // 2. Update main sidebar link badge (sum of task unread counts + direct unread counts)
                        updateMainSidebarBadge(data.total_unread);

                        // 3. If we are currently in an active direct chat, append new messages
                        if (activeChatType === 'direct' && activeDirectUserId && data.new_messages.length > 0) {
                            const messagesContainer = document.getElementById('chat-messages-container');
                            const chatBody = document.querySelector('.chat-body');
                            
                            let appendedAny = false;
                            let playSound = false;
                            
                            data.new_messages.forEach(msg => {
                                if (parseInt(msg.sender_id) === parseInt(activeDirectUserId)) {
                                    if (!document.getElementById(`msg-row-${msg.id}`)) {
                                        msg.is_sent = false;
                                        appendDirectMessageHtml(msg);
                                        appendedAny = true;
                                        playSound = true;
                                    }
                                }
                            });

                            if (appendedAny) {
                                groupChatMessagesByDate('chat-messages-container');
                                chatBody.scrollTop = chatBody.scrollHeight;
                                
                                // Mark as read
                                fetch(`/direct-chat/read/${activeDirectUserId}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                
                                if (playSound && typeof window.playNotificationSound === 'function') {
                                    window.playNotificationSound();
                                }
                            }
                        }
                    }
                })
                .catch(err => console.error('Error polling direct chat updates:', err));
        }, 5000);
    }

    // ─── Task Detail inline view ─────────────────────────────────────────────
    let _taskDetailOpen = false;

    function toggleInfoSidebar(e) {
        if (e) e.preventDefault();
        if (!currentTaskData) return; // not a task chat
        if (_taskDetailOpen) {
            closeInfoSidebar();
        } else {
            openInfoSidebar();
        }
    }

    function openInfoSidebar() {
        if (!currentTaskData) return;
        _taskDetailOpen = true;

        const d = currentTaskData;
        const attachments = d.attachments || [];
        const images = attachments.filter(a => a.is_image);
        const files  = attachments.filter(a => !a.is_image);

        // Status badge colours
        const statusColors = {
            'in_progress': ['#fef3c7','#92400e'], 'completed': ['#d1fae5','#065f46'],
            'review':      ['#e0f2fe','#075985'], 'rework':    ['#fef2f2','#991b1b'],
            'rejected':    ['#fef2f2','#991b1b'], 'pending':   ['#f1f5f9','#475569'],
        };
        const [sBg, sFg] = statusColors[d.status] || ['#f1f5f9','#475569'];

        // Priority badge colours
        const prioColors = {
            'Critical': ['#fef2f2','#991b1b'], 'High': ['#fef3c7','#92400e'],
            'Medium':   ['#eff6ff','#1e40af'], 'Low':  ['#f0fdf4','#166534'],
        };
        const [pBg, pFg] = prioColors[d.priority] || ['#f1f5f9','#475569'];

        // Attachments HTML
        let attachHtml = '';
        if (attachments.length === 0) {
            attachHtml = `<div class="text-center py-4 text-muted">
                <i class="bi bi-paperclip" style="font-size:32px;opacity:.35;"></i>
                <div class="mt-2" style="font-size:13px;">No attachments</div>
            </div>`;
        } else {
            if (images.length > 0) {
                attachHtml += `<div class="d-flex flex-wrap gap-2 mb-3">`;
                images.forEach(img => {
                    attachHtml += `
                    <a href="${img.url}" target="_blank" title="${img.name}"
                       style="width:90px;height:90px;border-radius:10px;overflow:hidden;display:block;
                              border:1px solid #e2e8f0;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.06);">
                        <img src="${img.url}" alt="${img.name}"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.parentElement.innerHTML='<div style=\"background:#f1f5f9;width:100%;height:100%;display:flex;align-items:center;justify-content:center;\"><i class=\"bi bi-image text-muted fs-4\"></i></div>'">
                    </a>`;
                });
                attachHtml += `</div>`;
            }
            const extIcons = { pdf:'bi-file-pdf text-danger', doc:'bi-file-word text-primary',
                docx:'bi-file-word text-primary', xls:'bi-file-excel text-success',
                xlsx:'bi-file-excel text-success', zip:'bi-file-zip text-warning',
                txt:'bi-file-text text-secondary' };
            files.forEach(f => {
                const icon = extIcons[f.ext] || 'bi-file-earmark text-muted';
                const short = f.name.length > 40 ? f.name.substring(0,37)+'...' : f.name;
                attachHtml += `
                <a href="${f.url}" target="_blank" download
                   class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none mb-2"
                   style="background:#fff;border:1px solid #e2e8f0;transition:background .15s;"
                   onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
                    <i class="bi ${icon}" style="font-size:22px;flex-shrink:0;"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark" style="font-size:13px;word-break:break-all;" title="${f.name}">${short}</div>
                        <div class="text-muted" style="font-size:11px;">.${f.ext.toUpperCase()}&nbsp;&bull;&nbsp;Click to download</div>
                    </div>
                    <i class="bi bi-download text-primary" style="font-size:16px;flex-shrink:0;"></i>
                </a>`;
            });
        }

        const html = `
        <div style="max-width:780px;margin:0 auto;">

            <!-- Back button strip -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <button onclick="closeInfoSidebar()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2"
                        style="border-radius:20px;font-size:13px;">
                    <i class="bi bi-arrow-left"></i> Back to messages
                </button>
                <a href="${d.task_url}" target="_blank" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2"
                   style="border-radius:20px;font-size:13px;">
                    <i class="bi bi-box-arrow-up-right"></i> Open full task
                </a>
            </div>

            <!-- Title card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:24px 28px;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="${d.assignee_avatar}" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.25);flex-shrink:0;">
                        <div>
                            <div class="text-white fw-bold" style="font-size:17px;line-height:1.3;">${d.task_title}</div>
                            <div class="text-white-50" style="font-size:13px;margin-top:2px;">${d.project_name}</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span style="background:${sBg};color:${sFg};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
                            ${d.status_text}
                        </span>
                        <span style="background:${pBg};color:${pFg};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
                            ${d.priority} Priority
                        </span>
                        ${d.deadline !== 'No deadline' ? `<span style="background:rgba(255,255,255,.12);color:#e2e8f0;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;"><i class="bi bi-calendar3 me-1"></i>${d.deadline}</span>` : ''}
                    </div>
                </div>

                <!-- Meta grid -->
                <div class="card-body p-0">
                    <div class="row g-0" style="border-top:1px solid #f1f5f9;">
                        <div class="col-6 col-md-3 p-3 border-end border-bottom">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;">Assignee</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <img src="${d.assignee_real_avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">
                                <span class="fw-semibold text-dark" style="font-size:13px;">${d.assignee_name}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 p-3 border-md-end border-bottom">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;">Creator</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <img src="${d.creator_avatar}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">
                                <span class="fw-semibold text-dark" style="font-size:13px;">${d.creator_name}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 p-3 border-end">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;">Created</div>
                            <div class="fw-semibold text-dark mt-1" style="font-size:13px;">${d.created_at}</div>
                        </div>
                        <div class="col-6 col-md-3 p-3">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;">Est. Hours</div>
                            <div class="fw-semibold text-dark mt-1" style="font-size:13px;">${d.estimated_hours ? d.estimated_hours + ' hrs' : '—'}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:14px;">
                        <i class="bi bi-file-text text-primary"></i> Description
                    </div>
                    <div style="white-space:pre-wrap;line-height:1.75;color:#334155;font-size:14px;min-height:48px;">${d.description || '<em class="text-muted">No description provided.</em>'}</div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size:14px;">
                        <i class="bi bi-paperclip text-primary"></i> Attachments
                        <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:11px;">${attachments.length}</span>
                    </div>
                    ${attachHtml}
                </div>
            </div>

        </div>`;

        const detailView = document.getElementById('chat-task-detail-view');
        const bodyWrap   = document.getElementById('chat-body-wrap');
        const inputBar   = document.getElementById('chat-form');

        detailView.innerHTML = html;
        detailView.style.display = 'block';
        if (bodyWrap)   bodyWrap.style.display = 'none';
        if (inputBar)   inputBar.style.display = 'none';
    }

    function closeInfoSidebar() {
        _taskDetailOpen = false;
        const detailView = document.getElementById('chat-task-detail-view');
        const bodyWrap   = document.getElementById('chat-body-wrap');
        const inputBar   = document.getElementById('chat-form');
        if (detailView) detailView.style.display = 'none';
        if (bodyWrap)   bodyWrap.style.display = 'block';
        if (inputBar)   inputBar.style.display = '';
    }

    function toggleChatSearch() {
        const searchBar = document.getElementById('chat-message-search-bar');
        if (!searchBar) return;
        
        if (searchBar.classList.contains('d-none')) {
            searchBar.classList.remove('d-none');
            searchBar.classList.add('d-flex');
            document.getElementById('chat-message-search-input').focus();
        } else {
            searchBar.classList.remove('d-flex');
            searchBar.classList.add('d-none');
            document.getElementById('chat-message-search-input').value = '';
            filterChatMessages('');
        }
    }

    function filterChatMessages(query) {
        query = query.toLowerCase().trim();
        const chatRows = document.querySelectorAll('#chat-messages-container .chat-row');
        
        chatRows.forEach(row => {
            if (!query) {
                row.classList.remove('d-none');
                removeHighlighting(row);
                return;
            }

            const commentEl = row.querySelector('.chat-text');
            const noteEl = row.querySelector('.time-log-note-content');
            
            let commentText = commentEl ? commentEl.textContent : '';
            let noteText = noteEl ? noteEl.textContent : '';
            
            const matchesComment = commentText.toLowerCase().includes(query);
            const matchesNote = noteText.toLowerCase().includes(query);
            
            if (matchesComment || matchesNote) {
                row.classList.remove('d-none');
                if (matchesComment && commentEl) {
                    highlightText(commentEl, query);
                }
                if (matchesNote && noteEl) {
                    highlightText(noteEl, query);
                }
            } else {
                row.classList.add('d-none');
            }
        });
    }

    function highlightText(element, query) {
        if (element.dataset.originalHtml) {
            element.innerHTML = element.dataset.originalHtml;
        } else {
            element.dataset.originalHtml = element.innerHTML;
        }

        const escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const walk = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, null, false);
        let node;
        const matches = [];
        while (node = walk.nextNode()) {
            if (node.nodeValue.toLowerCase().includes(query)) {
                matches.push(node);
            }
        }
        
        matches.forEach(textNode => {
            const parent = textNode.parentNode;
            if (parent && (parent.tagName !== 'SPAN' || !parent.classList.contains('chat-highlight'))) {
                const text = textNode.nodeValue;
                const regex = new RegExp(`(${escapedQuery})`, 'gi');
                const fragment = document.createDocumentFragment();
                let lastIndex = 0;
                let match;
                
                while (match = regex.exec(text)) {
                    const before = text.substring(lastIndex, match.index);
                    if (before) fragment.appendChild(document.createTextNode(before));
                    
                    const span = document.createElement('span');
                    span.className = 'chat-highlight bg-warning text-dark px-1 rounded';
                    span.appendChild(document.createTextNode(match[0]));
                    fragment.appendChild(span);
                    
                    lastIndex = regex.lastIndex;
                }
                
                const after = text.substring(lastIndex);
                if (after) fragment.appendChild(document.createTextNode(after));
                
                parent.replaceChild(fragment, textNode);
            }
        });
    }

    function removeHighlighting(row) {
        const commentEl = row.querySelector('.chat-text');
        const noteEl = row.querySelector('.time-log-note-content');
        
        if (commentEl && commentEl.dataset.originalHtml) {
            commentEl.innerHTML = commentEl.dataset.originalHtml;
            delete commentEl.dataset.originalHtml;
        }
        if (noteEl && noteEl.dataset.originalHtml) {
            noteEl.innerHTML = noteEl.dataset.originalHtml;
            delete noteEl.dataset.originalHtml;
        }
    }

    function openEndTaskModalChat(logId, currentStatus) {
        document.getElementById('endTaskModalForm').action = `/work-timer/end-task/${logId}`;
        const modal = new bootstrap.Modal(document.getElementById('endTaskModal'));
        modal.show();
    }

    function openTaskCompletionModalChat(taskId) {
        document.getElementById('taskCompletionModalForm').action = `/tasks/${taskId}/submit-completion`;
        const modal = new bootstrap.Modal(document.getElementById('taskCompletionModal'));
        modal.show();
    }

    function openEditTaskModalChat() {
        if (!currentTaskData) return;
        
        // Form action URL
        document.getElementById('editTaskModalForm').action = `/tasks/${currentTaskData.task_id}`;
        
        // Populate inputs
        document.getElementById('edit-task-title-input').value = currentTaskData.task_title || '';
        document.getElementById('edit-task-desc-input').value = currentTaskData.description || '';
        document.getElementById('edit-task-project-select').value = currentTaskData.project_id || '';
        document.getElementById('edit-task-assignee-select').value = currentTaskData.assignee_id || '';
        document.getElementById('edit-task-priority-select').value = currentTaskData.priority_raw ? currentTaskData.priority_raw.toLowerCase() : 'medium';
        
        const completedOption = document.querySelector('#edit-task-status-select .completed-option');
        if (completedOption) {
            if (currentTaskData.status === 'completed') {
                completedOption.classList.remove('d-none');
            } else {
                completedOption.classList.add('d-none');
            }
        }
        
        document.getElementById('edit-task-status-select').value = currentTaskData.status || 'pending';
        document.getElementById('edit-task-deadline-input').value = currentTaskData.deadline_raw || '';
        document.getElementById('edit-task-est-input').value = currentTaskData.estimated_hours || 0;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        modal.show();
    }

    function updateTaskStatusChat(taskId, status) {
        fetch(`/tasks/${taskId}/update-status`, {
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
        // Start polling for direct messages globally
        startGlobalDirectChatPolling();

        let savedTaskId = '{{ session('select_task_id') }}' || new URLSearchParams(window.location.search).get('select_task');
        let savedDirectUserId = new URLSearchParams(window.location.search).get('select_direct') || new URLSearchParams(window.location.search).get('select_user');

        if (savedTaskId && savedTaskId !== 'none') {
            const el = document.getElementById(`chat-task-item-${savedTaskId}`);
            if (el) {
                setTimeout(() => {
                    selectTask(parseInt(savedTaskId));
                }, 100);
            }
        } else if (savedDirectUserId) {
            const el = document.getElementById(`contact-${savedDirectUserId}`);
            if (el) {
                setTimeout(() => {
                    const name = el.querySelector('.user-name').textContent.trim();
                    const role = el.getAttribute('data-role') || 'Staff';
                    const avatar = el.getAttribute('data-avatar');
                    selectDirectUser(parseInt(savedDirectUserId), name, role, avatar);
                }, 100);
            }
        }

        const chatSearchInput = document.getElementById('chat-message-search-input');
        if (chatSearchInput) {
            chatSearchInput.addEventListener('input', function() {
                filterChatMessages(this.value);
            });
        }

        // Populate initial unread counts
        document.querySelectorAll('[id^="unread-badge-"]').forEach(badge => {
            const taskId = parseInt(badge.id.replace('unread-badge-', ''));
            if (!isNaN(taskId)) {
                previousUnreadCounts[taskId] = parseInt(badge.textContent) || 0;
            }
        });

        // Poll chat list unread counts every 10 seconds
        pollChatListUnreadCounts();
        setInterval(pollChatListUnreadCounts, 10000);
    });

    // Sidebar view transitions
    function showNewChatView() {
        document.getElementById('sidebar-main-view').classList.remove('d-flex');
        document.getElementById('sidebar-main-view').classList.add('d-none');
        
        document.getElementById('sidebar-new-chat-view').classList.remove('d-none');
        document.getElementById('sidebar-new-chat-view').classList.add('d-flex');
        
        document.getElementById('new-chat-search-input').value = '';
        document.getElementById('new-chat-search-input').focus();
        
        // Trigger input event to clear filter
        const event = new Event('input', { bubbles: true });
        document.getElementById('new-chat-search-input').dispatchEvent(event);
    }

    function hideNewChatView() {
        document.getElementById('sidebar-new-chat-view').classList.remove('d-flex');
        document.getElementById('sidebar-new-chat-view').classList.add('d-none');
        
        document.getElementById('sidebar-main-view').classList.remove('d-none');
        document.getElementById('sidebar-main-view').classList.add('d-flex');
    }

    // New Chat Direct user selection
    function startDirectFromNewChat(userId, name, role, avatar) {
        hideNewChatView();
        selectDirectUser(userId, name, role, avatar);
    }

    // New Chat sidebar employee search filter
    document.getElementById('new-chat-search-input').addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.new-chat-contact-item').forEach(item => {
            const name = item.dataset.name || '';
            const role = item.dataset.role || '';
            if (name.includes(query) || role.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    });

    // Category and Keyword Search Filters combining logic
    let activeChatFilter = 'all';

    function filterChats(filter) {
        activeChatFilter = filter;
        document.querySelectorAll('.chat-filter-btn').forEach(btn => {
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        applySidebarFilters();
    }

    function applySidebarFilters() {
        const query = document.getElementById('task-search-input').value.toLowerCase().trim();
        const items = document.querySelectorAll('.unified-chat-item');
        items.forEach(item => {
            // Search validation
            const title = item.dataset.title || '';
            const project = item.dataset.project || '';
            const name = item.dataset.name || '';
            const role = item.dataset.role || '';
            const matchesSearch = query === '' || title.includes(query) || project.includes(query) || name.includes(query) || role.includes(query);

            // Category filter validation
            const unreadCount = parseInt(item.getAttribute('data-unread-count') || '0');
            const isBug = item.getAttribute('data-is-bug') === '1';
            const priority = item.getAttribute('data-priority') || '';
            const status = item.getAttribute('data-status') || '';
            
            let matchesFilter = true;
            if (activeChatFilter === 'unread') {
                matchesFilter = unreadCount > 0;
            } else if (activeChatFilter === 'bugs') {
                matchesFilter = isBug;
            } else if (activeChatFilter === 'critical') {
                matchesFilter = priority === 'critical';
            } else if (activeChatFilter === 'review') {
                matchesFilter = status === 'review';
            }

            if (matchesSearch && matchesFilter) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    // Search input listener
    document.getElementById('task-search-input').addEventListener('input', function() {
        applySidebarFilters();
    });

    function goBackToChatList() {
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.remove('chat-show-main');
        }
        
        closeInfoSidebar();
        
        // Clear active task list selection highlight
        document.querySelectorAll('.chat-task-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.chat-user-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Remove task id and direct user id from local storage
        localStorage.removeItem('active_chat_task_id');
        localStorage.removeItem('active_chat_direct_user_id');
        
        // Clear activeTaskId and activeDirectUserId
        activeTaskId = null;
        activeDirectUserId = null;
        activeChatType = 'task';
        
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    }

    function selectTask(taskId) {
        hideCreationForms();
        closeInfoSidebar();
        if (activeTaskId === taskId) return;
        
        cancelReply();
        
        activeChatType = 'task';
        activeDirectUserId = null;
        localStorage.removeItem('active_chat_direct_user_id');
        document.querySelectorAll('.chat-user-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Hide search bar when switching chats
        const searchBar = document.getElementById('chat-message-search-bar');
        if (searchBar) {
            searchBar.classList.remove('d-flex');
            searchBar.classList.add('d-none');
        }
        const searchInput = document.getElementById('chat-message-search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
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

        // Add class to trigger mobile transition to active chat
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.add('chat-show-main');
        }

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
        fetch(`/chat/tasks/${taskId}?_t=${new Date().getTime()}`)
            .then(response => response.json())
            .then(data => {
                currentTaskData = data;

                // Update info sidebar if it's currently open
                const sidebar = document.getElementById('chat-info-sidebar');
                if (sidebar && !sidebar.classList.contains('d-none')) {
                    openInfoSidebar();
                }

                // Populate headers
                let titleHtml = '';
                let displayTitle = data.task_title;
                if (window.innerWidth <= 767.98) {
                    if (displayTitle.length > 25) {
                        displayTitle = displayTitle.substring(0, 25) + '...';
                    }
                }

                if (displayTitle.toLowerCase().startsWith('bug:')) {
                    titleHtml = `<i class="bi bi-bug-fill text-danger me-1"></i>${displayTitle}`;
                } else if (displayTitle.toLowerCase().startsWith('room calling:')) {
                    titleHtml = `<i class="bi bi-telephone-fill text-success me-1"></i>${displayTitle}`;
                } else {
                    titleHtml = displayTitle;
                }

                if (isLeaderOrAbove && data.is_working) {
                    titleHtml += ` <span class="badge bg-success text-white border border-success ms-2 d-inline-flex align-items-center gap-1" style="font-size: 11px; padding: 2px 6px; font-weight: 600;"><span class="pulse-dot-green"></span> working</span>`;
                }
                const titleLink = document.getElementById('chat-active-title');
                titleLink.innerHTML = titleHtml;
                titleLink.onclick = function(e) {
                    toggleInfoSidebar(e);
                };

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
                const projectSpan = document.getElementById('chat-active-project');
                projectSpan.textContent = data.project_name;
                projectSpan.classList.add('text-primary');
                projectSpan.classList.remove('text-muted');
                
                document.getElementById('chat-active-separator').style.display = 'inline';
                document.getElementById('chat-active-assignee-label').style.display = 'inline';
                
                // Show task avatar (project logo) in big header avatar container
                const avatarContainer = document.getElementById('chat-header-avatar-container');
                if (avatarContainer) {
                    avatarContainer.style.display = 'block';
                    document.getElementById('chat-header-avatar').src = data.assignee_avatar;
                    document.getElementById('chat-header-online-dot').style.display = 'none';
                }
                
                const activeAvatar = document.getElementById('chat-active-avatar');
                activeAvatar.style.display = 'inline-block';
                activeAvatar.src = data.assignee_real_avatar;
                
                document.getElementById('chat-active-assignee').textContent = data.assignee_name;
                document.getElementById('chat-active-title').href = data.task_url;
                
                // Form action
                document.getElementById('chat-form').action = data.store_url;
                activeStoreUrl = data.store_url;

                // Save selected taskId to localStorage
                localStorage.setItem('active_chat_task_id', taskId);

                // Sync deadline bracket next to assignee
                const deadlineEl = document.getElementById('chat-active-deadline');
                if (deadlineEl) {
                    if (data.deadline_days !== null && data.deadline_days !== undefined) {
                        let daysText = '';
                        if (data.deadline_days > 0) {
                            daysText = `(${data.deadline_days} days remaining)`;
                        } else if (data.deadline_days === 0) {
                            daysText = `(today)`;
                        } else {
                            daysText = `(overdue ${Math.abs(data.deadline_days)} days)`;
                        }
                        deadlineEl.textContent = daysText;
                        
                        // If it is less than 3 days, show in red colour
                        if (data.deadline_days < 3) {
                            deadlineEl.classList.add('text-danger');
                            deadlineEl.classList.remove('text-muted');
                        } else {
                            deadlineEl.classList.add('text-muted');
                            deadlineEl.classList.remove('text-danger');
                        }
                        
                        deadlineEl.classList.remove('d-none');
                    } else {
                        deadlineEl.textContent = '';
                        deadlineEl.classList.add('d-none');
                        deadlineEl.classList.remove('text-danger');
                        deadlineEl.classList.add('text-muted');
                    }
                }

                // Build header actions dropdown items (for mobile)
                let dropdownHtml = `
                    <li>
                        <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2 text-dark fw-semibold" onclick="openEditTaskModalChat()">
                            <i class="bi bi-pencil-fill fs-5"></i> Edit Task
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                `;
                // Build header actions desktop buttons (for PC)
                let desktopHtml = `
                    <button type="button" class="btn btn-outline-secondary btn-sm me-1" onclick="openEditTaskModalChat()" title="Edit Task">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                `;

                if (data.active_log_id) {
                    dropdownHtml += `
                        <li>
                            <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger fw-semibold" onclick="openEndTaskModalChat(${data.active_log_id}, '${data.status}')" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                <i class="bi bi-stop-fill fs-5"></i> End Work
                            </button>
                        </li>
                    `;
                    desktopHtml += `
                        <button type="button" class="btn btn-danger btn-sm" onclick="openEndTaskModalChat(${data.active_log_id}, '${data.status}')" ${data.is_buttons_disabled ? 'disabled' : ''}>
                            <i class="bi bi-stop-fill me-1"></i> End Work
                        </button>
                    `;
                } else {
                    dropdownHtml += `
                        <li>
                            <form method="POST" action="/work-timer/start-task/${taskId}" onsubmit="this.querySelector('button').disabled = true;">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-success fw-semibold" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                    <i class="bi bi-play-fill fs-5"></i> Start Work
                                </button>
                            </form>
                        </li>
                    `;
                    desktopHtml += `
                        <form method="POST" action="/work-timer/start-task/${taskId}" class="d-inline" onsubmit="this.querySelector('button').disabled = true;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                <i class="bi bi-play-fill me-1"></i> Start Work
                            </button>
                        </form>
                    `;

                    if (data.status !== 'pending') {
                        dropdownHtml += `
                            <li>
                                <button type="button" class="dropdown-item py-2 d-flex align-items-center gap-2 text-primary fw-semibold" onclick="openTaskCompletionModalChat(${taskId})" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                    <i class="bi bi-send-check fs-5"></i> Submit for Review
                                </button>
                            </li>
                        `;
                        desktopHtml += `
                            <button type="button" class="btn btn-primary btn-sm" onclick="openTaskCompletionModalChat(${taskId})" ${data.is_buttons_disabled ? 'disabled' : ''}>
                                <i class="bi bi-send-check me-1"></i> Submit for Review
                            </button>
                        `;
                    }
                }

                document.getElementById('chat-header-dropdown-menu').innerHTML = dropdownHtml;
                document.getElementById('chat-header-desktop-actions').innerHTML = desktopHtml;

                // Remove unread count badge in sidebar
                const badge = document.getElementById(`unread-badge-${taskId}`);
                if (badge) {
                    badge.remove();
                }
                previousUnreadCounts[taskId] = 0;

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
                    groupChatMessagesByDate('chat-messages-container');
                    window.updatePinnedMessagesList();
                }

                // Scroll to bottom
                const chatBody = document.querySelector('.chat-body');
                chatBody.scrollTop = chatBody.scrollHeight;

                latestFeedTime = data.latest_time;
                lastCommentId = data.last_comment_id;
                lastTimelogId = data.last_timelog_id;

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

    let previousUnreadCounts = {};

    const pollChatListUnreadCounts = () => {
        fetch("/chat/unread-counts")
            .then(response => response.json())
            .then(data => {
                const unreadCounts = data.unread_counts || {};
                let shouldPlaySound = false;
                let totalTaskUnread = 0;

                // Loop through all task items to update badges
                document.querySelectorAll('.chat-task-item').forEach(item => {
                    const taskId = parseInt(item.id.replace('chat-task-item-', ''));
                    if (isNaN(taskId)) return;

                    const count = (taskId === activeTaskId) ? 0 : (parseInt(unreadCounts[taskId]) || 0);
                    totalTaskUnread += count;
                    const container = document.getElementById('badge-container-' + taskId);
                    
                    if (container) {
                        let badge = document.getElementById('unread-badge-' + taskId);
                        if (count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.id = 'unread-badge-' + taskId;
                                badge.className = 'badge bg-success rounded-circle d-flex align-items-center justify-content-center';
                                badge.style.cssText = 'width: 18px; height: 18px; font-size: 9px; padding: 0; line-height: 1; flex-shrink: 0;';
                                container.appendChild(badge);
                            }
                            badge.textContent = count;
                            
                            // Move to top since it has unread comments
                            moveThreadToTop('task', taskId, null, 'Just now', count);
                        } else {
                            if (badge) {
                                badge.remove();
                            }
                        }
                    }

                    // Only play sound for tasks OTHER than the active task
                    if (taskId !== activeTaskId) {
                        const prevCount = previousUnreadCounts[taskId] || 0;
                        if (count > prevCount) {
                            shouldPlaySound = true;
                        }
                    }
                    
                    // Update tracked counts
                    previousUnreadCounts[taskId] = count;
                });

                currentTaskUnreadCount = totalTaskUnread;
                updateMainSidebarBadge();

                if (shouldPlaySound && typeof window.playNotificationSound === 'function') {
                    window.playNotificationSound();
                }
            })
            .catch(error => console.error('Error polling unread chat counts:', error));
    };

    const pollChatUpdates = () => {
        if (!activeTaskId || !latestFeedTime) return;

        let url = `/tasks/${activeTaskId}/feed-updates?since=${encodeURIComponent(latestFeedTime)}`;
        if (lastCommentId) {
            url += `&last_comment_id=${lastCommentId}`;
        }
        if (lastTimelogId) {
            url += `&last_timelog_id=${lastTimelogId}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.has_updates) {
                    latestFeedTime = data.latest_time;
                    lastCommentId = data.last_comment_id;
                    lastTimelogId = data.last_timelog_id;
                    
                    const messagesContainer = document.getElementById('chat-messages-container');
                    const chatBody = document.querySelector('.chat-body');
                    if (messagesContainer && chatBody) {
                        const noMessagesEl = document.getElementById('no-chat-messages');
                        if (noMessagesEl) {
                            noMessagesEl.remove();
                        }
                        
                        const isNearBottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop < 100;
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;
                        const newRows = tempDiv.querySelectorAll('.chat-row');
                        let appendedAny = false;
                        let playSound = false;
                        let hasSentMessage = false;
                        
                        newRows.forEach(row => {
                            if (!document.getElementById(row.id)) {
                                messagesContainer.appendChild(row);
                                appendedAny = true;
                                if (row.classList.contains('received')) {
                                    playSound = true;
                                }
                                if (row.classList.contains('sent')) {
                                    hasSentMessage = true;
                                }
                            }
                        });
                        
                        if (appendedAny) {
                            groupChatMessagesByDate('chat-messages-container');
                            window.updatePinnedMessagesList();
                            if (isNearBottom || hasSentMessage) {
                                chatBody.scrollTop = chatBody.scrollHeight;
                            }
                            
                            if (playSound && typeof window.playNotificationSound === 'function') {
                                window.playNotificationSound();
                            }

                            // Move task thread to the top of list
                            moveThreadToTop('task', activeTaskId, null, 'Just now', 0);
                        }
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
        const fileInput = document.getElementById('chat-image-input');
        const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

        if (!comment && !imageData && !hasFile) return;

        const submitBtn = this.querySelector('.whatsapp-send-btn');
        submitBtn.disabled = true;

        const editId = document.getElementById('edit-message-id')?.value;
        const editType = document.getElementById('edit-message-type')?.value;

        if (editId) {
            let url = editType === 'direct' 
                ? `/direct-chat/messages/${editId}/edit` 
                : `/tasks/comments/${editId}/edit`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: comment, comment: comment })
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                if (data.success) {
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    cancelEdit();
                    
                    // Update text in UI
                    const rowId = editType === 'direct' ? `msg-row-${editId}` : `chat-row-comment-${editId}`;
                    const row = document.getElementById(rowId);
                    if (row) {
                        const textEl = row.querySelector('.chat-text');
                        if (textEl) textEl.textContent = comment;
                        
                        // Add (edited) label
                        const bubble = row.querySelector('.chat-bubble');
                        if (bubble) {
                            const meta = bubble.querySelector('.chat-meta');
                            if (meta && !meta.querySelector('.text-muted')) {
                                const editedSpan = document.createElement('span');
                                editedSpan.className = 'text-muted me-1';
                                editedSpan.style.cssText = 'font-size: 9px; font-style: italic;';
                                editedSpan.textContent = '(edited)';
                                meta.insertBefore(editedSpan, meta.querySelector('span'));
                            }
                        }
                    }
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                console.error('Error editing message:', err);
            });
            return;
        }

        const formData = new FormData(this);
        if (activeChatType === 'direct') {
            formData.append('message', comment);
        }

        fetch(activeStoreUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    let errMsg = data.message || 'Something went wrong.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey].length > 0) {
                            errMsg = data.errors[firstKey][0];
                        }
                    }
                    throw new Error(errMsg);
                });
            }
            return response.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            textarea.value = '';
            textarea.style.height = 'auto';
            document.getElementById('chat-image-data').value = '';
            const chatImageInput = document.getElementById('chat-image-input');
            if (chatImageInput) chatImageInput.value = '';
            cancelReply();
            
            const preview = document.getElementById('chat-attachment-preview');
            if (preview) {
                preview.classList.add('d-none');
                preview.style.backgroundImage = '';
                preview.innerHTML = `
                    <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                        <i class="bi bi-x"></i>
                    </button>
                `;
            }
            
            // Trigger an immediate check for updates
            if (activeChatType === 'task') {
                pollChatUpdates();
                moveThreadToTop('task', activeTaskId, comment || 'Sent an attachment', 'Just now', 0);
            } else {
                if (data.success) {
                    appendDirectMessageHtml(data.message);
                    groupChatMessagesByDate('chat-messages-container');
                    
                    const chatBody = document.querySelector('.chat-body');
                    chatBody.scrollTop = chatBody.scrollHeight;
                    
                    moveThreadToTop('direct', activeDirectUserId, data.message.message || (data.message.file_url ? '[Document]' : '[Image]'), 'Just now', 0);
                }
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            alert(error.message || 'Error posting message.');
            console.error('Error posting message:', error);
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
                const imageVal = document.getElementById('chat-image-data').value;
                const fileInput = document.getElementById('chat-image-input');
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                if (this.value.trim().length > 0 || imageVal || hasFile) {
                    document.getElementById('chat-form').dispatchEvent(new Event('submit'));
                }
            }
        });

        commentInput.addEventListener('paste', function(e) {
            const items = (e.clipboardData || window.clipboardData || e.originalEvent.clipboardData).items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const base64 = event.target.result;
                        document.getElementById('chat-image-data').value = base64;
                        
                        const preview = document.getElementById('chat-attachment-preview');
                        if (preview) {
                            preview.style.backgroundImage = `url(${base64})`;
                            preview.classList.remove('d-none');
                        }
                    };
                    reader.readAsDataURL(file);
                    e.preventDefault();
                    break;
                }
            }
        });

        const removeBtn = document.getElementById('chat-attachment-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                document.getElementById('chat-image-data').value = '';
                const chatImageInput = document.getElementById('chat-image-input');
                if (chatImageInput) chatImageInput.value = '';
                const preview = document.getElementById('chat-attachment-preview');
                if (preview) {
                    preview.classList.add('d-none');
                    preview.style.backgroundImage = '';
                    preview.innerHTML = `
                        <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                }
            });
        }
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
                if (file.type.startsWith('image/')) {
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
                } else {
                    // Non-image file (e.g. PDF)
                    const preview = document.getElementById('chat-attachment-preview');
                    if (preview) {
                        preview.classList.remove('d-none');
                        preview.style.backgroundImage = 'none';
                        preview.innerHTML = `
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                <span class="text-xs text-truncate w-100 mt-1" style="font-size: 10px; max-width: 70px;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                            </div>
                            <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                                <i class="bi bi-x"></i>
                            </button>
                        `;

                        // Re-bind the remove event because we replaced innerHTML
                        document.getElementById('chat-attachment-remove').addEventListener('click', function() {
                            const chatImageInput = document.getElementById('chat-image-input');
                            if (chatImageInput) chatImageInput.value = '';
                            preview.classList.add('d-none');
                            preview.style.backgroundImage = '';
                            preview.innerHTML = `
                                <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                                    <i class="bi bi-x"></i>
                                </button>
                            `;
                        });
                    }
                }
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

    function openEmployeeTasksModal(userId) {
        const modalEl = document.getElementById('employeeTasksModal');
        const listContainer = document.getElementById('employee-tasks-list');
        const emptyState = document.getElementById('employee-tasks-empty');
        const nameSpan = document.getElementById('employee-tasks-name');
        
        nameSpan.textContent = 'Loading...';
        listContainer.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </td>
            </tr>
        `;
        emptyState.classList.add('d-none');
        
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        fetch(`/chat/employees/${userId}/tasks`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nameSpan.textContent = data.employee_name;
                    listContainer.innerHTML = '';
                    
                    if (data.tasks.length === 0) {
                        emptyState.classList.remove('d-none');
                    } else {
                        data.tasks.forEach(t => {
                            const tr = document.createElement('tr');
                            
                            let priorityClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                            if (t.priority.toLowerCase() === 'critical') priorityClass = 'bg-danger text-white border border-danger';
                            else if (t.priority.toLowerCase() === 'high') priorityClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                            else if (t.priority.toLowerCase() === 'medium') priorityClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                            else if (t.priority.toLowerCase() === 'low') priorityClass = 'bg-success-subtle text-success border border-success-subtle';
                            
                            let statusClass = 'bg-secondary-subtle text-secondary';
                            if (t.status.toLowerCase() === 'in progress') statusClass = 'bg-warning-subtle text-warning';
                            else if (t.status.toLowerCase() === 'review') statusClass = 'bg-info-subtle text-info';
                            else if (t.status.toLowerCase() === 'rework') statusClass = 'bg-danger-subtle text-danger';
                            else if (t.status.toLowerCase() === 'rejected') statusClass = 'bg-danger-subtle text-danger';
                            else if (t.status.toLowerCase() === 'completed') statusClass = 'bg-success-subtle text-success';
                            
                            tr.innerHTML = `
                                <td class="fw-semibold text-dark">${escapeHtml(t.title)}</td>
                                <td class="text-secondary">${escapeHtml(t.project_name)}</td>
                                <td><span class="badge ${statusClass}">${escapeHtml(t.status)}</span></td>
                                <td><span class="badge ${priorityClass}">${escapeHtml(t.priority)}</span></td>
                                <td class="text-muted">${escapeHtml(t.deadline)}</td>
                                <td>
                                    <button class="btn btn-outline-primary btn-sm py-0.5 px-2" style="font-size: 11.5px;" onclick="closeModalAndSelectTask(${t.id})">
                                        Open Chat
                                    </button>
                                </td>
                            `;
                            listContainer.appendChild(tr);
                        });
                    }
                }
            })
            .catch(err => {
                nameSpan.textContent = 'Error';
                listContainer.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Error loading tasks.</td></tr>`;
            });
    }

    function closeModalAndSelectTask(taskId) {
        const modalEl = document.getElementById('employeeTasksModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        
        selectTask(taskId);
    }

    function showCreateProjectForm() {
        hideCreationForms();
        // Hide chat content & placeholder
        document.getElementById('chat-no-task-placeholder').classList.add('d-none');
        document.getElementById('chat-content-container').classList.add('d-none');
        document.getElementById('chat-content-container').classList.remove('d-flex');
        
        // Deselect items in sidebar
        document.querySelectorAll('.chat-user-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.chat-task-item').forEach(item => item.classList.remove('active'));

        // Show project form
        const projectFormContainer = document.getElementById('chat-create-project-container');
        if (projectFormContainer) {
            projectFormContainer.classList.remove('d-none');
            projectFormContainer.classList.add('d-flex');
        }

        // Mobile transition
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.add('chat-show-main');
        }
    }

    function showCreateTaskForm() {
        hideCreationForms();
        // Hide chat content & placeholder
        document.getElementById('chat-no-task-placeholder').classList.add('d-none');
        document.getElementById('chat-content-container').classList.add('d-none');
        document.getElementById('chat-content-container').classList.remove('d-flex');
        
        // Deselect items in sidebar
        document.querySelectorAll('.chat-user-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.chat-task-item').forEach(item => item.classList.remove('active'));

        // Show task form
        const taskFormContainer = document.getElementById('chat-create-task-container');
        if (taskFormContainer) {
            taskFormContainer.classList.remove('d-none');
            taskFormContainer.classList.add('d-flex');
        }

        // Mobile transition
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.add('chat-show-main');
        }
    }

    function showCreateBugForm() {
        hideCreationForms();
        // Hide chat content & placeholder
        document.getElementById('chat-no-task-placeholder').classList.add('d-none');
        document.getElementById('chat-content-container').classList.add('d-none');
        document.getElementById('chat-content-container').classList.remove('d-flex');
        
        // Deselect items in sidebar
        document.querySelectorAll('.chat-user-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.chat-task-item').forEach(item => item.classList.remove('active'));

        // Show bug form
        const bugFormContainer = document.getElementById('chat-create-bug-container');
        if (bugFormContainer) {
            bugFormContainer.classList.remove('d-none');
            bugFormContainer.classList.add('d-flex');
        }

        // Mobile transition
        const layout = document.querySelector('.chat-layout');
        if (layout) {
            layout.classList.add('chat-show-main');
        }
    }

    function cancelCreationForm() {
        hideCreationForms();
        
        if (activeTaskId) {
            // Restore active task chat
            document.getElementById('chat-content-container').classList.remove('d-none');
            document.getElementById('chat-content-container').classList.add('d-flex');
            const activeItem = document.getElementById(`chat-task-item-${activeTaskId}`);
            if (activeItem) activeItem.classList.add('active');
        } else if (activeDirectUserId) {
            // Restore active direct chat
            document.getElementById('chat-content-container').classList.remove('d-none');
            document.getElementById('chat-content-container').classList.add('d-flex');
            const activeItem = document.getElementById(`contact-${activeDirectUserId}`);
            if (activeItem) activeItem.classList.add('active');
        } else {
            // Restore placeholder
            document.getElementById('chat-no-task-placeholder').classList.remove('d-none');
            // On mobile, if no active chat, slide back to sidebar list
            const layout = document.querySelector('.chat-layout');
            if (layout) {
                layout.classList.remove('chat-show-main');
            }
        }
    }

    function hideCreationForms() {
        const projectFormContainer = document.getElementById('chat-create-project-container');
        if (projectFormContainer) {
            projectFormContainer.classList.add('d-none');
            projectFormContainer.classList.remove('d-flex');
        }
        const taskFormContainer = document.getElementById('chat-create-task-container');
        if (taskFormContainer) {
            taskFormContainer.classList.add('d-none');
            taskFormContainer.classList.remove('d-flex');
        }
        const bugFormContainer = document.getElementById('chat-create-bug-container');
        if (bugFormContainer) {
            bugFormContainer.classList.add('d-none');
            bugFormContainer.classList.remove('d-flex');
        }
    }

    function resetValidation(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    function showErrors(form, errors, errorPrefix) {
        for (const field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
            }
            const feedback = form.querySelector(`#${errorPrefix}_${field}_error`);
            if (feedback) {
                feedback.textContent = errors[field][0];
                feedback.style.display = 'block';
            }
        }
    }

    // Monitor selectors in project form
    document.addEventListener('DOMContentLoaded', function() {
        const projectClientSelect = document.getElementById('project_client_select');
        const projectTlSelect = document.getElementById('project_team_leader_select');

        if (projectClientSelect) {
            let lastClientVal = projectClientSelect.value;
            projectClientSelect.addEventListener('change', function() {
                if (this.value === 'new_client') {
                    this.value = lastClientVal; // Reset to previous selection
                    const addClientModal = new bootstrap.Modal(document.getElementById('addClientModal'));
                    addClientModal.show();
                } else {
                    lastClientVal = this.value;
                }
            });
        }

        if (projectTlSelect) {
            let lastTlVal = projectTlSelect.value;
            projectTlSelect.addEventListener('change', function() {
                if (this.value === 'new_team_leader') {
                    this.value = lastTlVal; // Reset to previous selection
                    const addTeamLeaderModal = new bootstrap.Modal(document.getElementById('addTeamLeaderModal'));
                    addTeamLeaderModal.show();
                } else {
                    lastTlVal = this.value;
                }
            });
        }

        const projectTypeSelect = document.getElementById('project_type_select');
        if (projectTypeSelect) {
            let lastTypeVal = projectTypeSelect.value;
            projectTypeSelect.addEventListener('change', function() {
                if (this.value === 'new_type') {
                    this.value = lastTypeVal; // Reset to previous selection
                    const addProjectTypeModal = new bootstrap.Modal(document.getElementById('addProjectTypeModal'));
                    addProjectTypeModal.show();
                } else {
                    lastTypeVal = this.value;
                }
            });
        }

        const quickProjectTypeForm = document.getElementById('quickProjectTypeForm');
        if (quickProjectTypeForm) {
            quickProjectTypeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(quickProjectTypeForm);
                const newVal = document.getElementById('new_project_type').value.trim();
                
                if (newVal.length > 0) {
                    const select = document.getElementById('project_type_select');
                    if (select) {
                        const newOpt = new Option(newVal, newVal, true, true);
                        if (select.options.length > 2) {
                            select.add(newOpt, select.options[2]);
                        } else {
                            select.add(newOpt);
                        }
                        select.value = newVal;
                        select.dispatchEvent(new Event('change'));
                    }
                    
                    const modalEl = document.getElementById('addProjectTypeModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    quickProjectTypeForm.reset();
                }
            });
        }

        // Submit Project Form
        const chatProjectForm = document.getElementById('chat-project-create-form');
        if (chatProjectForm) {
            chatProjectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(chatProjectForm);
                const submitBtn = document.getElementById('chat-project-submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const formData = new FormData(chatProjectForm);

                fetch("{{ route('projects.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Project';

                    if (res.status === 422) {
                        showErrors(chatProjectForm, res.body.errors, 'project');
                    } else if (res.body.success) {
                        localStorage.removeItem('active_chat_task_id');
                        localStorage.removeItem('active_chat_direct_user_id');
                        window.location.reload();
                    } else {
                        alert('Error saving project.');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Project';
                    alert('Something went wrong. Please try again.');
                    console.error(err);
                });
            });
        }

        // Submit Task Form
        const chatTaskForm = document.getElementById('chat-task-create-form');
        if (chatTaskForm) {
            chatTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(chatTaskForm);
                const submitBtn = document.getElementById('chat-task-submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const formData = new FormData(chatTaskForm);

                fetch("{{ route('tasks.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Task';

                    if (res.status === 422) {
                        showErrors(chatTaskForm, res.body.errors, 'task');
                    } else if (res.body.success) {
                        localStorage.removeItem('active_chat_task_id');
                        localStorage.removeItem('active_chat_direct_user_id');
                        window.location.reload();
                    } else {
                        alert('Error saving task.');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Task';
                    alert('Something went wrong. Please try again.');
                    console.error(err);
                });
            });
        }

        // Submit Quick Client Form
        const quickClientForm = document.getElementById('quickClientForm');
        if (quickClientForm) {
            quickClientForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(quickClientForm);
                const saveBtn = document.getElementById('saveClientBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const formData = new FormData(quickClientForm);

                fetch("{{ route('clients.quick-store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Client';

                    if (res.status === 422) {
                        showErrors(quickClientForm, res.body.errors, 'client');
                    } else if (res.body.success) {
                        const client = res.body.client;
                        const newOpt = new Option(client.company_name, client.id, true, true);
                        
                        const clientSelect = document.getElementById('project_client_select');
                        const newClientOpt = clientSelect.querySelector('option[value="new_client"]');
                        clientSelect.insertBefore(newOpt, newClientOpt);

                        clientSelect.value = client.id;

                        const modalEl = document.getElementById('addClientModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        quickClientForm.reset();
                    }
                })
                .catch(err => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Client';
                    alert('Something went wrong. Please try again.');
                    console.error(err);
                });
            });
        }

        // Submit Quick Team Leader Form
        const quickTeamLeaderForm = document.getElementById('quickTeamLeaderForm');
        if (quickTeamLeaderForm) {
            quickTeamLeaderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(quickTeamLeaderForm);
                const saveBtn = document.getElementById('saveTLBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const formData = new FormData(quickTeamLeaderForm);

                fetch("{{ route('users.quick-store-team-leader') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Team Leader';

                    if (res.status === 422) {
                        showErrors(quickTeamLeaderForm, res.body.errors, 'tl');
                    } else if (res.body.success) {
                        const tl = res.body.team_leader;
                        const newOpt = new Option(tl.name, tl.id, true, true);
                        
                        const tlSelect = document.getElementById('project_team_leader_select');
                        const newTlOpt = tlSelect.querySelector('option[value="new_team_leader"]');
                        tlSelect.insertBefore(newOpt, newTlOpt);

                        tlSelect.value = tl.id;

                        const modalEl = document.getElementById('addTeamLeaderModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        quickTeamLeaderForm.reset();
                    }
                })
                .catch(err => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Team Leader';
                    alert('Something went wrong. Please try again.');
                    console.error(err);
                });
            });
        }

        // Auto-detect environment info for Bug Form
        const ua = navigator.userAgent;
        let os = "Unknown OS";
        let browser = "Unknown Browser";

        if (ua.indexOf("Win") != -1) os = "Windows";
        else if (ua.indexOf("Mac") != -1) os = "MacOS";
        else if (ua.indexOf("X11") != -1) os = "UNIX";
        else if (ua.indexOf("Linux") != -1) os = "Linux";

        if (ua.indexOf("Chrome") != -1) browser = "Chrome";
        else if (ua.indexOf("Safari") != -1) browser = "Safari";
        else if (ua.indexOf("Firefox") != -1) browser = "Firefox";
        else if (ua.indexOf("MSIE") != -1 || !!document.documentMode == true) browser = "IE";

        const bugBrowserInput = document.getElementById('bug_chat_browser_info');
        const bugOsInput = document.getElementById('bug_chat_os_info');
        if (bugBrowserInput) bugBrowserInput.value = browser;
        if (bugOsInput) bugOsInput.value = os + " (Agent: " + navigator.platform + ")";

        // Image preview logic for Bug Form
        document.querySelectorAll('.bug-chat-file-input').forEach(input => {
            input.addEventListener('change', function() {
                const previewId = this.dataset.preview;
                const index = previewId.split('-')[3];
                const container = document.getElementById('bug-chat-preview-container-' + index);
                const previewImg = document.getElementById(previewId);

                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        container.classList.remove('d-none');
                    }
                    reader.readAsDataURL(this.files[0]);
                } else {
                    previewImg.src = '';
                    container.classList.add('d-none');
                }
            });
        });

        // Submit Bug Form
        const chatBugForm = document.getElementById('chat-bug-create-form');
        if (chatBugForm) {
            chatBugForm.addEventListener('submit', function(e) {
                e.preventDefault();
                resetValidation(chatBugForm);
                const submitBtn = document.getElementById('chat-bug-submit-btn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Filing...';

                const formData = new FormData(chatBugForm);

                fetch("{{ route('bugs.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'File Bug';

                    if (res.status === 422) {
                        showErrors(chatBugForm, res.body.errors, 'bug');
                    } else if (res.body.success) {
                        localStorage.removeItem('active_chat_task_id');
                        localStorage.removeItem('active_chat_direct_user_id');
                        window.location.reload();
                    } else {
                        alert('Error saving bug.');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'File Bug';
                    alert('Something went wrong. Please try again.');
                    console.error(err);
                });
            });
        }
    });
</script>
@endpush
