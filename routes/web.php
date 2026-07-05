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
use App\Http\Controllers\ProformaInvoiceController;
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
use App\Http\Controllers\SalaryDisbursalController;
use App\Http\Controllers\JobVacancyController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\PublicJobVacancyController;
use App\Http\Controllers\InternController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/diagnose-storage', function () {
    if (request()->query('secret') !== 'workex-storage-fix') {
        abort(404);
    }

    $storageLinkPath = public_path('storage');
    $storageRealPath = storage_path('app/public');
    
    $output = "<h1>Storage Link Diagnostics</h1>";
    $output .= "<p><b>Public Storage Link Path:</b> {$storageLinkPath}</p>";
    $output .= "<p><b>Actual Storage Directory Path:</b> {$storageRealPath}</p>";
    
    // Check actual directory
    if (is_dir($storageRealPath)) {
        $output .= "<p style='color: green;'>âœ” Actual storage directory exists.</p>";
        $output .= "<p>Actual storage directory permissions: " . substr(sprintf('%o', fileperms($storageRealPath)), -4) . "</p>";
    } else {
        $output .= "<p style='color: red;'>âœ˜ Actual storage directory does NOT exist at {$storageRealPath}!</p>";
        // Try to create it
        if (mkdir($storageRealPath, 0755, true)) {
            $output .= "<p style='color: green;'>âœ” Created actual storage directory.</p>";
        } else {
            $output .= "<p style='color: red;'>âœ˜ Failed to create actual storage directory.</p>";
        }
    }
    
    // Check reports directory
    $reportsPath = $storageRealPath . '/reports';
    if (is_dir($reportsPath)) {
        $output .= "<p style='color: green;'>âœ” 'reports' directory exists inside actual storage.</p>";
        $output .= "<p>'reports' directory permissions: " . substr(sprintf('%o', fileperms($reportsPath)), -4) . "</p>";
        
        // List files in reports directory
        $files = scandir($reportsPath);
        $output .= "<p><b>Files in 'reports':</b></p><ul>";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $reportsPath . '/' . $file;
                $perms = substr(sprintf('%o', fileperms($filePath)), -4);
                $size = filesize($filePath);
                $output .= "<li>{$file} (Size: {$size} bytes, Perms: {$perms})</li>";
            }
        }
        $output .= "</ul>";
    } else {
        $output .= "<p style='color: orange;'>âš  'reports' directory does NOT exist yet. (It will be created when a report is saved).</p>";
    }

    // Check public storage link
    if (file_exists($storageLinkPath) || is_link($storageLinkPath)) {
        $isLink = is_link($storageLinkPath) ? 'Yes' : 'No';
        $output .= "<p><b>Is public/storage a symbolic link?</b> {$isLink}</p>";
        
        if (is_link($storageLinkPath)) {
            $target = readlink($storageLinkPath);
            $output .= "<p><b>Link target:</b> {$target}</p>";
            if (file_exists($target)) {
                $output .= "<p style='color: green;'>âœ” Link target exists and is accessible.</p>";
            } else {
                $output .= "<p style='color: red;'>âœ˜ Link target does NOT exist! (Broken symbolic link)</p>";
            }
        } else {
            $output .= "<p style='color: orange;'>âš  public/storage exists but is a regular DIRECTORY/FILE, not a symbolic link! This will prevent proper symlinking.</p>";
        }
    } else {
        $output .= "<p style='color: red;'>âœ˜ public/storage does NOT exist at all.</p>";
    }

    // Actions
    if (request()->has('fix')) {
        $output .= "<h2>Fixing storage link...</h2>";
        
        // Remove existing link/directory if it exists
        if (file_exists($storageLinkPath) || is_link($storageLinkPath)) {
            $removed = @rmdir($storageLinkPath) || @unlink($storageLinkPath);
            if ($removed) {
                $output .= "<p style='color: green;'>âœ” Successfully removed existing symbolic link/directory.</p>";
            } else {
                // If rmdir and unlink failed, it might be a real non-empty directory. Rename it to preserve files.
                $backupPath = $storageLinkPath . '_backup_' . time();
                if (rename($storageLinkPath, $backupPath)) {
                    $output .= "<p style='color: green;'>âœ” Successfully renamed public/storage directory to {$backupPath} to free up path.</p>";
                } else {
                    $output .= "<p style='color: red;'>âœ˜ Failed to remove or rename public/storage directory.</p>";
                }
            }
        }
        
        // Recreate storage link using PHP symlink
        try {
            if (symlink($storageRealPath, $storageLinkPath)) {
                $output .= "<p style='color: green;'><b>âœ” Successfully created symbolic link via PHP symlink()!</b></p>";
            } else {
                $output .= "<p style='color: red;'>âœ˜ symlink() returned false.</p>";
                // Try Artisan storage:link
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                $artisanOutput = \Illuminate\Support\Facades\Artisan::output();
                $output .= "<p>Artisan storage:link output: <pre>{$artisanOutput}</pre></p>";
            }
        } catch (\Throwable $e) {
            $output .= "<p style='color: red;'>âœ˜ Error: {$e->getMessage()}</p>";
            // Try Artisan storage:link
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                $artisanOutput = \Illuminate\Support\Facades\Artisan::output();
                $output .= "<p>Artisan storage:link output: <pre>{$artisanOutput}</pre></p>";
            } catch (\Throwable $ex) {
                $output .= "<p style='color: red;'>âœ˜ Artisan storage:link also failed: {$ex->getMessage()}</p>";
            }
        }
        
        $output .= "<p><a href='/diagnose-storage?secret=workex-storage-fix' style='display:inline-block;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:4px;'>Refresh Diagnosis</a></p>";
    } else {
        $output .= "<p><a href='/diagnose-storage?secret=workex-storage-fix&fix=1' style='display:inline-block;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:4px;'>Attempt Auto-Fix (Recreate Symlink)</a></p>";
    }
    
    return $output;
});

