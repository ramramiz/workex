<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TaskUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SettingsSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $adminRole = Role::where('slug', 'admin')->first();

        // Create admin user
        $this->employeeUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create a project
        $this->project = Project::create([
            'project_code' => 'PROJ-TASK-TEST',
            'name'         => 'Test Project',
            'status'       => 'in_progress',
        ]);
    }

    public function test_user_can_create_task_with_base64_attachments()
    {
        Storage::fake('public');

        // A mock base64 image (small red pixel JPEG)
        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $response = $this->actingAs($this->employeeUser)
            ->post(route('tasks.store'), [
                'title'        => 'Task with base64 Attachments',
                'project_id'   => $this->project->id,
                'assigned_to'  => $this->employeeUser->id,
                'priority'     => 'high',
                'description'  => 'Test task description.',
                'attachments'  => [
                    $base64Image,
                    $base64Image,
                ]
            ]);

        // Retrieve created task
        $task = Task::where('title', 'Task with base64 Attachments')->first();
        $this->assertNotNull($task);

        $taskFiles = TaskFile::where('task_id', $task->id)->get();
        $this->assertCount(2, $taskFiles);

        // Verify files exist in public storage
        foreach ($taskFiles as $taskFile) {
            Storage::disk('public')->assertExists($taskFile->file_path);
        }
    }

    public function test_user_can_create_task_with_standard_file_attachment()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test_doc.jpg');

        $response = $this->actingAs($this->employeeUser)
            ->post(route('tasks.store'), [
                'title'        => 'Task with File Attachment',
                'project_id'   => $this->project->id,
                'assigned_to'  => $this->employeeUser->id,
                'priority'     => 'medium',
                'description'  => 'Test task description with file attachment.',
                'attachment'   => $file,
            ]);

        $task = Task::where('title', 'Task with File Attachment')->first();
        $this->assertNotNull($task);

        $taskFiles = TaskFile::where('task_id', $task->id)->get();
        $this->assertCount(1, $taskFiles);

        $taskFile = $taskFiles->first();
        $this->assertEquals('test_doc.jpg', $taskFile->file_name);
        Storage::disk('public')->assertExists($taskFile->file_path);
    }
}
