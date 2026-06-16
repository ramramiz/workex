<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard',        'slug' => 'dashboard.view',           'module' => 'Dashboard'],
            // Employees
            ['name' => 'View Employees',         'slug' => 'employees.view',           'module' => 'Employees'],
            ['name' => 'Create Employees',       'slug' => 'employees.create',         'module' => 'Employees'],
            ['name' => 'Edit Employees',         'slug' => 'employees.edit',           'module' => 'Employees'],
            ['name' => 'Delete Employees',       'slug' => 'employees.delete',         'module' => 'Employees'],
            // Clients
            ['name' => 'View Clients',           'slug' => 'clients.view',             'module' => 'Clients'],
            ['name' => 'Create Clients',         'slug' => 'clients.create',           'module' => 'Clients'],
            ['name' => 'Edit Clients',           'slug' => 'clients.edit',             'module' => 'Clients'],
            ['name' => 'Delete Clients',         'slug' => 'clients.delete',           'module' => 'Clients'],
            // Leads
            ['name' => 'View Leads',             'slug' => 'leads.view',               'module' => 'Leads'],
            ['name' => 'Create Leads',           'slug' => 'leads.create',             'module' => 'Leads'],
            ['name' => 'Edit Leads',             'slug' => 'leads.edit',               'module' => 'Leads'],
            ['name' => 'Delete Leads',           'slug' => 'leads.delete',             'module' => 'Leads'],
            // Quotations
            ['name' => 'View Quotations',        'slug' => 'quotations.view',          'module' => 'Quotations'],
            ['name' => 'Create Quotations',      'slug' => 'quotations.create',        'module' => 'Quotations'],
            ['name' => 'Edit Quotations',        'slug' => 'quotations.edit',          'module' => 'Quotations'],
            // Projects
            ['name' => 'View All Projects',      'slug' => 'projects.view-all',        'module' => 'Projects'],
            ['name' => 'View Own Projects',      'slug' => 'projects.view-own',        'module' => 'Projects'],
            ['name' => 'Create Projects',        'slug' => 'projects.create',          'module' => 'Projects'],
            ['name' => 'Edit Projects',          'slug' => 'projects.edit',            'module' => 'Projects'],
            ['name' => 'Delete Projects',        'slug' => 'projects.delete',          'module' => 'Projects'],
            // Tasks
            ['name' => 'View All Tasks',         'slug' => 'tasks.view-all',           'module' => 'Tasks'],
            ['name' => 'View Own Tasks',         'slug' => 'tasks.view-own',           'module' => 'Tasks'],
            ['name' => 'Create Tasks',           'slug' => 'tasks.create',             'module' => 'Tasks'],
            ['name' => 'Edit Tasks',             'slug' => 'tasks.edit',               'module' => 'Tasks'],
            ['name' => 'Delete Tasks',           'slug' => 'tasks.delete',             'module' => 'Tasks'],
            // Work Timer
            ['name' => 'Use Work Timer',         'slug' => 'timer.use',                'module' => 'Work Timer'],
            ['name' => 'View All Timers',        'slug' => 'timer.view-all',           'module' => 'Work Timer'],
            // Reports
            ['name' => 'Submit Daily Report',    'slug' => 'reports.submit',           'module' => 'Reports'],
            ['name' => 'Approve Daily Reports',  'slug' => 'reports.approve',          'module' => 'Reports'],
            ['name' => 'View All Reports',       'slug' => 'reports.view-all',         'module' => 'Reports'],
            // Attendance
            ['name' => 'View Own Attendance',    'slug' => 'attendance.view-own',      'module' => 'Attendance'],
            ['name' => 'View All Attendance',    'slug' => 'attendance.view-all',      'module' => 'Attendance'],
            ['name' => 'Edit Attendance',        'slug' => 'attendance.edit',          'module' => 'Attendance'],
            // Leaves
            ['name' => 'Apply Leave',            'slug' => 'leaves.apply',             'module' => 'Leaves'],
            ['name' => 'Approve Leaves (TL)',    'slug' => 'leaves.approve-tl',        'module' => 'Leaves'],
            ['name' => 'Approve Leaves (HR)',    'slug' => 'leaves.approve-hr',        'module' => 'Leaves'],
            ['name' => 'View All Leaves',        'slug' => 'leaves.view-all',          'module' => 'Leaves'],
            // Bugs
            ['name' => 'View Bugs',              'slug' => 'bugs.view',                'module' => 'Bugs'],
            ['name' => 'Create Bugs',            'slug' => 'bugs.create',              'module' => 'Bugs'],
            ['name' => 'Edit Bugs',              'slug' => 'bugs.edit',                'module' => 'Bugs'],
            // Invoices
            ['name' => 'View Invoices',          'slug' => 'invoices.view',            'module' => 'Invoices'],
            ['name' => 'Create Invoices',        'slug' => 'invoices.create',          'module' => 'Invoices'],
            ['name' => 'Edit Invoices',          'slug' => 'invoices.edit',            'module' => 'Invoices'],
            // Payments
            ['name' => 'View Payments',          'slug' => 'payments.view',            'module' => 'Payments'],
            ['name' => 'Record Payments',        'slug' => 'payments.create',          'module' => 'Payments'],
            // Expenses
            ['name' => 'View Expenses',          'slug' => 'expenses.view',            'module' => 'Expenses'],
            ['name' => 'Create Expenses',        'slug' => 'expenses.create',          'module' => 'Expenses'],
            // Support
            ['name' => 'View Support Tickets',   'slug' => 'support.view',             'module' => 'Support'],
            ['name' => 'Manage Support Tickets', 'slug' => 'support.manage',           'module' => 'Support'],
            // Settings
            ['name' => 'Manage Settings',        'slug' => 'settings.manage',          'module' => 'Settings'],
            ['name' => 'Manage Users',           'slug' => 'users.manage',             'module' => 'Users'],
            // Reports
            ['name' => 'Export Reports',         'slug' => 'reports.export',           'module' => 'Reports'],
            ['name' => 'View Live Status Board', 'slug' => 'status-board.view',        'module' => 'Status Board'],
            ['name' => 'View Activity Logs',     'slug' => 'activity-logs.view',       'module' => 'Activity Logs'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign all permissions to super-admin
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allIds = Permission::pluck('id')->toArray();
            $superAdmin->permissions()->sync($allIds);
        }

        // Admin permissions
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPerms = Permission::whereNotIn('slug', ['settings.manage', 'users.manage', 'activity-logs.view'])->pluck('id')->toArray();
            $admin->permissions()->sync($adminPerms);
        }

        // Team leader permissions
        $tl = Role::where('slug', 'team-leader')->first();
        if ($tl) {
            $tlSlugs = ['dashboard.view','projects.view-own','tasks.view-all','tasks.create','tasks.edit','reports.approve','reports.view-all','attendance.view-own','attendance.view-all','leaves.approve-tl','leaves.view-all','bugs.view','bugs.create','bugs.edit','timer.view-all','status-board.view','reports.export'];
            $tlPerms = Permission::whereIn('slug', $tlSlugs)->pluck('id')->toArray();
            $tl->permissions()->sync($tlPerms);
        }

        // Employee permissions
        $emp = Role::where('slug', 'employee')->first();
        if ($emp) {
            $empSlugs = ['dashboard.view','projects.view-own','tasks.view-own','timer.use','reports.submit','attendance.view-own','leaves.apply','bugs.create'];
            $empPerms = Permission::whereIn('slug', $empSlugs)->pluck('id')->toArray();
            $emp->permissions()->sync($empPerms);
        }

        // HR permissions
        $hr = Role::where('slug', 'hr')->first();
        if ($hr) {
            $hrSlugs = ['dashboard.view','employees.view','employees.create','employees.edit','attendance.view-all','attendance.edit','leaves.approve-hr','leaves.view-all','reports.view-all','reports.export'];
            $hrPerms = Permission::whereIn('slug', $hrSlugs)->pluck('id')->toArray();
            $hr->permissions()->sync($hrPerms);
        }

        // Accounts permissions
        $accounts = Role::where('slug', 'accounts')->first();
        if ($accounts) {
            $accSlugs = ['dashboard.view','clients.view','projects.view-all','invoices.view','invoices.create','invoices.edit','payments.view','payments.create','expenses.view','expenses.create','reports.view-all','reports.export'];
            $accPerms = Permission::whereIn('slug', $accSlugs)->pluck('id')->toArray();
            $accounts->permissions()->sync($accPerms);
        }

        // Client permissions
        $client = Role::where('slug', 'client')->first();
        if ($client) {
            $clientSlugs = ['dashboard.view','support.view'];
            $clientPerms = Permission::whereIn('slug', $clientSlugs)->pluck('id')->toArray();
            $client->permissions()->sync($clientPerms);
        }
    }
}
