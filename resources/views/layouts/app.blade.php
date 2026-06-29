<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="apple-touch-icon" href="/pwa-icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>

    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Employee Work Monitoring & Project Management System')">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #6366f1;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #f1f5f9;
            --topnav-height: 64px;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            color: var(--text-primary);
            margin: 0;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: width 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        #sidebar.collapsed { width: var(--sidebar-collapsed-width); }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            min-height: var(--topnav-height);
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white; flex-shrink: 0;
        }
        .sidebar-brand .brand-text { font-size: 16px; font-weight: 700; color: #f1f5f9; white-space: nowrap; }
        .sidebar-brand .brand-text span { color: #818cf8; }
        #sidebar.collapsed .brand-text { display: none; }

        .sidebar-nav {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 8px 0;
            scrollbar-width: thin; scrollbar-color: #334155 transparent;
        }
        .sidebar-section-label {
            font-size: 10px; font-weight: 600; letter-spacing: 0.1em;
            text-transform: uppercase; color: #475569;
            padding: 16px 20px 6px; white-space: nowrap;
        }
        #sidebar.collapsed .sidebar-section-label { display: none; }

        .sidebar-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 18px; color: var(--sidebar-text);
            text-decoration: none; font-size: 14px; font-weight: 500;
            border-radius: 8px; margin: 1px 8px;
            transition: all 0.2s ease; white-space: nowrap; position: relative;
        }
        .sidebar-item:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .sidebar-item.active { background: var(--sidebar-active); color: white; }
        .sidebar-item .nav-icon { font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; }
        .sidebar-item .badge-count {
            margin-left: auto; background: #ef4444; color: white;
            font-size: 10px; padding: 2px 6px; border-radius: 10px; font-weight: 600;
        }
        #sidebar.collapsed .nav-text, #sidebar.collapsed .badge-count { display: none; }
        #sidebar.collapsed .sidebar-item { justify-content: center; }
        #sidebar.collapsed .sidebar-item::after {
            content: attr(data-title); position: absolute;
            left: calc(var(--sidebar-collapsed-width) - 4px);
            background: #1e293b; color: #f1f5f9;
            padding: 6px 12px; border-radius: 8px; font-size: 12px;
            white-space: nowrap; display: none; z-index: 9999; border: 1px solid #334155;
        }
        #sidebar.collapsed .sidebar-item:hover::after { display: block; }

        .sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,0.06); }

        /* ===== TOP NAV ===== */
        #topnav {
            position: fixed; top: 0;
            left: var(--sidebar-width); right: 0;
            height: var(--topnav-height);
            background: var(--card-bg); border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; z-index: 999;
            transition: left 0.3s ease; gap: 12px;
        }
        #topnav.sidebar-collapsed { left: var(--sidebar-collapsed-width); }
        #topnav.no-sidebar { left: 0 !important; }
        .topnav-status-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            flex-shrink: 1;
            min-width: 0;
            margin: 0 15px;
            overflow: hidden;
        }
        .topnav-status-track {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--body-bg);
            border: 1px solid var(--border-color);
            padding: 4px;
            border-radius: 30px;
            overflow-x: auto;
            white-space: nowrap;
            max-width: 100%;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .topnav-status-track::-webkit-scrollbar {
            display: none;
        }
        .topnav-status-pill {
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            background: transparent;
        }
        .topnav-status-pill:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.04);
        }
        .topnav-status-pill.active {
            background: #4f46e5;
            color: white;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }
        .topnav-status-pill .count {
            font-size: 11px;
            font-weight: 700;
            background: var(--border-color);
            color: var(--text-secondary);
            padding: 1px 6px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .topnav-status-pill:hover .count {
            background: #b8c1ec;
            color: #1e293b;
        }
        .topnav-status-pill.active .count {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }
        @media (max-width: 767.98px) {
            .topnav-status-container { display: none !important; }
        }
        .topnav-left { display: flex; align-items: center; gap: 12px; white-space: nowrap; flex-shrink: 0; }
        .btn-sidebar-toggle {
            background: none; border: none; cursor: pointer;
            color: var(--text-secondary); font-size: 20px;
            padding: 6px 8px; border-radius: 8px; transition: all 0.2s;
        }
        .btn-sidebar-toggle:hover { background: var(--body-bg); color: var(--text-primary); }
        .breadcrumb { margin: 0; font-size: 14px; }
        .breadcrumb-item a { color: var(--text-secondary); text-decoration: none; }
        .breadcrumb-item.active { color: var(--text-primary); font-weight: 500; }
        .topnav-right { display: flex; align-items: center; gap: 8px; white-space: nowrap; flex-shrink: 0; }

        .work-timer-badge {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 20px;
            font-size: 13px; font-weight: 600;
        }
        .work-timer-badge.working { background: #d1fae5; color: #065f46; }
        .work-timer-badge.not-started { background: #fef3c7; color: #92400e; }

        .topnav-icon-btn {
            position: relative; width: 40px; height: 40px;
            border-radius: 10px; border: 1px solid var(--border-color);
            background: var(--card-bg); display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); cursor: pointer; font-size: 18px; transition: all 0.2s;
        }
        .topnav-icon-btn:hover { background: var(--body-bg); color: var(--text-primary); }
        .notification-badge {
            position: absolute; top: -4px; right: -4px;
            background: #ef4444; color: white; font-size: 9px;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; border: 2px solid white;
        }

        .user-avatar-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 12px 4px 4px; border-radius: 40px;
            border: 1px solid var(--border-color); background: var(--card-bg);
            cursor: pointer; transition: all 0.2s;
        }
        .user-avatar-btn:hover { background: var(--body-bg); }
        .avatar-circle { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .user-info .user-name { font-size: 13px; font-weight: 600; line-height: 1.2; }
        .user-info .user-role { font-size: 11px; color: var(--text-secondary); }

        /* ===== MAIN ===== */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topnav-height);
            min-height: calc(100vh - var(--topnav-height));
            padding: 24px;
            transition: margin-left 0.3s ease;
        }
        #main-content.sidebar-collapsed { margin-left: var(--sidebar-collapsed-width); }
        #main-content.no-sidebar { margin-left: 0 !important; }
        #main-content.no-header { margin-top: 0 !important; min-height: 100vh !important; }

        /* ===== CARDS ===== */
        .stat-card {
            background: var(--card-bg); border-radius: 16px; padding: 24px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .card { border: 1px solid var(--border-color); border-radius: 16px; box-shadow: none; }
        .card-header {
            background: var(--card-bg); border-bottom: 1px solid var(--border-color);
            padding: 18px 24px; font-weight: 600;
            border-radius: 16px 16px 0 0 !important;
        }

        /* ===== TABLES ===== */
        .table th {
            font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color); padding: 12px 16px;
        }
        .table td { padding: 14px 16px; vertical-align: middle; font-size: 14px; }
        .table tbody tr:hover { background: rgba(255, 255, 255, 0.02); }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            border: 1px solid var(--border-color); border-radius: 8px;
            padding: 10px 14px; font-size: 14px; transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        /* ===== BUTTONS ===== */
        .btn { border-radius: 8px; font-weight: 500; font-size: 14px; padding: 8px 16px; transition: all 0.2s; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }

        /* ===== PAGE HEADER ===== */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .page-title { font-size: 22px; font-weight: 700; margin: 0; }
        .page-subtitle { font-size: 14px; color: var(--text-secondary); margin: 2px 0 0; }

        /* ===== NOTIFICATIONS ===== */
        .notification-dropdown { width: 360px; max-height: 480px; overflow-y: auto; }
        .notification-item {
            padding: 12px 16px; border-bottom: 1px solid var(--border-color);
            display: flex; gap: 12px; transition: background 0.2s;
        }
        .notification-item:hover { background: var(--body-bg); }
        .notification-item.unread { background: #f0f4ff; }

        /* ===== STATUS ===== */
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .status-dot.working { background: #10b981; animation: pulse 2s infinite; }
        .status-dot.break { background: #f59e0b; }
        .status-dot.not-started { background: #94a3b8; }
        .status-dot.idle { background: #ef4444; }

        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .alert { border-radius: 10px; border: none; }

        /* ===== MOBILE ===== */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            #sidebar.mobile-open { transform: translateX(0); }
            #topnav { left: 0 !important; }
            #main-content { margin-left: 0 !important; padding: 16px; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; }
            .sidebar-overlay.active { display: block; }
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .global-color-btn.active {
            outline: 2px solid #000000;
            outline-offset: 2px;
        }

        /* ===== DARK THEME OVERRIDES ===== */
        [data-bs-theme="dark"] {
            --body-bg: #0b0f17;
            --card-bg: #111c2a;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --border-color: #1e293b;
        }

        /* Global cards background overrides for inline white styles */
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .card.border-0,
        [data-bs-theme="dark"] div.card[style*="background: #ffffff"],
        [data-bs-theme="dark"] div.card[style*="background: rgb(255, 255, 255)"],
        [data-bs-theme="dark"] div.card[style*="background-color: #ffffff"],
        [data-bs-theme="dark"] div.card[style*="background-color: rgb(255, 255, 255)"],
        [data-bs-theme="dark"] div.card[style*="background:white"],
        [data-bs-theme="dark"] div.card[style*="background-color:white"] {
            background: var(--card-bg) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2) !important;
        }

        /* Bootstrap inputs overrides in dark theme */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .input-group-text {
            background-color: #162235;
            border-color: #1e293b;
            color: #f8fafc;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #162235;
            color: #f8fafc;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Dropdowns overrides in dark theme */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #111c2a;
            border-color: #1e293b;
            color: #f8fafc;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }
        [data-bs-theme="dark"] .dropdown-item {
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: rgba(99, 102, 241, 0.1);
            color: #ffffff;
        }
        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #1e293b;
        }

        /* Modal overrides in dark theme */
        [data-bs-theme="dark"] .modal-content {
            background-color: #111c2a;
            border-color: #1e293b;
            color: #f8fafc;
        }
        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: #1e293b;
        }
        [data-bs-theme="dark"] .btn-close {
            filter: invert(1) grayscale(1) brightness(2);
        }

        /* Tables overrides in dark theme */
        [data-bs-theme="dark"] .table {
            color: #f8fafc;
            border-color: #1e293b;
        }
        [data-bs-theme="dark"] .table th {
            background-color: #162235 !important;
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .table > :not(caption) > * > * {
            background-color: transparent;
            color: inherit;
            border-bottom-color: #1e293b;
        }
        [data-bs-theme="dark"] .table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.04) !important;
        }

        /* Scrollbar in dark theme */
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* Alerts in dark theme */
        [data-bs-theme="dark"] .alert-success {
            background-color: #064e3b;
            color: #34d399;
        }
        [data-bs-theme="dark"] .alert-danger {
            background-color: #7f1d1d;
            color: #f87171;
        }

        /* Badge or user details elements and list items style */
        [data-bs-theme="dark"] .list-group-item {
            background-color: #111c2a;
            border-color: #1e293b;
            color: #f8fafc;
        }
        [data-bs-theme="dark"] .list-group-item:hover {
            background-color: #162235;
        }

        /* ===== CHAT WORKSPACE & TASK CHAT DARK THEME OVERRIDES ===== */
        [data-bs-theme="dark"] .chat-layout {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-sidebar {
            background: var(--card-bg);
            border-right-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-sidebar-search {
            background: var(--card-bg);
            border-bottom-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-sidebar-search .input-group {
            background-color: var(--body-bg);
            border-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-sidebar-search .form-control {
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .chat-sidebar-search .input-group-text {
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .chat-task-item {
            border-bottom-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-task-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        [data-bs-theme="dark"] .chat-task-item.active {
            background: rgba(99, 102, 241, 0.15);
        }
        [data-bs-theme="dark"] .chat-task-item .task-title {
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .chat-task-item .task-project {
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .chat-main {
            background: #0e1622;
        }
        [data-bs-theme="dark"] .chat-main-placeholder {
            background: var(--body-bg);
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .chat-header {
            background: var(--card-bg);
            border-bottom-color: var(--border-color);
        }
        [data-bs-theme="dark"] .chat-header-title {
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .chat-header-subtitle {
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .chat-body.chat-container {
            background-color: #0e1622;
            background-image: radial-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        }
        [data-bs-theme="dark"] .chat-row.sent .chat-bubble {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #ffffff;
            border-color: #4f46e5;
        }
        [data-bs-theme="dark"] .chat-row.received .chat-bubble {
            background-color: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .chat-row.sent .chat-sender {
            color: #a5b4fc;
        }
        [data-bs-theme="dark"] .chat-row.received .chat-sender {
            color: #38bdf8;
        }
        [data-bs-theme="dark"] .chat-meta {
            color: #94a3b8;
        }
        [data-bs-theme="dark"] .chat-row.sent .time-log-box {
            background-color: rgba(0, 0, 0, 0.25);
            border-left-color: #818cf8;
        }
        [data-bs-theme="dark"] .chat-row.received .time-log-box {
            background-color: rgba(0, 0, 0, 0.25);
            border-left-color: #6366f1;
        }
        [data-bs-theme="dark"] .time-log-value {
            color: #f1f5f9;
        }
        [data-bs-theme="dark"] .time-log-note-content {
            color: #cbd5e1;
        }
        [data-bs-theme="dark"] .whatsapp-input-bar {
            background-color: #111c2a;
            border-top-color: var(--border-color);
        }
        [data-bs-theme="dark"] .whatsapp-input-container {
            background-color: #1e293b;
        }
        [data-bs-theme="dark"] .whatsapp-input {
            color: #f1f5f9;
        }
        [data-bs-theme="dark"] .whatsapp-input::placeholder {
            color: #94a3b8;
        }
        [data-bs-theme="dark"] .mention-dropdown {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }
        [data-bs-theme="dark"] .mention-item {
            border-bottom-color: var(--border-color);
        }
        [data-bs-theme="dark"] .mention-item:hover, [data-bs-theme="dark"] .mention-item.active {
            background-color: var(--body-bg);
        }
        [data-bs-theme="dark"] .mention-name {
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .mention-email {
            color: var(--text-secondary);
        }

        /* Ensure font colors in dark mode are not black or dark gray */
        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] .chat-text,
        [data-bs-theme="dark"] .chat-bubble {
            color: var(--text-primary) !important;
        }
        [data-bs-theme="dark"] .time-log-label,
        [data-bs-theme="dark"] .time-log-note-title {
            color: var(--text-secondary) !important;
        }

        /* ===== GLOBAL BG-LIGHT & HARDCODED COLOR OVERRIDES IN DARK MODE ===== */
        [data-bs-theme="dark"] .bg-light {
            background-color: #162235 !important;
            color: var(--text-primary) !important;
        }
        [data-bs-theme="dark"] .card.bg-light {
            background-color: #162235 !important;
            border-color: var(--border-color) !important;
        }
        [data-bs-theme="dark"] .card-body.bg-light,
        [data-bs-theme="dark"] .card-body.bg-light.border-bottom {
            background-color: #162235 !important;
        }
        [data-bs-theme="dark"] .badge.bg-light {
            background-color: #1e293b !important;
            color: var(--text-primary) !important;
            border-color: #334155 !important;
        }
        /* Fix hardcoded text-dark and inline dark color attributes */
        [data-bs-theme="dark"] [style*="color:#0f172a"],
        [data-bs-theme="dark"] [style*="color: #0f172a"],
        [data-bs-theme="dark"] [style*="color:#1e293b"],
        [data-bs-theme="dark"] [style*="color: #1e293b"] {
            color: var(--text-primary) !important;
        }
        /* Fix hardcoded muted colors - keep readable */
        [data-bs-theme="dark"] [style*="color:#64748b"],
        [data-bs-theme="dark"] [style*="color: #64748b"],
        [data-bs-theme="dark"] [style*="color:#94a3b8"],
        [data-bs-theme="dark"] [style*="color: #94a3b8"] {
            color: #94a3b8 !important;
        }
        /* Live status board & employee card specific dark overrides */
        [data-bs-theme="dark"] .live-status-task-row {
            background-color: #1e293b !important;
            border-left-color: #22c55e !important;
        }
        [data-bs-theme="dark"] .live-status-task-row .text-dark {
            color: var(--text-primary) !important;
        }
        [data-bs-theme="dark"] .p-3.bg-success-subtle {
            background-color: rgba(16, 185, 129, 0.12) !important;
        }
        [data-bs-theme="dark"] .p-3.bg-warning-subtle {
            background-color: rgba(245, 158, 11, 0.12) !important;
        }
        [data-bs-theme="dark"] .p-3.bg-primary-subtle {
            background-color: rgba(99, 102, 241, 0.12) !important;
        }
        [data-bs-theme="dark"] .p-3.bg-light {
            background-color: #1e293b !important;
        }
        [data-bs-theme="dark"] .border-light-subtle {
            border-color: var(--border-color) !important;
        }
        [data-bs-theme="dark"] .shadow-xs {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }
        /* stat-card icon backgrounds */
        [data-bs-theme="dark"] [style*="background:#ede9fe"],
        [data-bs-theme="dark"] [style*="background: #ede9fe"] {
            background: rgba(99, 102, 241, 0.18) !important;
        }
        [data-bs-theme="dark"] [style*="background:#dbeafe"],
        [data-bs-theme="dark"] [style*="background: #dbeafe"] {
            background: rgba(37, 99, 235, 0.18) !important;
        }
        [data-bs-theme="dark"] [style*="background:#dcfce7"],
        [data-bs-theme="dark"] [style*="background: #dcfce7"] {
            background: rgba(22, 163, 74, 0.18) !important;
        }
        [data-bs-theme="dark"] [style*="background:#fef9c3"],
        [data-bs-theme="dark"] [style*="background: #fef9c3"] {
            background: rgba(202, 138, 4, 0.18) !important;
        }
        [data-bs-theme="dark"] [style*="background:#fee2e2"],
        [data-bs-theme="dark"] [style*="background: #fee2e2"] {
            background: rgba(239, 68, 68, 0.18) !important;
        }
        [data-bs-theme="dark"] [style*="background:#fef3c7"],
        [data-bs-theme="dark"] [style*="background: #fef3c7"] {
            background: rgba(245, 158, 11, 0.18) !important;
        }
        /* Nav tabs dark overrides */
        [data-bs-theme="dark"] .nav-tabs {
            border-bottom-color: var(--border-color);
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            color: var(--text-secondary);
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            background-color: var(--card-bg);
            border-color: var(--border-color) var(--border-color) var(--card-bg);
            color: var(--text-primary);
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link:hover {
            color: var(--text-primary);
            border-color: transparent;
        }

        /* WhatsApp-like Date Grouping Headers */
        .chat-date-group-header .badge {
            background-color: #e7fedc !important;
            color: #54656f !important;
            font-size: 11px !important;
            padding: 6px 12px !important;
            border-radius: 7px !important;
            box-shadow: 0 1px 1px rgba(11,20,26,.08) !important;
            border: none !important;
        }
        [data-bs-theme="dark"] .chat-date-group-header .badge {
            background-color: #182229 !important;
            color: #8696a0 !important;
            box-shadow: 0 1px 1px rgba(0,0,0,.15) !important;
        }

        /* Hover Actions for Chat Bubbles */
        .chat-row {
            position: relative;
        }
        .chat-bubble-actions {
            display: none;
            align-items: center;
            gap: 4px;
            margin: 0 8px;
            align-self: center;
        }
        .chat-row:hover .chat-bubble-actions,
        .chat-bubble-actions:has(.show),
        .chat-bubble-actions:focus-within {
            display: flex !important;
        }
        .chat-bubble-action-btn {
            background: transparent;
            border: none;
            color: #667781;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }
        .chat-bubble-action-btn:hover {
            background-color: rgba(0,0,0,0.06);
            color: #111b21;
        }
        [data-bs-theme="dark"] .chat-bubble-action-btn:hover {
            background-color: rgba(255,255,255,0.08);
            color: #f8fafc;
        }
    </style>

    @stack('styles')
    @if(request()->routeIs('chat.index') || request()->is('chat*'))
        <style>
            #floating-chat-container {
                display: none !important;
            }
        </style>
    @endif
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

@if(!isset($noSidebar) || !$noSidebar)
<!-- SIDEBAR -->
<aside id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        @php
            $companyLogo = \App\Models\Setting::get('company_logo');
            $companyName = \App\Models\Setting::get('company_name', 'WorkeX');
        @endphp
        @if($companyLogo)
            <div class="brand-logo-container d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; overflow: hidden; border-radius: 10px; flex-shrink: 0; background: white;">
                <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 2px;">
            </div>
        @else
            <div class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></div>
        @endif
        <div class="brand-text d-flex flex-column" style="line-height: 1.1;">
            <div>{!! str_replace('eX', '<span>eX</span>', e($companyName)) !!}</div>
            <span style="font-size: 9px; font-weight: 500; color: #94a3b8; letter-spacing: 0.05em; margin-top: 2px;">By Techsoul</span>
        </div>
    </a>

    <nav class="sidebar-nav">
        @if(auth()->user()->isReseller())
            <div class="sidebar-section-label">Reseller Portal</div>
            <a href="{{ route('reseller.dashboard') }}" class="sidebar-item {{ request()->routeIs('reseller.dashboard*') ? 'active' : '' }}" data-title="Companies">
                <i class="bi bi-building nav-icon"></i><span class="nav-text">Companies Directory</span>
            </a>
            <a href="{{ route('reseller.companies.create') }}" class="sidebar-item {{ request()->routeIs('reseller.companies.create*') ? 'active' : '' }}" data-title="New Company">
                <i class="bi bi-plus-circle nav-icon"></i><span class="nav-text">Create Company</span>
            </a>
        @else
            <!-- Workspace / Main Details -->
            <div class="sidebar-section-label">Workspace</div>
            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-title="Dashboard">
                <i class="bi bi-grid-1x2-fill nav-icon"></i><span class="nav-text">Dashboard</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('live-status') }}" class="sidebar-item {{ request()->routeIs('live-status') ? 'active' : '' }}" data-title="Live Status">
                <i class="bi bi-broadcast nav-icon"></i><span class="nav-text">Live Status Board</span>
            </a>
            @endif
            <a href="{{ route('chat.index') }}" class="sidebar-item {{ (request()->routeIs('chat*') || request()->routeIs('direct-chat*')) ? 'active' : '' }}" data-title="Chat">
                <i class="bi bi-chat-fill nav-icon"></i><span class="nav-text">Chat Workspace</span>
                @php
                    $unreadDirectMessagesCount = \App\Models\DirectMessage::where('receiver_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                    $unreadTaskCommentsCount = \App\Models\TaskComment::whereHas('task', function($q) {
                            $q->where('status', '!=', 'completed')
                              ->when(!auth()->user()->isLeaderOrAbove(), fn($sq) => $sq->where('assigned_to', auth()->id()))
                              ->when(auth()->user()->isTeamLeader(), function($sq) {
                                  $sq->where(function($ssq) {
                                      $ssq->whereDoesntHave('assignee')
                                         ->orWhereHas('assignee.role', function($r) {
                                             $r->where('slug', '!=', 'telecaller');
                                         });
                                  });
                              });
                        })
                        ->where('user_id', '!=', auth()->id())
                        ->whereDoesntHave('views', function($q) {
                            $q->where('user_id', auth()->id());
                        })
                        ->count();
                    $totalUnreadChatCount = $unreadDirectMessagesCount + $unreadTaskCommentsCount;
                @endphp
                <span class="badge-count {{ $totalUnreadChatCount > 0 ? '' : 'd-none' }}" id="sidebar-chat-badge">
                    {{ $totalUnreadChatCount }}
                </span>
            </a>
            <a href="{{ route('mailbox.index') }}" class="sidebar-item {{ request()->routeIs('mailbox*') ? 'active' : '' }}" data-title="Mailbox">
                <i class="bi bi-envelope-fill nav-icon"></i>
                <span class="nav-text">Mailbox</span>
                @php
                    $sidebarMailsCount = \App\Models\MailboxMessage::where('receiver_id', auth()->id())
                        ->where('is_read', false)
                        ->whereNull('receiver_deleted_at')
                        ->count();
                @endphp
                <span class="badge-count {{ $sidebarMailsCount > 0 ? '' : 'd-none' }}" id="sidebar-mailbox-badge">
                    {{ $sidebarMailsCount }}
                </span>
            </a>
            @if(!auth()->user()->isTelecaller())
                @if(auth()->user()->isEmployee() || auth()->user()->isTeamLeader())
                <a href="{{ route('work-timer.index') }}" class="sidebar-item {{ request()->routeIs('work-timer*') ? 'active' : '' }}" data-title="Work Timer">
                    <i class="bi bi-stopwatch-fill nav-icon"></i><span class="nav-text">Work Timer</span>
                </a>
                @endif
                <a href="{{ route('tasks.approved') }}" class="sidebar-item {{ request()->routeIs('tasks.approved*') ? 'active' : '' }}" data-title="Approved Tasks">
                    <i class="bi bi-check-circle nav-icon"></i><span class="nav-text">Approved Tasks</span>
                </a>
                @if(!auth()->user()->isEmployee())
                <a href="{{ route('daily-reports.index') }}" class="sidebar-item {{ request()->routeIs('daily-reports*') ? 'active' : '' }}" data-title="Daily Reports">
                    <i class="bi bi-journal-text nav-icon"></i><span class="nav-text">Daily Reports</span>
                    @php
                        $pendingReportsCount = auth()->user()->isAdminOrAbove() ? \App\Models\DailyReport::where('status', 'pending')->count() : 0;
                    @endphp
                    <span class="badge-count {{ $pendingReportsCount > 0 ? '' : 'd-none' }}">
                        {{ $pendingReportsCount }}
                    </span>
                </a>
                <a href="{{ route('bugs.index') }}" class="sidebar-item {{ request()->routeIs('bugs*') ? 'active' : '' }}" data-title="Bug Tracker">
                    <i class="bi bi-bug-fill nav-icon"></i><span class="nav-text">Bug Tracker</span>
                </a>
                <a href="{{ route('meetings.index') }}" class="sidebar-item {{ request()->routeIs('meetings*') ? 'active' : '' }}" data-title="Meetings">
                    <i class="bi bi-chat-left-quote nav-icon"></i><span class="nav-text">Meetings & Discussions</span>
                </a>
                @endif
            @endif

            <!-- Calling & Leads -->
            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTelecaller() || auth()->user()->isHR() || auth()->user()->isTeamLeader())
            <div class="sidebar-section-label">Calling & Leads</div>
            @if(auth()->user()->isTelecaller())
            <a href="{{ route('leads.start-work.index') }}" class="sidebar-item {{ request()->routeIs('leads.start-work*') ? 'active' : '' }}" data-title="Start Today Work">
                <i class="bi bi-play-circle-fill nav-icon"></i><span class="nav-text">Start Today Work</span>
            </a>
            @endif
            @if(auth()->user()->isAdminOrAbove())
            <a href="{{ route('leads.index') }}" class="sidebar-item {{ request()->routeIs('leads*') && !request()->routeIs('leads.start-work*') ? 'active' : '' }}" data-title="Leads">
                <i class="bi bi-funnel-fill nav-icon"></i><span class="nav-text">Leads & Enquiries</span>
            </a>
            @endif
            @if(!auth()->user()->isTelecaller() && auth()->user()->isAdminOrAbove())
            <a href="{{ route('quotations.index') }}" class="sidebar-item {{ request()->routeIs('quotations*') ? 'active' : '' }}" data-title="Quotations">
                <i class="bi bi-file-earmark-text-fill nav-icon"></i><span class="nav-text">Quotations</span>
            </a>
            @endif
            <a href="{{ route('reports.telecaller-performance') }}" class="sidebar-item {{ request()->routeIs('reports.telecaller-performance*') ? 'active' : '' }}" data-title="Performance">
                <i class="bi bi-graph-up-arrow nav-icon"></i><span class="nav-text">Telecaller Performance</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.telecaller-sessions.index') }}" class="sidebar-item {{ request()->routeIs('admin.telecaller-sessions*') ? 'active' : '' }}" data-title="Room Approvals">
                <i class="bi bi-clipboard-check nav-icon"></i><span class="nav-text">Room Work Approvals</span>
                @php
                    $pendingRoomApprovalsCount = \App\Models\LeadRoomWorkSession::where('status', 'pending')->count();
                @endphp
                <span class="badge-count {{ $pendingRoomApprovalsCount > 0 ? '' : 'd-none' }}">
                    {{ $pendingRoomApprovalsCount }}
                </span>
            </a>
            @endif
            @endif

            <!-- Customer Details -->
            @if(!auth()->user()->isTelecaller())
            <div class="sidebar-section-label">Customer Details</div>
            @if(!auth()->user()->isEmployee())
            <a href="{{ route('projects.index') }}" class="sidebar-item {{ request()->routeIs('projects*') ? 'active' : '' }}" data-title="Projects">
                <i class="bi bi-kanban-fill nav-icon"></i><span class="nav-text">Projects</span>
            </a>
            @endif
            @if(auth()->user()->isAdminOrAbove())
            <a href="{{ route('clients.index') }}" class="sidebar-item {{ request()->routeIs('clients*') ? 'active' : '' }}" data-title="Clients">
                <i class="bi bi-building nav-icon"></i><span class="nav-text">Clients</span>
            </a>
            @endif
            @endif

            <!-- ERP Details -->
            @if(!auth()->user()->isClient())
            <div class="sidebar-section-label">ERP Details</div>
            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees*') ? 'active' : '' }}" data-title="Employees">
                <i class="bi bi-people-fill nav-icon"></i><span class="nav-text">Employees</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('attendance.view-own') || auth()->user()->hasPermission('attendance.view-all'))
            <a href="{{ route('attendance.index') }}" class="sidebar-item {{ request()->routeIs('attendance*') ? 'active' : '' }}" data-title="Attendance">
                <i class="bi bi-calendar2-check-fill nav-icon"></i><span class="nav-text">Attendance</span>
            </a>
            @endif
            <a href="{{ route('leaves.index') }}" class="sidebar-item {{ request()->routeIs('leaves*') ? 'active' : '' }}" data-title="Leaves">
                <i class="bi bi-calendar-x-fill nav-icon"></i><span class="nav-text">Leave Management</span>
                @php
                    $pendingLeavesCount = 0;
                    if (auth()->user()->isLeaderOrAbove() || auth()->user()->isHR()) {
                        $pendingLeavesCount = \App\Models\Leave::where(function($q) {
                            $user = auth()->user();
                            if ($user->isHR() || $user->isAdminOrAbove()) {
                                $q->whereIn('status', ['pending', 'team_leader_approved']);
                            } else if ($user->isTeamLeader()) {
                                $q->where('status', 'pending');
                            }
                        })->count();
                    }
                @endphp
                <span class="badge-count {{ $pendingLeavesCount > 0 ? '' : 'd-none' }}">
                    {{ $pendingLeavesCount }}
                </span>
            </a>
            @endif

            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts() || (auth()->user()->isHR() && !auth()->user()->isTelecaller()))
            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
            <a href="{{ route('invoices.index') }}" class="sidebar-item {{ request()->routeIs('invoices*') ? 'active' : '' }}" data-title="Invoices">
                <i class="bi bi-receipt nav-icon"></i><span class="nav-text">Invoices</span>
            </a>
            <a href="{{ route('payments.index') }}" class="sidebar-item {{ request()->routeIs('payments*') ? 'active' : '' }}" data-title="Payments">
                <i class="bi bi-credit-card-fill nav-icon"></i><span class="nav-text">Payments</span>
            </a>
            <a href="{{ route('expenses.index') }}" class="sidebar-item {{ request()->routeIs('expenses*') ? 'active' : '' }}" data-title="Expenses">
                <i class="bi bi-cash-stack nav-icon"></i><span class="nav-text">Expenses</span>
            </a>
            @if(auth()->user()->isAdminOrAbove())
            <a href="{{ route('admin.payroll.index') }}" class="sidebar-item {{ request()->routeIs('admin.payroll*') ? 'active' : '' }}" data-title="Payroll">
                <i class="bi bi-wallet2 nav-icon"></i><span class="nav-text">Salary Disbursal</span>
            </a>
            @endif
            @endif
            @if(!auth()->user()->isTelecaller())
            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR() || auth()->user()->isAccounts())
            <a href="{{ route('reports.index') }}" class="sidebar-item {{ request()->routeIs('reports*') && !request()->routeIs('reports.telecaller-performance*') ? 'active' : '' }}" data-title="Reports">
                <i class="bi bi-bar-chart-fill nav-icon"></i><span class="nav-text">Reports</span>
            </a>
            @endif
            @endif
            @endif

            <!-- Settings -->
            @if(auth()->user()->isSuperAdmin())
            <div class="sidebar-section-label">Settings</div>
            <a href="{{ route('admin.alerts.index') }}" class="sidebar-item {{ request()->routeIs('admin.alerts*') ? 'active' : '' }}" data-title="Global Alerts">
                <i class="bi bi-exclamation-triangle nav-icon"></i><span class="nav-text">Global Alerts</span>
            </a>
            <a href="{{ route('activity-logs.index') }}" class="sidebar-item {{ request()->routeIs('activity-logs*') ? 'active' : '' }}" data-title="Activity Logs">
                <i class="bi bi-clock-history nav-icon"></i><span class="nav-text">Activity Logs</span>
            </a>
            <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings*') ? 'active' : '' }}" data-title="Settings">
                <i class="bi bi-gear-fill nav-icon"></i><span class="nav-text">Settings</span>
            </a>
            @endif
        @endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <a href="#" class="sidebar-item" data-title="Logout"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left nav-icon"></i>
                <span class="nav-text">Logout</span>
            </a>
        </form>
    </div>
</aside>
@endif

@if(!isset($noHeader) || !$noHeader)
<!-- TOP NAV -->
<header id="topnav" class="{{ (isset($noSidebar) && $noSidebar) ? 'no-sidebar' : '' }}">
    <div class="topnav-left">
        @if(isset($noSidebar) && $noSidebar)
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-2" style="height: 36px; border-radius: 8px; font-weight: 600;">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        @else
            <button class="btn-sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb')
                    <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                </ol>
            </nav>
        @endif
    </div>

    @yield('topnav-middle')    <div class="topnav-right">
        @if(!auth()->user()->isReseller())
            @php
                $activeTaskLog = \App\Models\TaskTimeLog::where('user_id', auth()->id())->where('status', 'running')->with('task')->first();
            @endphp
             @if(!auth()->user()->isSuperAdmin())
                 @if(session('active_room_work'))
                     @php
                         $activeRoomWork = session('active_room_work');
                         $isFollowups = ($activeRoomWork['room_id'] === 'followups');
                         $roomName = $isFollowups ? "Today's Follow-ups" : (\App\Models\LeadRoom::find($activeRoomWork['room_id'])?->name ?? 'Select Room');
                         $timerUrl = $isFollowups ? route('leads.start-work.followup-leads') : ($activeRoomWork['room_id'] ? route('leads.start-work.leads', $activeRoomWork['room_id']) : route('leads.start-work.select-room'));
                     @endphp
                    <a href="{{ $timerUrl }}" class="work-timer-badge working d-none d-md-flex align-items-center gap-2 text-decoration-none" id="nav-room-timer" data-start-time="{{ $activeRoomWork['started_at'] ?? '' }}" data-status="{{ $activeRoomWork['status'] ?? '' }}" data-accumulated="{{ $activeRoomWork['accumulated_seconds'] ?? 0 }}" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#fffbeb' : '#f1f5f9' }}; color: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#b45309' : '#475569' }}; border: 1px solid {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#fde68a' : '#cbd5e1' }}; cursor: pointer;">
                        <span class="status-dot {{ ($activeRoomWork['status'] ?? '') === 'active' ? 'working' : '' }}" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#d97706' : '#94a3b8' }};"></span>
                        <span style="font-size: 12px; font-weight: 600; max-width: 150px;" class="text-truncate">{{ $roomName }}</span>
                        <span class="badge text-white ms-1" id="nav-room-timer-counter" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#d97706' : '#94a3b8' }}; font-size: 11px;">00:00:00</span>
                    </a>
                @elseif($activeTaskLog)
                    <a href="{{ route('tasks.show', $activeTaskLog->task) }}" class="work-timer-badge working d-none d-md-flex align-items-center gap-2 text-decoration-none" id="nav-task-timer" data-start-time="{{ $activeTaskLog->started_at->toISOString() }}" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; cursor: pointer;">
                        <span class="status-dot working"></span>
                        <span style="font-size: 12px; font-weight: 600; max-width: 150px;" class="text-truncate">Task: {{ $activeTaskLog->task->title }}</span>
                        <span class="badge text-white ms-1" id="nav-timer-counter" style="background: #4f46e5; font-size: 11px;">00:00:00</span>
                    </a>
                @else
                    <a href="{{ route('chat.index') }}" class="work-timer-badge text-decoration-none d-none d-md-flex align-items-center gap-2" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                        <i class="bi bi-play-fill text-secondary"></i> Start Work
                    </a>
                @endif
            @endif

            <!-- Theme Toggle -->
            <button type="button" class="topnav-icon-btn" id="theme-toggle-btn" onclick="toggleTheme()" aria-label="Toggle theme">
                <i class="bi bi-moon" id="theme-toggle-icon"></i>
            </button>

            <!-- Notifications -->
            <div class="dropdown">
                <div class="topnav-icon-btn" data-bs-toggle="dropdown" id="topnav-bell-button">
                    <i class="bi bi-bell"></i>
                    @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                    <span class="notification-badge {{ $unreadCount > 0 ? '' : 'd-none' }}" id="topnav-bell-badge">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                </div>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" id="topnav-notifications-dropdown">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <span style="font-weight:600;">Notifications</span>
                        <a href="{{ route('notifications.mark-all-read') }}" id="topnav-mark-all-read" class="text-primary text-decoration-none {{ $unreadCount > 0 ? '' : 'd-none' }}" style="font-size:12px">Mark all read</a>
                    </div>
                    <div id="topnav-notifications-list">
                        @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                            <a href="{{ $notif->url ?? '#' }}" class="notification-item text-decoration-none {{ is_null($notif->read_at) ? 'unread' : '' }}">
                                <div style="font-size:13px;font-weight:500;">{{ $notif->title }}</div>
                                <div style="font-size:12px;color:#64748b;">{{ Str::limit($notif->message, 60) }}</div>
                            </a>
                        @empty
                            <div class="text-center py-4 text-muted"><i class="bi bi-bell-slash" style="font-size:32px;"></i><div class="mt-2" style="font-size:13px;">No notifications</div></div>
                        @endforelse
                    </div>
                    <div class="text-center py-2 border-top">
                        <a href="{{ route('notifications.index') }}" style="font-size:13px;" class="text-primary text-decoration-none">View all</a>
                    </div>
                </div>
            </div>
        @endif

        <!-- User -->->
        <div class="dropdown">
            <div class="user-avatar-btn" data-bs-toggle="dropdown">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="avatar-circle">
                <div class="user-info d-none d-md-block">
                    <div class="user-name">{{ Str::limit(auth()->user()->name, 16) }}</div>
                    <div class="user-role">{{ auth()->user()->role?->name }}</div>
                </div>
                <i class="bi bi-chevron-down ms-1 d-none d-md-block" style="font-size:12px;color:#94a3b8;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;border-radius:12px;">
                <li class="px-3 py-2 border-bottom">
                    <div style="font-size:13px;font-weight:600;">{{ auth()->user()->name }}</div>
                    <div style="font-size:12px;color:#64748b;">{{ auth()->user()->email }}</div>
                </li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
@endif

<!-- MAIN CONTENT -->
<main id="main-content" class="{{ (isset($noSidebar) && $noSidebar) ? 'no-sidebar' : '' }} {{ (isset($noHeader) && $noHeader) ? 'no-header' : '' }}">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const topnav = document.getElementById('topnav');
    const mainContent = document.getElementById('main-content');
    const overlay = document.getElementById('sidebarOverlay');
    let isMobile = window.innerWidth <= 768;

    function updateThemeUI(theme) {
        const toggleIcon = document.getElementById('theme-toggle-icon');
        if (toggleIcon) {
            if (theme === 'dark') {
                toggleIcon.className = 'bi bi-sun';
            } else {
                toggleIcon.className = 'bi bi-moon';
            }
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    }

    function toggleSidebar() {
        if (isMobile) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            topnav.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        isMobile = window.innerWidth <= 768;
        if (!isMobile && localStorage.getItem('sidebar-collapsed') === 'true') {
            sidebar.classList.add('collapsed');
            topnav.classList.add('sidebar-collapsed');
            mainContent.classList.add('sidebar-collapsed');
        }
        
        // Restore sidebar scroll position
        const sidebarNav = document.querySelector('.sidebar-nav');
        if (sidebarNav) {
            const savedScroll = localStorage.getItem('sidebar-scroll-position');
            if (savedScroll) {
                sidebarNav.scrollTop = parseInt(savedScroll, 10);
            }
            sidebarNav.addEventListener('scroll', () => {
                localStorage.setItem('sidebar-scroll-position', sidebarNav.scrollTop);
            });
        }
        
        // Theme initialization
        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateThemeUI(currentTheme);
    });

    window.addEventListener('resize', () => { isMobile = window.innerWidth <= 768; });

    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(alert).close(); } catch(e){} }, 5000);
    });
</script>
<script>
    // AI Auto-Correct Feature for every text box
    document.addEventListener('DOMContentLoaded', () => {
        const attachAiCorrector = (input) => {
            if (input.dataset.aiCorrectAttached) return;
            input.dataset.aiCorrectAttached = 'true';

            // Find or create parent position relative
            const parent = input.parentElement;
            if (!parent) return;
            
            const computedStyle = window.getComputedStyle(parent);
            if (computedStyle.position === 'static') {
                parent.style.position = 'relative';
            }

            // Create AI Button
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-ai-correct';
            btn.title = 'AI Auto-Correct Sentence';
            btn.innerHTML = '<i class="bi bi-stars" style="font-size: 11px; pointer-events: none;"></i>';
            
            // Add Styles
            Object.assign(btn.style, {
                position: 'absolute',
                zIndex: '99',
                border: 'none',
                background: 'linear-gradient(135deg, #6366f1, #818cf8)',
                color: 'white',
                borderRadius: '50%',
                width: '26px',
                height: '26px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                boxShadow: '0 2px 8px rgba(99, 102, 241, 0.4)',
                cursor: 'pointer',
                transition: 'opacity 0.2s ease, transform 0.2s ease',
                opacity: '0',
                pointerEvents: 'none',
                padding: '0'
            });

            parent.appendChild(btn);

            // Reposition button dynamically
            const repositionButton = () => {
                const inputRect = input.getBoundingClientRect();
                const parentRect = parent.getBoundingClientRect();
                
                let top;
                if (input.tagName.toLowerCase() === 'textarea') {
                    top = inputRect.bottom - parentRect.top - 26 - 8; // 8px from bottom
                } else {
                    top = inputRect.top - parentRect.top + (inputRect.height - 26) / 2; // centered
                }
                const left = inputRect.right - parentRect.left - 26 - 8; // 8px from right

                btn.style.top = `${top}px`;
                btn.style.left = `${left}px`;
            };

            const showButton = () => {
                if (input.value.trim().length > 3 && !input.readOnly && !input.disabled) {
                    repositionButton();
                    btn.style.opacity = '0.9';
                    btn.style.pointerEvents = 'auto';
                } else {
                    hideButton();
                }
            };

            const hideButton = () => {
                btn.style.opacity = '0';
                btn.style.pointerEvents = 'none';
            };

            // Event Listeners
            input.addEventListener('focus', showButton);
            input.addEventListener('input', showButton);
            
            // Reposition on window resize or scroll
            window.addEventListener('resize', repositionButton);

            // Initialize button state immediately on load (if input already has text)
            showButton();

            btn.addEventListener('click', async () => {
                const originalText = input.value;
                if (!originalText.trim()) return;

                // Show spinner
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px; border-width: 2px;" role="status" aria-hidden="true"></span>';
                btn.style.background = '#94a3b8'; // gray during load

                try {
                    const response = await fetch('/grammar/correct', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ text: originalText })
                    });

                    const data = await response.json();
                    if (data.success && data.corrected) {
                        input.value = data.corrected;
                        
                        // Trigger standard events for model reactivity
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));

                        // Show checkmark success
                        btn.style.background = '#10b981'; // green for success
                        btn.innerHTML = '<i class="bi bi-check-lg" style="font-size: 11px;"></i>';
                        
                        setTimeout(() => {
                            btn.style.background = 'linear-gradient(135deg, #6366f1, #818cf8)';
                            btn.innerHTML = '<i class="bi bi-stars" style="font-size: 11px;"></i>';
                            btn.disabled = false;
                            showButton();
                        }, 2000);
                    } else {
                        throw new Error('Correction failed');
                    }
                } catch (error) {
                    console.error('AI Error:', error);
                    btn.style.background = '#ef4444'; // red for error
                    btn.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="font-size: 11px;"></i>';
                    
                    setTimeout(() => {
                        btn.style.background = 'linear-gradient(135deg, #6366f1, #818cf8)';
                        btn.innerHTML = '<i class="bi bi-stars" style="font-size: 11px;"></i>';
                        btn.disabled = false;
                        showButton();
                    }, 2000);
                }
            });

            // Hover effects
            btn.addEventListener('mouseenter', () => {
                if (!btn.disabled) {
                    btn.style.transform = 'scale(1.15)';
                    btn.style.opacity = '1';
                }
            });
            btn.addEventListener('mouseleave', () => {
                if (!btn.disabled) {
                    btn.style.transform = 'scale(1)';
                }
            });
        };

        const initAiCorrections = () => {
            // Target all editable text inputs and textareas (except searches and passwords)
            const inputs = document.querySelectorAll('textarea, input[type="text"]:not([readonly]):not([disabled]):not([name="search"]):not(.no-ai)');
            inputs.forEach(attachAiCorrector);
        };

        // Initialize on load
        initAiCorrections();

        // Also watch for dynamically added DOM elements (e.g. inside modals)
        const observer = new MutationObserver((mutations) => {
            initAiCorrections();
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>
<script>
    // Live Nav Task Timer Ticker
    document.addEventListener('DOMContentLoaded', () => {
        const timerContainer = document.getElementById('nav-task-timer');
        const counterEl = document.getElementById('nav-timer-counter');
        
        if (timerContainer && counterEl) {
            const startTimeStr = timerContainer.dataset.startTime;
            if (startTimeStr) {
                const startTime = new Date(startTimeStr).getTime();
                
                const updateTimer = () => {
                    const now = new Date().getTime();
                    const diff = now - startTime;
                    if (diff > 0) {
                        const hours = Math.floor(diff / 3600000);
                        const minutes = Math.floor((diff % 3600000) / 60000);
                        const seconds = Math.floor((diff % 60000) / 1000);
                        
                        counterEl.textContent = [
                            String(hours).padStart(2, '0'),
                            String(minutes).padStart(2, '0'),
                            String(seconds).padStart(2, '0')
                        ].join(':');
                    }
                };
                
                updateTimer();
                setInterval(updateTimer, 1000);
            }
        }
    });
</script>
<script>
    // Live Nav Room Timer Ticker
    document.addEventListener('DOMContentLoaded', () => {
        const timerContainer = document.getElementById('nav-room-timer');
        const counterEl = document.getElementById('nav-room-timer-counter');
        
        if (timerContainer && counterEl) {
            const status = timerContainer.dataset.status;
            const accumulated = parseInt(timerContainer.dataset.accumulated || 0);
            
            const formatTime = (totalSeconds) => {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                return [
                    String(hours).padStart(2, '0'),
                    String(minutes).padStart(2, '0'),
                    String(seconds).padStart(2, '0')
                ].join(':');
            };

            if (status === 'paused') {
                counterEl.textContent = formatTime(accumulated) + ' (Paused)';
            } else {
                const startTimeStr = timerContainer.dataset.startTime;
                if (startTimeStr) {
                    const startTime = new Date(startTimeStr).getTime();
                    
                    const updateTimer = () => {
                        const now = new Date().getTime();
                        const diff = now - startTime;
                        if (diff > 0) {
                            const totalSecs = Math.floor(diff / 1000) + accumulated;
                            counterEl.textContent = formatTime(totalSecs);
                        }
                    };
                    
                    updateTimer();
                    setInterval(updateTimer, 1000);
                }
            }
        }
    });
</script>
<script>
    // Global notification sound utility using Web Audio API with autoplay gesture fix
    let globalAudioCtx = null;
    function initAudioContext() {
        if (!globalAudioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                globalAudioCtx = new AudioContext();
            }
        }
        if (globalAudioCtx && globalAudioCtx.state === 'suspended') {
            globalAudioCtx.resume();
        }
    }
    // Listen for user interaction to initialize/resume the AudioContext
    ['click', 'keydown', 'touchstart'].forEach(event => {
        document.addEventListener(event, initAudioContext, { once: false });
    });

    window.playNotificationSound = function() {
        try {
            initAudioContext();
            if (!globalAudioCtx) return;
            const now = globalAudioCtx.currentTime;
            
            // Note 1 (E5, 659.25Hz)
            const osc1 = globalAudioCtx.createOscillator();
            const gain1 = globalAudioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.15, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
            osc1.connect(gain1);
            gain1.connect(globalAudioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.4);
            
            // Note 2 (A5, 880Hz)
            const osc2 = globalAudioCtx.createOscillator();
            const gain2 = globalAudioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880.00, now + 0.08);
            gain2.gain.setValueAtTime(0.15, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc2.connect(gain2);
            gain2.connect(globalAudioCtx.destination);
            osc2.start(now + 0.08);
            osc2.stop(now + 0.5);
        } catch (e) {
            console.error("Failed to play notification sound:", e);
        }
    };

    // Polling logic for notifications
    document.addEventListener('DOMContentLoaded', () => {
        let currentUnreadCount = parseInt("{{ auth()->user()->unreadNotifications()->count() }}") || 0;
        let currentUnreadEmailsCount = parseInt("{{ \App\Models\MailboxMessage::where('receiver_id', auth()->id())->where('is_read', false)->whereNull('receiver_deleted_at')->count() }}") || 0;
        
        const pollNotifications = () => {
            fetch("/notifications/unread-count")
                .then(response => response.json())
                .then(data => {
                    const newUnreadCount = data.unread_count;
                    const newUnreadEmailsCount = data.unread_emails_count || 0;
                    
                    // Update badge and display
                    const badge = document.getElementById('topnav-bell-badge');
                    if (badge) {
                        badge.textContent = newUnreadCount > 9 ? '9+' : newUnreadCount;
                        if (newUnreadCount > 0) {
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }

                    // Update mailbox sidebar badge
                    const mailboxBadge = document.getElementById('sidebar-mailbox-badge');
                    if (mailboxBadge) {
                        mailboxBadge.textContent = newUnreadEmailsCount;
                        if (newUnreadEmailsCount > 0) {
                            mailboxBadge.classList.remove('d-none');
                        } else {
                            mailboxBadge.classList.add('d-none');
                        }
                    }

                    // Update mark all read link
                    const markAllRead = document.getElementById('topnav-mark-all-read');
                    if (markAllRead) {
                        if (newUnreadCount > 0) {
                            markAllRead.classList.remove('d-none');
                        } else {
                            markAllRead.classList.add('d-none');
                        }
                    }

                    // Update dropdown notifications list
                    const listContainer = document.getElementById('topnav-notifications-list');
                    if (listContainer && data.latest_notifications) {
                        if (data.latest_notifications.length === 0) {
                            listContainer.innerHTML = `
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash" style="font-size:32px;"></i>
                                    <div class="mt-2" style="font-size:13px;">No notifications</div>
                                </div>
                            `;
                        } else {
                            listContainer.innerHTML = data.latest_notifications.map(n => `
                                <a href="${n.url}" class="notification-item text-decoration-none ${n.is_unread ? 'unread' : ''}">
                                    <div style="font-size:13px;font-weight:500;">${escapeHtml(n.title)}</div>
                                    <div style="font-size:12px;color:#64748b;">${escapeHtml(n.message)}</div>
                                </a>
                            `).join('');
                        }
                    }
                    
                    // If count increased, play sound!
                    if (newUnreadCount > currentUnreadCount || newUnreadEmailsCount > currentUnreadEmailsCount) {
                        window.playNotificationSound();
                    }
                    
                    currentUnreadCount = newUnreadCount;
                    currentUnreadEmailsCount = newUnreadEmailsCount;
                })
                .catch(error => console.error('Error polling notifications:', error));
        };

        const escapeHtml = (text) => {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        };

        // Poll every 10 seconds
        setInterval(pollNotifications, 10000);
    });
</script>


<!-- Global Image Annotation Markup Modal -->
<div class="modal fade" id="globalImageMarkupModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="globalImageMarkupModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="globalImageMarkupModalLabel">Annotate Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="global-markup-modal-close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <!-- Toolbar -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 bg-light p-2 rounded-3 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark active" id="global-tool-pencil" title="Pencil Tool">
                        <i class="bi bi-pencil-fill me-1"></i> Pencil
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="global-tool-circle" title="Circle Tool">
                        <i class="bi bi-circle me-1"></i> Circle
                    </button>
                    <div class="vr mx-2"></div>
                    <!-- Colors -->
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 global-color-btn active" data-color="#ef4444" style="width: 24px; height: 24px; background-color: #ef4444;" title="Red"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 global-color-btn" data-color="#3b82f6" style="width: 24px; height: 24px; background-color: #3b82f6;" title="Blue"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 global-color-btn" data-color="#22c55e" style="width: 24px; height: 24px; background-color: #22c55e;" title="Green"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 global-color-btn" data-color="#eab308" style="width: 24px; height: 24px; background-color: #eab308;" title="Yellow"></button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="global-btn-clear-canvas">
                        <i class="bi bi-trash3-fill me-1"></i> Clear
                    </button>
                </div>
                
                <!-- Canvas Container -->
                <div class="d-flex justify-content-center align-items-center border rounded-3 bg-dark overflow-auto p-2" style="max-height: 400px; min-height: 250px;">
                    <canvas id="global-markup-canvas" style="cursor: crosshair; display: block; max-width: 100%; height: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></canvas>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="global-btn-save-markup">Save Annotation</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('globalImageMarkupModal');
        if (!modalEl) return;
        
        const canvas = document.getElementById('global-markup-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;
        if (!canvas || !ctx) return;

        let drawing = false;
        let currentTool = 'pencil'; // pencil or circle
        let currentColor = '#ef4444'; // default red
        let currentLineWidth = 3;
        let startX = 0;
        let startY = 0;
        let savedImageData = null;
        let loadedImg = null;
        let activeInput = null; // Keeps track of the input element that triggered the editor
        let originalFileName = '';

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

        const pencilBtn = document.getElementById('global-tool-pencil');
        const circleBtn = document.getElementById('global-tool-circle');
        if (pencilBtn && circleBtn) {
            pencilBtn.addEventListener('click', () => {
                currentTool = 'pencil';
                pencilBtn.classList.add('active');
                circleBtn.classList.remove('active');
            });
            circleBtn.addEventListener('click', () => {
                currentTool = 'circle';
                circleBtn.classList.add('active');
                pencilBtn.classList.remove('active');
            });
        }

        const colorBtns = document.querySelectorAll('.global-color-btn');
        colorBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                colorBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentColor = this.getAttribute('data-color');
            });
        });

        const clearBtn = document.getElementById('global-btn-clear-canvas');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (loadedImg) {
                    ctx.drawImage(loadedImg, 0, 0, canvas.width, canvas.height);
                }
            });
        }

        const saveMarkupBtn = document.getElementById('global-btn-save-markup');
        if (saveMarkupBtn) {
            saveMarkupBtn.addEventListener('click', () => {
                if (!activeInput) return;
                const base64 = canvas.toDataURL('image/jpeg', 0.9);
                
                function dataURLtoFile(dataurl, filename) {
                    var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                        bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
                    while(n--){
                        u8arr[n] = bstr.charCodeAt(n);
                    }
                    return new File([u8arr], filename, {type:mime});
                }

                const file = dataURLtoFile(base64, "annotated_" + originalFileName);
                const container = new DataTransfer();
                container.items.add(file);
                
                activeInput.files = container.files;
                activeInput.dispatchEvent(new Event('change', { bubbles: true }));

                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
        }

        const initFileInputs = () => {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                if (input.id === 'bug-image-input' || input.id === 'chat-image-input') return;
                if (input.dataset.markupInitialized) return;
                input.dataset.markupInitialized = 'true';

                const editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'btn btn-outline-secondary btn-edit-selected-image';
                editBtn.innerHTML = '<i class="bi bi-pencil-fill text-primary"></i>';
                editBtn.title = 'Edit / Annotate selected image';
                editBtn.style.display = 'none';

                if (input.parentNode.classList.contains('input-group')) {
                    input.parentNode.insertBefore(editBtn, input.nextSibling);
                } else {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'd-flex align-items-center w-100';
                    input.parentNode.insertBefore(wrapper, input);
                    wrapper.appendChild(input);
                    wrapper.appendChild(editBtn);
                    editBtn.style.marginLeft = '8px';
                }

                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        editBtn.style.display = 'inline-block';
                    } else {
                        editBtn.style.display = 'none';
                    }
                });

                editBtn.addEventListener('click', () => {
                    const file = input.files[0];
                    if (file) {
                        originalFileName = file.name;
                        activeInput = input;
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            loadedImg = new Image();
                            loadedImg.onload = function() {
                                const maxDimension = 700;
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

                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                            };
                            loadedImg.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
        };

        initFileInputs();

        const observer = new MutationObserver(() => {
            initFileInputs();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>

<!-- Global Floating Chat Style & Markup -->
<style>
    /* Floating Chat container */
    #floating-chat-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1050;
        font-family: 'Inter', sans-serif;
    }
    #floating-chat-trigger {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00a884, #008f72);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(0, 168, 132, 0.35);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        border: none;
        outline: none;
    }
    #floating-chat-trigger:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 168, 132, 0.45);
    }
    #floating-chat-trigger:active {
        transform: scale(0.95);
    }
    #floating-chat-trigger .badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background-color: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        min-width: 20px;
        height: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        border: 2px solid white;
    }
    #floating-chat-panel {
        position: fixed;
        bottom: 90px;
        right: 24px;
        width: 380px;
        height: 550px;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.08);
        display: none;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    #floating-chat-panel.active {
        display: flex;
        animation: floating-slide-up 0.25s ease-out;
    }
    @keyframes floating-slide-up {
        from { transform: translateY(15px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    /* Header */
    .floating-chat-header {
        background: #008069;
        color: white;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    .floating-chat-header-info {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .floating-chat-header-title {
        font-size: 14.5px;
        font-weight: 600;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: white !important;
    }
    .floating-chat-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .floating-chat-header-btn {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.85);
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    }
    .floating-chat-header-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
    }
    /* Search */
    .floating-chat-search {
        padding: 8px 12px;
        background: #ffffff;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    .floating-chat-search .input-group {
        background-color: #f0f2f5;
        border: 1px solid transparent;
        border-radius: 8px;
        overflow: hidden;
    }
    .floating-chat-search .form-control {
        background: transparent;
        border: none;
        font-size: 13px;
        padding: 6px 10px;
        box-shadow: none !important;
    }
    /* Body Lists */
    .floating-chat-body {
        flex: 1;
        overflow-y: auto;
        background: #efeae2;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .floating-chat-list-container {
        background: #ffffff;
        height: 100%;
        overflow-y: auto;
    }
    .floating-chat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-bottom: 1px solid #f8fafc;
        cursor: pointer;
        transition: background-color 0.15s;
        text-decoration: none !important;
        color: inherit !important;
    }
    .floating-chat-item:hover {
        background-color: #f8fafc;
    }
    .floating-chat-item .avatar-container {
        position: relative;
        flex-shrink: 0;
    }
    .floating-chat-item .avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
    }
    .floating-chat-item .info {
        flex: 1;
        min-width: 0;
    }
    .floating-chat-item .title-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }
    .floating-chat-item .title {
        font-size: 13px;
        font-weight: 600;
        color: #111b21;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .floating-chat-item .time {
        font-size: 9.5px;
        color: #667781;
    }
    .floating-chat-item .subtitle-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2px;
    }
    .floating-chat-item .subtitle {
        font-size: 11.5px;
        color: #667781;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 80%;
    }
    .floating-chat-item .badge-count {
        background-color: #22c55e;
        color: white;
        font-size: 9px;
        font-weight: 700;
        min-width: 16px;
        height: 16px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
    }
    /* Messages Area */
    .floating-chat-messages {
        padding: 16px;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    /* Input bar */
    .floating-chat-input-bar {
        background: #f0f2f5;
        padding: 8px 12px;
        display: none;
        align-items: center;
        gap: 8px;
        border-top: 1px solid rgba(0,0,0,0.05);
        flex-shrink: 0;
    }
    .floating-chat-input-container {
        flex: 1;
        background: white;
        border-radius: 20px;
        padding: 4px 12px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }
    .floating-chat-input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 13px;
        resize: none;
        max-height: 80px;
        width: 100%;
        line-height: 1.3;
    }
    .floating-chat-send-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background-color: #00a884;
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }
    .floating-chat-send-btn:hover {
        background-color: #008f72;
    }
    /* Attachment Preview */
    .floating-chat-preview {
        width: 50px;
        height: 50px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .floating-chat-preview-remove {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 16px;
        height: 16px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        cursor: pointer;
        border: 1px solid white;
    }

    /* Dark Mode styles for floating chat */
    [data-bs-theme="dark"] #floating-chat-panel {
        background: #111c2a;
        border-color: #1e293b;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
    }
    [data-bs-theme="dark"] .floating-chat-header {
        background: #1e293b;
        border-bottom-color: #334155;
    }
    [data-bs-theme="dark"] .floating-chat-search {
        background: #111c2a;
        border-bottom-color: #1e293b;
    }
    [data-bs-theme="dark"] .floating-chat-search .input-group {
        background-color: #1e293b;
    }
    [data-bs-theme="dark"] .floating-chat-search .form-control {
        color: #f8fafc;
    }
    [data-bs-theme="dark"] .floating-chat-list-container {
        background: #111c2a;
    }
    [data-bs-theme="dark"] .floating-chat-item {
        border-bottom-color: #1e293b;
    }
    [data-bs-theme="dark"] .floating-chat-item:hover {
        background-color: #162235;
    }
    [data-bs-theme="dark"] .floating-chat-item .title {
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .floating-chat-item .subtitle {
        color: #94a3b8;
    }
    [data-bs-theme="dark"] .floating-chat-body {
        background: #0e1622;
    }
    [data-bs-theme="dark"] .floating-chat-input-bar {
        background: #111c2a;
        border-top-color: #1e293b;
    }
    [data-bs-theme="dark"] .floating-chat-input-container {
        background: #1e293b;
        color: #f1f5f9;
    }
    [data-bs-theme="dark"] .floating-chat-input {
        color: #f1f5f9;
    }
    [data-bs-theme="dark"] #floating-chat-trigger {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    }
    [data-bs-theme="dark"] #floating-chat-trigger .badge {
        border-color: #111c2a;
    }

    /* Small Screen adaptations */
    @media (max-width: 576px) {
        #floating-chat-panel {
            width: calc(100% - 32px);
            height: calc(100% - 110px);
            right: 16px;
            bottom: 85px;
        }
    }
