<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin',    'slug' => 'super-admin',   'description' => 'Full system access', 'color' => '#dc2626'],
            ['name' => 'Admin',          'slug' => 'admin',         'description' => 'Project & admin management', 'color' => '#7c3aed'],
            ['name' => 'Team Leader',    'slug' => 'team-leader',   'description' => 'Manages team and tasks', 'color' => '#2563eb'],
            ['name' => 'Employee',       'slug' => 'employee',      'description' => 'Developer / team member', 'color' => '#059669'],
            ['name' => 'HR Manager',     'slug' => 'hr',            'description' => 'HR and attendance management', 'color' => '#d97706'],
            ['name' => 'Accounts',       'slug' => 'accounts',      'description' => 'Invoice and payment management', 'color' => '#0891b2'],
            ['name' => 'Client',         'slug' => 'client',        'description' => 'Client portal access', 'color' => '#6b7280'],
            ['name' => 'Telecaller',     'slug' => 'telecaller',    'description' => 'Telecaller & Lead Caller', 'color' => '#14b8a6'],
            ['name' => 'Reseller',       'slug' => 'reseller',      'description' => 'Reseller account to manage sub-companies', 'color' => '#f43f5e'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
