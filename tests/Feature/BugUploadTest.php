<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Bug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class BugUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $employeeRole = Role::where('slug', 'admin')->first();

        // Create employee user
        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        // Create a project
        $this->project = Project::create([
            'project_code' => 'PROJ-BUG-TEST',
            'name'         => 'Bug Project',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);
    }

    public function test_user_can_create_bug_with_annotated_screenshots()
    {
        Storage::fake('public');

        // A mock base64 image (small red pixel JPEG)
        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.store'), [
                'title'       => 'Annotated Bug Title',
                'project_id'  => $this->project->id,
                'priority'    => 'high',
                'description' => 'Test annotated description.',
                'screenshots' => [
                    $base64Image,
                    $base64Image,
                ]
            ]);

        $response->assertRedirect(route('bugs.index'));

        // Retrieve created bug
        $bug = Bug::where('title', 'Annotated Bug Title')->first();
        $this->assertNotNull($bug);
        $this->assertCount(2, $bug->screenshots);

        // Verify images were saved in storage
        foreach ($bug->screenshots as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_user_can_create_bug_without_screenshots()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.store'), [
                'title'       => 'Bug Without Screenshots',
                'project_id'  => $this->project->id,
                'priority'    => 'medium',
                'description' => 'Test description without screenshots.',
            ]);

        $response->assertRedirect(route('bugs.index'));

        $bug = Bug::where('title', 'Bug Without Screenshots')->first();
        $this->assertNotNull($bug);
        $this->assertEmpty($bug->screenshots);
    }

    public function test_user_can_create_bug_with_single_screenshot()
    {
        Storage::fake('public');

        $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.store'), [
                'title'       => 'Bug With Single Screenshot',
                'project_id'  => $this->project->id,
                'priority'    => 'low',
                'description' => 'Test description with one screenshot.',
                'screenshots' => [
                    $base64Image
                ]
            ]);

        $response->assertRedirect(route('bugs.index'));

        $bug = Bug::where('title', 'Bug With Single Screenshot')->first();
        $this->assertNotNull($bug);
        $this->assertCount(1, $bug->screenshots);
        Storage::disk('public')->assertExists($bug->screenshots[0]);
    }

    public function test_user_can_create_bug_with_link()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.store'), [
                'title'       => 'Bug With Link',
                'project_id'  => $this->project->id,
                'priority'    => 'medium',
                'description' => 'Test description with a link.',
                'link'        => 'http://example.com/bug-page',
            ]);

        $response->assertRedirect(route('bugs.index'));

        $bug = Bug::where('title', 'Bug With Link')->first();
        $this->assertNotNull($bug);
        $this->assertEquals('http://example.com/bug-page', $bug->link);
    }

    public function test_bug_creation_creates_task_and_syncs_comments()
    {
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.store'), [
                'title'       => 'Bug For Sync Test',
                'project_id'  => $this->project->id,
                'priority'    => 'medium',
                'description' => 'Test description for sync.',
                'assigned_to' => $this->employeeUser->id,
            ]);

        $response->assertRedirect(route('bugs.index'));

        $bug = Bug::where('title', 'Bug For Sync Test')->first();
        $this->assertNotNull($bug);
        
        $this->assertNotNull($bug->task_id);
        $task = \App\Models\Task::find($bug->task_id);
        $this->assertNotNull($task);
        $this->assertEquals('Bug: Bug For Sync Test', $task->title);
        $this->assertEquals($this->employeeUser->id, $task->assigned_to);

        $responseComment = $this->actingAs($this->employeeUser)
            ->post(route('bugs.comments.store', $bug), [
                'comment' => 'Comment on Bug Log',
            ]);

        $responseComment->assertRedirect();
        
        $this->assertDatabaseHas('bug_comments', [
            'bug_id' => $bug->id,
            'comment' => 'Comment on Bug Log',
        ]);
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'comment' => 'Comment on Bug Log',
        ]);
    }
}