</style>

<!-- Floating Chat Markup -->
<div id="floating-chat-container">
    <button type="button" id="floating-chat-trigger" onclick="toggleFloatingChat()">
        <i class="bi bi-chat-dots-fill fs-4"></i>
        <span class="badge d-none" id="floating-chat-global-badge">0</span>
    </button>
    <div id="floating-chat-panel">
        <!-- Header -->
        <div class="floating-chat-header">
            <div class="floating-chat-header-info">
                <button type="button" class="floating-chat-header-btn d-none" id="floating-chat-back-btn" onclick="backToFloatingList()">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <div class="d-flex flex-column min-width-0">
                    <h6 class="floating-chat-header-title text-white" id="floating-chat-header-title">Work Chat</h6>
                    <small id="floating-chat-header-subtitle" style="font-size: 10px; color: rgba(255,255,255,0.7); display: none;"></small>
                </div>
            </div>
            <div class="floating-chat-header-actions d-flex align-items-center gap-1">
                <!-- Floating Pinned Messages Dropdown -->
                <div class="dropdown me-1" id="floating-chat-pinned-dropdown" style="display: none;">
                    <button class="btn btn-link text-white p-1 hover-bg-light-circle position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pinned Messages" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border: none; background: transparent;">
                        <i class="bi bi-pin-angle" style="font-size: 14px;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="floating-chat-pinned-count" style="font-size: 8px; padding: 2px 4px; display: none;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border border-light-subtle py-2" id="floating-chat-pinned-list" style="border-radius: 12px; min-width: 220px; max-width: 280px; max-height: 250px; overflow-y: auto; font-size: 12px; z-index: 1060;">
                        <li class="dropdown-header text-muted fw-semibold">Pinned Messages</li>
                        <div id="floating-chat-pinned-items-container">
                            <li class="text-center py-2 text-muted fs-8">No pinned messages</li>
                        </div>
                    </ul>
                </div>
                <div id="floating-chat-task-actions" style="display:none;"></div>
                <button type="button" class="floating-chat-header-btn" onclick="toggleFloatingChat()">
                    <i class="bi bi-x-lg" style="font-size: 14px;"></i>
                </button>
            </div>
        </div>
        <!-- Search bar (only for threads list) -->
        <div class="floating-chat-search" id="floating-chat-search-bar">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 text-muted" style="padding: 6px 0 6px 12px;"><i class="bi bi-search" style="font-size: 12px;"></i></span>
                <input type="text" id="floating-chat-search-input" class="form-control" placeholder="Search chats...">
            </div>
        </div>
        <!-- Body -->
        <div class="floating-chat-body" id="floating-chat-body">
            <!-- List of Chats -->
            <div class="floating-chat-list-container" id="floating-chat-list">
                <!-- Renders dynamically -->
            </div>
            <!-- Messages inside thread -->
            <div class="floating-chat-messages d-none" id="floating-chat-messages">
                <!-- Renders dynamically -->
            </div>
        </div>
        <!-- Footer input bar -->
        <div class="floating-chat-input-bar" id="floating-chat-input-bar">
            <label for="floating-chat-file" class="btn btn-link text-muted p-0 m-0 d-flex align-items-center justify-content-center" style="font-size: 18px; width: 36px; height: 36px; cursor: pointer;" title="Attach Image">
                <i class="bi bi-paperclip"></i>
            </label>
            <input type="file" id="floating-chat-file" style="display:none;" accept="image/*">
            <div class="floating-chat-input-container">
                <!-- Attachment preview box -->
                <div id="floating-chat-preview-box" class="d-none mb-1 mt-1">
                    <div class="floating-chat-preview" id="floating-chat-preview-img">
                        <span class="floating-chat-preview-remove" id="floating-chat-preview-remove"><i class="bi bi-x"></i></span>
                    </div>
                </div>

                <!-- Floating Reply Preview -->
                <div id="floating-reply-preview-container" class="d-none w-100 p-2 mb-2 rounded border-start border-4 border-primary position-relative" style="font-size: 11px; max-height: 70px; overflow: hidden; background-color: rgba(0, 0, 0, 0.04);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-primary mb-1" id="floating-reply-preview-sender">Sender</div>
                            <div class="text-muted text-truncate" id="floating-reply-preview-text" style="max-width: 80%;">Message text</div>
                        </div>
                        <button type="button" class="btn-close shadow-none p-1 position-absolute" style="top: 8px; right: 8px; font-size: 8px;" id="floating-reply-preview-close" onclick="cancelFloatingReply()"></button>
                    </div>
                </div>
                <!-- Floating Edit Preview -->
                <div id="floating-edit-preview-container" class="d-none w-100 p-2 mb-2 rounded border-start border-4 border-warning position-relative" style="font-size: 11px; max-height: 70px; overflow: hidden; background-color: rgba(255, 193, 7, 0.08);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-warning mb-1">Editing Message</div>
                            <div class="text-muted text-truncate" id="floating-edit-preview-text" style="max-width: 80%;">Original text</div>
                        </div>
                        <button type="button" class="btn-close shadow-none p-1 position-absolute" style="top: 8px; right: 8px; font-size: 8px;" id="floating-edit-preview-close" onclick="cancelFloatingEdit()"></button>
                    </div>
                </div>
                <input type="hidden" id="floating-reply-parent-id">
                <input type="hidden" id="floating-edit-message-id">
                <input type="hidden" id="floating-edit-message-type">

                <textarea id="floating-chat-textarea" class="floating-chat-input" placeholder="Type a message..." rows="1"></textarea>
            </div>
            <button type="button" class="floating-chat-send-btn" id="floating-chat-send-btn">
                <i class="bi bi-send-fill" style="font-size: 13px; margin-left: 2px;"></i>
            </button>
        </div>
    </div>
