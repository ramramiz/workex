<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Models\WorkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class LiveStatusTaskLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->task = Task::create([
            'title' => 'Test Task Redesign',
            'assigned_to' => $this->employeeUser->id,
            'priority' => 'high',
            'status' => 'in_progress',
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_live_status_data_returns_task_id_for_active_and_completed_tasks()
    {
        $session = WorkSession::create([
            'user_id' => $this->employeeUser->id,
            'date' => today(),
            'started_at' => now()->subHours(2),
            'status' => 'active',
        ]);

        // Start working on the task (active log)
        $session->timeLogs()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->employeeUser->id,
            'started_at' => now()->subHour(),
            'status' => 'running',
        ]);

        // End another log for completed tasks
        $session->timeLogs()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->employeeUser->id,
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'status' => 'ended',
            'note' => 'Worked on header',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);

        $employees = $response->json('employees');
        $employeeData = collect($employees)->firstWhere('id', $this->employeeUser->id);

        $this->assertNotNull($employeeData);
        $this->assertEquals($this->task->id, $employeeData['current_task_id']);
        $this->assertEquals($this->task->id, $employeeData['working_tasks'][0]['task_id']);
        $this->assertEquals($this->task->id, $employeeData['completed_work'][0]['task_id']);
    }
}
