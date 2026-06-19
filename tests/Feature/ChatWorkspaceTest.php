<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskCommentView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class ChatWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;
    protected User $employee2;
    protected Task $assignedTask;
    protected Task $unassignedTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
        ]);

        $this->employee = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->employee2 = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->assignedTask = Task::create([
            'title'       => 'Assigned to Employee',
            'assigned_to' => $this->employee->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
        ]);

        $this->unassignedTask = Task::create([
            'title'       => 'Assigned to Employee 2',
            'assigned_to' => $this->employee2->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
        ]);
    }

    public function test_guest_cannot_access_chat_workspace()
    {
        $this->get(route('chat.index'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_can_see_all_tasks_in_chat_list()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('chat.index'));

        $response->assertStatus(200);
        $response->assertSee('Assigned to Employee');
        $response->assertSee('Assigned to Employee 2');
    }

    public function test_employee_only_sees_assigned_tasks_in_chat_list()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('chat.index'));

        $response->assertStatus(200);
        $response->assertSee('Assigned to Employee');
        $response->assertDontSee('Assigned to Employee 2');
    }

    public function test_employee_can_fetch_chat_history_for_assigned_task()
    {
        $comment = TaskComment::create([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->superAdmin->id,
            'comment' => 'Hello employee!',
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('chat.show', $this->assignedTask));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'html',
            'latest_time',
            'task_title',
            'project_name',
            'assignee_name',
            'assignee_avatar',
            'task_url',
            'task_id',
            'store_url',
        ]);
        $this->assertStringContainsString('Hello employee!', $response->json('html'));
    }

    public function test_employee_cannot_fetch_chat_history_for_unassigned_task()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('chat.show', $this->unassignedTask));

        $response->assertStatus(403);
    }

    public function test_fetching_chat_history_marks_unread_comments_as_viewed()
    {
        $comment = TaskComment::create([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->superAdmin->id,
            'comment' => 'Unread message',
        ]);

        $this->assertEquals(0, TaskCommentView::where('task_comment_id', $comment->id)->where('user_id', $this->employee->id)->count());

        $response = $this->actingAs($this->employee)
            ->get(route('chat.show', $this->assignedTask));

        $response->assertStatus(200);

        // Verify it recorded the view
        $this->assertEquals(1, TaskCommentView::where('task_comment_id', $comment->id)->where('user_id', $this->employee->id)->count());
    }

    public function test_team_leader_does_not_see_telecaller_chats()
    {
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();

        $teamLeader = User::factory()->create(['role_id' => $teamLeaderRole->id]);
        $telecaller = User::factory()->create(['role_id' => $telecallerRole->id]);

        $telecallerTask = Task::create([
            'title'       => 'Telecaller Room Calling Task',
            'assigned_to' => $telecaller->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
        ]);

        // Team leader views chat list
        $response = $this->actingAs($teamLeader)->get(route('chat.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Telecaller Room Calling Task');

        // Super Admin views chat list and CAN see it
        $response2 = $this->actingAs($this->superAdmin)->get(route('chat.index'));
        $response2->assertStatus(200);
        $response2->assertSee('Telecaller Room Calling Task');
    }

    public function test_team_leader_cannot_view_telecaller_chat_details()
    {
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();

        $teamLeader = User::factory()->create(['role_id' => $teamLeaderRole->id]);
        $telecaller = User::factory()->create(['role_id' => $telecallerRole->id]);

        $telecallerTask = Task::create([
            'title'       => 'Telecaller Room Calling Task',
            'assigned_to' => $telecaller->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
        ]);

        // Team leader tries to view telecaller task chat
        $response = $this->actingAs($teamLeader)->get(route('chat.show', $telecallerTask));
        $response->assertStatus(403);

        // Super Admin CAN view
        $response2 = $this->actingAs($this->superAdmin)->get(route('chat.show', $telecallerTask));
        $response2->assertStatus(200);
    }

    public function test_super_admin_sees_bugs_prioritized_in_chat_list()
    {
        // Create a regular task first (so it has an older updated_at)
        $regularTask = Task::create([
            'title'       => 'Normal Task 1',
            'assigned_to' => $this->employee->id,
            'priority'    => 'medium',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
            'updated_at'  => now()->subMinutes(10),
        ]);

        // Create a bug task
        $bugTask = Task::create([
            'title'       => 'Bug: Critical Login Issue',
            'assigned_to' => $this->employee->id,
            'priority'    => 'high',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
            'updated_at'  => now()->subMinutes(5),
        ]);

        // Create another regular task that is updated last
        $latestRegularTask = Task::create([
            'title'       => 'Normal Task 2',
            'assigned_to' => $this->employee->id,
            'priority'    => 'low',
            'status'      => 'pending',
            'created_by'  => $this->superAdmin->id,
            'updated_at'  => now(),
        ]);

        // Fetch index as super admin
        $response = $this->actingAs($this->superAdmin)->get(route('chat.index'));
        $response->assertStatus(200);

        // Verify ordering: bugTask should be first in the tasks variable
        $tasks = $response->viewData('tasks');
        $this->assertNotEmpty($tasks);
        
        // The first task must be the bug task
        $this->assertEquals($bugTask->id, $tasks->first()->id);
    }

    public function test_chat_show_returns_working_status()
    {
        // Assert is_working is false initially
        $response = $this->actingAs($this->superAdmin)
            ->get(route('chat.show', $this->assignedTask));
        $response->assertStatus(200);
        $this->assertFalse($response->json('is_working'));

        // Start working on the task
        $this->assignedTask->timeLogs()->create([
            'user_id' => $this->employee->id,
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Assert is_working is true
        $response = $this->actingAs($this->superAdmin)
            ->get(route('chat.show', $this->assignedTask));
        $response->assertStatus(200);
        $this->assertTrue($response->json('is_working'));
    }

    public function test_chat_show_returns_deadline_days()
    {
        // Task with no deadline
        $response = $this->actingAs($this->superAdmin)
            ->get(route('chat.show', $this->assignedTask));
        $response->assertStatus(200);
        $this->assertNull($response->json('deadline_days'));

        // Task with a deadline 5 days from now
        $this->assignedTask->update([
            'deadline' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('chat.show', $this->assignedTask));
        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('deadline_days'));
    }

    public function test_unread_counts_endpoint()
    {
        // Initially should be empty
        $response = $this->actingAs($this->employee)
            ->get(route('chat.unread-counts'));
        $response->assertStatus(200);
        $this->assertEmpty($response->json('unread_counts'));

        // Post a comment from superAdmin to the employee's assigned task
        TaskComment::create([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->superAdmin->id,
            'comment' => 'Hey employee!',
        ]);

        // Fetch unread counts as employee
        $response = $this->actingAs($this->employee)
            ->get(route('chat.unread-counts'));
        $response->assertStatus(200);
        $response->assertJson([
            'unread_counts' => [
                (string)$this->assignedTask->id => 1
            ]
        ]);

        // Post a comment from employee (should not show up in unread counts for them)
        TaskComment::create([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->employee->id,
            'comment' => 'Self comment',
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('chat.unread-counts'));
        $response->assertStatus(200);
        $response->assertJson([
            'unread_counts' => [
                (string)$this->assignedTask->id => 1
            ]
        ]);
    }

    public function test_feed_updates_endpoint()
    {
        // Setup initial comment
        $comment1 = TaskComment::create([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->superAdmin->id,
            'comment' => 'Initial comment',
        ]);

        $since = $comment1->created_at->toISOString();

        // Add a second comment
        $comment2 = new TaskComment([
            'task_id' => $this->assignedTask->id,
            'user_id' => $this->employee->id,
            'comment' => 'New updates',
        ]);
        $comment2->created_at = now()->addSeconds(5);
        $comment2->save();

        $response = $this->actingAs($this->employee)
            ->get(route('tasks.feed-updates', ['task' => $this->assignedTask->id, 'since' => $since]));

        $response->assertStatus(200);
        $response->assertJson([
            'has_updates' => true,
        ]);
        $this->assertStringContainsString('New updates', $response->json('html'));
    }
}
