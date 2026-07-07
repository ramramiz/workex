<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\TaskTimeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class AdminCurrentWorksUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create employee user
        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);
    }

    public function test_admin_can_view_current_works_update_modal_on_dashboard()
    {
        // Create a project and task for the employee
        $project = Project::create([
            'project_code' => 'PROJ-TEST-LIVE',
            'name'         => 'Test Live Project',
            'description'  => 'Test Description',
            'status'       => 'in_progress',
        ]);

        $task = Task::create([
            'title'       => 'Live Tracking Task',
            'project_id'  => $project->id,
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'high',
            'status'      => 'in_progress',
            'created_by'  => $this->adminUser->id,
        ]);

        // Create active work session and running time log
        $session = WorkSession::create([
            'user_id'     => $this->employeeUser->id,
            'date'        => today(),
            'started_at'  => now()->subHours(2),
            'status'      => 'active',
        ]);

        $runningLog = TaskTimeLog::create([
            'task_id'         => $task->id,
            'user_id'         => $this->employeeUser->id,
            'work_session_id' => $session->id,
            'started_at'      => now()->subMinutes(30),
            'status'          => 'running',
        ]);

        // Access the dashboard as an Admin
        $response = $this->actingAs($this->adminUser)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('employees');
        $response->assertSee('Current Works Update');
        $response->assertSee($this->employeeUser->name);
        $response->assertSee('Live Tracking Task');
    }

    public function test_admin_dashboard_pending_projects_filtering_and_count()
    {
        // 1. Create active projects (should be included)
        $activeProject = Project::create([
            'project_code' => 'PRJ-ACT-001',
            'name'         => 'Active Development Project',
            'status'       => 'development',
        ]);

        $planningProject = Project::create([
            'project_code' => 'PRJ-ACT-002',
            'name'         => 'Planning Project',
            'status'       => 'planning',
        ]);

        // 2. Create completed/cancelled projects (should be excluded)
        $completedProject = Project::create([
            'project_code' => 'PRJ-COM-001',
            'name'         => 'Completed Project',
            'status'       => 'completed',
        ]);

        $amcProject = Project::create([
            'project_code' => 'PRJ-AMC-001',
            'name'         => 'Completed & Started AMC Project',
            'status'       => 'completed_started_amc',
        ]);

        $cancelledProject = Project::create([
            'project_code' => 'PRJ-CAN-001',
            'name'         => 'Cancelled Project',
            'status'       => 'cancelled',
        ]);

        // Access dashboard as admin
        $response = $this->actingAs($this->adminUser)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check recentProjects passed to view has the active ones and excludes completed ones
        $recentProjects = $response->viewData('recentProjects');
        $this->assertNotNull($recentProjects);

        $projectNames = $recentProjects->pluck('name')->toArray();
        $this->assertContains('Active Development Project', $projectNames);
        $this->assertContains('Planning Project', $projectNames);
        
        $this->assertNotContains('Completed Project', $projectNames);
        $this->assertNotContains('Completed & Started AMC Project', $projectNames);
        $this->assertNotContains('Cancelled Project', $projectNames);

        // Verify the HTML contains the active count badge and project names, but not the completed ones
        $response->assertSee('Active Development Project');
        $response->assertSee('Planning Project');
        $response->assertDontSee('Completed & Started AMC Project');
        
        // Assert the exact "Active" badge count is correct in the view (there are 2 active projects in this context)
        // Active projects in DB = PRJ-ACT-001 (development), PRJ-ACT-002 (planning). Total = 2.
        $response->assertSee('2 Active');

        // Assert KPI boxes are linked to their respective routes
        $response->assertSee(route('employees.index'));
        $response->assertSee(route('projects.index'));
        $response->assertSee(route('tasks.index'));
        $response->assertSee(route('chat.index') . '?filter=review');
    }
}
