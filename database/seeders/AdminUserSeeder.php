<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@workmonitor.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create employee record for super admin
        $mgmtDept = Department::where('name', 'Management')->first();
        Employee::updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'employee_code' => 'EMP001',
                'department_id' => $mgmtDept?->id,
                'joining_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        // Demo Admin user
        $adminRole = Role::where('slug', 'admin')->first();
        $admin = User::updateOrCreate(
            ['email' => 'manager@workmonitor.com'],
            [
                'name' => 'Project Manager',
                'password' => Hash::make('Admin@123'),
                'role_id' => $adminRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'employee_code' => 'EMP002',
                'department_id' => $mgmtDept?->id,
                'joining_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        // Demo Team Leader user (Vijil A)
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();
        $vijil = User::updateOrCreate(
            ['email' => 'vijil.techsoul@gmail.com'],
            [
                'name' => 'Vijil A',
                'password' => Hash::make('Admin@123'),
                'role_id' => $teamLeaderRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $vijil->id],
            [
                'employee_code' => 'EMP003',
                'department_id' => $mgmtDept?->id,
                'joining_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        // Demo Team Leader user (Souban)
        $souban = User::updateOrCreate(
            ['email' => 'souban.techsoul@gmail.com'],
            [
                'name' => 'Souban',
                'password' => Hash::make('Admin@123'),
                'role_id' => $teamLeaderRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $souban->id],
            [
                'employee_code' => 'EMP004',
                'department_id' => $mgmtDept?->id,
                'joining_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        // Demo Employee Developer (Hisham)
        $employeeRole = Role::where('slug', 'employee')->first();
        $hisham = User::updateOrCreate(
            ['email' => 'hisham.techsoul@gmail.com'],
            [
                'name' => 'Hisham',
                'password' => Hash::make('Admin@123'),
                'role_id' => $employeeRole?->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        Employee::updateOrCreate(
            ['user_id' => $hisham->id],
            [
                'employee_code' => 'EMP005',
                'department_id' => $mgmtDept?->id,
                'joining_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        // Demo Telecaller
        $telecallerRole = Role::where('slug', 'telecaller')->first();
        if ($telecallerRole) {
            $telecaller = User::updateOrCreate(
                ['email' => 'telecaller@workmonitor.com'],
                [
                    'name' => 'Demo Telecaller',
                    'password' => Hash::make('Admin@123'),
                    'role_id' => $telecallerRole->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Employee::updateOrCreate(
                ['user_id' => $telecaller->id],
                [
                    'employee_code' => 'EMP006',
                    'department_id' => $mgmtDept?->id,
                    'joining_date' => now()->toDateString(),
                    'status' => 'active',
                ]
            );
        }

        // Demo Reseller
        $resellerRole = Role::where('slug', 'reseller')->first();
        if ($resellerRole) {
            $reseller = User::updateOrCreate(
                ['email' => 'reseller@workmonitor.com'],
                [
                    'name' => 'Demo Reseller',
                    'password' => Hash::make('Reseller@123'),
                    'role_id' => $resellerRole->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Employee::updateOrCreate(
                ['user_id' => $reseller->id],
                [
                    'employee_code' => 'EMP007',
                    'department_id' => $mgmtDept?->id,
                    'joining_date' => now()->toDateString(),
                    'status' => 'active',
                ]
            );
        }

        // Seed Project: Biznx ERP
        $project = \App\Models\Project::updateOrCreate(
            ['project_code' => 'BIZNX-ERP'],
            [
                'name' => 'Biznx ERP',
                'description' => 'Biznx is a global software',
                'status' => 'development',
                'priority' => 'high',
                'manager_id' => $admin->id,
                'team_leader_id' => $vijil->id,
                'start_date' => now(),
                'created_by' => $superAdmin->id,
            ]
        );

        // Sync project members (Hisham, Vijil, Souban)
        $project->members()->syncWithoutDetaching([
            $hisham->id => ['role' => 'developer'],
            $vijil->id => ['role' => 'team_leader'],
            $souban->id => ['role' => 'team_leader'],
        ]);

        // Seed Task: Update login page design in Biznx ERP project
        \App\Models\Task::updateOrCreate(
            [
                'project_id' => $project->id,
                'title' => 'Update login page design',
            ],
            [
                'description' => 'Update the login page design to be modern and premium, using customized responsive layout elements and dynamic animations.',
                'assigned_to' => $hisham->id,
                'created_by' => $vijil->id,
                'status' => 'pending',
                'priority' => 'high',
                'start_date' => now(),
            ]
        );

        $this->command->info('✅ Admin users created:');
        $this->command->info('   Super Admin: admin@workmonitor.com / Admin@123');
        $this->command->info('   PM:          manager@workmonitor.com / Admin@123');
        $this->command->info('   Team Leader (Vijil): vijil.techsoul@gmail.com / Admin@123');
        $this->command->info('   Team Leader (Souban): souban.techsoul@gmail.com / Admin@123');
        $this->command->info('   Developer (Hisham):   hisham.techsoul@gmail.com / Admin@123');
        $this->command->info('   Telecaller:          telecaller@workmonitor.com / Admin@123');
        $this->command->info('   Reseller:            reseller@workmonitor.com / Reseller@123');
    }
}
