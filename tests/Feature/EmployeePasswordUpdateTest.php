<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\RoleSeeder;
use Database\Seeders\DepartmentSeeder;

class EmployeePasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Employee $employee;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & departments
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();
        $this->department = Department::first();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create employee user
        $user = User::factory()->create([
            'role_id' => $employeeRole->id,
            'password' => Hash::make('OldPassword@123'),
        ]);

        $this->employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => 'EMP9999',
                'joining_date' => now()->toDateString(),
                'department_id' => $this->department->id,
                'status' => 'active',
            ]
        );
    }

    public function test_admin_can_update_employee_password()
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('employees.update', $this->employee), [
                'name' => 'Updated Name',
                'email' => 'updated@email.com',
                'role_id' => $this->employee->user->role_id,
                'department_id' => $this->department->id,
                'joining_date' => now()->toDateString(),
                'password' => 'NewSecurePassword@123',
            ]);

        $response->assertRedirect(route('employees.index'));

        // Refresh and check password
        $user = $this->employee->user->fresh();
        $this->assertTrue(Hash::check('NewSecurePassword@123', $user->password));
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@email.com', $user->email);
    }

    public function test_employee_password_remains_unchanged_if_left_blank()
    {
        $response = $this->actingAs($this->adminUser)
            ->put(route('employees.update', $this->employee), [
                'name' => 'Another Name',
                'email' => $this->employee->user->email,
                'role_id' => $this->employee->user->role_id,
                'department_id' => $this->department->id,
                'joining_date' => now()->toDateString(),
                'password' => '', // blank password should not update it
            ]);

        $response->assertRedirect(route('employees.index'));

        // Refresh and check password is still the old one
        $user = $this->employee->user->fresh();
        $this->assertTrue(Hash::check('OldPassword@123', $user->password));
        $this->assertEquals('Another Name', $user->name);
    }
}
