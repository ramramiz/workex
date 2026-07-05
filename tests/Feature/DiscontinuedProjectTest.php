<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class DiscontinuedProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@example.com'
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee@example.com'
        ]);

        $this->client = Client::create([
            'name' => 'Test Client',
            'company_name' => 'Client Inc.',
            'contact_person' => 'Test Contact Person',
            'email' => 'client@example.com',
            'phone' => '1234567890',
            'status' => 'active'
        ]);
    }

    public function test_discontinued_projects_are_hidden_by_default_and_visible_in_settings()
    {
        // 1. Create a project
        $project = Project::create([
            'name' => 'Active Project Test',
            'project_code' => 'PRJ001',
            'client_id' => $this->client->id,
            'status' => 'planning',
            'priority' => 'medium',
        ]);

        // 2. Admin can discontinue the project via POST
        $responseDiscontinue = $this->actingAs($this->adminUser)
            ->post(route('projects.discontinue', $project->id));
        $responseDiscontinue->assertRedirect(route('projects.index'));

        // Refresh and check status is discontinued
        $project->refresh();
        $this->assertEquals('discontinued', $project->status);

        // 3. Main projects index should NOT list the project now that it is discontinued
        $response = $this->actingAs($this->adminUser)->get(route('projects.index'));
        $response->assertOk();
        $response->assertDontSee('Active Project Test');

        // 4. Admin can access discontinued settings page
        $responseSettings = $this->actingAs($this->adminUser)->get(route('settings.discontinued-projects'));
        $responseSettings->assertOk();
        $responseSettings->assertSee('Active Project Test');

        // 5. Admin can view discontinued project details
        $responseShow = $this->actingAs($this->adminUser)->get(route('settings.discontinued-projects.show', $project->id));
        $responseShow->assertOk();
        $responseShow->assertSee('Active Project Test');

        // 6. Admin can reactivate project
        $responseReactivate = $this->actingAs($this->adminUser)->post(route('settings.discontinued-projects.reactivate', $project->id));
        $responseReactivate->assertRedirect(route('settings.discontinued-projects'));

        // 7. Verify status updated to planning and is now visible on main index again
        $project->refresh();
        $this->assertEquals('planning', $project->status);

        $responseIndexAfter = $this->actingAs($this->adminUser)->get(route('projects.index'));
        $responseIndexAfter->assertSee('Active Project Test');
    }
}
