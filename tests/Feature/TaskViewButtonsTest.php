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

class TaskViewButtonsTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;
    protected Project $project;
    protected Task $task;

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
            'project_code' => 'PROJ-TEST-TIMER',
            'name'         => 'Test Project',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);

        // Create a task
        $this->task = Task::create([
            'title'       => 'Work Timer Test Task',
            'project_id'  => $this->project->id,
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->employeeUser->id,
        ]);
    }

    public function test_task_view_shows_start_work_and_complete_buttons_when_idle()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('tasks.show', $this->task));

        $response->assertStatus(200);
        $response->assertSee('Start Work');
        $response->assertSee('Submit for Review');
        $response->assertDontSee('End Work');
    }

    public function test_can_start_work_timer_from_task_view()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-task', $this->task));

        $response->assertRedirect();

        // Verify task status changed to in_progress
        $this->assertEquals('in_progress', $this->task->fresh()->status);

        // Verify time log is running
        $this->assertDatabaseHas('task_time_logs', [
            'task_id' => $this->task->id,
            'user_id' => $this->employeeUser->id,
            'status'  => 'running',
        ]);

        // Verify WorkSession and Attendance were auto-created in background
        $this->assertDatabaseHas('work_sessions', [
            'user_id' => $this->employeeUser->id,
            'status'  => 'active',
        ]);
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->employeeUser->id,
            'status'  => 'present',
        ]);
    }

    public function test_task_view_shows_end_work_button_when_timer_running()
    {
        // Start task directly
        $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-task', $this->task));

        $response = $this->actingAs($this->employeeUser)
            ->get(route('tasks.show', $this->task));

        $response->assertStatus(200);
        $response->assertSee('End Work');
        $response->assertDontSee('Start Work');
        $response->assertDontSee('data-bs-target="#taskCompletionModal"');
    }

    public function test_can_end_work_timer_from_task_view()
    {
        // Start task directly
        $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-task', $this->task));

        $activeLog = TaskTimeLog::where('task_id', $this->task->id)->where('status', 'running')->first();
        $this->assertNotNull($activeLog);

        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.end-task', $activeLog));

        $response->assertRedirect();

        // Verify time log is ended
        $this->assertEquals('ended', $activeLog->fresh()->status);
    }

    public function test_employee_cannot_complete_task_directly_from_task_view()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('tasks.update-status', $this->task), [
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Task completion must be approved through the approvals queue.');

        // Verify task status remains pending
        $this->assertEquals('pending', $this->task->fresh()->status);
    }

    public function test_admin_cannot_complete_task_directly_from_task_view()
    {
        $adminRole = Role::where('slug', 'super-admin')->first();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->post(route('tasks.update-status', $this->task), [
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Task completion must be approved through the approvals queue.');

        // Verify task status remains pending
        $this->assertEquals('pending', $this->task->fresh()->status);
    }

    public function test_reactivates_ended_work_session_when_starting_task()
    {
        // Create an ended work session for today
        $session = WorkSession::create([
            'user_id' => $this->employeeUser->id,
            'date' => today(),
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'status' => 'ended',
        ]);

        // Start a task
        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-task', $this->task));

        $response->assertRedirect();

        // Verify it didn't throw UniqueConstraintViolationException and reactivated the session
        $this->assertEquals('active', $session->fresh()->status);
    }
}
