<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class VerifyMenuPermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
            'status' => 'active',
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee@test.com',
            'status' => 'active',
        ]);
    }

    public function test_employee_cannot_access_employees_section_returns_404(): void
    {
        // An employee should get a 404 when trying to view employees.index
        $response = $this->actingAs($this->employeeUser)
            ->get(route('employees.index'));

        $response->assertStatus(404);
    }

    public function test_admin_can_access_employees_section(): void
    {
        // An admin should access employees.index successfully (200)
        $response = $this->actingAs($this->adminUser)
            ->get(route('employees.index'));

        $response->assertStatus(200);
    }

    public function test_employee_can_access_dashboard_and_chat(): void
    {
        // Employee is allowed to view dashboard and chat
        $response = $this->actingAs($this->employeeUser)
            ->get(route('dashboard'));
        $response->assertStatus(200);

        $responseChat = $this->actingAs($this->employeeUser)
            ->get(route('chat.index'));
        $responseChat->assertStatus(200);
    }

    public function test_impersonating_user_can_return_to_admin_account(): void
    {
        // Save impersonating_from in session
        $response = $this->actingAs($this->employeeUser)
            ->withSession(['impersonating_from' => $this->adminUser->id])
            ->post(route('employees.return-account'));

        // It should authenticate back as admin and redirect
        $response->assertRedirect('/dashboard');
        $this->assertEquals($this->adminUser->id, auth()->id());
    }

    public function test_project_budget_is_hidden_from_team_leader_but_visible_to_super_admin_and_accounts(): void
    {
        $project = \App\Models\Project::create([
            'project_code' => 'PRJ-TEST-1234',
            'name' => 'Secret Financial Project',
            'project_value' => 50000.00,
            'priority' => 'high',
            'status' => 'planning',
            'project_type' => 'web',
        ]);

        // Create a Team Leader user
        $tlRole = Role::where('slug', 'team-leader')->first();
        $tlUser = User::factory()->create([
            'role_id' => $tlRole->id,
            'status' => 'active',
        ]);

        // Create an Accountant user
        $accRole = Role::where('slug', 'accounts')->first();
        $accUser = User::factory()->create([
            'role_id' => $accRole->id,
            'status' => 'active',
        ]);

        // 1. Visit as Team Leader -> budget amount is hidden (rendered as —)
        $responseTl = $this->actingAs($tlUser)->get(route('projects.show', $project));
        $responseTl->assertStatus(200);
        $responseTl->assertDontSee('₹50,000.00');

        // 2. Visit as Super Admin -> budget amount is visible
        $responseAdmin = $this->actingAs($this->adminUser)->get(route('projects.show', $project));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('₹50,000.00');

        // 3. Visit as Accountant -> budget amount is visible
        $responseAcc = $this->actingAs($accUser)->get(route('projects.show', $project));
        $responseAcc->assertStatus(200);
        $responseAcc->assertSee('₹50,000.00');
    }

    public function test_telecaller_can_access_start_work_but_others_cannot(): void
    {
        // Create a Telecaller user
        $tcRole = Role::where('slug', 'telecaller')->first();
        $tcUser = User::factory()->create([
            'role_id' => $tcRole->id,
            'status' => 'active',
        ]);

        // 1. Visit as Telecaller -> index page responds 200
        $responseTc = $this->actingAs($tcUser)->get(route('leads.start-work.index'));
        $responseTc->assertStatus(200);

        // 2. Visit as Employee -> index page responds 404
        $responseEmp = $this->actingAs($this->employeeUser)->get(route('leads.start-work.index'));
        $responseEmp->assertStatus(404);
    }

    public function test_telecaller_can_store_lead_call_log(): void
    {
        // Create a Telecaller user
        $tcRole = Role::where('slug', 'telecaller')->first();
        $tcUser = User::factory()->create([
            'role_id' => $tcRole->id,
            'status' => 'active',
        ]);

        // Create a lead assigned to the telecaller
        $lead = \App\Models\Lead::create([
            'client_name' => 'Test Lead Corp',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'status' => 'new',
            'assigned_to' => $tcUser->id,
            'source' => 'direct',
            'requirement' => 'Test Requirement',
        ]);

        // Post a call log
        $response = $this->actingAs($tcUser)->post(route('leads.calls.store', $lead), [
            'status' => 'Connected',
            'customer_response' => 'Interested',
            'lead_status' => 'interested',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_calls', [
            'lead_id' => $lead->id,
            'status' => 'Connected',
            'customer_response' => 'Interested',
        ]);
    }
}
