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

        $employeeRole = Role::where('slug', 'employee')->first();

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
}