// Auth routes (Breeze)
require __DIR__.'/auth.php';

// Public Careers & Job Applications
Route::get('careers/{token}', [PublicJobVacancyController::class, 'show'])->name('careers.vacancy.show');
Route::post('careers/{token}/apply', [PublicJobVacancyController::class, 'apply'])->name('careers.vacancy.apply');
Route::get('careers/{token}/success', [PublicJobVacancyController::class, 'success'])->name('careers.success');

// Public Certificate Verification
Route::get('verify-certificate/{code}', [InternController::class, 'verifyPublic'])
    ->name('interns.verify.public')
    ->where('code', '.*');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search (AJAX)
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global.search');

    // Job Vacancies & Hiring
    Route::middleware(['role:super-admin,admin,hr'])->group(function () {
        Route::get('job-vacancies/mail-logs', [JobVacancyController::class, 'mailLogs'])->name('job-vacancies.mail-logs');
        Route::resource('job-vacancies', JobVacancyController::class);
        Route::get('job-vacancies/{job_vacancy}/applications', [JobVacancyController::class, 'applications'])->name('job-vacancies.applications');
        Route::patch('job-applications/{application}/status', [JobApplicationController::class, 'updateStatus'])->name('job-applications.update-status');
        Route::post('job-applications/schedule-interview', [JobApplicationController::class, 'scheduleInterview'])->name('job-applications.schedule-interview');
        Route::delete('job-applications/{application}', [JobApplicationController::class, 'destroy'])->name('job-applications.destroy');
        
        // Interns
        Route::get('interns/{intern}/certificate', [InternController::class, 'generateCertificate'])->name('interns.certificate');
        Route::get('interns/{intern}/qr-code', [InternController::class, 'downloadQrCode'])->name('interns.qr-code');
        Route::resource('interns', InternController::class);
    });

    // Live Status Board
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('/live-status', [LiveStatusController::class, 'index'])->name('live-status');
        Route::get('/live-status/data', [LiveStatusController::class, 'data'])->name('live-status.data');
        Route::get('/live-status/telecaller/{user}/room/{room}', [LiveStatusController::class, 'telecallerRoomCalls'])->name('live-status.telecaller-room-calls');
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
    Route::get('employees/{employee}/permissions', [EmployeeController::class, 'getPermissions'])->name('employees.permissions.get');
    Route::post('employees/{employee}/permissions', [EmployeeController::class, 'updatePermissions'])->name('employees.permissions.update');
    Route::post('employees/{employee}/login-as', [EmployeeController::class, 'loginAs'])->name('employees.login-as');
    Route::post('employees/return-account', [EmployeeController::class, 'returnAccount'])->name('employees.return-account');

    // Employee Session Management
    Route::get('users/{user}/sessions', [\App\Http\Controllers\UserSessionController::class, 'index'])->name('users.sessions.index');
    Route::delete('users/{user}/sessions/{sessionId}', [\App\Http\Controllers\UserSessionController::class, 'destroy'])->name('users.sessions.destroy');
    Route::delete('users/{user}/sessions', [\App\Http\Controllers\UserSessionController::class, 'destroyAll'])->name('users.sessions.destroy-all');

    // Departments & Designations
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['show']);
    Route::resource('designations', \App\Http\Controllers\DesignationController::class)->except(['show']);

    // Clients
    Route::post('clients/quick-store', [ClientController::class, 'quickStore'])->name('clients.quick-store');
    Route::get('clients/import/template', [ClientController::class, 'downloadTemplate'])->name('clients.import.template');
    Route::post('clients/import/preview', [ClientController::class, 'preview'])->name('clients.import.preview');
    Route::post('clients/import/submit', [ClientController::class, 'submit'])->name('clients.import.submit');
    Route::resource('clients', ClientController::class);

    // Lead Import
    Route::get('leads/import/template', [\App\Http\Controllers\LeadImportController::class, 'downloadTemplate'])->name('leads.import.template');
    Route::get('leads/import', [\App\Http\Controllers\LeadImportController::class, 'showImportForm'])->name('leads.import.form');
    Route::post('leads/import/preview', [\App\Http\Controllers\LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('leads/import/submit', [\App\Http\Controllers\LeadImportController::class, 'submit'])->name('leads.import.submit');

    // Leads
    Route::post('leads/export-customer-leads', [LeadController::class, 'exportCustomerLeads'])->name('leads.export-customer-leads');
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

    // Projects, Meetings, Daily Reports, Attendance, and Bug Listings restricted to non-employees
    Route::middleware(['role:super-admin,admin,team-leader,hr,accounts'])->group(function () {
        // Projects
        Route::get('projects/previews', [ProjectController::class, 'previews'])->name('projects.previews');
        Route::get('projects/import/template', [ProjectController::class, 'downloadTemplate'])->name('projects.import.template');
        Route::post('projects/import/preview', [ProjectController::class, 'preview'])->name('projects.import.preview');
        Route::post('projects/import/submit', [ProjectController::class, 'submit'])->name('projects.import.submit');
        Route::resource('projects', ProjectController::class);
        Route::post('projects/{project}/update-status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
        Route::post('projects/{project}/team', [ProjectController::class, 'updateTeam'])->name('projects.team.update');
        Route::post('projects/{project}/discontinue', [ProjectController::class, 'discontinue'])->name('projects.discontinue');

        // Meetings
        Route::resource('meetings', MeetingController::class);

        // Daily Reports
        Route::resource('daily-reports', DailyReportController::class);
        Route::post('daily-reports/{report}/approve', [DailyReportController::class, 'approve'])->name('daily-reports.approve');
        Route::post('daily-reports/{report}/reject', [DailyReportController::class, 'reject'])->name('daily-reports.reject');

        // Bugs listing & actions (except creation/comments)
        Route::get('bugs', [BugController::class, 'index'])->name('bugs.index');
        Route::get('bugs/create', [BugController::class, 'create'])->name('bugs.create');
        Route::get('bugs/{bug}', [BugController::class, 'show'])->name('bugs.show');
        Route::get('bugs/{bug}/edit', [BugController::class, 'edit'])->name('bugs.edit');
        Route::put('bugs/{bug}', [BugController::class, 'update'])->name('bugs.update');
        Route::patch('bugs/{bug}', [BugController::class, 'update'])->name('bugs.update');
        Route::delete('bugs/{bug}', [BugController::class, 'destroy'])->name('bugs.destroy');
        Route::post('bugs/{bug}/update-status', [BugController::class, 'updateStatus'])->name('bugs.update-status');
    });

    // Publicly accessible Bug creation/comment endpoints for all authenticated users
    Route::post('bugs', [BugController::class, 'store'])->name('bugs.store');
    Route::post('bugs/{bug}/comments', [BugController::class, 'addComment'])->name('bugs.comments.store');

    // Restored Tasks Routes
    Route::get('/tasks/approved', [TaskController::class, 'approvedTasks'])->name('tasks.approved');
    Route::get('/tasks/completed-approvals', [TaskController::class, 'completedApprovals'])->name('tasks.completed-approvals');
    Route::get('tasks/{task}/feed-updates', [TaskController::class, 'getFeedUpdates'])->name('tasks.feed-updates');
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/submit-completion', [TaskController::class, 'submitCompletion'])->name('tasks.submit-completion');
    Route::post('/tasks/{task}/approve-completion', [TaskController::class, 'approveCompletion'])->name('tasks.approve-completion');
    Route::post('/tasks/{task}/reject-completion', [TaskController::class, 'rejectCompletion'])->name('tasks.reject-completion');
    Route::post('tasks/{task}/comments', [TaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('tasks/comments/{comment}/edit', [TaskController::class, 'editComment'])->name('tasks.comments.edit');
    Route::post('tasks/comments/{comment}/toggle-pin', [TaskController::class, 'toggleCommentPin'])->name('tasks.comments.toggle-pin');
    Route::post('tasks/comments/{comment}/toggle-important', [TaskController::class, 'toggleCommentImportant'])->name('tasks.comments.toggle-important');
    Route::post('tasks/{task}/files', [TaskController::class, 'uploadFile'])->name('tasks.files.store');
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Restored Leaves Routes
    Route::resource('leaves', LeaveController::class)->parameters(['leaves' => 'leave']);
    Route::post('leaves/{leave}/approve-tl', [LeaveController::class, 'approveTL'])->name('leaves.approve-tl');
    Route::post('leaves/{leave}/approve-hr', [LeaveController::class, 'approveHR'])->name('leaves.approve-hr');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');

    // Proforma Invoices
    Route::resource('proforma-invoices', ProformaInvoiceController::class);
    Route::get('proforma-invoices/{proforma_invoice}/pdf', [ProformaInvoiceController::class, 'pdf'])->name('proforma-invoices.pdf');
    Route::post('proforma-invoices/{proforma_invoice}/send', [ProformaInvoiceController::class, 'send'])->name('proforma-invoices.send');
    Route::post('proforma-invoices/{proforma_invoice}/convert', [ProformaInvoiceController::class, 'convertToInvoice'])->name('proforma-invoices.convert');

    // Payments
    Route::resource('payments', PaymentController::class)->except(['edit', 'update']);

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Banks
    Route::resource('banks', \App\Http\Controllers\BankController::class);

    // Investors
    Route::resource('investors', \App\Http\Controllers\InvestorController::class);
    Route::post('investors/{investor}/transactions', [\App\Http\Controllers\InvestorController::class, 'storeTransaction'])->name('investors.transactions.store');

    // Hosting Providers
    Route::resource('hosting-providers', \App\Http\Controllers\HostingProviderController::class);

    // Domain Registrations
    Route::resource('domain-registrations', \App\Http\Controllers\DomainRegistrationController::class);

    // Project AMCs
    Route::get('project-amcs/import/template', [\App\Http\Controllers\ProjectAmcController::class, 'downloadTemplate'])->name('project-amcs.import.template');
    Route::post('project-amcs/import/preview', [\App\Http\Controllers\ProjectAmcController::class, 'preview'])->name('project-amcs.import.preview');
    Route::post('project-amcs/import/submit', [\App\Http\Controllers\ProjectAmcController::class, 'submit'])->name('project-amcs.import.submit');
    Route::resource('project-amcs', \App\Http\Controllers\ProjectAmcController::class);
    Route::post('project-amcs/{project_amc}/logs', [\App\Http\Controllers\ProjectAmcController::class, 'storeLog'])->name('project-amcs.logs.store');

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
    Route::get('chat/unified-list', [ChatController::class, 'getUnifiedList'])->name('chat.unified-list');
    Route::get('chat/unread-counts', [ChatController::class, 'getUnreadCounts'])->name('chat.unread-counts');
    Route::get('chat/tasks/{task}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('chat/employees/{employee}/tasks', [ChatController::class, 'getEmployeeTasks'])->name('chat.employees.tasks');

    // Direct Message Chat
    Route::get('direct-chat', [\App\Http\Controllers\DirectChatController::class, 'index'])->name('direct-chat.index');
    Route::get('direct-chat/updates', [\App\Http\Controllers\DirectChatController::class, 'getUpdates'])->name('direct-chat.updates');
    Route::get('direct-chat/messages/{user}', [\App\Http\Controllers\DirectChatController::class, 'show'])->name('direct-chat.show');
    Route::post('direct-chat/messages/{user}', [\App\Http\Controllers\DirectChatController::class, 'send'])->name('direct-chat.send');
    Route::post('direct-chat/read/{user}', [\App\Http\Controllers\DirectChatController::class, 'markAsRead'])->name('direct-chat.mark-read');
    Route::post('direct-chat/messages/{message}/edit', [\App\Http\Controllers\DirectChatController::class, 'edit'])->name('direct-chat.messages.edit');
    Route::post('direct-chat/messages/{message}/toggle-pin', [\App\Http\Controllers\DirectChatController::class, 'togglePin'])->name('direct-chat.messages.toggle-pin');
    Route::post('direct-chat/messages/{message}/toggle-important', [\App\Http\Controllers\DirectChatController::class, 'toggleImportant'])->name('direct-chat.messages.toggle-important');

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
    Route::get('settings/discontinued-projects', [ProjectController::class, 'discontinuedProjects'])->name('settings.discontinued-projects');
    Route::get('settings/discontinued-projects/{id}', [ProjectController::class, 'showDiscontinuedProject'])->name('settings.discontinued-projects.show');
    Route::post('settings/discontinued-projects/{id}/reactivate', [ProjectController::class, 'reactivateProject'])->name('settings.discontinued-projects.reactivate');
    Route::resource('settings/holidays', \App\Http\Controllers\HolidayController::class)->names([
        'index' => 'settings.holidays.index',
        'create' => 'settings.holidays.create',
        'store' => 'settings.holidays.store',
        'edit' => 'settings.holidays.edit',
        'update' => 'settings.holidays.update',
        'destroy' => 'settings.holidays.destroy',
    ])->except(['show']);
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
    Route::post('start-work/start', [\App\Http\Controllers\LeadRoomWorkController::class, 'startWorkSession'])->name('leads.start-work.start-session');
    Route::get('start-work/select-customer', [\App\Http\Controllers\LeadRoomWorkController::class, 'selectCustomerForm'])->name('leads.start-work.select-customer');
    Route::post('start-work/select-customer', [\App\Http\Controllers\LeadRoomWorkController::class, 'updateCustomer'])->name('leads.start-work.update-customer');
    Route::get('start-work/select-room', [\App\Http\Controllers\LeadRoomWorkController::class, 'selectRoomList'])->name('leads.start-work.select-room');
    Route::get('start-work/followups/select', [\App\Http\Controllers\LeadRoomWorkController::class, 'selectFollowupRoom'])->name('leads.start-work.select-followups');
    Route::get('start-work/followups/leads', [\App\Http\Controllers\LeadRoomWorkController::class, 'followupLeads'])->name('leads.start-work.followup-leads');
    Route::get('start-work/interested/leads', [\App\Http\Controllers\LeadRoomWorkController::class, 'interestedLeads'])->name('leads.start-work.interested-leads');
    Route::get('start-work/interested/leads/export', [\App\Http\Controllers\LeadRoomWorkController::class, 'exportInterestedLeads'])->name('leads.start-work.interested-leads.export');
    Route::get('start-work/not-connected/leads', [\App\Http\Controllers\LeadRoomWorkController::class, 'notConnectedLeads'])->name('leads.start-work.not-connected-leads');
    Route::post('start-work/followups/pause', [\App\Http\Controllers\LeadRoomWorkController::class, 'pauseFollowupWork'])->name('leads.start-work.pause-followups');
    Route::post('start-work/followups/resume', [\App\Http\Controllers\LeadRoomWorkController::class, 'resumeFollowupWork'])->name('leads.start-work.resume-followups');
    Route::get('start-work/room/{room}/select', [\App\Http\Controllers\LeadRoomWorkController::class, 'selectRoom'])->name('leads.start-work.select-room-join');
    Route::get('start-work/room/{room}', [\App\Http\Controllers\LeadRoomWorkController::class, 'room'])->name('leads.start-work.room');
    Route::post('start-work/room/{room}/start', [\App\Http\Controllers\LeadRoomWorkController::class, 'startWork'])->name('leads.start-work.start');
    Route::get('start-work/room/{room}/leads', [\App\Http\Controllers\LeadRoomWorkController::class, 'leads'])->name('leads.start-work.leads');
    Route::post('start-work/room/{room}/pause', [\App\Http\Controllers\LeadRoomWorkController::class, 'pauseWork'])->name('leads.start-work.pause');
    Route::post('start-work/room/{room}/resume', [\App\Http\Controllers\LeadRoomWorkController::class, 'resumeWork'])->name('leads.start-work.resume');
    Route::post('start-work/stop', [\App\Http\Controllers\LeadRoomWorkController::class, 'stopWork'])->name('leads.start-work.stop');
    Route::get('start-work/room/{room}/summary/{session}', [\App\Http\Controllers\LeadRoomWorkController::class, 'summary'])->name('leads.start-work.summary');
    Route::get('start-work/session/{session}/download-report', [\App\Http\Controllers\LeadRoomWorkController::class, 'downloadReport'])->name('leads.start-work.download-report');
    Route::post('start-work/current-call', [\App\Http\Controllers\LeadRoomWorkController::class, 'setCurrentCall'])->name('leads.start-work.set-current-call');
    Route::post('start-work/current-call/clear', [\App\Http\Controllers\LeadRoomWorkController::class, 'clearCurrentCall'])->name('leads.start-work.clear-current-call');

    // Admin Room Session Approvals
    Route::middleware(['role:super-admin'])->group(function () {
        Route::get('admin/telecaller-sessions', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminIndex'])->name('admin.telecaller-sessions.index');
        Route::post('admin/telecaller-sessions/{session}/approve', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminApprove'])->name('admin.telecaller-sessions.approve');
        Route::post('admin/telecaller-sessions/{session}/reject', [\App\Http\Controllers\LeadRoomWorkController::class, 'adminReject'])->name('admin.telecaller-sessions.reject');
        
        // Super Admin Global Alerts
        Route::get('admin/alerts', [\App\Http\Controllers\Admin\AppAlertController::class, 'index'])->name('admin.alerts.index');
        Route::get('admin/alerts/create', [\App\Http\Controllers\Admin\AppAlertController::class, 'create'])->name('admin.alerts.create');
        Route::post('admin/alerts', [\App\Http\Controllers\Admin\AppAlertController::class, 'store'])->name('admin.alerts.store');
        Route::delete('admin/alerts/{alert}', [\App\Http\Controllers\Admin\AppAlertController::class, 'destroy'])->name('admin.alerts.destroy');
    });

    // Verification Captcha and Confirmation for Users
    Route::get('alerts/check-active', [\App\Http\Controllers\Admin\AppAlertController::class, 'checkActive'])->name('alerts.check-active');
    Route::get('alerts/captcha-code', [\App\Http\Controllers\Admin\AppAlertController::class, 'captchaCode'])->name('alerts.captcha-code');
    Route::post('alerts/confirm', [\App\Http\Controllers\Admin\AppAlertController::class, 'confirm'])->name('alerts.confirm');

    // Payroll Management
    Route::middleware(['role:super-admin,admin'])->group(function () {
        Route::get('admin/payroll', [SalaryDisbursalController::class, 'index'])->name('admin.payroll.index');
        Route::get('admin/payroll/disburse', [SalaryDisbursalController::class, 'create'])->name('admin.payroll.create');
        Route::post('admin/payroll/disburse', [SalaryDisbursalController::class, 'store'])->name('admin.payroll.store');
        Route::delete('admin/payroll/{slip}', [SalaryDisbursalController::class, 'destroy'])->name('admin.payroll.destroy');
        Route::get('admin/payroll/employee/{employee}/attendance-report', [SalaryDisbursalController::class, 'attendanceReport'])->name('admin.payroll.attendance-report');
    });
    Route::get('admin/payroll/{slip}/payslip', [SalaryDisbursalController::class, 'show'])->name('admin.payroll.show');

    // Reseller Management
    Route::middleware(['role:reseller'])->group(function () {
        Route::get('reseller/dashboard', [\App\Http\Controllers\ResellerController::class, 'index'])->name('reseller.dashboard');
        Route::get('reseller/companies/create', [\App\Http\Controllers\ResellerController::class, 'create'])->name('reseller.companies.create');
        Route::post('reseller/companies', [\App\Http\Controllers\ResellerController::class, 'store'])->name('reseller.companies.store');
        Route::get('reseller/companies/{company}/edit', [\App\Http\Controllers\ResellerController::class, 'edit'])->name('reseller.companies.edit');
        Route::put('reseller/companies/{company}', [\App\Http\Controllers\ResellerController::class, 'update'])->name('reseller.companies.update');
        Route::post('reseller/companies/{company}/toggle-status', [\App\Http\Controllers\ResellerController::class, 'toggleStatus'])->name('reseller.companies.toggle-status');
    });

    // Attendance management (permission-based)
    Route::middleware(['permission:attendance.view-all'])->group(function () {
        Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    });
    Route::middleware(['permission:attendance.view-own|attendance.view-all'])->group(function () {
        Route::resource('attendance', AttendanceController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:attendance.edit'])->group(function () {
        Route::resource('attendance', AttendanceController::class)->only(['edit', 'update']);
    });

    // Documents Management
    Route::middleware(['role:super-admin,admin,hr'])->group(function () {
        Route::post('documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/view', [\App\Http\Controllers\DocumentController::class, 'view'])->name('documents.view');
        Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    });
});


