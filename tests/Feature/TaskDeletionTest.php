<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class TaskDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $employee;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->task = Task::create([
            'title'       => 'Task to delete',
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->admin->id,
        ]);
    }

    public function test_super_admin_can_delete_task()
    {
        $response = $this->actingAs($this->superAdmin)
            ->delete(route('tasks.destroy', $this->task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Task deleted.');
        
        $this->assertSoftDeleted('tasks', [
            'id' => $this->task->id,
        ]);
    }

    public function test_regular_admin_cannot_delete_task()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('tasks.destroy', $this->task));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
        ]);
    }

    public function test_employee_cannot_delete_task()
    {
        $response = $this->actingAs($this->employee)
            ->delete(route('tasks.destroy', $this->task));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
        ]);
    }
}
