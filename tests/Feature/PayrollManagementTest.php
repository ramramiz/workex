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
        \Illuminate\Support\Facades\Mail::fake();

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

        // Assert email dispatched to employee
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\SalaryDisbursedMail::class, function ($mail) {
            return $mail->hasTo($this->employee->user->email);
        });
    }

    public function test_user_can_view_payslip(): void
    {
        $slip = SalaryDisbursal::create([
            'company_id' => 1,
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'basic_salary' => 50000,
            'allowances' => 1500,
            'deductions' => 500,
            'net_salary' => 51000,
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-07-01',
            'status' => 'paid',
            'remarks' => 'Good performance bonus'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payroll.show', $slip));

        $response->assertStatus(200);
        $response->assertViewIs('admin.payroll.show');
        $response->assertSee('EMP001');
        $response->assertSee('John Doe');
        $response->assertSee('Earnings');
        $response->assertSee('Deductions');
    }

    public function test_admin_can_disburse_semi_monthly_salary_cycle_1_and_cycle_2(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        // 1. Set company cycle to twice_monthly
        $company = \App\Models\Company::first() ?: \App\Models\Company::create(['name' => 'Test Company']);
        $this->admin->update(['company_id' => $company->id]);
        $this->employee->user->update(['company_id' => $company->id]);

        $company->update([
            'salary_cycle' => 'twice_monthly',
            'salary_payment_date_1' => 15,
            'salary_payment_date_2' => 30,
        ]);

        // 2. Disburse Cycle 1
        $dataCycle1 = [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'cycle' => 1,
            'basic_salary' => 25000,
            'allowances' => 500,
            'deductions' => 200,
            'net_salary' => 25300,
            'payment_method' => 'bank_transfer',
            'remarks' => 'Cycle 1 disbursal',
        ];

        $response1 = $this->actingAs($this->admin)
            ->post(route('admin.payroll.store'), $dataCycle1);

        $response1->assertRedirect(route('admin.payroll.index'));

        $this->assertDatabaseHas('salary_disbursals', [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'cycle' => 1,
            'net_salary' => 25300,
        ]);

        // 3. Disburse Cycle 2
        $dataCycle2 = [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'cycle' => 2,
            'basic_salary' => 25000,
            'allowances' => 500,
            'deductions' => 200,
            'net_salary' => 25300,
            'payment_method' => 'bank_transfer',
            'remarks' => 'Cycle 2 disbursal',
        ];

        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.payroll.store'), $dataCycle2);

        $response2->assertRedirect(route('admin.payroll.index'));

        $this->assertDatabaseHas('salary_disbursals', [
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'cycle' => 2,
            'net_salary' => 25300,
        ]);
    }

    public function test_admin_can_retrieve_attendance_report_json(): void
    {
        $company = \App\Models\Company::first() ?: \App\Models\Company::create(['name' => 'Test Company']);
        $this->admin->update(['company_id' => $company->id]);
        $this->employee->user->update(['company_id' => $company->id]);

        // Create an attendance record
        \App\Models\Attendance::create([
            'user_id' => $this->employee->user_id,
            'date' => '2026-06-05',
            'login_time' => '2026-06-05 09:00:00',
            'logout_time' => '2026-06-05 17:30:00',
            'total_minutes' => 510,
            'status' => 'present',
            'type' => 'office',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payroll.attendance-report', [
                'employee' => $this->employee->id,
                'month' => 6,
                'year' => 2026,
                'cycle' => 1
            ]));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('employee.name', $this->employee->name);
        $response->assertJsonFragment([
            'status' => 'Present',
            'worked_hours' => '08:30',
        ]);
    }

    public function test_disburse_sheet_shows_pending_disbursals_first(): void
    {
        // 1. Create a second employee who is unpaid
        $role = \App\Models\Role::where('slug', 'employee')->first() ?: \App\Models\Role::create(['name' => 'Employee', 'slug' => 'employee']);
        $user2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 'active'
        ]);
        $employee2 = Employee::create([
            'user_id' => $user2->id,
            'employee_code' => 'EMP002',
            'salary' => 60000,
            'salary_type' => 'monthly',
            'status' => 'active',
            'joining_date' => '2026-01-01',
        ]);

        // Ensure all users have same company_id in this test to avoid tenant scopes
        $company = \App\Models\Company::first() ?: \App\Models\Company::create(['name' => 'Test Company']);
        $this->admin->update(['company_id' => $company->id]);
        $this->employee->user->update(['company_id' => $company->id]);
        $user2->update(['company_id' => $company->id]);

        // 2. Mark the first employee ($this->employee) as paid for this month (June 2026)
        SalaryDisbursal::create([
            'company_id' => $company->id,
            'employee_id' => $this->employee->id,
            'month' => 6,
            'year' => 2026,
            'cycle' => 1,
            'basic_salary' => 50000,
            'net_salary' => 50000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'status' => 'paid',
        ]);

        // 3. Request the disburse sheet for June 2026
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create', [
                'month' => 6,
                'year' => 2026,
                'cycle' => 1
            ]));

        $response->assertStatus(200);

        // 4. Retrieve the payrollList variable from the view
        $payrollList = $response->viewData('payrollList');

        // 5. Assert that the sorting invariant holds: all unpaid (is_paid = false) items must appear before paid (is_paid = true) ones.
        $seenPaid = false;
        $foundUnpaid = false;
        $foundPaid = false;

        foreach ($payrollList as $item) {
            if ($item->is_paid) {
                $seenPaid = true;
                $foundPaid = true;
            } else {
                $foundUnpaid = true;
                $this->assertFalse($seenPaid, "An unpaid employee was found after a paid employee in the sorted list.");
            }
        }

        // Assert that both paid and unpaid records were actually present in the list to validate the sort logic
        $this->assertTrue($foundPaid, "Expected at least one paid employee in the list.");
        $this->assertTrue($foundUnpaid, "Expected at least one unpaid employee in the list.");
    }
}
