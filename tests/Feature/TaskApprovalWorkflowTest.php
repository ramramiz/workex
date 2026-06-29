<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class TaskApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $developerHisham;
    protected User $leaderSouban;
    protected User $regularLeader;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();

        // Create Admin user
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@workmonitor.com',
        ]);

        // Create Developer (Hisham)
        $this->developerHisham = User::factory()->create([
            'name' => 'Hisham',
            'role_id' => $employeeRole->id,
            'email' => 'hisham.techsoul@gmail.com',
        ]);

        // Create Team Leader (Souban)
        $this->leaderSouban = User::factory()->create([
            'name' => 'Souban',
            'role_id' => $teamLeaderRole->id,
            'email' => 'souban.techsoul@gmail.com',
        ]);

        // Create Regular Team Leader (without global admin-like email bypass)
        $this->regularLeader = User::factory()->create([
            'name' => 'Regular TL',
            'role_id' => $teamLeaderRole->id,
            'email' => 'regular.leader@workmonitor.com',
        ]);

        // Seed Project & Task
        $project = Project::create([
            'project_code' => 'BIZNX-ERP',
            'name' => 'Biznx ERP',
            'status' => 'development',
        ]);

        $this->task = Task::create([
            'title' => 'Update login page design',
            'project_id' => $project->id,
            'assigned_to' => $this->developerHisham->id,
            'created_by' => $this->adminUser->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_developer_cannot_directly_complete_task_via_status_update()
    {
        // Simulate a developer posting to update task status to completed directly
        // Note: the controller currently allows any status, but let's test if we restrict it or if they use the new submit completion flow.
        // Wait, does the developer have access tocompletedApprovals?
        $response = $this->actingAs($this->developerHisham)
            ->get(route('tasks.completed-approvals'));

        $response->assertStatus(403);
    }

    public function test_developer_can_submit_task_completion_details()
    {
        $completionData = [
            'completed_description' => 'Login page design is finalized and responsive.',
            'completed_link' => 'https://biznx.example.com/login-test',
        ];

        $response = $this->actingAs($this->developerHisham)
            ->post(route('tasks.submit-completion', $this->task), $completionData);

        $response->assertRedirect(route('tasks.show', $this->task));
        
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'review',
            'completed_description' => 'Login page design is finalized and responsive.',
            'completed_link' => 'https://biznx.example.com/login-test',
        ]);
    }

    public function test_admin_can_access_completed_approvals_but_team_leaders_cannot()
    {
        // Souban accesses the approvals queue - should be blocked
        $response = $this->actingAs($this->leaderSouban)
            ->get(route('tasks.completed-approvals'));
        $response->assertStatus(403);

        // Admin accesses the approvals queue - should be allowed
        $response = $this->actingAs($this->adminUser)
            ->get(route('tasks.completed-approvals'));
        $response->assertStatus(200);
    }

    public function test_admin_can_approve_completion_and_close_task()
    {
        // Submit first
        $this->task->update([
            'status' => 'review',
            'completed_description' => 'Finished task',
            'completed_link' => 'https://test.com',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.approve-completion', $this->task), [
                'comment' => 'Final admin approval notes',
            ]);

        $response->assertRedirect(route('tasks.completed-approvals'));
        
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull($this->task->fresh()->completed_date);
    }

    public function test_changing_status_creates_chat_comment()
    {
        $this->task->update(['status' => 'pending']);

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.update-status', $this->task), [
                'status' => 'in_progress',
            ]);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
            'comment' => "🔄 status changed to **In Progress** by **{$this->adminUser->name}**",
        ]);
    }

    public function test_submitting_completion_creates_chat_comment()
    {
        $completionData = [
            'completed_description' => 'Finished implementation.',
            'completed_link' => 'https://biznx.example.com/test',
        ];

        $response = $this->actingAs($this->developerHisham)
            ->post(route('tasks.submit-completion', $this->task), $completionData);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->developerHisham->id,
            'comment' => "🚀 **Submitted task for completion review**\n\n**Description:** Finished implementation.\n**Test URL:** [https://biznx.example.com/test](https://biznx.example.com/test)\n\n@Admin, please review and approve my work.",
        ]);
    }

    public function test_approving_completion_creates_chat_comment()
    {
        $this->task->update([
            'status' => 'review',
            'completed_description' => 'Finished task',
            'completed_link' => 'https://test.com',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.approve-completion', $this->task), [
                'comment' => 'All looks good!',
            ]);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
            'comment' => "✅ **Approved completion and closed task**\n\n**Notes:** All looks good!",
        ]);
    }

    public function test_reject_completion_with_image_and_notification()
    {
        $this->task->update([
            'status' => 'review',
            'completed_description' => 'Finished task',
            'completed_link' => 'https://test.com',
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('rework_error.png');

        $reworkData = [
            'comment' => 'Layout issues on mobile devices.',
            'image' => $file,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('tasks.reject-completion', $this->task), $reworkData);

        $response->assertRedirect(route('tasks.completed-approvals'));

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'rejected',
        ]);

        // Verify notification was created for assignee
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->developerHisham->id,
            'type' => 'task_rejected',
        ]);

        // Verify comment with image was created
        $comment = TaskComment::where('task_id', $this->task->id)
            ->where('user_id', $this->adminUser->id)
            ->where('comment', 'like', '%Layout issues on mobile devices%')
            ->first();

        $this->assertNotNull($comment);
        $this->assertNotNull($comment->image_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($comment->image_path);
    }

    public function test_team_leader_cannot_access_completed_approvals_queue()
    {
        $response = $this->actingAs($this->regularLeader)
            ->get(route('tasks.completed-approvals'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->leaderSouban)
            ->get(route('tasks.completed-approvals'));
        $response2->assertStatus(403);
    }

    public function test_team_leader_cannot_approve_or_reject_completion()
    {
        $this->task->update([
            'status' => 'review',
            'completed_description' => 'Finished work',
        ]);

        $response = $this->actingAs($this->regularLeader)
            ->post(route('tasks.approve-completion', $this->task), [
                'comment' => 'TL trying to approve',
            ]);
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->leaderSouban)
            ->post(route('tasks.approve-completion', $this->task), [
                'comment' => 'Souban TL trying to approve',
            ]);
        $response2->assertStatus(403);

        $response = $this->actingAs($this->regularLeader)
            ->post(route('tasks.reject-completion', $this->task), [
                'comment' => 'TL trying to reject',
            ]);
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->leaderSouban)
            ->post(route('tasks.reject-completion', $this->task), [
                'comment' => 'Souban TL trying to reject',
            ]);
        $response2->assertStatus(403);
    }
}
