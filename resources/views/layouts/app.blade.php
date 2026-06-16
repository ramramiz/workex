<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            background: white; border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; z-index: 999;
            transition: left 0.3s ease; gap: 12px;
        }
        #topnav.sidebar-collapsed { left: var(--sidebar-collapsed-width); }
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
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
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
            color: #475569;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            background: transparent;
        }
        .topnav-status-pill:hover {
            color: #1e293b;
            background: rgba(0, 0, 0, 0.04);
        }
        .topnav-status-pill.active {
            background: #4f46e5;
            color: white;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        }
        .topnav-status-pill .count {
            font-size: 11px;
            font-weight: 700;
            background: #cbd5e1;
            color: #475569;
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
            background: white; display: flex; align-items: center; justify-content: center;
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
            border: 1px solid var(--border-color); background: white;
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
            background: white; border-radius: 16px; padding: 24px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .card { border: 1px solid var(--border-color); border-radius: 16px; box-shadow: none; }
        .card-header {
            background: white; border-bottom: 1px solid var(--border-color);
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
        .table tbody tr:hover { background: #f8fafc; }

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
    </style>

    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

@if(!isset($noSidebar) || !$noSidebar)
<!-- SIDEBAR -->
<aside id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></div>
        <div class="brand-text d-flex flex-column" style="line-height: 1.1;">
            <div>Work<span>eX</span></div>
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
            <div class="sidebar-section-label">Main</div>
            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-title="Dashboard">
                <i class="bi bi-grid-1x2-fill nav-icon"></i><span class="nav-text">Dashboard</span>
            </a>
            @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('live-status') }}" class="sidebar-item {{ request()->routeIs('live-status') ? 'active' : '' }}" data-title="Live Status">
            <i class="bi bi-broadcast nav-icon"></i><span class="nav-text">Live Status Board</span>
        </a>
        @endif
        <a href="{{ route('chat.index') }}" class="sidebar-item {{ request()->routeIs('chat*') ? 'active' : '' }}" data-title="Chat">
            <i class="bi bi-chat-fill nav-icon"></i><span class="nav-text">Chat Workspace</span>
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
        <div class="sidebar-section-label">Work</div>
        @if(auth()->user()->isEmployee() || auth()->user()->isTeamLeader())
        <a href="{{ route('work-timer.index') }}" class="sidebar-item {{ request()->routeIs('work-timer*') ? 'active' : '' }}" data-title="Work Timer">
            <i class="bi bi-stopwatch-fill nav-icon"></i><span class="nav-text">Work Timer</span>
        </a>
        @endif
        <a href="{{ route('tasks.index') }}" class="sidebar-item {{ request()->routeIs('tasks*') && !request()->routeIs('tasks.completed-approvals*') ? 'active' : '' }}" data-title="Tasks">
            <i class="bi bi-check2-square nav-icon"></i><span class="nav-text">Tasks</span>
        </a>
        <a href="{{ route('daily-reports.index') }}" class="sidebar-item {{ request()->routeIs('daily-reports*') ? 'active' : '' }}" data-title="Daily Reports">
            <i class="bi bi-journal-text nav-icon"></i><span class="nav-text">Daily Reports</span>
        </a>
        <a href="{{ route('meetings.index') }}" class="sidebar-item {{ request()->routeIs('meetings*') ? 'active' : '' }}" data-title="Meetings">
            <i class="bi bi-chat-left-quote nav-icon"></i><span class="nav-text">Meetings & Discussions</span>
        </a>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->email === 'souban.techsoul@gmail.com')
        <a href="{{ route('tasks.completed-approvals') }}" class="sidebar-item {{ request()->routeIs('tasks.completed-approvals*') ? 'active' : '' }}" data-title="Approvals">
            <i class="bi bi-patch-check-fill nav-icon"></i><span class="nav-text">Approve Completed Work</span>
        </a>
        @endif
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.telecaller-sessions.index') }}" class="sidebar-item {{ request()->routeIs('admin.telecaller-sessions*') ? 'active' : '' }}" data-title="Room Approvals">
            <i class="bi bi-clipboard-check nav-icon"></i><span class="nav-text">Room Work Approvals</span>
        </a>
        @endif

        <div class="sidebar-section-label">Projects</div>
        <a href="{{ route('projects.index') }}" class="sidebar-item {{ request()->routeIs('projects*') ? 'active' : '' }}" data-title="Projects">
            <i class="bi bi-kanban-fill nav-icon"></i><span class="nav-text">Projects</span>
        </a>
        @if(auth()->user()->isAdminOrAbove())
        <a href="{{ route('clients.index') }}" class="sidebar-item {{ request()->routeIs('clients*') ? 'active' : '' }}" data-title="Clients">
            <i class="bi bi-building nav-icon"></i><span class="nav-text">Clients</span>
        </a>
        @endif
        @endif

        @if(auth()->user()->isAdminOrAbove())
        <div class="sidebar-section-label">Leads</div>
        <a href="{{ route('leads.index') }}" class="sidebar-item {{ request()->routeIs('leads*') && !request()->routeIs('leads.start-work*') ? 'active' : '' }}" data-title="Leads">
            <i class="bi bi-funnel-fill nav-icon"></i><span class="nav-text">Leads & Enquiries</span>
        </a>
        @endif

        @if(auth()->user()->isTelecaller())
        <div class="sidebar-section-label">Leads</div>
        <a href="{{ route('leads.start-work.index') }}" class="sidebar-item {{ request()->routeIs('leads.start-work*') ? 'active' : '' }}" data-title="Start Today Work">
            <i class="bi bi-play-circle-fill nav-icon"></i><span class="nav-text">Start Today Work</span>
        </a>
        @endif

        @if(!auth()->user()->isTelecaller())
        @if(auth()->user()->isAdminOrAbove())
        <a href="{{ route('quotations.index') }}" class="sidebar-item {{ request()->routeIs('quotations*') ? 'active' : '' }}" data-title="Quotations">
            <i class="bi bi-file-earmark-text-fill nav-icon"></i><span class="nav-text">Quotations</span>
        </a>
        @endif
        <a href="{{ route('bugs.index') }}" class="sidebar-item {{ request()->routeIs('bugs*') ? 'active' : '' }}" data-title="Bug Tracker">
            <i class="bi bi-bug-fill nav-icon"></i><span class="nav-text">Bug Tracker</span>
        </a>
        @endif

        @if(!auth()->user()->isClient())
        <div class="sidebar-section-label">People</div>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
        <a href="{{ route('employees.index') }}" class="sidebar-item {{ request()->routeIs('employees*') ? 'active' : '' }}" data-title="Employees">
            <i class="bi bi-people-fill nav-icon"></i><span class="nav-text">Employees</span>
        </a>
        @endif
        @if(!auth()->user()->isTelecaller())
        <a href="{{ route('attendance.index') }}" class="sidebar-item {{ request()->routeIs('attendance*') ? 'active' : '' }}" data-title="Attendance">
            <i class="bi bi-calendar2-check-fill nav-icon"></i><span class="nav-text">Attendance</span>
        </a>
        @endif
        <a href="{{ route('leaves.index') }}" class="sidebar-item {{ request()->routeIs('leaves*') ? 'active' : '' }}" data-title="Leaves">
            <i class="bi bi-calendar-x-fill nav-icon"></i><span class="nav-text">Leave Management</span>
        </a>
        @endif

        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
        <div class="sidebar-section-label">Finance</div>
        <a href="{{ route('invoices.index') }}" class="sidebar-item {{ request()->routeIs('invoices*') ? 'active' : '' }}" data-title="Invoices">
            <i class="bi bi-receipt nav-icon"></i><span class="nav-text">Invoices</span>
        </a>
        <a href="{{ route('payments.index') }}" class="sidebar-item {{ request()->routeIs('payments*') ? 'active' : '' }}" data-title="Payments">
            <i class="bi bi-credit-card-fill nav-icon"></i><span class="nav-text">Payments</span>
        </a>
        <a href="{{ route('expenses.index') }}" class="sidebar-item {{ request()->routeIs('expenses*') ? 'active' : '' }}" data-title="Expenses">
            <i class="bi bi-cash-stack nav-icon"></i><span class="nav-text">Expenses</span>
        </a>
        @endif

        <div class="sidebar-section-label">More</div>
        @if(!auth()->user()->isTelecaller())
        <a href="{{ route('support.index') }}" class="sidebar-item {{ request()->routeIs('support*') ? 'active' : '' }}" data-title="Support">
            <i class="bi bi-headset nav-icon"></i><span class="nav-text">Support / AMC</span>
        </a>
        @endif

        @if(auth()->user()->isTelecaller() || auth()->user()->isAdminOrAbove() || auth()->user()->isHR() || auth()->user()->isTeamLeader())
        <a href="{{ route('reports.telecaller-performance') }}" class="sidebar-item {{ request()->routeIs('reports.telecaller-performance*') ? 'active' : '' }}" data-title="Performance">
            <i class="bi bi-graph-up-arrow nav-icon"></i><span class="nav-text">Telecaller Performance</span>
        </a>
        @endif

        @if(!auth()->user()->isTelecaller())
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR() || auth()->user()->isAccounts())
        <a href="{{ route('reports.index') }}" class="sidebar-item {{ request()->routeIs('reports*') && !request()->routeIs('reports.telecaller-performance*') ? 'active' : '' }}" data-title="Reports">
            <i class="bi bi-bar-chart-fill nav-icon"></i><span class="nav-text">Reports</span>
        </a>
        @endif
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('activity-logs.index') }}" class="sidebar-item {{ request()->routeIs('activity-logs*') ? 'active' : '' }}" data-title="Activity Logs">
            <i class="bi bi-clock-history nav-icon"></i><span class="nav-text">Activity Logs</span>
        </a>
        <a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings*') ? 'active' : '' }}" data-title="Settings">
            <i class="bi bi-gear-fill nav-icon"></i><span class="nav-text">Settings</span>
        </a>
        @endif
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
<header id="topnav">
    <div class="topnav-left">
        <button class="btn-sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                @yield('breadcrumb')
                <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
            </ol>
        </nav>
    </div>

    @yield('topnav-middle')    <div class="topnav-right">
        @if(!auth()->user()->isReseller())
            @php
                $activeTaskLog = \App\Models\TaskTimeLog::where('user_id', auth()->id())->where('status', 'running')->with('task')->first();
            @endphp
             @if(session('active_room_work'))
                 @php
                     $activeRoomWork = session('active_room_work');
                     $roomName = \App\Models\LeadRoom::find($activeRoomWork['room_id'])?->name ?? 'Room';
                 @endphp
                <a href="{{ route('leads.start-work.leads', $activeRoomWork['room_id']) }}" class="work-timer-badge working d-none d-md-flex align-items-center gap-2 text-decoration-none" id="nav-room-timer" data-start-time="{{ $activeRoomWork['started_at'] ?? '' }}" data-status="{{ $activeRoomWork['status'] ?? '' }}" data-accumulated="{{ $activeRoomWork['accumulated_seconds'] ?? 0 }}" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#fffbeb' : '#f1f5f9' }}; color: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#b45309' : '#475569' }}; border: 1px solid {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#fde68a' : '#cbd5e1' }}; cursor: pointer;">
                    <span class="status-dot {{ ($activeRoomWork['status'] ?? '') === 'active' ? 'working' : '' }}" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#d97706' : '#94a3b8' }};"></span>
                    <span style="font-size: 12px; font-weight: 600; max-width: 150px;" class="text-truncate">Room: {{ $roomName }}</span>
                    <span class="badge text-white ms-1" id="nav-room-timer-counter" style="background: {{ ($activeRoomWork['status'] ?? '') === 'active' ? '#d97706' : '#94a3b8' }}; font-size: 11px;">00:00:00</span>
                </a>
            @elseif($activeTaskLog)
                <a href="{{ route('tasks.show', $activeTaskLog->task) }}" class="work-timer-badge working d-none d-md-flex align-items-center gap-2 text-decoration-none" id="nav-task-timer" data-start-time="{{ $activeTaskLog->started_at->toISOString() }}" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; cursor: pointer;">
                    <span class="status-dot working"></span>
                    <span style="font-size: 12px; font-weight: 600; max-width: 150px;" class="text-truncate">Task: {{ $activeTaskLog->task->title }}</span>
                    <span class="badge text-white ms-1" id="nav-timer-counter" style="background: #4f46e5; font-size: 11px;">00:00:00</span>
                </a>
            @else
                <a href="{{ auth()->user()->isTelecaller() ? route('leads.start-work.index') : route('tasks.index') }}" class="work-timer-badge text-decoration-none d-none d-md-flex align-items-center gap-2" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                    <i class="bi bi-play-fill text-secondary"></i> Start Task
                </a>
            @endif

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
    // Global notification sound utility using Web Audio API
    window.playNotificationSound = function() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const audioCtx = new AudioContext();
            const now = audioCtx.currentTime;
            
            // Note 1 (E5, 659.25Hz)
            const osc1 = audioCtx.createOscillator();
            const gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.15, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.4);
            
            // Note 2 (A5, 880Hz)
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880.00, now + 0.08);
            gain2.gain.setValueAtTime(0.15, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
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
            fetch("{{ route('notifications.unread-count') }}")
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
@stack('scripts')

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
    @stack('scripts')
</body>
</html>
