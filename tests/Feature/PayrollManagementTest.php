<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\SalaryDisbursal;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Standard employee',
            'color' => '#6366f1'
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active'
        ]);

        $this->employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP001',
            'salary' => 50000,
            'salary_type' => 'monthly',
            'status' => 'active',
            'joining_date' => '2026-01-01',
        ]);
    }

    public function test_admin_can_access_payroll_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payroll.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.payroll.index');
    }

    public function test_admin_can_access_payroll_disburse_sheet(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.payroll.create');
    }

    public function test_admin_can_disburse_salary_and_generates_expense(): void
    {
        $data = [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'basic_salary' => 50000,
            'allowances' => 1500,
            'deductions' => 500,
            'net_salary' => 51000,
            'payment_method' => 'bank_transfer',
            'remarks' => 'Good performance bonus',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payroll.store'), $data);

        $response->assertRedirect(route('admin.payroll.index'));

        // Assert salary disbursal recorded
        $this->assertDatabaseHas('salary_disbursals', [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'net_salary' => 51000,
            'payment_method' => 'bank_transfer',
        ]);

        // Assert Expense auto-created
        $this->assertDatabaseHas('expenses', [
            'category' => 'salary',
            'amount' => 51000,
            'status' => 'paid',
        ]);
    }
}
