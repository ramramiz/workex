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
}
