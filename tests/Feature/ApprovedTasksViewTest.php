<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class ApprovedTasksViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employee1;
    protected User $employee2;
    protected User $teamLeader;
    protected Project $project1;
    protected Project $project2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();

        // Create Users
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@test.com',
        ]);

        $this->employee1 = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee1@test.com',
        ]);

        $this->employee2 = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee2@test.com',
        ]);

        $this->teamLeader = User::factory()->create([
            'role_id' => $teamLeaderRole->id,
            'email' => 'tl@test.com',
        ]);

        // Create Projects
        $this->project1 = Project::create([
            'project_code' => 'P1',
            'name' => 'Project One',
            'status' => 'in_progress',
        ]);

        $this->project2 = Project::create([
            'project_code' => 'P2',
            'name' => 'Project Two',
            'status' => 'in_progress',
        ]);
    }

    public function test_guests_cannot_access_approved_tasks()
    {
        $response = $this->get(route('tasks.approved'));
        $response->assertRedirect(route('login'));
    }

    public function test_employee_can_only_see_their_own_approved_tasks()
    {
        // Create approved tasks
        $task1 = Task::create([
            'title' => 'Approved Task for Employee 1',
            'project_id' => $this->project1->id,
            'assigned_to' => $this->employee1->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $task2 = Task::create([
            'title' => 'Approved Task for Employee 2',
            'project_id' => $this->project2->id,
            'assigned_to' => $this->employee2->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        // Create a non-completed task for employee 1 (should not appear in approved tasks)
        $task3 = Task::create([
            'title' => 'Pending Task for Employee 1',
            'project_id' => $this->project1->id,
            'assigned_to' => $this->employee1->id,
            'created_by' => $this->adminUser->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->employee1)
            ->get(route('tasks.approved'));

        $response->assertStatus(200);
        $response->assertSee($task1->title);
        $response->assertDontSee($task2->title);
        $response->assertDontSee($task3->title);
    }

    public function test_admin_and_team_leader_can_see_all_approved_tasks()
    {
        $task1 = Task::create([
            'title' => 'Task One Completed',
            'project_id' => $this->project1->id,
            'assigned_to' => $this->employee1->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        $task2 = Task::create([
            'title' => 'Task Two Completed',
            'project_id' => $this->project2->id,
            'assigned_to' => $this->employee2->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'completed_date' => now(),
        ]);

        // Admin checks approved tasks
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get(route('tasks.approved'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee($task1->title);
        $responseAdmin->assertSee($task2->title);

        // Team Leader checks approved tasks
        $responseTL = $this->actingAs($this->teamLeader)
            ->get(route('tasks.approved'));
        $responseTL->assertStatus(200);
        $responseTL->assertSee($task1->title);
        $responseTL->assertSee($task2->title);
    }

    public function test_filters_on_approved_tasks_page()
    {
        $task1 = Task::create([
            'title' => 'Query Matching Completed',
            'project_id' => $this->project1->id,
            'assigned_to' => $this->employee1->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'priority' => 'high',
            'completed_date' => now(),
        ]);

        $task2 = Task::create([
            'title' => 'Other Title Completed',
            'project_id' => $this->project2->id,
            'assigned_to' => $this->employee2->id,
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
            'priority' => 'low',
            'completed_date' => now(),
        ]);

        // Test search query filter
        $response = $this->actingAs($this->adminUser)
            ->get(route('tasks.approved', ['search' => 'Query']));
        $response->assertStatus(200);
        $response->assertSee($task1->title);
        $response->assertDontSee($task2->title);

        // Test project filter
        $response = $this->actingAs($this->adminUser)
            ->get(route('tasks.approved', ['project' => $this->project2->id]));
        $response->assertStatus(200);
        $response->assertDontSee($task1->title);
        $response->assertSee($task2->title);

        // Test priority filter
        $response = $this->actingAs($this->adminUser)
            ->get(route('tasks.approved', ['priority' => 'high']));
        $response->assertStatus(200);
        $response->assertSee($task1->title);
        $response->assertDontSee($task2->title);
    }
}