</div>

<!-- Comment Info Modal (Global) -->
<div class="modal fade" id="commentInfoModal" tabindex="-1" aria-labelledby="commentInfoModalLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="commentInfoModalLabel">Message Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="mb-3 p-2 bg-light rounded text-dark fs-7" id="comment-info-sent-at-container" style="display:none;">
                    <strong>Sent on:</strong> <span id="comment-info-sent-at"></span>
                </div>
                <div class="text-muted mb-3 fs-7">People who viewed this message:</div>
                <div id="comment-viewers-list" class="d-flex flex-column gap-2">
                    <!-- Dynamic List of Viewers -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Script -->
<script>
    const FloatingChatState = {
        isOpen: false,
        activeType: null, // 'task' or 'direct'
        activeId: null, // taskId or userId
        lastPolledAt: new Date().toISOString(),
        latestTime: null,
        lastCommentId: null,
        lastTimelogId: null,
        unreadCheckTimer: null,
        panelPollTimer: null,
        unifiedList: [],
        imageData: null
    };

    function escapeQuote(str) {
        if (!str) return '';
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    function toggleFloatingChat() {
        const panel = document.getElementById('floating-chat-panel');
        if (!panel) return;

        if (panel.classList.contains('active')) {
            panel.classList.remove('active');
            FloatingChatState.isOpen = false;
            
            // Clear polling timers
            if (FloatingChatState.panelPollTimer) {
                clearInterval(FloatingChatState.panelPollTimer);
                FloatingChatState.panelPollTimer = null;
            }
            backToFloatingList();
        } else {
            panel.classList.add('active');
            FloatingChatState.isOpen = true;
            loadFloatingUnifiedList();
            
            // Poll unified list updates every 5 seconds
            FloatingChatState.panelPollTimer = setInterval(loadFloatingUnifiedList, 5000);
        }
    }

    function loadFloatingUnifiedList() {
        if (!FloatingChatState.isOpen || FloatingChatState.activeId) return;

        const searchInput = document.getElementById('floating-chat-search-input');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        fetch('/chat/unified-list')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    FloatingChatState.unifiedList = data.items;
                    renderFloatingUnifiedList(query);
                }
            })
            .catch(err => console.error('Error listing unified chats:', err));
    }

    function renderFloatingUnifiedList(query) {
        const container = document.getElementById('floating-chat-list');
        if (!container) return;

        let html = '';
        const items = FloatingChatState.unifiedList;

        items.forEach(item => {
            // Apply query search filter if present
            if (query) {
                const title = (item.title || '').toLowerCase();
                const subtitle = (item.subtitle || '').toLowerCase();
                if (!title.includes(query) && !subtitle.includes(query)) {
                    return;
                }
            }

            if (item.type === 'task') {
                const badgeHtml = item.unread_count > 0 ? `<span class="badge-count">${item.unread_count}</span>` : '';
                const priorityChar = item.priority ? item.priority.charAt(0).toUpperCase() : 'M';
                const indicatorHtml = item.is_bug ? `<span class="position-absolute bottom-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 16px; height: 16px; font-size: 8px;" title="Bug"><i class="bi bi-bug-fill"></i></span>` : '';
                
                html += `
                    <div class="floating-chat-item" onclick="openFloatingThread('task', ${item.id}, '${escapeQuote(item.title)}')">
                        <div class="avatar-container">
                            <img src="${item.avatar}" class="avatar">
                            <span class="position-absolute top-0 start-0 badge bg-${item.priority_badge || 'secondary'} rounded-circle d-flex align-items-center justify-content-center" style="width: 14px; height: 14px; font-size: 7px; color: white;">${priorityChar}</span>
                            ${indicatorHtml}
                        </div>
                        <div class="info">
                            <div class="title-row">
                                <span class="title">${item.title}</span>
                                <span class="time">${item.time_formatted || ''}</span>
                            </div>
                            <div class="subtitle-row">
                                <span class="subtitle" style="color: var(--primary); font-weight: 500;">${item.subtitle}</span>
                                ${badgeHtml}
                            </div>
                        </div>
                    </div>
                `;
            } else {
                const badgeHtml = item.unread_count > 0 ? `<span class="badge-count">${item.unread_count}</span>` : '';
                const onlineHtml = item.is_online ? `<div class="online-dot position-absolute" style="bottom: 0; right: 0; width: 10px; height: 10px; background: #10b981; border: 2px solid white; border-radius: 50%;"></div>` : '';
                
                html += `
                    <div class="floating-chat-item" onclick="openFloatingThread('direct', ${item.id}, '${escapeQuote(item.title)}', '${item.avatar}', '${escapeQuote(item.subtitle)}')">
                        <div class="avatar-container">
                            <img src="${item.avatar}" class="avatar">
                            ${onlineHtml}
                        </div>
                        <div class="info">
                            <div class="title-row">
                                <span class="title">${item.title}</span>
                                <span class="time">${item.time_formatted || ''}</span>
                            </div>
                            <div class="subtitle-row">
                                <span class="subtitle">${item.last_message || 'No messages yet'}</span>
                                ${badgeHtml}
                            </div>
                        </div>
                    </div>
                `;
            }
        });

        if (html === '') {
            container.innerHTML = `<div class="text-center py-5 text-muted" style="font-size:12.5px;"><i class="bi bi-chat-text fs-4 d-block mb-2"></i>No active chats.</div>`;
        } else {
            container.innerHTML = html;
        }
    }

    function backToFloatingList() {
        // Clear active thread state
        FloatingChatState.activeType = null;
        FloatingChatState.activeId = null;
        FloatingChatState.latestTime = null;
        FloatingChatState.lastCommentId = null;
        FloatingChatState.lastTimelogId = null;

        // Reset panel UI
        document.getElementById('floating-chat-back-btn').classList.add('d-none');
        document.getElementById('floating-chat-search-bar').classList.remove('d-none');
        document.getElementById('floating-chat-list').classList.remove('d-none');
        document.getElementById('floating-chat-messages').classList.add('d-none');
        document.getElementById('floating-chat-input-bar').style.display = 'none';
        document.getElementById('floating-chat-task-actions').style.display = 'none';
        
        document.getElementById('floating-chat-header-title').textContent = 'Work Chat';
        document.getElementById('floating-chat-header-subtitle').style.display = 'none';
        
        // Remove active poll
        if (FloatingChatState.panelPollTimer) {
            clearInterval(FloatingChatState.panelPollTimer);
        }
        loadFloatingUnifiedList();
        FloatingChatState.panelPollTimer = setInterval(loadFloatingUnifiedList, 5000);
    }

    function openFloatingThread(type, id, title, avatar, subtitle) {
        if (FloatingChatState.panelPollTimer) {
            clearInterval(FloatingChatState.panelPollTimer);
            FloatingChatState.panelPollTimer = null;
        }

        FloatingChatState.activeType = type;
        FloatingChatState.activeId = id;
        FloatingChatState.lastPolledAt = '{{ now()->toISOString() }}';

        if (type === 'direct') {
            window.activeFloatingDirectUserName = title;
            window.activeFloatingDirectUserAvatar = avatar;
        }
        cancelFloatingReply();

        // Update headers
        document.getElementById('floating-chat-back-btn').classList.remove('d-none');
        document.getElementById('floating-chat-search-bar').classList.add('d-none');
        document.getElementById('floating-chat-list').classList.add('d-none');
        
        const msgContainer = document.getElementById('floating-chat-messages');
        msgContainer.classList.remove('d-none');
        msgContainer.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm" role="status"></div></div>`;
        
        document.getElementById('floating-chat-input-bar').style.display = 'flex';
        document.getElementById('floating-chat-header-title').textContent = title;

        // Load messages history
        if (type === 'task') {
            document.getElementById('floating-chat-header-subtitle').textContent = 'Group / Task Chat';
            document.getElementById('floating-chat-header-subtitle').style.display = 'block';

            fetch(`/chat/tasks/${id}?_t=${new Date().getTime()}`)
                .then(res => res.json())
                .then(data => {
                    msgContainer.innerHTML = data.html || `<div class="text-center py-4 text-muted" style="font-size:12px;">No messages yet.</div>`;
                    if (data.html) {
                        groupChatMessagesByDate('floating-chat-messages');
                        window.updatePinnedMessagesList();
                    }
                    FloatingChatState.latestTime = data.latest_time;
                    FloatingChatState.lastCommentId = data.last_comment_id;
                    FloatingChatState.lastTimelogId = data.last_timelog_id;
                    
                    const chatBody = document.getElementById('floating-chat-body');
                    chatBody.scrollTop = chatBody.scrollHeight;

                    // Header actions (Start task timer)
                    renderFloatingTaskHeaderActions(data);

                    // Sync index unread badge locally if we are on index
                    const mainBadge = document.getElementById(`unread-badge-${id}`);
                    if (mainBadge) mainBadge.remove();

                    // Start active polling
                    FloatingChatState.panelPollTimer = setInterval(pollFloatingThreadUpdates, 5000);
                })
                .catch(err => {
                    msgContainer.innerHTML = `<div class="text-center text-danger py-4" style="font-size:12px;">Error loading messages.</div>`;
                });
        } else {
            document.getElementById('floating-chat-header-subtitle').textContent = subtitle || 'Personal Chat';
            document.getElementById('floating-chat-header-subtitle').style.display = 'block';
            document.getElementById('floating-chat-task-actions').style.display = 'none';

            fetch(`/direct-chat/messages/${id}?_t=${new Date().getTime()}`)
                .then(res => res.json())
                .then(data => {
                    msgContainer.innerHTML = '';
                    if (data.success) {
                        FloatingChatState.lastPolledAt = data.latest_time || new Date().toISOString();
                    }
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            appendFloatingDirectMessage(msg);
                        });
                        groupChatMessagesByDate('floating-chat-messages');
                        window.updatePinnedMessagesList();
                    } else {
                        msgContainer.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:12px;">No messages yet.</div>`;
                    }
                    
                    const chatBody = document.getElementById('floating-chat-body');
                    chatBody.scrollTop = chatBody.scrollHeight;

                    // Clear local sidebar unread badge if we are on main index
                    const mainBadge = document.getElementById(`badge-${id}`);
                    if (mainBadge) {
                        mainBadge.classList.add('d-none');
                        mainBadge.textContent = '0';
                    }

                    // Start active polling
                    FloatingChatState.panelPollTimer = setInterval(pollFloatingThreadUpdates, 5000);
                })
                .catch(err => {
                    msgContainer.innerHTML = `<div class="text-center text-danger py-4" style="font-size:12px;">Error loading messages.</div>`;
                });
        }
    }

    function renderFloatingTaskHeaderActions(data) {
        const actionsContainer = document.getElementById('floating-chat-task-actions');
        if (!actionsContainer) return;
        actionsContainer.innerHTML = '';
        actionsContainer.style.display = 'none';

        if (data.active_log_id) {
            // Stop task timer
            actionsContainer.innerHTML = `
                <button type="button" class="floating-chat-header-btn text-danger" onclick="endWorkFloating(${data.active_log_id})" title="End\u0020Work">
                    <i class="bi bi-stop-fill"></i>
                </button>
            `;
            actionsContainer.style.display = 'block';
        } else if (data.status !== 'completed' && data.status !== 'review') {
            // Start task timer
            actionsContainer.innerHTML = `
                <form method="POST" action="/work-timer/start-task/${data.task_id}" style="display:inline;" onsubmit="this.querySelector('button').disabled = true;">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <button type="submit" class="floating-chat-header-btn text-success" title="Start\u0020Work">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </form>
            `;
            actionsContainer.style.display = 'block';
        }
    }

    function endWorkFloating(logId) {
        if (confirm("Are you sure you want to end work on this task?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/work-timer/end-task/${logId}`;
            form.style.display = 'none';
            
            const csrfInput = document.createElement('input');
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const noteInput = document.createElement('input');
            noteInput.name = 'note';
            noteInput.value = 'Time log completed via floating chat widget';

            form.appendChild(csrfInput);
            form.appendChild(noteInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function appendFloatingDirectMessage(msg) {
        const msgContainer = document.getElementById('floating-chat-messages');
        if (!msgContainer) return;

        const row = document.createElement('div');
        row.className = `chat-row ${msg.is_sent ? 'sent' : 'received'} mb-2`;
        row.id = `floating-msg-row-${msg.id}`;
        row.dataset.date = msg.date || '';
        row.dataset.time = msg.time || '';

        let imgHtml = '';
        if (msg.image_url) {
            imgHtml = `<img src="${msg.image_url}" class="rounded-3 mb-1 d-block" style="max-width: 180px; cursor: pointer;" onclick="window.open('${msg.image_url}', '_blank')">`;
        }

        let textHtml = '';
        if (msg.message) {
            const div = document.createElement('div');
            div.textContent = msg.message;
            textHtml = `<div class="chat-text" style="font-size:12.5px;">${div.innerHTML}</div>`;
        }

        let replyHtml = '';
        if (msg.reply_to_message) {
            replyHtml = `
                <div class="reply-quote-box p-2 mb-1 rounded border-start border-3 border-primary bg-light-subtle" style="font-size: 10px; opacity: 0.85; background-color: rgba(0, 0, 0, 0.03); max-width: 100%;">
                    <div class="fw-bold text-primary mb-1">${escapeHtml(msg.reply_to_message.sender_name)}</div>
                    <div class="text-truncate text-muted text-dark" style="max-width: 90%;">${escapeHtml(msg.reply_to_message.message || '[Image]')}</div>
                </div>
            `;
        }

        const targetUser = window.activeFloatingDirectUserName || 'Recipient';
        const targetAvatar = window.activeFloatingDirectUserAvatar || '';
        const viewersData = JSON.stringify(msg.seen_by || []);

        const escapedMsgText = (msg.message || '[Image]').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, '\\n');
        const contactName = msg.is_sent ? 'You' : targetUser;

        const actionsHtml = `
            <div class="chat-bubble-actions dropdown">
                <button class="chat-bubble-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                    <i class="bi bi-three-dots-vertical" style="font-size: 12px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-${msg.is_sent ? 'start' : 'end'} shadow-sm border border-light-subtle py-1" style="font-size: 11px; z-index: 1050; min-width: 130px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-1.5 py-1" href="javascript:void(0)" onclick="replyToFloatingMessage(${msg.id}, '${escapeHtml(contactName)}')">
                            <i class="bi bi-reply-fill text-muted" style="font-size: 11px;"></i> Reply
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-1.5 py-1" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#commentInfoModal" data-viewers='${viewersData}' data-sent-at="${msg.formatted_time || msg.time}">
                            <i class="bi bi-info-circle text-muted" style="font-size: 11px;"></i> Message Info
                        </a>
                    </li>
                    ${msg.is_sent && msg.is_editable ? `
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-1.5 py-1" href="javascript:void(0)" onclick="startFloatingEditMessage(${msg.id}, 'direct', \`${escapedMsgText}\`)">
                            <i class="bi bi-pencil text-muted" style="font-size: 11px;"></i> Edit Message
                        </a>
                    </li>
                    ` : ''}
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-1.5 py-1 text-warning" href="javascript:void(0)" onclick="toggleMessageImportant(${msg.id}, 'direct')">
                            <i class="bi ${msg.is_important ? 'bi-star-fill text-warning' : 'bi-star text-muted'}" style="font-size: 11px;"></i> ${msg.is_important ? 'Unstar' : 'Star'}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-1.5 py-1 text-danger" href="javascript:void(0)" onclick="toggleMessagePin(${msg.id}, 'direct')">
                            <i class="bi ${msg.is_pinned ? 'bi-pin-angle-fill text-danger' : 'bi-pin text-muted'}" style="font-size: 11px;"></i> ${msg.is_pinned ? 'Unpin' : 'Pin'}
                        </a>
                    </li>
                </ul>
            </div>
        `;

        const starIconHtml = msg.is_important ? `<i class="bi bi-star-fill text-warning me-1" style="font-size: 9px;" title="Starred"></i>` : '';
        const pinIconHtml = msg.is_pinned ? `<i class="bi bi-pin-angle-fill text-danger me-1" style="font-size: 9px;" title="Pinned"></i>` : '';
        const editedHtml = msg.is_edited ? `<span class="text-muted me-1" style="font-size: 8px; font-style: italic;">(edited)</span>` : '';

        const metaHtml = `
            <div class="chat-meta d-flex align-items-center gap-1" style="font-size: 8px;">
                ${starIconHtml}
                ${pinIconHtml}
                ${editedHtml}
                <span>${msg.time || msg.formatted_time}</span>
                ${msg.is_sent ? `<i class="bi ${msg.read_at ? 'bi-check2-all text-primary' : 'bi-check2'} ms-1" style="font-size:10px;"></i>` : ''}
            </div>
        `;

        if (msg.is_sent) {
            row.innerHTML = `
                ${actionsHtml}
                <div class="chat-bubble" style="padding: 6px 10px 18px 10px; max-width: 85%;">
                    ${replyHtml}
                    ${imgHtml}
                    ${textHtml}
                    ${metaHtml}
                </div>
            `;
        } else {
            row.innerHTML = `
                <div class="chat-bubble" style="padding: 6px 10px 18px 10px; max-width: 85%;">
                    ${replyHtml}
                    ${imgHtml}
                    ${textHtml}
                    ${metaHtml}
                </div>
                ${actionsHtml}
            `;
        }

        msgContainer.appendChild(row);
    }

    function pollFloatingThreadUpdates() {
        if (!FloatingChatState.isOpen || !FloatingChatState.activeId) return;

        if (FloatingChatState.activeType === 'task') {
            let url = `/tasks/${FloatingChatState.activeId}/feed-updates?since=${encodeURIComponent(FloatingChatState.latestTime)}`;
            if (FloatingChatState.lastCommentId) {
                url += `&last_comment_id=${FloatingChatState.lastCommentId}`;
            }
            if (FloatingChatState.lastTimelogId) {
                url += `&last_timelog_id=${FloatingChatState.lastTimelogId}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.has_updates) {
                        FloatingChatState.latestTime = data.latest_time;
                        FloatingChatState.lastCommentId = data.last_comment_id;
                        FloatingChatState.lastTimelogId = data.last_timelog_id;
                        
                        const msgContainer = document.getElementById('floating-chat-messages');
                        const chatBody = document.getElementById('floating-chat-body');
                        
                        if (msgContainer && chatBody) {
                            const isNearBottom = chatBody.scrollHeight - chatBody.clientHeight - chatBody.scrollTop < 80;
                            
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data.html;
                            const newRows = tempDiv.querySelectorAll('.chat-row');
                            let appendedAny = false;
                            
                            newRows.forEach(row => {
                                if (!document.getElementById(row.id)) {
                                    msgContainer.appendChild(row);
                                    appendedAny = true;
                                }
                            });
                            
                            if (appendedAny) {
                                groupChatMessagesByDate('floating-chat-messages');
                                window.updatePinnedMessagesList();
                                if (isNearBottom) {
                                    chatBody.scrollTop = chatBody.scrollHeight;
                                }
                                if (data.play_sound && typeof window.playNotificationSound === 'function') {
                                    window.playNotificationSound();
                                }
                            }
                        }
                    }
                })
                .catch(err => console.error('Error polling floating task updates:', err));
        } else {
            let url = `/direct-chat/updates?since=${encodeURIComponent(FloatingChatState.lastPolledAt)}`;
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        FloatingChatState.lastPolledAt = data.timestamp;

                        if (data.new_messages.length > 0) {
                            const msgContainer = document.getElementById('floating-chat-messages');
                            const chatBody = document.getElementById('floating-chat-body');
                            
                            let appendedAny = false;
                            let playSound = false;
                            
                            data.new_messages.forEach(msg => {
                                if (parseInt(msg.sender_id) === parseInt(FloatingChatState.activeId)) {
                                    if (!document.getElementById(`floating-msg-row-${msg.id}`)) {
                                        appendFloatingDirectMessage(msg);
                                        appendedAny = true;
                                        playSound = true;
                                    }
                                }
                            });

                            if (appendedAny) {
                                groupChatMessagesByDate('floating-chat-messages');
                                window.updatePinnedMessagesList();
                                chatBody.scrollTop = chatBody.scrollHeight;
                                
                                // Mark as read
                                fetch(`/direct-chat/read/${FloatingChatState.activeId}`, {
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
                .catch(err => console.error('Error polling floating direct updates:', err));
        }
    }

    function sendFloatingChatMessage() {
        const textarea = document.getElementById('floating-chat-textarea');
        if (!textarea) return;

        const comment = textarea.value.trim();
        const base64Img = FloatingChatState.imageData;
        const editId = document.getElementById('floating-edit-message-id')?.value;
        const editType = document.getElementById('floating-edit-message-type')?.value;

        if (!comment && !base64Img) return;

        const sendBtn = document.getElementById('floating-chat-send-btn');
        sendBtn.disabled = true;

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
                sendBtn.disabled = false;
                textarea.value = '';
                textarea.style.height = 'auto';
                cancelFloatingEdit();

                if (editType === 'direct') {
                    const row = document.getElementById(`floating-msg-row-${editId}`);
                    if (row) {
                        const textEl = row.querySelector('.chat-text');
                        if (textEl) textEl.textContent = comment;
                        
                        const meta = row.querySelector('.chat-meta');
                        if (meta && !meta.innerHTML.includes('(edited)')) {
                            const editedSpan = document.createElement('span');
                            editedSpan.className = 'text-muted me-1';
                            editedSpan.style.cssText = 'font-size: 9px; font-style: italic;';
                            editedSpan.textContent = '(edited)';
                            meta.insertBefore(editedSpan, meta.firstChild);
                        }
                    }
                } else {
                    const row = document.getElementById(`floating-chat-row-comment-${editId}`);
                    if (row) {
                        const textEl = row.querySelector('.chat-text');
                        if (textEl) textEl.textContent = comment;
                        
                        const meta = row.querySelector('.chat-meta');
                        if (meta && !meta.innerHTML.includes('(edited)')) {
                            const editedSpan = document.createElement('span');
                            editedSpan.className = 'text-muted me-1';
                            editedSpan.style.cssText = 'font-size: 9px; font-style: italic;';
                            editedSpan.textContent = '(edited)';
                            meta.insertBefore(editedSpan, meta.querySelector('span'));
                        }
                    }
                }
            })
            .catch(error => {
                sendBtn.disabled = false;
                console.error('Error editing floating message:', error);
            });
            return;
        }

        const formData = new FormData();
        const parentId = document.getElementById('floating-reply-parent-id')?.value;
        if (parentId) {
            formData.append('parent_id', parentId);
        }
        
        let url = '';
        if (FloatingChatState.activeType === 'task') {
            url = `/tasks/${FloatingChatState.activeId}/comments`;
            formData.append('comment', comment);
            if (base64Img) {
                formData.append('image_data', base64Img);
            }
        } else {
            url = `/direct-chat/messages/${FloatingChatState.activeId}`;
            formData.append('message', comment);
            if (base64Img) {
                formData.append('image_data', base64Img);
            }
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            sendBtn.disabled = false;
            textarea.value = '';
            textarea.style.height = 'auto';
            cancelFloatingReply();
            
            // Clear attachments preview
            FloatingChatState.imageData = null;
            document.getElementById('floating-chat-preview-box').classList.add('d-none');
            document.getElementById('floating-chat-preview-img').style.backgroundImage = '';
            document.getElementById('floating-chat-file').value = '';

            const chatBody = document.getElementById('floating-chat-body');

            if (FloatingChatState.activeType === 'task') {
                pollFloatingThreadUpdates();
            } else {
                if (data.success) {
                    appendFloatingDirectMessage(data.message);
                    groupChatMessagesByDate('floating-chat-messages');
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            }
        })
        .catch(err => {
            sendBtn.disabled = false;
            console.error('Error sending message:', err);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Start background checks for unread badge counts on target trigger
        checkFloatingUnreadCounts();
        FloatingChatState.unreadCheckTimer = setInterval(checkFloatingUnreadCounts, 10000);

        // Attachment file parser
        const fileInput = document.getElementById('floating-chat-file');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        FloatingChatState.imageData = event.target.result;
                        const previewImg = document.getElementById('floating-chat-preview-img');
                        previewImg.style.backgroundImage = `url(${event.target.result})`;
                        document.getElementById('floating-chat-preview-box').classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        const removePreview = document.getElementById('floating-chat-preview-remove');
        if (removePreview) {
            removePreview.addEventListener('click', () => {
                FloatingChatState.imageData = null;
                document.getElementById('floating-chat-preview-box').classList.add('d-none');
                document.getElementById('floating-chat-preview-img').style.backgroundImage = '';
                document.getElementById('floating-chat-file').value = '';
            });
        }

        // Send triggers
        const sendBtn = document.getElementById('floating-chat-send-btn');
        if (sendBtn) {
            sendBtn.addEventListener('click', sendFloatingChatMessage);
        }

        const textarea = document.getElementById('floating-chat-textarea');
        if (textarea) {
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendFloatingChatMessage();
                }
            });
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        }

        // Global Message Info Modal Viewers population
        const commentInfoModal = document.getElementById('commentInfoModal');
        if (commentInfoModal) {
            commentInfoModal.addEventListener('show.bs.modal', function(event) {
                const triggerButton = event.relatedTarget;
                const sentAt = triggerButton.getAttribute('data-sent-at');
                const sentAtContainer = document.getElementById('comment-info-sent-at-container');
                const sentAtSpan = document.getElementById('comment-info-sent-at');
                if (sentAt) {
                    sentAtSpan.textContent = sentAt;
                    sentAtContainer.style.display = 'block';
                } else {
                    sentAtContainer.style.display = 'none';
                }
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

        // Search trigger for sidebar chats
        const searchInput = document.getElementById('floating-chat-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                renderFloatingUnifiedList(this.value.toLowerCase().trim());
            });
        }
    });

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

    window.replyToComment = function(commentId, senderName) {
        const isFloating = document.getElementById('floating-chat-panel')?.classList.contains('active') && FloatingChatState.activeType === 'task';
        const containerPrefix = isFloating ? 'floating-' : '';
        
        // Find row
        const row = document.getElementById(`chat-row-comment-${commentId}`);
        const textEl = row ? row.querySelector('.chat-text') : null;
        const commentText = textEl ? textEl.innerText.trim() : '[Attachment]';
        
        const preview = document.getElementById(`${containerPrefix}reply-preview-container`);
        const sender = document.getElementById(`${containerPrefix}reply-preview-sender`);
        const text = document.getElementById(`${containerPrefix}reply-preview-text`);
        const input = document.getElementById(`${containerPrefix}reply-parent-id`);
        
        if (preview && sender && text && input) {
            sender.textContent = senderName;
            text.textContent = commentText;
            input.value = commentId;
            preview.classList.remove('d-none');
            
            const textInput = document.getElementById(isFloating ? 'floating-chat-textarea' : 'whatsapp-comment-input');
            if (textInput) textInput.focus();
        }
    };

    window.replyToDirectMessage = function(msgId, senderName) {
        const preview = document.getElementById('reply-preview-container');
        const sender = document.getElementById('reply-preview-sender');
        const text = document.getElementById('reply-preview-text');
        const input = document.getElementById('reply-parent-id');
        
        const row = document.getElementById(`msg-row-${msgId}`);
        const textEl = row ? row.querySelector('.chat-text') : null;
        const commentText = textEl ? textEl.innerText.trim() : '[Attachment]';
        
        if (preview && sender && text && input) {
            sender.textContent = senderName;
            text.textContent = commentText;
            input.value = msgId;
            preview.classList.remove('d-none');
            document.getElementById('whatsapp-comment-input').focus();
        }
    };

    window.replyToFloatingMessage = function(msgId, senderName) {
        const preview = document.getElementById('floating-reply-preview-container');
        const sender = document.getElementById('floating-reply-preview-sender');
        const text = document.getElementById('floating-reply-preview-text');
        const input = document.getElementById('floating-reply-parent-id');
        
        const row = document.getElementById(`floating-msg-row-${msgId}`);
        const textEl = row ? row.querySelector('.chat-text') : null;
        const commentText = textEl ? textEl.innerText.trim() : '[Attachment]';
        
        if (preview && sender && text && input) {
            sender.textContent = senderName;
            text.textContent = commentText;
            input.value = msgId;
            preview.classList.remove('d-none');
            document.getElementById('floating-chat-textarea').focus();
        }
    };

    window.cancelReply = function() {
        const preview = document.getElementById('reply-preview-container');
        const input = document.getElementById('reply-parent-id');
        if (preview) preview.classList.add('d-none');
        if (input) input.value = '';
    };

    window.cancelFloatingReply = function() {
        const preview = document.getElementById('floating-reply-preview-container');
        const input = document.getElementById('floating-reply-parent-id');
        if (preview) preview.classList.add('d-none');
        if (input) input.value = '';
    };

    window.editComment = function(commentId, type) {
        const isFloating = document.getElementById('floating-chat-panel')?.classList.contains('active');
        const rowId = isFloating ? `floating-chat-row-comment-${commentId}` : `chat-row-comment-${commentId}`;
        const row = document.getElementById(rowId);
        const textEl = row ? row.querySelector('.chat-text') : null;
        const commentText = textEl ? textEl.innerText.trim() : '';

        if (isFloating) {
            window.startFloatingEditMessage(commentId, type, commentText);
        } else {
            window.startEditMessage(commentId, type, commentText);
        }
    };

    window.startEditMessage = function(id, type, currentText) {
        window.cancelReply();
        const textarea = document.getElementById('whatsapp-comment-input');
        const container = document.getElementById('edit-preview-container');
        const textSpan = document.getElementById('edit-preview-text');
        const inputId = document.getElementById('edit-message-id');
        const inputType = document.getElementById('edit-message-type');
        if (textarea && container && textSpan && inputId && inputType) {
            textarea.value = currentText;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
            textSpan.textContent = currentText;
            inputId.value = id;
            inputType.value = type;
            container.classList.remove('d-none');
            textarea.focus();
        }
    };

    window.cancelEdit = function() {
        const textarea = document.getElementById('whatsapp-comment-input');
        const container = document.getElementById('edit-preview-container');
        const inputId = document.getElementById('edit-message-id');
        const inputType = document.getElementById('edit-message-type');
        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
        }
        if (container) container.classList.add('d-none');
        if (inputId) inputId.value = '';
        if (inputType) inputType.value = '';
    };

    window.startFloatingEditMessage = function(id, type, currentText) {
        window.cancelFloatingReply();
        const textarea = document.getElementById('floating-chat-textarea');
        const container = document.getElementById('floating-edit-preview-container');
        const textSpan = document.getElementById('floating-edit-preview-text');
        const inputId = document.getElementById('floating-edit-message-id');
        const inputType = document.getElementById('floating-edit-message-type');
        if (textarea && container && textSpan && inputId && inputType) {
            textarea.value = currentText;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
            textSpan.textContent = currentText;
            inputId.value = id;
            inputType.value = type;
            container.classList.remove('d-none');
            textarea.focus();
        }
    };

    window.cancelFloatingEdit = function() {
        const textarea = document.getElementById('floating-chat-textarea');
        const container = document.getElementById('floating-edit-preview-container');
        const inputId = document.getElementById('floating-edit-message-id');
        const inputType = document.getElementById('floating-edit-message-type');
        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
        }
        if (container) container.classList.add('d-none');
        if (inputId) inputId.value = '';
        if (inputType) inputType.value = '';
    };

    window.toggleMessagePin = function(id, type) {
        let url = type === 'direct' 
            ? `/direct-chat/messages/${id}/toggle-pin` 
            : `/tasks/comments/${id}/toggle-pin`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const rowSuffixes = [
                    `chat-row-comment-${id}`,
                    `msg-row-${id}`,
                    `floating-msg-row-${id}`,
                    `floating-chat-row-comment-${id}`
                ];
                
                rowSuffixes.forEach(rowId => {
                    const row = document.getElementById(rowId);
                    if (row) {
                        const meta = row.querySelector('.chat-meta');
                        if (meta) {
                            let pinIcon = meta.querySelector('.bi-pin-angle-fill');
                            if (data.is_pinned) {
                                if (!pinIcon) {
                                    pinIcon = document.createElement('i');
                                    pinIcon.className = 'bi bi-pin-angle-fill text-danger me-1';
                                    pinIcon.style.cssText = 'font-size: 10px;';
                                    pinIcon.title = 'Pinned';
                                    meta.insertBefore(pinIcon, meta.querySelector('span'));
                                }
                            } else {
                                if (pinIcon) pinIcon.remove();
                            }
                        }
                        
                        const dropItem = row.querySelector('.text-danger i');
                        if (dropItem) {
                            if (data.is_pinned) {
                                dropItem.className = 'bi bi-pin-angle-fill text-danger';
                                dropItem.parentNode.innerHTML = `<i class="bi bi-pin-angle-fill text-danger"></i> Unpin Message`;
                            } else {
                                dropItem.className = 'bi bi-pin text-muted';
                                dropItem.parentNode.innerHTML = `<i class="bi bi-pin text-muted"></i> Pin Message`;
                            }
                        }
                    }
                });

                if (typeof window.updatePinnedMessagesList === 'function') {
                    window.updatePinnedMessagesList();
                }
            }
        })
        .catch(err => console.error('Error toggling pin:', err));
    };

    window.toggleMessageImportant = function(id, type) {
        let url = type === 'direct' 
            ? `/direct-chat/messages/${id}/toggle-important` 
            : `/tasks/comments/${id}/toggle-important`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const rowSuffixes = [
                    `chat-row-comment-${id}`,
                    `msg-row-${id}`,
                    `floating-msg-row-${id}`,
                    `floating-chat-row-comment-${id}`
                ];
                
                rowSuffixes.forEach(rowId => {
                    const row = document.getElementById(rowId);
                    if (row) {
                        const meta = row.querySelector('.chat-meta');
                        if (meta) {
                            let starIcon = meta.querySelector('.bi-star-fill');
                            if (data.is_important) {
                                if (!starIcon) {
                                    starIcon = document.createElement('i');
                                    starIcon.className = 'bi bi-star-fill text-warning me-1';
                                    starIcon.style.cssText = 'font-size: 10px;';
                                    starIcon.title = 'Starred';
                                    meta.insertBefore(starIcon, meta.querySelector('span'));
                                }
                            } else {
                                if (starIcon) starIcon.remove();
                            }
                        }
                        
                        const dropItem = row.querySelector('.text-warning i');
                        if (dropItem) {
                            if (data.is_important) {
                                dropItem.className = 'bi bi-star-fill text-warning';
                                dropItem.parentNode.innerHTML = `<i class="bi bi-star-fill text-warning"></i> Unstar Message`;
                            } else {
                                dropItem.className = 'bi bi-star text-muted';
                                dropItem.parentNode.innerHTML = `<i class="bi bi-star text-muted"></i> Star Important`;
                            }
                        }
                    }
                });
            }
        })
        .catch(err => console.error('Error toggling important:', err));
    };

    window.scrollToMessage = function(elementId) {
        const el = document.getElementById(elementId);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add premium flashing highlights effect
            const originalBg = el.style.backgroundColor;
            el.style.transition = 'background-color 0.3s ease';
            el.style.backgroundColor = 'rgba(255, 193, 7, 0.25)';
            setTimeout(() => {
                el.style.backgroundColor = originalBg;
                setTimeout(() => {
                    el.style.backgroundColor = 'rgba(255, 193, 7, 0.25)';
                    setTimeout(() => {
                        el.style.backgroundColor = originalBg;
                        setTimeout(() => {
                            el.style.backgroundColor = '';
                        }, 300);
                    }, 300);
                }, 300);
            }, 800);
        }
    };

    window.updatePinnedMessagesList = function() {
        const isFloating = document.getElementById('floating-chat-panel')?.classList.contains('active');
        const containerPrefix = isFloating ? 'floating-' : '';
        const messagesContainer = document.getElementById(isFloating ? 'floating-chat-messages' : 'chat-messages-container');
        const dropdown = document.getElementById(`${containerPrefix}chat-pinned-dropdown`);
        const countBadge = document.getElementById(`${containerPrefix}chat-pinned-count`);
        const listContainer = document.getElementById(`${containerPrefix}chat-pinned-items-container`);

        if (!messagesContainer || !listContainer) return;

        const rows = Array.from(messagesContainer.querySelectorAll('.chat-row'));
        const pinnedItems = [];

        rows.forEach(row => {
            const meta = row.querySelector('.chat-meta');
            if (meta && meta.querySelector('.bi-pin-angle-fill')) {
                const idAttr = row.id;
                const textEl = row.querySelector('.chat-text');
                const noteEl = row.querySelector('.time-log-note-content');
                let contentText = '';
                if (textEl) contentText = textEl.textContent.trim();
                else if (noteEl) contentText = noteEl.textContent.trim();
                else if (row.querySelector('.chat-image-preview') || row.querySelector('.comment-image')) contentText = '[Image]';
                else contentText = '[Attachment]';

                const senderEl = row.querySelector('.chat-sender');
                const senderName = senderEl ? senderEl.textContent.trim() : 'System';

                pinnedItems.push({
                    id: idAttr,
                    sender: senderName,
                    text: contentText
                });
            }
        });

        if (pinnedItems.length > 0) {
            if (dropdown) dropdown.style.display = 'block';
            if (countBadge) {
                countBadge.textContent = pinnedItems.length;
                countBadge.style.display = 'inline-block';
            }
            
            listContainer.innerHTML = '';
            pinnedItems.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <a class="dropdown-item py-2 border-bottom border-light" href="javascript:void(0)" onclick="scrollToMessage('${item.id}')" style="white-space: normal;">
                        <div class="fw-bold text-dark fs-8 mb-0.5">${escapeHtml(item.sender)}</div>
                        <div class="text-muted text-truncate fs-7" style="max-width: 250px;">${escapeHtml(item.text)}</div>
                    </a>
                `;
                listContainer.appendChild(li);
            });
        } else {
            if (countBadge) countBadge.style.display = 'none';
            if (dropdown) dropdown.style.display = 'none';
            listContainer.innerHTML = `
                <li class="text-center py-3 text-muted fs-7">No pinned messages</li>
            `;
        }
    };

    function groupChatMessagesByDate(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        // Remove any previously inserted date headers
        container.querySelectorAll('.chat-date-group-header').forEach(el => el.remove());

        const rows = Array.from(container.querySelectorAll('.chat-row'));
        if (rows.length === 0) return;

        // Helper to format Date to 'dd Mmm yyyy' string
        const formatDateToDMY = (dateObj) => {
            const day = String(dateObj.getDate()).padStart(2, '0');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = months[dateObj.getMonth()];
            const year = dateObj.getFullYear();
            return `${day} ${month} ${year}`;
        };

        const todayObj = new Date();
        const todayStr = formatDateToDMY(todayObj);

        const yesterdayObj = new Date();
        yesterdayObj.setDate(yesterdayObj.getDate() - 1);
        const yesterdayStr = formatDateToDMY(yesterdayObj);

        let lastDate = null;
        rows.forEach(row => {
            const rawDate = row.getAttribute('data-date');
            if (!rawDate) return;

            // Trim in case of any spacing issues
            const msgDate = rawDate.trim();

            if (msgDate !== lastDate) {
                let dateLabel = msgDate;
                if (msgDate === todayStr) {
                    dateLabel = 'Today';
                } else if (msgDate === yesterdayStr) {
                    dateLabel = 'Yesterday';
                }

                const header = document.createElement('div');
                header.className = 'chat-date-group-header text-center my-3 w-100 d-flex justify-content-center';
                header.innerHTML = `
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill shadow-sm fs-8 fw-semibold">
                        ${dateLabel}
                    </span>
                `;
                
                // Insert header before the current row
                row.parentNode.insertBefore(header, row);
                lastDate = msgDate;
            }
        });
    }
</script>

    @auth
    <!-- Global Alert Block Overlay Container -->
    <div id="global-alert-container">
        @if(isset($unconfirmedAlert) && $unconfirmedAlert)
        <div id="global-alert-overlay" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(17, 24, 39, 0.95); z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
            <div class="card border-0 shadow-lg text-center animate__animated animate__fadeInUp" style="width: 100%; max-width: 550px; border-radius: 20px; background: #ffffff;">
                <div class="card-body p-5">
                    <!-- Icon and Heading -->
                    <div class="mb-4">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background-color: #fef2f2 !important; color: #dc2626 !important;">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                            {{ strtoupper($unconfirmedAlert->heading) }}
                        </span>
                    </div>

                    <!-- Alert Message -->
                    <h3 class="fw-extrabold text-dark mb-3" style="font-size: 22px;">{{ $unconfirmedAlert->title }}</h3>
                    <p class="text-secondary mb-4" style="font-size: 14px; line-height: 1.6;">
                        Please read the message carefully. You are required to confirm that you have read and understood this alert to continue using the application.
                    </p>

                    <!-- CAPTCHA and Confirmation Section -->
                    <div class="bg-light p-4 rounded-3 mb-4" style="border-radius: 14px !important; background-color: #f3f4f6 !important;">
                        <label class="form-label text-dark fw-bold mb-2 d-block" style="font-size: 13px;">Security Verification</label>
                        
                        <!-- Code Display Box (HTML Text instead of Canvas) -->
                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                            <div id="alert-captcha-box" class="rounded border bg-white d-flex align-items-center justify-content-center fw-bold text-dark" style="height: 45px; width: 120px; font-size: 28px; font-family: Arial, sans-serif; letter-spacing: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); border-color: #d1d5db !important;">
                                --
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; line-height: 1;" onclick="refreshAlertCaptcha()" title="Refresh Image">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <!-- Input with Onscreen Keyboard Toggle Icon -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" id="alert-captcha-input" class="form-control text-center fw-bold fs-5 py-2.5" placeholder="Enter 2-digit number" maxlength="2" inputmode="numeric" pattern="[0-9]*" required style="border-radius: 10px 0 0 10px; letter-spacing: 2px;">
                                <button class="btn btn-outline-secondary px-3 d-flex align-items-center justify-content-center" type="button" id="alert-keyboard-toggle-btn" style="border-radius: 0 10px 10px 0;" onclick="toggleOnscreenKeyboard()" title="Onscreen Keyboard">
                                    <i class="bi bi-keyboard-fill fs-5"></i>
                                </button>
                            </div>
                            <div id="alert-captcha-error" class="text-danger small mt-2 d-none"></div>
                        </div>

                        <!-- Onscreen Keyboard Grid -->
                        <div id="onscreen-keyboard-container" class="d-none mt-3 p-3 bg-white border rounded" style="border-radius: 14px; max-width: 280px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div class="row g-2">
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('1')">1</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('2')">2</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('3')">3</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('4')">4</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('5')">5</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('6')">6</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('7')">7</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('8')">8</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('9')">9</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-semibold text-secondary border" style="font-size: 12px; background-color: #f3f4f6;" onclick="pressOnscreenKey('clear')">Clear</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('0')">0</button></div>
                                <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-semibold text-secondary border d-flex align-items-center justify-content-center" style="background-color: #f3f4f6;" onclick="pressOnscreenKey('backspace')"><i class="bi bi-backspace-fill fs-5"></i></button></div>
                            </div>
                        </div>
                    </div>

                    <!-- Read and Confirmed Button -->
                    <button type="button" id="alert-confirm-btn" class="btn btn-warning w-100 fw-bold py-3 text-dark text-uppercase" style="border-radius: 12px; letter-spacing: 1px;" onclick="submitAlertConfirmation({{ $unconfirmedAlert->id }})">
                        Read and Confirmed
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal for Task Approvals and Rejections -->
    <div class="modal fade" id="chatActionFeedbackModal" tabindex="-1" aria-labelledby="chatActionFeedbackModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="chatActionFeedbackModalLabel">Task Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="chatActionFeedbackForm" onsubmit="submitChatActionFeedback(event)">
                    <div class="modal-body py-3">
                        <input type="hidden" id="chatActionTaskId">
                        <input type="hidden" id="chatActionType">
                        
                        <div class="mb-3">
                            <label for="chatActionNotes" class="form-label fw-bold text-dark" id="chatActionNotesLabel" style="font-size: 13.5px;">Notes</label>
                            <textarea class="form-control" id="chatActionNotes" rows="3" placeholder="Enter notes..." style="border-radius: 8px; font-size: 13.5px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-sm px-3 border" style="border-radius: 8px;" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold" id="chatActionSubmitBtn" style="border-radius: 8px;">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let alertRingInterval = null;
        let alertPollInterval = null;
        let alertCaptchaToken = '';

        function playAlertRingSound() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                
                const audioCtx = new AudioContext();
                
                function ring() {
                    if (!document.getElementById('global-alert-overlay')) {
                        if (alertRingInterval) clearInterval(alertRingInterval);
                        return;
                    }

                    let time = audioCtx.currentTime;
                    beep(time, 0.12, 880);
                    beep(time + 0.18, 0.12, 880);
                }
                
                function beep(start, duration, freq) {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    
                    gain.gain.setValueAtTime(0, start);
                    gain.gain.linearRampToValueAtTime(0.15, start + 0.02);
                    gain.gain.setValueAtTime(0.15, start + duration - 0.02);
                    gain.gain.linearRampToValueAtTime(0, start + duration);
                    
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    
                    osc.start(start);
                    osc.stop(start + duration);
                }
                
                setTimeout(ring, 500);
                alertRingInterval = setInterval(ring, 3000);
            } catch (e) {
                console.warn('AudioContext not allowed or failed to initialize:', e);
            }
        }

        function refreshAlertCaptcha() {
            const box = document.getElementById('alert-captcha-box');
            if (!box) return;
            
            box.textContent = '--';
            box.style.opacity = '0.5';

            fetch("{{ route('alerts.captcha-code') }}")
                .then(response => response.json())
                .then(data => {
                    box.textContent = data.code;
                    box.style.opacity = '1';
                    alertCaptchaToken = data.token || '';
                })
                .catch(err => {
                    console.error('Error fetching captcha code:', err);
                    box.textContent = 'Err';
                });
                
            document.getElementById('alert-captcha-input').value = '';
            document.getElementById('alert-captcha-error').classList.add('d-none');
        }

        function toggleOnscreenKeyboard() {
            const container = document.getElementById('onscreen-keyboard-container');
            const btn = document.getElementById('alert-keyboard-toggle-btn');
            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
                btn.classList.add('active');
                btn.classList.add('btn-warning');
                btn.classList.remove('btn-outline-secondary');
            } else {
                container.classList.add('d-none');
                btn.classList.remove('active');
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-outline-secondary');
            }
        }

        function pressOnscreenKey(key) {
            const input = document.getElementById('alert-captcha-input');
            const errorDiv = document.getElementById('alert-captcha-error');
            errorDiv.classList.add('d-none');

            if (key === 'clear') {
                input.value = '';
            } else if (key === 'backspace') {
                input.value = input.value.slice(0, -1);
            } else {
                if (input.value.length < 2) {
                    input.value += key;
                }
            }
        }

        function showDynamicAlert(alertId, heading, title) {
            if (document.getElementById('global-alert-overlay')) return;

            if (alertPollInterval) clearInterval(alertPollInterval);

            const overlay = document.createElement('div');
            overlay.id = 'global-alert-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.background = 'rgba(17, 24, 39, 0.95)';
            overlay.style.zIndex = '999999';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.padding = '20px';
            overlay.style.overflowY = 'auto';

            overlay.innerHTML = `
                <div class="card border-0 shadow-lg text-center animate__animated animate__fadeInUp" style="width: 100%; max-width: 550px; border-radius: 20px; background: #ffffff;">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; background-color: #fef2f2 !important; color: #dc2626 !important;">
                                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                            </div>
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                                ${heading.toUpperCase()}
                            </span>
                        </div>

                        <h3 class="fw-extrabold text-dark mb-3" style="font-size: 22px;">${title}</h3>
                        <p class="text-secondary mb-4" style="font-size: 14px; line-height: 1.6;">
                            Please read the message carefully. You are required to confirm that you have read and understood this alert to continue using the application.
                        </p>

                        <div class="bg-light p-4 rounded-3 mb-4" style="border-radius: 14px !important; background-color: #f3f4f6 !important;">
                            <label class="form-label text-dark fw-bold mb-2 d-block" style="font-size: 13px;">Security Verification</label>
                            
                            <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                                <div id="alert-captcha-box" class="rounded border bg-white d-flex align-items-center justify-content-center fw-bold text-dark" style="height: 45px; width: 120px; font-size: 28px; font-family: Arial, sans-serif; letter-spacing: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); border-color: #d1d5db !important;">
                                    --
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; line-height: 1;" onclick="refreshAlertCaptcha()" title="Refresh Image">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" id="alert-captcha-input" class="form-control text-center fw-bold fs-5 py-2.5" placeholder="Enter 2-digit number" maxlength="2" inputmode="numeric" pattern="[0-9]*" required style="border-radius: 10px 0 0 10px; letter-spacing: 2px;">
                                    <button class="btn btn-outline-secondary px-3 d-flex align-items-center justify-content-center" type="button" id="alert-keyboard-toggle-btn" style="border-radius: 0 10px 10px 0;" onclick="toggleOnscreenKeyboard()" title="Onscreen Keyboard">
                                        <i class="bi bi-keyboard-fill fs-5"></i>
                                    </button>
                                </div>
                                <div id="alert-captcha-error" class="text-danger small mt-2 d-none"></div>
                            </div>

                            <div id="onscreen-keyboard-container" class="d-none mt-3 p-3 bg-white border rounded" style="border-radius: 14px; max-width: 280px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                <div class="row g-2">
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('1')">1</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('2')">2</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('3')">3</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('4')">4</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('5')">5</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('6')">6</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('7')">7</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('8')">8</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('9')">9</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-semibold text-secondary border" style="font-size: 12px; background-color: #f3f4f6;" onclick="pressOnscreenKey('clear')">Clear</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-bold text-dark border bg-white" onclick="pressOnscreenKey('0')">0</button></div>
                                    <div class="col-4"><button type="button" class="btn btn-light w-100 py-2.5 fw-semibold text-secondary border d-flex align-items-center justify-content-center" style="background-color: #f3f4f6;" onclick="pressOnscreenKey('backspace')"><i class="bi bi-backspace-fill fs-5"></i></button></div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="alert-confirm-btn" class="btn btn-warning w-100 fw-bold py-3 text-dark text-uppercase" style="border-radius: 12px; letter-spacing: 1px;" onclick="submitAlertConfirmation(${alertId})">
                            Read and Confirmed
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('global-alert-container').appendChild(overlay);
            refreshAlertCaptcha();
            playAlertRingSound();
        }

        function checkPendingAlerts() {
            // Must include Accept: application/json so middleware returns JSON instead of a redirect
            fetch("{{ route('alerts.check-active') }}", {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) return; // silently skip auth redirects (401/403)
                return response.json();
            })
            .then(data => {
                if (data && data.has_alert) {
                    showDynamicAlert(data.alert_id, data.heading, data.title);
                }
            })
            .catch(err => console.warn('Alert poll error:', err));
        }

        function startAlertPolling() {
            if (alertPollInterval) clearInterval(alertPollInterval);
            checkPendingAlerts();
            alertPollInterval = setInterval(checkPendingAlerts, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('global-alert-overlay');
            if (overlay) {
                // Alert was server-rendered on page load
                refreshAlertCaptcha();
                playAlertRingSound();
            } else {
                // No pending alert on load — start polling for live alerts
                startAlertPolling();
            }
        });

        function submitAlertConfirmation(alertId) {
            const input = document.getElementById('alert-captcha-input');
            const code = input.value.trim();
            const errorDiv = document.getElementById('alert-captcha-error');
            const btn = document.getElementById('alert-confirm-btn');

            if (!code || code.length !== 2 || isNaN(code)) {
                errorDiv.textContent = 'Please enter the 2-digit number shown in the box.';
                errorDiv.classList.remove('d-none');
                return;
            }

            errorDiv.classList.add('d-none');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Confirming...';

            fetch("{{ route('alerts.confirm') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    alert_id: alertId,
                    captcha: code,
                    token: alertCaptchaToken
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                if (res.status === 200 && res.body.success) {
                    if (alertRingInterval) clearInterval(alertRingInterval);
                    alertRingInterval = null;
                    const overlayEl = document.getElementById('global-alert-overlay');
                    if (overlayEl) overlayEl.remove();
                    // Restart polling to catch any subsequent alerts without a full page reload
                    startAlertPolling();
                } else {
                    errorDiv.textContent = res.body.message || 'Verification failed. Please try again.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = 'Read and Confirmed';
                    refreshAlertCaptcha();
                }
            })
            .catch(err => {
                console.error(err);
                errorDiv.textContent = 'A system error occurred. Please try again later.';
                errorDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Read and Confirmed';
                refreshAlertCaptcha();
            });
        }

        window.handleCommentApprove = function(event, taskId) {
            event.preventDefault();
            document.getElementById('chatActionTaskId').value = taskId;
            document.getElementById('chatActionType').value = 'approve';
            document.getElementById('chatActionFeedbackModalLabel').textContent = 'Approve Task Completion';
            document.getElementById('chatActionNotesLabel').textContent = 'Approval Notes (optional)';
            document.getElementById('chatActionNotes').value = 'Approved via Chat';
            document.getElementById('chatActionNotes').placeholder = 'Enter approval notes...';
            document.getElementById('chatActionNotes').removeAttribute('required');
            
            const submitBtn = document.getElementById('chatActionSubmitBtn');
            submitBtn.textContent = 'Approve';
            submitBtn.className = 'btn btn-success btn-sm px-3 text-white fw-bold';

            const modal = new bootstrap.Modal(document.getElementById('chatActionFeedbackModal'));
            modal.show();
        };

        window.handleCommentReject = function(event, taskId) {
            event.preventDefault();
            document.getElementById('chatActionTaskId').value = taskId;
            document.getElementById('chatActionType').value = 'reject';
            document.getElementById('chatActionFeedbackModalLabel').textContent = 'Reject Task Completion';
            document.getElementById('chatActionNotesLabel').textContent = 'Rejection Reasons (required)';
            document.getElementById('chatActionNotes').value = '';
            document.getElementById('chatActionNotes').placeholder = 'Enter rejection feedback reasons...';
            document.getElementById('chatActionNotes').setAttribute('required', 'required');
            
            const submitBtn = document.getElementById('chatActionSubmitBtn');
            submitBtn.textContent = 'Reject';
            submitBtn.className = 'btn btn-danger btn-sm px-3 text-white fw-bold';

            const modal = new bootstrap.Modal(document.getElementById('chatActionFeedbackModal'));
            modal.show();
        };

        window.submitChatActionFeedback = function(event) {
            event.preventDefault();
            
            const taskId = document.getElementById('chatActionTaskId').value;
            const actionType = document.getElementById('chatActionType').value;
            const comment = document.getElementById('chatActionNotes').value.trim();

            if (actionType === 'reject' && !comment) {
                alert('Rejection reasons are required.');
                return;
            }

            const submitBtn = document.getElementById('chatActionSubmitBtn');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            const url = actionType === 'approve' 
                ? `/tasks/${taskId}/approve-completion` 
                : `/tasks/${taskId}/reject-completion`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ comment: comment || 'Approved' })
            })
            .then(response => {
                if (response.ok) {
                    const modalEl = document.getElementById('chatActionFeedbackModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                    
                    if (typeof selectTask === 'function') {
                        selectTask(taskId);
                    } else {
                        location.reload();
                    }
                } else {
                    alert(`Failed to ${actionType} task.`);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error(`Error during task ${actionType}:`, error);
                alert('An error occurred.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
        };
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
