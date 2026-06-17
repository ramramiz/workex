<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\WorkTimerController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\BugController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LiveStatusController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LeadCallController;
use App\Http\Controllers\LeadAppointmentController;
use App\Http\Controllers\PerformanceReportController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Live Status Board
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/live-status', [LiveStatusController::class, 'index'])->name('live-status');
        Route::get('/live-status/data', [LiveStatusController::class, 'data'])->name('live-status.data');
    });

    // Work Timer
    Route::get('/work-timer', [WorkTimerController::class, 'index'])->name('work-timer.index');
    Route::post('/work-timer/start-day', [WorkTimerController::class, 'startDay'])->name('work-timer.start-day');
    Route::post('/work-timer/end-day', [WorkTimerController::class, 'endDay'])->name('work-timer.end-day');
    Route::post('/work-timer/start-task/{task}', [WorkTimerController::class, 'startTask'])->name('work-timer.start-task');
    Route::post('/work-timer/pause-task/{log}', [WorkTimerController::class, 'pauseTask'])->name('work-timer.pause-task');
    Route::post('/work-timer/resume-task/{log}', [WorkTimerController::class, 'resumeTask'])->name('work-timer.resume-task');
    Route::post('/work-timer/end-task/{log}', [WorkTimerController::class, 'endTask'])->name('work-timer.end-task');
    Route::get('/work-timer/status', [WorkTimerController::class, 'status'])->name('work-timer.status');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    // Departments & Designations
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['show']);
    Route::resource('designations', \App\Http\Controllers\DesignationController::class)->except(['show']);

    // Clients
    Route::post('clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');
    Route::resource('clients', ClientController::class);

    // Lead Import
    Route::get('leads/import/template', [\App\Http\Controllers\LeadImportController::class, 'downloadTemplate'])->name('leads.import.template');
    Route::get('leads/import', [\App\Http\Controllers\LeadImportController::class, 'showImportForm'])->name('leads.import.form');
    Route::post('leads/import/preview', [\App\Http\Controllers\LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('leads/import/submit', [\App\Http\Controllers\LeadImportController::class, 'submit'])->name('leads.import.submit');

    // Leads
    Route::resource('leads', LeadController::class);
    Route::post('leads/{lead}/follow-up', [LeadController::class, 'addFollowUp'])->name('leads.follow-up');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/requirements', [LeadController::class, 'updateRequirements'])->name('leads.requirements.update');
    Route::post('leads/{lead}/calls', [LeadCallController::class, 'store'])->name('leads.calls.store');
    Route::post('leads/{lead}/appointments', [LeadAppointmentController::class, 'store'])->name('leads.appointments.store');

    // Lead Rooms
    Route::get('lead-rooms', [\App\Http\Controllers\LeadRoomController::class, 'index'])->name('lead-rooms.index');
    Route::post('lead-rooms', [\App\Http\Controllers\LeadRoomController::class, 'store'])->name('lead-rooms.store');
    Route::put('lead-rooms/{room}', [\App\Http\Controllers\LeadRoomController::class, 'update'])->name('lead-rooms.update');
    Route::delete('lead-rooms/{room}', [\App\Http\Controllers\LeadRoomController::class, 'destroy'])->name('lead-rooms.destroy');
    Route::post('lead-rooms/{room}/assign', [\App\Http\Controllers\LeadRoomController::class, 'assign'])->name('lead-rooms.assign');

    // Quotations
    Route::resource('quotations', QuotationController::class);
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/convert-to-project', [QuotationController::class, 'convertToProject'])->name('quotations.convert');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/update-status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::post('projects/{project}/team', [ProjectController::class, 'updateTeam'])->name('projects.team.update');


    // Task Completion & Approvals (Static routes first)
    Route::get('/tasks/approved', [TaskController::class, 'approvedTasks'])->name('tasks.approved');
    Route::get('/tasks/completed-approvals', [TaskController::class, 'completedApprovals'])->name('tasks.completed-approvals');

    // Tasks
    Route::get('tasks/{task}/feed-updates', [TaskController::class, 'getFeedUpdates'])->name('tasks.feed-updates');
    Route::resource('tasks', TaskController::class);

    // Meetings
    Route::resource('meetings', MeetingController::class);

    // Task Completion & Approvals Actions
    Route::post('/tasks/{task}/submit-completion', [TaskController::class, 'submitCompletion'])->name('tasks.submit-completion');
    Route::post('/tasks/{task}/approve-completion', [TaskController::class, 'approveCompletion'])->name('tasks.approve-completion');
    Route::post('/tasks/{task}/reject-completion', [TaskController::class, 'rejectCompletion'])->name('tasks.reject-completion');
    Route::post('tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/files', [TaskController::class, 'uploadFile'])->name('tasks.files.store');
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Daily Reports
    Route::resource('daily-reports', DailyReportController::class);
    Route::post('daily-reports/{report}/approve', [DailyReportController::class, 'approve'])->name('daily-reports.approve');
    Route::post('daily-reports/{report}/reject', [DailyReportController::class, 'reject'])->name('daily-reports.reject');

    // Attendance
    Route::resource('attendance', AttendanceController::class)->only(['index', 'show', 'edit', 'update']);
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Leaves
    Route::resource('leaves', LeaveController::class)->parameters(['leaves' => 'leave']);
    Route::post('leaves/{leave}/approve-tl', [LeaveController::class, 'approveTL'])->name('leaves.approve-tl');
    Route::post('leaves/{leave}/approve-hr', [LeaveController::class, 'approveHR'])->name('leaves.approve-hr');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

    // Bugs
    Route::resource('bugs', BugController::class);
    Route::post('bugs/{bug}/comments', [BugController::class, 'addComment'])->name('bugs.comments.store');
    Route::post('bugs/{bug}/update-status', [BugController::class, 'updateStatus'])->name('bugs.update-status');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');

    // Payments
    Route::resource('payments', PaymentController::class)->except(['edit', 'update']);

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Support Tickets
    Route::resource('support', SupportTicketController::class);
    Route::post('support/{ticket}/replies', [SupportTicketController::class, 'addReply'])->name('support.reply');
    Route::post('support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Unified Tasks Chat
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/tasks/{task}', [ChatController::class, 'show'])->name('chat.show');

    // Custom Domain Mailbox
    Route::get('mailbox', [\App\Http\Controllers\MailboxController::class, 'index'])->name('mailbox.index');
    Route::get('mailbox/official', [\App\Http\Controllers\MailboxController::class, 'officialIndex'])->name('mailbox.official.index');
    Route::post('mailbox/fetch-new', [\App\Http\Controllers\MailboxController::class, 'fetchNew'])->name('mailbox.fetch-new');
    Route::get('mailbox/official/{uid}', [\App\Http\Controllers\MailboxController::class, 'officialShow'])->name('mailbox.official.show');
    Route::post('mailbox/messages', [\App\Http\Controllers\MailboxController::class, 'store'])->name('mailbox.store');
    Route::delete('mailbox/official/{uid}', [\App\Http\Controllers\MailboxController::class, 'officialDestroy'])->name('mailbox.official.destroy');
    Route::post('mailbox/settings', [\App\Http\Controllers\MailboxController::class, 'saveSettings'])->name('mailbox.settings.save');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/daily-work', [ReportController::class, 'dailyWork'])->name('reports.daily-work');
    Route::get('reports/project-progress', [ReportController::class, 'projectProgress'])->name('reports.project-progress');
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/leaves', [ReportController::class, 'leaves'])->name('reports.leaves');
    Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/telecaller-performance', [PerformanceReportController::class, 'index'])->name('reports.telecaller-performance');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/users/quick-store-team-leader', [UserController::class, 'quickStoreTeamLeader'])->name('users.quick-store-team-leader');
    Route::resource('settings/users', UserController::class)->names([
        'index' => 'users.index', 'create' => 'users.create', 'store' => 'users.store',
        'edit' => 'users.edit', 'update' => 'users.update', 'destroy' => 'users.destroy',
    ])->except(['show']);

    Route::get('settings/users/{user}/emails', [UserController::class, 'emails'])->name('users.emails.index');
    Route::post('settings/users/{user}/emails', [UserController::class, 'storeEmail'])->name('users.emails.store');
    Route::delete('settings/users/{user}/emails/{email}', [UserController::class, 'destroyEmail'])->name('users.emails.destroy');

    // AI Correction Route
    Route::post('/grammar/correct', [\App\Http\Controllers\AiController::class, 'correct'])->name('ai.correct');

    // Profile (Breeze)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Telecaller Start Today Work Section
    Route::get('start-work', [\App\Http\Controllers\LeadRoomWorkController::class, 'index'])->name('leads.start-work.index');
    Route::get('start-work/room/{room}', [\App\Http\Controllers\LeadRoomWorkController::class, 'room'])->name('leads.start-work.room');
    Route::post('start-work/room/{room}/start', [\App\Http\Controllers\LeadRoomWorkController::class, 'startWork'])->name('leads.start-work.start');
    Route::get('start-work/room/{room}/leads', [\App\Http\Controllers\LeadRoomWorkController::class, 'leads'])->name('leads.start-work.leads');
    Route::post('start-work/room/{room}/pause', [\App\Http\Controllers\LeadRoomWorkController::class, 'pauseWork'])->name('leads.start-work.pause');
    Route::post('start-work/room/{room}/resume', [\App\Http\Controllers\LeadRoomWorkController::class, 'resumeWork'])->name('leads.start-work.resume');
    Route::post('start-work/stop', [\App\Http\Controllers\LeadRoomWorkController::class, 'stopWork'])->name('leads.start-work.stop');
    Route::get('start-work/room/{room}/summary/{session}', [\App\Http\Controllers\LeadRoomWorkController::class, 'summary'])->name('leads.start-work.summary');
    Route::post('start-work/current-call', [\App\Http\Controllers\LeadRoomWorkController::class, 'setCurrentCall'])->name('leads.start-work.set-current-call');
    Route::post('start-work/current-call/clear', [\App\Http\Controllers\LeadRoomWorkController::class, 'clearCurrentCall'])->name('leads.start-work.clear-current-call');

    // Admin Room Session Approvals
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('admin/telecaller-sessions', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminIndex'])->name('admin.telecaller-sessions.index');
        Route::post('admin/telecaller-sessions/{session}/approve', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminApprove'])->name('admin.telecaller-sessions.approve');
        Route::post('admin/telecaller-sessions/{session}/reject', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminReject'])->name('admin.telecaller-sessions.reject');
    });

    // Reseller Management
    Route::middleware(['role:reseller'])->group(function () {
        Route::get('reseller/dashboard', [\App\Http\Controllers\ResellerController::class, 'index'])->name('reseller.dashboard');
        Route::get('reseller/companies/create', [\App\Http\Controllers\ResellerController::class, 'create'])->name('reseller.companies.create');
        Route::post('reseller/companies', [\App\Http\Controllers\ResellerController::class, 'store'])->name('reseller.companies.store');
        Route::post('reseller/companies/{company}/toggle-status', [\App\Http\Controllers\ResellerController::class, 'toggleStatus'])->name('reseller.companies.toggle-status');
    });
});
