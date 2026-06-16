<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\RoleSeeder;

class TaskUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(RoleSeeder::class);

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

        // Create a project
        $this->project = Project::create([
            'project_code' => 'PROJ-TEST',
            'name'         => 'Test Project',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);
    }

    public function test_can_create_task_with_image_attachment()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('screenshot.png');

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), [
                'title'       => 'New Task with File',
                'project_id'  => $this->project->id,
                'assigned_to' => $this->employeeUser->id,
                'priority'    => 'medium',
                'description' => 'Test task description',
                'attachment'  => $file,
            ]);

        $response->assertSessionHasNoErrors();

        $task = Task::where('title', 'New Task with File')->first();
        $this->assertNotNull($task);

        $response->assertRedirect(route('tasks.show', $task));

        // Check file was uploaded
        Storage::disk('public')->assertExists('tasks/' . $task->id . '/' . $file->hashName());

        // Check task file record
        $this->assertDatabaseHas('task_files', [
            'task_id'   => $task->id,
            'user_id'   => $this->adminUser->id,
            'file_name' => 'screenshot.png',
        ]);
    }

    public function test_can_create_task_with_pdf_attachment()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), [
                'title'       => 'New Task with PDF',
                'project_id'  => $this->project->id,
                'assigned_to' => $this->employeeUser->id,
                'priority'    => 'medium',
                'description' => 'Test task description',
                'attachment'  => $file,
            ]);

        $response->assertSessionHasNoErrors();

        $task = Task::where('title', 'New Task with PDF')->first();
        $this->assertNotNull($task);

        $response->assertRedirect(route('tasks.show', $task));

        // Check file was uploaded
        Storage::disk('public')->assertExists('tasks/' . $task->id . '/' . $file->hashName());

        // Check task file record
        $this->assertDatabaseHas('task_files', [
            'task_id'   => $task->id,
            'user_id'   => $this->adminUser->id,
            'file_name' => 'document.pdf',
        ]);
    }

    public function test_can_add_attachment_when_updating_task()
    {
        Storage::fake('public');

        $task = Task::create([
            'title'       => 'Existing Task',
            'project_id'  => $this->project->id,
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->adminUser->id,
        ]);

        $file = UploadedFile::fake()->image('design.jpg');

        $response = $this->actingAs($this->adminUser)
            ->put(route('tasks.update', $task), [
                'title'       => 'Updated Task Title',
                'priority'    => 'high',
                'attachment'  => $file,
            ]);

        $response->assertRedirect(route('tasks.show', $task));

        // Check file was uploaded
        Storage::disk('public')->assertExists('tasks/' . $task->id . '/' . $file->hashName());

        // Check task file record
        $this->assertDatabaseHas('task_files', [
            'task_id'   => $task->id,
            'user_id'   => $this->adminUser->id,
            'file_name' => 'design.jpg',
        ]);
    }

    public function test_upload_file_route_uses_user_id_column()
    {
        Storage::fake('public');

        $task = Task::create([
            'title'       => 'Test uploadFile Method Bugfix',
            'project_id'  => $this->project->id,
            'assigned_to' => $this->employeeUser->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->adminUser->id,
        ]);

        $file = UploadedFile::fake()->image('direct_upload.png');

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.files.store', $task), [
                'file' => $file,
            ]);

        $response->assertRedirect();

        // Check file was uploaded
        Storage::disk('public')->assertExists('tasks/' . $task->id . '/' . $file->hashName());

        // Check task file record was saved successfully without DB error on uploaded_by column
        $this->assertDatabaseHas('task_files', [
            'task_id'   => $task->id,
            'user_id'   => $this->adminUser->id,
            'file_name' => 'direct_upload.png',
        ]);
    }
}
