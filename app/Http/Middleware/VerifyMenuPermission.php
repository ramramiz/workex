<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        if (!$routeName) {
            return $next($request);
        }

        // Bypass check for return account and public verification pages
        if (in_array($routeName, ['employees.return-account', 'interns.verify.public'])) {
            return $next($request);
        }

        $hasPermissionsTable = \App\Models\Permission::where('slug', 'projects.view-own')->exists();

        // Map route name patterns to checking functions
        $permissions = [
            'employees.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isHR();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('employees.view')) : $roleOk;
            },
            'job-vacancies.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isHR();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('job-vacancies.view')) : $roleOk;
            },
            'interns.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isHR();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('interns.view')) : $roleOk;
            },
            'projects.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = !$user->isEmployee() && !$user->isTelecaller() && !$user->isClient();
                return $hasPermissionsTable ? ($roleOk && ($user->hasPermission('projects.view-all') || $user->hasPermission('projects.view-own'))) : $roleOk;
            },
            'project-amcs.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isTeamLeader();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('project-amcs.view')) : $roleOk;
            },
            'hosting-providers.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isTeamLeader();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('hosting-providers.view')) : $roleOk;
            },
            'domain-registrations.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isTeamLeader();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('domain-registrations.view')) : $roleOk;
            },
            'clients.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('clients.view')) : $roleOk;
            },
            'quotations.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = !$user->isTelecaller() && $user->isAdminOrAbove();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('quotations.view')) : $roleOk;
            },
            'leads.start-work.' => function ($user) {
                return $user->isTelecaller();
            },
            'leads.calls.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isTelecaller() || $user->isAdminOrAbove();
                return $hasPermissionsTable ? ($roleOk && ($user->isTelecaller() || $user->hasPermission('leads.view'))) : $roleOk;
            },
            'leads.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('leads.view')) : $roleOk;
            },
            'reports.telecaller-performance' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isTelecaller() || $user->isHR() || $user->isTeamLeader();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('reports.view-all')) : $roleOk;
            },
            'daily-reports.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = !$user->isTelecaller() && !$user->isEmployee();
                return $hasPermissionsTable ? ($roleOk && ($user->hasPermission('reports.view-all') || $user->hasPermission('reports.approve'))) : $roleOk;
            },
            'bugs.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = !$user->isTelecaller() && !$user->isEmployee();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('bugs.view')) : $roleOk;
            },
            'meetings.' => function ($user) {
                return !$user->isTelecaller() && !$user->isEmployee();
            },
            'work-timer.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = !$user->isTelecaller() && ($user->isEmployee() || $user->isTeamLeader());
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('timer.use')) : $roleOk;
            },
            'invoices.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('invoices.view')) : $roleOk;
            },
            'proforma-invoices.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('proforma-invoices.view')) : $roleOk;
            },
            'payments.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('payments.view')) : $roleOk;
            },
            'expenses.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('expenses.view')) : $roleOk;
            },
            'banks.' => function ($user) {
                return $user->isAdminOrAbove() || $user->isAccounts();
            },
            'investors.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isAdminOrAbove() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('investors.view')) : $roleOk;
            },
            'admin.payroll.' => function ($user) {
                return $user->isAdminOrAbove();
            },
            'reports.' => function ($user) use ($hasPermissionsTable) {
                if ($user->isTelecaller()) {
                    return false;
                }
                $roleOk = $user->isAdminOrAbove() || $user->isHR() || $user->isAccounts();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('reports.view-all')) : $roleOk;
            },
            'live-status' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isSuperAdmin();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('status-board.view')) : $roleOk;
            },
            'admin.telecaller-sessions.' => function ($user) {
                return $user->isSuperAdmin();
            },
            'admin.alerts.' => function ($user) {
                return $user->isSuperAdmin();
            },
            'activity-logs.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isSuperAdmin();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('activity-logs.view')) : $roleOk;
            },
            'settings.' => function ($user) use ($hasPermissionsTable) {
                $roleOk = $user->isSuperAdmin();
                return $hasPermissionsTable ? ($roleOk && $user->hasPermission('settings.manage')) : $roleOk;
            },
            'attendance.' => function ($user) {
                return $user->hasPermission('attendance.view-own') || $user->hasPermission('attendance.view-all');
            },
        ];

        // Check if the current route matches any patterns
        foreach ($permissions as $pattern => $check) {
            if (str_starts_with($routeName, $pattern) || $routeName === rtrim($pattern, '.')) {
                // For reports pattern, verify we are not intercepting reports.telecaller-performance
                if ($pattern === 'reports.' && $routeName === 'reports.telecaller-performance') {
                    continue;
                }
                
                // For leads pattern, verify we are not intercepting leads.start-work
                if ($pattern === 'leads.' && str_starts_with($routeName, 'leads.start-work.')) {
                    continue;
                }

                if (!$check($user)) {
                    abort(404);
                }
                break;
            }
        }

        return $next($request);
    }
}
