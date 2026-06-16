<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class TaskProjectAssociationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected Project $project;

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

        $this->project = Project::create([
            'project_code' => 'PROJ-TEST',
            'name'         => 'Test Project',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);
    }

    public function test_can_create_task_with_project()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), [
                'title'       => 'Task with project',
                'project_id'  => $this->project->id,
                'assigned_to' => $this->employeeUser->id,
                'priority'    => 'medium',
                'description' => 'Test description',
            ]);

        $response->assertSessionHasNoErrors();
        
        $task = Task::where('title', 'Task with project')->first();
        $this->assertNotNull($task);
        $this->assertEquals($this->project->id, $task->project_id);
    }

    public function test_can_create_task_without_project()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), [
                'title'       => 'Task without project',
                'project_id'  => '',
                'assigned_to' => $this->employeeUser->id,
                'priority'    => 'medium',
                'description' => 'Test description',
            ]);

        $response->assertSessionHasNoErrors();
        
        $task = Task::where('title', 'Task without project')->first();
        $this->assertNotNull($task);
        $this->assertNull($task->project_id);
    }

    public function test_can_update_task_project_association()
    {
        $task = Task::create([
            'title'       => 'Task to Update',
            'project_id'  => $this->project->id,
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->adminUser->id,
        ]);

        // Remove project
        $response = $this->actingAs($this->adminUser)
            ->put(route('tasks.update', $task), [
                'title'      => 'Updated Title',
                'project_id' => '',
                'priority'   => 'high',
            ]);

        $response->assertSessionHasNoErrors();
        $task->refresh();
        $this->assertNull($task->project_id);

        // Re-add project
        $response = $this->actingAs($this->adminUser)
            ->put(route('tasks.update', $task), [
                'title'      => 'Updated Title 2',
                'project_id' => $this->project->id,
                'priority'   => 'high',
            ]);

        $response->assertSessionHasNoErrors();
        $task->refresh();
        $this->assertEquals($this->project->id, $task->project_id);
    }
}
