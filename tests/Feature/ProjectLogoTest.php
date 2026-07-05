<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProjectLogoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'avatar'  => 'avatars/admin.jpg',
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'avatar'  => 'avatars/employee.jpg',
        ]);
    }

    public function test_create_project_with_logo_uploads_and_stores()
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->image('project_logo.png');

        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.store'), [
                'name' => 'Project with Logo',
                'priority' => 'high',
                'logo' => $logoFile,
            ]);

        $project = Project::where('name', 'Project with Logo')->first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->logo_path);

        Storage::disk('public')->assertExists($project->logo_path);
    }

    public function test_update_project_logo_deletes_old_logo_and_stores_new()
    {
        Storage::fake('public');

        // Create initial project with logo
        $logoFile1 = UploadedFile::fake()->image('old_logo.png');
        $oldPath = $logoFile1->store('project_logos', 'public');

        $project = Project::create([
            'project_code' => 'PRJ-1234',
            'name' => 'Project to Update',
            'logo_path' => $oldPath,
            'priority' => 'medium',
            'status' => 'planning',
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Update project with new logo
        $logoFile2 = UploadedFile::fake()->image('new_logo.png');

        $response = $this->actingAs($this->adminUser)
            ->put(route('projects.update', $project), [
                'name' => 'Project to Update',
                'priority' => 'high',
                'logo' => $logoFile2,
            ]);

        $project->refresh();
        $this->assertNotEquals($oldPath, $project->logo_path);
        Storage::disk('public')->assertExists($project->logo_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_task_avatar_url_returns_project_logo_when_available()
    {
        $project = Project::create([
            'project_code' => 'PRJ-5678',
            'name' => 'Task Project',
            'logo_path' => 'project_logos/task_project_logo.png',
            'priority' => 'medium',
            'status' => 'planning',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'assigned_to' => $this->employeeUser->id,
            'created_by' => $this->adminUser->id,
            'title' => 'Sample Task',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // When project has logo, task avatar_url should be the asset of the project logo
        $this->assertEquals(asset('storage/' . $project->logo_path), $task->avatar_url);

        // When task does not have project, task avatar_url should fallback to assignee avatar
        $task->project_id = null;
        $task->save();
        $task->refresh();

        $this->assertEquals($this->employeeUser->avatar_url, $task->avatar_url);
    }

    public function test_create_project_with_url_stores_url()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.store'), [
                'name' => 'Project with URL',
                'priority' => 'high',
                'url' => 'https://example.com/project-landing',
            ]);

        $project = Project::where('name', 'Project with URL')->first();
        $this->assertNotNull($project);
        $this->assertEquals('https://example.com/project-landing', $project->url);
    }

    public function test_update_project_url()
    {
        $project = Project::create([
            'project_code' => 'PRJ-URL-TEST',
            'name' => 'Project to Update URL',
            'priority' => 'medium',
            'status' => 'planning',
            'url' => 'https://oldurl.com',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('projects.update', $project), [
                'name' => 'Project to Update URL',
                'priority' => 'high',
                'url' => 'https://newurl.com',
            ]);

        $project->refresh();
        $this->assertEquals('https://newurl.com', $project->url);
    }
}
