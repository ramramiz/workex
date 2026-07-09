<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollLeaveDeductionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Employee $employee;
    private Company $company;

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

        $this->company = Company::create([
            'name' => 'Test Company',
            'salary_cycle' => 'twice_monthly',
            'salary_payment_date_1' => 15,
            'salary_payment_date_2' => 30,
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active',
            'company_id' => $this->company->id,
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active',
            'company_id' => $this->company->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP001',
            'salary' => 52000, // Daily rate = 52000 / 26 = 2000
            'salary_type' => 'monthly',
            'status' => 'active',
            'joining_date' => '2026-01-01',
        ]);
    }

    public function test_casual_leave_and_deduction_split_across_terms(): void
    {
        // 1. Employee takes 1 leave in Term 1 (June 5) and 1 leave in Term 2 (June 20)
        $leave1 = Leave::create([
            'user_id' => $this->employee->user_id,
            'leave_type' => 'sick_leave',
            'from_date' => '2026-06-05',
            'to_date' => '2026-06-05',
            'total_days' => 1.0,
            'status' => 'approved',
            'reason' => 'Sick',
        ]);

        $leave2 = Leave::create([
            'user_id' => $this->employee->user_id,
            'leave_type' => 'sick_leave',
            'from_date' => '2026-06-20',
            'to_date' => '2026-06-20',
            'total_days' => 1.0,
            'status' => 'approved',
            'reason' => 'Sick',
        ]);

        // Fetch payroll create for Cycle 1 (1st - 15th)
        $response1 = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create', [
                'month' => 6,
                'year' => 2026,
                'cycle' => 1
            ]));

        $response1->assertStatus(200);
        $payrollList1 = $response1->viewData('payrollList');
        $item1 = collect($payrollList1)->firstWhere('employee.id', $this->employee->id);

        $this->assertNotNull($item1);
        // Cycle 1: 1 leave -> CL = 1.0, LOP = 0.0
        $this->assertEquals(1.0, $item1->cl_count);
        $this->assertEquals(0.0, $item1->lop_count);
        $this->assertEquals(0, $item1->lop_deduction);

        // Fetch payroll create for Cycle 2 (16th - End)
        $response2 = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create', [
                'month' => 6,
                'year' => 2026,
                'cycle' => 2
            ]));

        $response2->assertStatus(200);
        $payrollList2 = $response2->viewData('payrollList');
        $item2 = collect($payrollList2)->firstWhere('employee.id', $this->employee->id);

        $this->assertNotNull($item2);
        // Cycle 2: 1 leave -> CL = 0.0, LOP = 1.0
        $this->assertEquals(0.0, $item2->cl_count);
        $this->assertEquals(1.0, $item2->lop_count);
        $this->assertEquals(2000.0, $item2->lop_deduction); // 1 day LOP * daily rate (2000)
    }

    public function test_no_leave_in_term1_and_multiple_leaves_in_term2(): void
    {
        // 2. Employee takes 0 leaves in Term 1, and 2 leaves in Term 2 (June 18 & 19)
        Leave::create([
            'user_id' => $this->employee->user_id,
            'leave_type' => 'sick_leave',
            'from_date' => '2026-06-18',
            'to_date' => '2026-06-19',
            'total_days' => 2.0,
            'status' => 'approved',
            'reason' => 'Sick',
        ]);

        // Fetch payroll create for Cycle 1 (1st - 15th)
        $response1 = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create', [
                'month' => 6,
                'year' => 2026,
                'cycle' => 1
            ]));

        $response1->assertStatus(200);
        $payrollList1 = $response1->viewData('payrollList');
        $item1 = collect($payrollList1)->firstWhere('employee.id', $this->employee->id);

        $this->assertNotNull($item1);
        $this->assertEquals(0.0, $item1->cl_count);
        $this->assertEquals(0.0, $item1->lop_count);
        $this->assertEquals(0, $item1->lop_deduction);

        // Fetch payroll create for Cycle 2 (16th - End)
        $response2 = $this->actingAs($this->admin)
            ->get(route('admin.payroll.create', [
                'month' => 6,
                'year' => 2026,
                'cycle' => 2
            ]));

        $response2->assertStatus(200);
        $payrollList2 = $response2->viewData('payrollList');
        $item2 = collect($payrollList2)->firstWhere('employee.id', $this->employee->id);

        $this->assertNotNull($item2);
        // Cycle 2: 2 leaves -> CL = 1.0, LOP = 1.0
        $this->assertEquals(1.0, $item2->cl_count);
        $this->assertEquals(1.0, $item2->lop_count);
        $this->assertEquals(2000.0, $item2->lop_deduction); // 1 day LOP * daily rate (2000)
    }
}
