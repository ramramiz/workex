<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Models\SalaryDisbursal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employeeUser;
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

        $this->employeeUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active'
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP001',
            'salary' => 50000,
            'salary_type' => 'monthly',
            'status' => 'active',
            'joining_date' => '2026-01-01',
            'is_applicable_for_salary' => true,
        ]);
    }

    public function test_admin_can_access_investors_index_and_create_investor(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('investors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('investors.index');

        $investorData = [
            'name'            => 'Alice Cooper',
            'email'           => 'alice@example.com',
            'phone'           => '1234567890',
            'description'     => 'Strategic partner',
            'opening_balance' => 100000.00,
        ];

        $createResponse = $this->actingAs($this->admin)
            ->post(route('investors.store'), $investorData);

        $createResponse->assertRedirect(route('investors.index'));
        $this->assertDatabaseHas('investors', [
            'name'  => 'Alice Cooper',
            'email' => 'alice@example.com',
        ]);
    }

    public function test_employee_cannot_access_investors(): void
    {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('investors.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_add_manual_transaction(): void
    {
        $investor = Investor::create([
            'name'            => 'Bob Marley',
            'email'           => 'bob@example.com',
            'opening_balance' => 50000.00,
            'status'          => 'active',
        ]);

        $txnData = [
            'type'        => 'Credit',
            'amount'      => 25000.00,
            'date'        => '2026-07-04',
            'reference'   => 'REF-999',
            'description' => 'Bonus deposit',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('investors.transactions.store', $investor), $txnData);

        $response->assertRedirect(route('investors.show', $investor));
        $this->assertDatabaseHas('investor_transactions', [
            'investor_id' => $investor->id,
            'type'        => 'Credit',
            'amount'      => 25000.00,
            'reference'   => 'REF-999',
        ]);
    }

    public function test_disbursing_salary_using_investor_shows_in_ledger(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $investor = Investor::create([
            'name'            => 'Charlie Brown',
            'opening_balance' => 200000.00,
            'status'          => 'active',
        ]);

        // Disburse salary using this investor
        $disburseData = [
            'employee_id'    => $this->employee->id,
            'month'          => 7,
            'year'           => 2026,
            'basic_salary'   => 50000,
            'allowances'     => 0,
            'deductions'     => 0,
            'net_salary'     => 50000,
            'payment_method' => 'Investor: Charlie Brown',
            'remarks'        => 'Salary payment via Charlie Brown fund',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payroll.store'), $disburseData);

        // Access investor show to verify the ledger
        $showResponse = $this->actingAs($this->admin)
            ->get(route('investors.show', $investor));

        $showResponse->assertStatus(200);
        
        // Assert the ledger calculates the balance correctly (Opening 200k - Disbursal 50k = Current 150k)
        $showResponse->assertViewHas('currentBalance', 150000.00);
        $showResponse->assertSee('Salary Disbursal');
        $showResponse->assertSee('Salary disbursed to John Doe');
    }
}
