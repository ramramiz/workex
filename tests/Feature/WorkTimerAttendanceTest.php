<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Models\Project;
use App\Models\Attendance;
use App\Models\WorkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkTimerAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Standard employee',
            'color' => '#6366f1'
        ]);

        $this->employee = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active'
        ]);

        $project = Project::create([
            'project_code' => 'PROJ-TEST-ATT',
            'name' => 'Test Project',
            'description' => 'Test description',
            'status' => 'development',
        ]);

        $this->task = Task::create([
            'title' => 'Test Task',
            'description' => 'Task desc',
            'project_id' => $project->id,
            'status' => 'pending',
            'assigned_to' => $this->employee->id,
        ]);
    }

    public function test_login_time_is_null_on_start_day_but_populated_on_first_task_start(): void
    {
        // 1. Start Day
        $response = $this->actingAs($this->employee)
            ->post(route('work-timer.start-day'));

        $response->assertRedirect();
        
        // Assert attendance record exists, but login_time is null
        $attendance = Attendance::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($attendance);
        $this->assertNull($attendance->login_time);

        // 2. Start Task
        $response2 = $this->actingAs($this->employee)
            ->post(route('work-timer.start-task', $this->task));

        $response2->assertRedirect();

        // Assert attendance record login_time is now populated
        $attendance->refresh();
        $this->assertNotNull($attendance->login_time);
    }
}
