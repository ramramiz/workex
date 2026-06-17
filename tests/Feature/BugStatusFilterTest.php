<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Bug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class BugStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $employeeRole = Role::where('slug', 'employee')->first();

        // Create employee user
        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        // Create a project
        $this->project = Project::create([
            'project_code' => 'PROJ-BUG-TEST-2',
            'name'         => 'Bug Project 2',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);
    }

    public function test_can_update_bug_status_to_new_statuses()
    {
        // Create an open bug
        $bug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Test Status Bug',
            'description' => 'Testing bug status transitions',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);

        // Update status to completed
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'completed']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('completed', $bug->fresh()->status);

        // Update status to approved
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'approved']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('approved', $bug->fresh()->status);

        // Update status to cleared
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'cleared']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('cleared', $bug->fresh()->status);
    }

    public function test_solved_bugs_filter_only_returns_approved_and_cleared_bugs()
    {
        // 1. Create bugs with various statuses
        $openBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Open Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $completedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Completed Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'completed',
        ]);

        $approvedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Approved Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'approved',
        ]);

        $clearedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Cleared Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'cleared',
        ]);

        // 2. Fetch index normally (without filter)
        $responseAll = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index'));
        $responseAll->assertOk();
        $responseAll->assertSee('Open Bug');
        $responseAll->assertSee('Completed Bug');
        $responseAll->assertSee('Approved Bug');
        $responseAll->assertSee('Cleared Bug');

        // 3. Fetch index with solved filter
        $responseSolved = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index', ['filter' => 'solved']));
        $responseSolved->assertOk();
        $responseSolved->assertSee('Approved Bug');
        $responseSolved->assertSee('Cleared Bug');
        $responseSolved->assertDontSee('Open Bug');
        $responseSolved->assertDontSee('Completed Bug');
    }
}
