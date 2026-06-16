<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Models\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class TaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected User $employeeUser2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->employeeUser2 = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);
    }

    public function test_creating_task_generates_notification_for_assignee()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), [
                'title'       => 'Test Notification Task',
                'assigned_to' => $this->employeeUser->id,
                'priority'    => 'medium',
                'description' => 'Test description',
            ]);

        $response->assertSessionHasNoErrors();

        // Assert notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employeeUser->id,
            'type'    => 'task_assigned',
            'title'   => 'New Task Assigned',
        ]);
    }

    public function test_updating_task_assignee_generates_notification()
    {
        $task = Task::create([
            'title'       => 'Task to reassign',
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->adminUser->id,
        ]);

        // Reassign to employeeUser2
        $response = $this->actingAs($this->adminUser)
            ->put(route('tasks.update', $task), [
                'title'       => 'Task to reassign',
                'assigned_to' => $this->employeeUser2->id,
                'priority'    => 'medium',
            ]);

        $response->assertSessionHasNoErrors();

        // Assert notification created for the new assignee
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employeeUser2->id,
            'type'    => 'task_assigned',
            'title'   => 'New Task Assigned',
        ]);
    }
}
