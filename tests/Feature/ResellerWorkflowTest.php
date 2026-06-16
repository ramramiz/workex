<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResellerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_reseller_can_login_and_redirect_to_dashboard(): void
    {
        $resellerRole = Role::where('slug', 'reseller')->first();
        $reseller = User::factory()->create([
            'role_id' => $resellerRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($reseller)->get('/dashboard');
        $response->assertRedirect(route('reseller.dashboard'));
    }

    public function test_reseller_can_create_a_new_company_and_admin(): void
    {
        $resellerRole = Role::where('slug', 'reseller')->first();
        $reseller = User::factory()->create([
            'role_id' => $resellerRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($reseller)->post(route('reseller.companies.store'), [
            'company_name' => 'Acme Inc',
            'admin_name' => 'Acme Admin',
            'admin_email' => 'admin@acme.com',
            'admin_password' => 'Password@123',
        ]);

        $response->assertRedirect(route('reseller.dashboard'));

        $this->assertDatabaseHas('companies', ['name' => 'Acme Inc', 'email' => 'admin@acme.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@acme.com', 'name' => 'Acme Admin']);
        
        $adminUser = User::where('email', 'admin@acme.com')->first();
        $this->assertNotNull($adminUser->company_id);
        $this->assertTrue($adminUser->isSuperAdmin());
    }

    public function test_company_isolation_works(): void
    {
        $defaultCompany = Company::find(1);
        $defaultUser = User::factory()->create(['company_id' => $defaultCompany->id]);
        $defaultProject = Project::create([
            'project_code' => 'PRJ-DEF',
            'name' => 'Default Company Project',
            'company_id' => $defaultCompany->id,
            'status' => 'planning',
        ]);

        $newCompany = Company::create(['name' => 'Second Company', 'status' => 'active']);
        $newCompanyAdminRole = Role::where('slug', 'super-admin')->first();
        $newCompanyAdmin = User::create([
            'name' => 'New Admin',
            'email' => 'newadmin@company.com',
            'password' => bcrypt('Password@123'),
            'role_id' => $newCompanyAdminRole->id,
            'company_id' => $newCompany->id,
            'status' => 'active',
        ]);

        $newProject = Project::create([
            'project_code' => 'PRJ-NEW',
            'name' => 'Second Company Project',
            'company_id' => $newCompany->id,
            'status' => 'planning',
        ]);

        $this->actingAs($defaultUser);
        $this->assertEquals(1, Project::count());
        $this->assertEquals('Default Company Project', Project::first()->name);

        $this->actingAs($newCompanyAdmin);
        $this->assertEquals(1, Project::count());
        $this->assertEquals('Second Company Project', Project::first()->name);
    }

    public function test_standard_users_cannot_access_reseller_dashboard(): void
    {
        $employeeRole = Role::where('slug', 'employee')->first();
        $employee = User::factory()->create([
            'role_id' => $employeeRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($employee)->get(route('reseller.dashboard'));
        $response->assertStatus(403);
    }

    public function test_suspended_companies_cannot_access_app(): void
    {
        $newCompany = Company::create(['name' => 'Suspended Company', 'status' => 'suspended']);
        $adminRole = Role::where('slug', 'super-admin')->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'company_id' => $newCompany->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
