<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class MeetingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

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
    }

    public function test_user_can_access_meetings_index()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('meetings.index'));

        $response->assertStatus(200);
        $response->assertSee('Meetings & Discussions');
    }

    public function test_user_can_view_create_meeting_form_with_default_location()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('meetings.create'));

        $response->assertStatus(200);
        $response->assertSee('Kottakkal');
    }

    public function test_user_can_create_meeting()
    {
        $meetingData = [
            'title' => 'Important Strategic Session',
            'meeting_date' => today()->addDays(2)->toDateString(),
            'location' => 'Kottakkal',
            'description' => 'Planning Q3 milestones and deliverables.',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('meetings.store'), $meetingData);

        $this->assertDatabaseHas('meetings', [
            'title' => 'Important Strategic Session',
            'location' => 'Kottakkal',
            'created_by' => $this->adminUser->id,
        ]);

        $meeting = Meeting::first();
        $response->assertRedirect(route('meetings.show', $meeting));
    }

    public function test_user_can_create_task_linked_to_meeting()
    {
        $meeting = Meeting::create([
            'title' => 'Brainstorming Session',
            'meeting_date' => today()->toDateString(),
            'location' => 'Kottakkal',
            'created_by' => $this->adminUser->id,
        ]);

        // Access task creation page passing meeting_id
        $response = $this->actingAs($this->adminUser)
            ->get(route('tasks.create', ['meeting_id' => $meeting->id]));

        $response->assertStatus(200);
        $response->assertSee($meeting->title);

        // Store task and redirect back to meeting details page
        $taskData = [
            'title' => 'Create High-Fidelity UI Prototypes',
            'description' => 'Task assigned from meeting.',
            'assigned_to' => $this->employeeUser->id,
            'priority' => 'high',
            'meeting_id' => $meeting->id,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.store'), $taskData);

        $response->assertRedirect(route('meetings.show', $meeting));

        $this->assertDatabaseHas('tasks', [
            'title' => 'Create High-Fidelity UI Prototypes',
            'meeting_id' => $meeting->id,
        ]);

        // Verify that the task contains the tag on the tasks index
        $indexResponse = $this->actingAs($this->adminUser)
            ->get(route('tasks.index'));
        
        $expectedTag = 'meeting-' . $meeting->meeting_date->format('Y-m-d');
        $indexResponse->assertSee($expectedTag);
    }
}
