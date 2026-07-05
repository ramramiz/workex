<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newPermissions = [
            // Hosting Providers
            ['name' => 'View Hosting Providers',   'slug' => 'hosting-providers.view',   'module' => 'Hosting Providers'],
            ['name' => 'Create Hosting Providers', 'slug' => 'hosting-providers.create', 'module' => 'Hosting Providers'],
            ['name' => 'Edit Hosting Providers',   'slug' => 'hosting-providers.edit',   'module' => 'Hosting Providers'],
            ['name' => 'Delete Hosting Providers', 'slug' => 'hosting-providers.delete', 'module' => 'Hosting Providers'],

            // Domain Registrations
            ['name' => 'View Domain Registrations',   'slug' => 'domain-registrations.view',   'module' => 'Domain Registrations'],
            ['name' => 'Create Domain Registrations', 'slug' => 'domain-registrations.create', 'module' => 'Domain Registrations'],
            ['name' => 'Edit Domain Registrations',   'slug' => 'domain-registrations.edit',   'module' => 'Domain Registrations'],
            ['name' => 'Delete Domain Registrations', 'slug' => 'domain-registrations.delete', 'module' => 'Domain Registrations'],

            // Project AMC
            ['name' => 'View Project AMC',   'slug' => 'project-amcs.view',   'module' => 'Project AMC'],
            ['name' => 'Create Project AMC', 'slug' => 'project-amcs.create', 'module' => 'Project AMC'],
            ['name' => 'Edit Project AMC',   'slug' => 'project-amcs.edit',   'module' => 'Project AMC'],
            ['name' => 'Delete Project AMC', 'slug' => 'project-amcs.delete', 'module' => 'Project AMC'],

            // Proforma Invoices
            ['name' => 'View Proforma Invoices',   'slug' => 'proforma-invoices.view',   'module' => 'Proforma Invoices'],
            ['name' => 'Create Proforma Invoices', 'slug' => 'proforma-invoices.create', 'module' => 'Proforma Invoices'],
            ['name' => 'Edit Proforma Invoices',   'slug' => 'proforma-invoices.edit',   'module' => 'Proforma Invoices'],
            ['name' => 'Delete Proforma Invoices', 'slug' => 'proforma-invoices.delete', 'module' => 'Proforma Invoices'],

            // Investors
            ['name' => 'View Investors',   'slug' => 'investors.view',   'module' => 'Investors'],
            ['name' => 'Create Investors', 'slug' => 'investors.create', 'module' => 'Investors'],
            ['name' => 'Edit Investors',   'slug' => 'investors.edit',   'module' => 'Investors'],
            ['name' => 'Delete Investors', 'slug' => 'investors.delete', 'module' => 'Investors'],

            // Hiring & Vacancies
            ['name' => 'View Job Vacancies',   'slug' => 'job-vacancies.view',   'module' => 'Hiring & Vacancies'],
            ['name' => 'Create Job Vacancies', 'slug' => 'job-vacancies.create', 'module' => 'Hiring & Vacancies'],
            ['name' => 'Edit Job Vacancies',   'slug' => 'job-vacancies.edit',   'module' => 'Hiring & Vacancies'],
            ['name' => 'Delete Job Vacancies', 'slug' => 'job-vacancies.delete', 'module' => 'Hiring & Vacancies'],

            // Interns
            ['name' => 'View Interns',   'slug' => 'interns.view',   'module' => 'Interns'],
            ['name' => 'Create Interns', 'slug' => 'interns.create', 'module' => 'Interns'],
            ['name' => 'Edit Interns',   'slug' => 'interns.edit',   'module' => 'Interns'],
            ['name' => 'Delete Interns', 'slug' => 'interns.delete', 'module' => 'Interns'],
        ];

        foreach ($newPermissions as $perm) {
            \App\Models\Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Sync to Super Admin role
        $superAdmin = \App\Models\Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allIds = \App\Models\Permission::pluck('id')->toArray();
            $superAdmin->permissions()->sync($allIds);
        }

        // Sync to Admin role (excluding settings and logs)
        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPerms = \App\Models\Permission::whereNotIn('slug', ['settings.manage', 'users.manage', 'activity-logs.view'])->pluck('id')->toArray();
            $admin->permissions()->sync($adminPerms);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugs = [
            'hosting-providers.view', 'hosting-providers.create', 'hosting-providers.edit', 'hosting-providers.delete',
            'domain-registrations.view', 'domain-registrations.create', 'domain-registrations.edit', 'domain-registrations.delete',
            'project-amcs.view', 'project-amcs.create', 'project-amcs.edit', 'project-amcs.delete',
            'proforma-invoices.view', 'proforma-invoices.create', 'proforma-invoices.edit', 'proforma-invoices.delete',
            'investors.view', 'investors.create', 'investors.edit', 'investors.delete',
            'job-vacancies.view', 'job-vacancies.create', 'job-vacancies.edit', 'job-vacancies.delete',
            'interns.view', 'interns.create', 'interns.edit', 'interns.delete'
        ];

        \App\Models\Permission::whereIn('slug', $slugs)->delete();
    }
};
