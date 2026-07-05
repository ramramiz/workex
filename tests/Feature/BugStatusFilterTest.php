<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Bug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class BugStatusFilterTest extends TestCase
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

        $employeeRole = Role::where('slug', 'super-admin')->first();

        // Create employee user
        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        // Create a project
        $this->project = Project::create([
            'project_code' => 'PROJ-BUG-TEST-2',
            'name'         => 'Bug Project 2',
            'description'  => 'Test Project Description',
            'status'       => 'in_progress',
        ]);
    }

    public function test_can_update_bug_status_to_new_statuses()
    {
        // Create an open bug
        $bug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Test Status Bug',
            'description' => 'Testing bug status transitions',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);

        // Update status to completed
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'completed']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('completed', $bug->fresh()->status);

        // Update status to approved
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'approved']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('approved', $bug->fresh()->status);

        // Update status to cleared
        $response = $this->actingAs($this->employeeUser)
            ->post(route('bugs.update-status', $bug), ['status' => 'cleared']);
        $response->assertJson(['success' => true]);
        $this->assertEquals('cleared', $bug->fresh()->status);
    }

    public function test_completed_bugs_filter_only_returns_completed_approved_and_cleared_bugs()
    {
        // 1. Create bugs with various statuses
        $openBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Open Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $completedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Completed Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'completed',
        ]);

        $approvedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Approved Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'approved',
        ]);

        $clearedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Cleared Bug',
            'description' => 'Test description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'cleared',
        ]);

        // 2. Fetch index normally (without filter) - defaults to pending, so it should see Open Bug but NOT completed/approved/cleared bugs
        $responseAll = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index'));
        $responseAll->assertOk();
        $responseAll->assertSee('Open Bug');
        // "Completed Bug" title should not appear in the bug table (nav tab "Completed Bugs" still shows, so check for the specific bug title as a link text)
        $responseAll->assertDontSee('href="' . route('bugs.show', $completedBug) . '"', false);
        $responseAll->assertDontSee('href="' . route('bugs.show', $approvedBug) . '"', false);
        $responseAll->assertDontSee('href="' . route('bugs.show', $clearedBug) . '"', false);

        // 3. Fetch index with completed filter
        $responseSolved = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index', ['filter' => 'completed']));
        $responseSolved->assertOk();
        $responseSolved->assertSee('href="' . route('bugs.show', $completedBug) . '"', false);
        $responseSolved->assertSee('href="' . route('bugs.show', $approvedBug) . '"', false);
        $responseSolved->assertSee('href="' . route('bugs.show', $clearedBug) . '"', false);
        $responseSolved->assertDontSee('href="' . route('bugs.show', $openBug) . '"', false);
    }

    public function test_task_status_sync_updates_bug_status(): void
    {
        $task = \App\Models\Task::create([
            'project_id' => $this->project->id,
            'title' => 'Bug Task Test',
            'description' => 'Test task description',
            'status' => 'pending',
        ]);

        $bug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Linked Bug Test',
            'description' => 'Bug details',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
            'task_id' => $task->id,
        ]);

        // 1. Test updateStatus to review
        $response = $this->actingAs($this->employeeUser)
            ->postJson(route('tasks.update-status', $task), ['status' => 'review']);
        $response->assertStatus(200);
        $this->assertEquals('under_review', $bug->fresh()->status);

        // 2. Test approveCompletion
        $responseApprove = $this->actingAs($this->employeeUser)
            ->post(route('tasks.approve-completion', $task), ['comment' => 'Approval notes']);
        $responseApprove->assertRedirect();
        $this->assertEquals('closed', $bug->fresh()->status);

        // 3. Test rejectCompletion
        $task->update(['status' => 'review']);
        $responseReject = $this->actingAs($this->employeeUser)
            ->post(route('tasks.reject-completion', $task), ['comment' => 'Rejection feedback']);
        $responseReject->assertRedirect();
        $this->assertEquals('reopened', $bug->fresh()->status);
    }

    public function test_closed_bugs_show_in_both_pending_and_completed(): void
    {
        $closedBug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Closed Bug Title',
            'description' => 'Closed bug description',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'closed',
        ]);

        // Default view (pending) should NOT see closed bug by default
        $responsePending = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index'));
        $responsePending->assertOk();
        $responsePending->assertDontSee('Closed Bug Title');

        // Bypassing default tab by using the status filter should see closed bug
        $responseFiltered = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index', ['status' => 'closed']));
        $responseFiltered->assertOk();
        $responseFiltered->assertSee('Closed Bug Title');

        // Completed view should see closed bug
        $responseCompleted = $this->actingAs($this->employeeUser)
            ->get(route('bugs.index', ['filter' => 'completed']));
        $responseCompleted->assertOk();
        $responseCompleted->assertSee('Closed Bug Title');
    }

    public function test_bug_edit_page_updates_status_and_syncs_task(): void
    {
        $task = \App\Models\Task::create([
            'project_id' => $this->project->id,
            'title' => 'Bug Task Test 2',
            'description' => 'Test task description 2',
            'status' => 'pending',
        ]);

        $bug = Bug::create([
            'project_id' => $this->project->id,
            'title' => 'Linked Bug Test 2',
            'description' => 'Bug details 2',
            'reported_by' => $this->employeeUser->id,
            'priority' => 'medium',
            'status' => 'open',
            'task_id' => $task->id,
        ]);

        $response = $this->actingAs($this->employeeUser)
            ->put(route('bugs.update', $bug), [
                'title' => 'Linked Bug Test 2 Updated',
                'project_id' => $this->project->id,
                'priority' => 'critical',
                'status' => 'closed',
                'description' => 'Updated description text',
            ]);

        $response->assertRedirect();
        $this->assertEquals('closed', $bug->fresh()->status);
        $this->assertEquals('completed', $task->fresh()->status);
    }
}
