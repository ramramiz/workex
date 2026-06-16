<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\WorkSession;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class WorkTimerTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

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
    }

    public function test_employee_can_start_work_day()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-day'));

        $response->assertRedirect();

        // Check WorkSession record
        $this->assertDatabaseHas('work_sessions', [
            'user_id' => $this->employeeUser->id,
            'status' => 'active',
        ]);

        // Check Attendance record matches singular table "attendance"
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->employeeUser->id,
            'status' => 'present',
        ]);
    }

    public function test_employee_can_end_work_day()
    {
        // Start the day first
        $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-day'));

        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.end-day'), [
                'work_done' => 'Completed task #123 and updated documentation.'
            ]);

        $response->assertRedirect();

        // Check WorkSession is ended
        $this->assertDatabaseHas('work_sessions', [
            'user_id' => $this->employeeUser->id,
            'status' => 'ended',
            'work_done' => 'Completed task #123 and updated documentation.',
        ]);

        // Check Attendance record has logout time updated
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->employeeUser->id,
            'status' => 'present',
        ]);
    }

    public function test_employee_cannot_end_work_day_without_work_done()
    {
        // Start the day first
        $this->actingAs($this->employeeUser)
            ->post(route('work-timer.start-day'));

        $response = $this->actingAs($this->employeeUser)
            ->post(route('work-timer.end-day'), []);

        $response->assertSessionHasErrors('work_done');

        // Check WorkSession is still active
        $this->assertDatabaseHas('work_sessions', [
            'user_id' => $this->employeeUser->id,
            'status' => 'active',
        ]);
    }

    public function test_employee_can_view_active_timers_on_work_timer_page()
    {
        // Create another employee with an active log
        $otherEmployee = User::factory()->create([
            'role_id' => $this->employeeUser->role_id,
        ]);
        
        $project = \App\Models\Project::create([
            'project_code' => 'PROJ-TEST-TIMERS',
            'name'         => 'Test Running Timers Project',
            'description'  => 'Test Description',
            'status'       => 'in_progress',
        ]);
        
        $task = \App\Models\Task::create([
            'title'       => 'Other Task Title',
            'project_id'  => $project->id,
            'assigned_to' => $otherEmployee->id,
            'priority'    => 'high',
            'status'      => 'in_progress',
            'created_by'  => $otherEmployee->id,
        ]);
        
        $session = \App\Models\WorkSession::create([
            'user_id'    => $otherEmployee->id,
            'date'       => today(),
            'started_at' => now()->subHours(2),
            'status'     => 'active',
        ]);
        
        $runningLog = \App\Models\TaskTimeLog::create([
            'task_id'         => $task->id,
            'user_id'         => $otherEmployee->id,
            'work_session_id' => $session->id,
            'started_at'      => now()->subMinutes(10),
            'status'          => 'running',
        ]);

        $response = $this->actingAs($this->employeeUser)
            ->get(route('work-timer.index'));

        $response->assertStatus(200);
        $response->assertViewHas('runningTimers');
        $response->assertSee('Running Timers');
        $response->assertSee($otherEmployee->name);
        $response->assertSee('Other Task Title');
    }
}
