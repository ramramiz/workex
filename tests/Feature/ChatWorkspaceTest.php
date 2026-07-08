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

    public function test_unified_list_endpoint()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('chat.unified-list'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'items' => [
                '*' => [
                    'type',
                    'id',
                    'title',
                    'subtitle',
                    'avatar',
                    'unread_count',
                    'last_message',
                    'timestamp',
                    'time_formatted',
                ]
            ]
        ]);
        $response->assertJsonFragment([
            'type' => 'task',
            'id' => $this->assignedTask->id,
            'title' => $this->assignedTask->title,
        ]);
    }

    public function test_sidebar_includes_header_new_chat_and_filter_elements()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('chat.index'));

        $response->assertStatus(200);
        // Assert container elements exist
        $response->assertSee('id="sidebar-main-view"', false);
        $response->assertSee('id="sidebar-new-chat-view"', false);
        $response->assertSee('class="chat-filter-btn', false);
        
        // Assert filters exist
        $response->assertSee('data-filter="unread"', false);
        $response->assertSee('data-filter="bugs"', false);
        $response->assertSee('data-filter="review"', false);

        // Assert dynamic data attributes are rendered on list items
        $response->assertSee('data-unread-count="', false);
        $response->assertSee('data-is-bug="', false);
        $response->assertSee('data-priority="', false);
    }

    public function test_sidebar_includes_my_tasks_filter_for_team_leader_and_admin()
    {
        // For employee, should NOT see my-tasks filter
        $response = $this->actingAs($this->employee)
            ->get(route('chat.index'));
        $response->assertStatus(200);
        $response->assertDontSee('data-filter="my-tasks"', false);

        // For team leader, should see my-tasks filter
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();
        $teamLeader = User::factory()->create(['role_id' => $teamLeaderRole->id]);
        $responseTL = $this->actingAs($teamLeader)
            ->get(route('chat.index'));
        $responseTL->assertStatus(200);
        $responseTL->assertSee('data-filter="my-tasks"', false);
    }

    public function test_ajax_project_and_task_store_endpoints()
    {
        $adminRole = \App\Models\Role::where('slug', 'super-admin')->first();
        $adminUser = \App\Models\User::factory()->create(['role_id' => $adminRole->id]);

        // 1. Project Store AJAX test with custom type
        $responseProject = $this->actingAs($adminUser)
            ->postJson(route('projects.store'), [
                'name' => 'AJAX Project Board',
                'priority' => 'high',
                'project_type' => 'Custom Type API',
            ]);

        $responseProject->assertStatus(200);
        $responseProject->assertJson(['success' => true, 'message' => 'Project created!']);
        $this->assertDatabaseHas('projects', ['name' => 'AJAX Project Board', 'type' => 'Custom Type API']);

        // Check if custom type is returned in the view projectTypes array
        $chatIndexResponse = $this->actingAs($adminUser)->get(route('chat.index'));
        $chatIndexResponse->assertStatus(200);
        $this->assertContains('Custom Type API', $chatIndexResponse->viewData('projectTypes'));

        // 2. Task Store AJAX test
        $responseTask = $this->actingAs($adminUser)
            ->postJson(route('tasks.store'), [
                'title' => 'AJAX Task Card',
                'assigned_to' => $this->employee->id,
                'priority' => 'critical',
            ]);

        $responseTask->assertStatus(200);
        $responseTask->assertJson(['success' => true, 'message' => 'Task created!']);
        $this->assertDatabaseHas('tasks', ['title' => 'AJAX Task Card']);
    }

    public function test_ajax_bug_store_endpoint()
    {
        $adminRole = \App\Models\Role::where('slug', 'super-admin')->first();
        $adminUser = \App\Models\User::factory()->create(['role_id' => $adminRole->id]);

        $project = \App\Models\Project::create([
            'project_code' => 'PRJ-BUG-TEST',
            'name' => 'Bug Test Project',
            'priority' => 'high',
        ]);

        $responseBug = $this->actingAs($adminUser)
            ->postJson(route('bugs.store'), [
                'title' => 'AJAX Bug Title',
                'project_id' => $project->id,
                'priority' => 'critical',
                'description' => 'Failing test description',
                'assigned_to' => $this->employee->id,
            ]);

        $responseBug->assertStatus(200);
        $responseBug->assertJson(['success' => true, 'message' => 'Bug reported!']);
        $this->assertDatabaseHas('bugs', ['title' => 'AJAX Bug Title', 'project_id' => $project->id]);
        $this->assertDatabaseHas('tasks', ['title' => 'Bug: AJAX Bug Title', 'project_id' => $project->id]);
    }

    public function test_employee_cannot_create_or_manage_projects_tasks_or_bugs()
    {
        $project = \App\Models\Project::create([
            'project_code' => 'PRJ-RESTRICT',
            'name' => 'Restricted Project',
            'priority' => 'high',
        ]);
        
        $bug = \App\Models\Bug::create([
            'title' => 'Sample Bug',
            'project_id' => $project->id,
            'priority' => 'medium',
            'description' => 'Bug details',
            'reported_by' => $this->superAdmin->id,
            'status' => 'open',
        ]);

        // 1. Employee cannot view create/edit bug pages, or store/update/delete bugs
        $this->actingAs($this->employee)->get(route('bugs.create'))->assertStatus(403);
        $this->actingAs($this->employee)->post(route('bugs.store'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('bugs.edit', $bug))->assertStatus(403);
        $this->actingAs($this->employee)->put(route('bugs.update', $bug))->assertStatus(403);
        $this->actingAs($this->employee)->delete(route('bugs.destroy', $bug))->assertStatus(403);

        // 2. Employee cannot view create project page or store projects
        $this->actingAs($this->employee)->get(route('projects.create'))->assertStatus(403);
        $this->actingAs($this->employee)->post(route('projects.store'))->assertStatus(403);

        // 3. Employee cannot view create task page or store tasks
        $this->actingAs($this->employee)->get(route('tasks.create'))->assertStatus(403);
        $this->actingAs($this->employee)->post(route('tasks.store'))->assertStatus(403);

        // 4. Employee views chat index and does not see action options in the sidebar
        $response = $this->actingAs($this->employee)->get(route('chat.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Add new project');
        $response->assertDontSee('Add new task');
        $response->assertDontSee('Register bug');
        $response->assertSee('WorkeX Chat');
        $response->assertSee('Select a task or contact on the left to start collaborating.');
    }

    public function test_super_admin_can_manage_employee_roles_and_permissions()
    {
        $employeeRecord = \App\Models\Employee::where('user_id', $this->employee->id)->first();
        if (!$employeeRecord) {
            $employeeRecord = \App\Models\Employee::create([
                'user_id' => $this->employee->id,
                'employee_code' => 'EMP-TEST',
                'status' => 'active',
                'joining_date' => now(),
            ]);
        }

        // 1. Employee cannot view/update permissions
        $this->actingAs($this->employee)->get(route('employees.permissions.get', $employeeRecord))->assertStatus(403);
        $this->actingAs($this->employee)->post(route('employees.permissions.update', $employeeRecord))->assertStatus(403);

        // 2. Super Admin can view permissions
        $getResponse = $this->actingAs($this->superAdmin)->get(route('employees.permissions.get', $employeeRecord));
        $getResponse->assertStatus(200);
        $getResponse->assertJsonStructure(['roles', 'current_role_id', 'permissions']);

        // Find or create a permission
        $permission = \App\Models\Permission::firstOrCreate(
            ['slug' => 'projects.view-all'],
            ['name' => 'View All Projects', 'module' => 'Projects']
        );

        // 3. Super Admin can update permissions
        $postResponse = $this->actingAs($this->superAdmin)->post(route('employees.permissions.update', $employeeRecord), [
            'role_id' => $this->employee->role_id,
            'permissions' => [$permission->id],
        ]);
        $postResponse->assertStatus(200);
        $postResponse->assertJson(['success' => true]);

        // Assert direct permission database relation
        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $this->employee->id,
            'permission_id' => $permission->id,
        ]);

        // 4. Test that the employee now passes the hasPermission check
        $this->assertTrue($this->employee->fresh()->hasPermission('projects.view-all'));
    }

    public function test_employee_with_attendance_permission_can_access_attendance_routes()
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // Employee user fresh instance
        $employeeUser = $this->employee->fresh();

        // 2. By default, employee has 'attendance.view-own' permission via role fallback
        $this->assertTrue($employeeUser->hasPermission('attendance.view-own'));

        // 3. Access attendance index as employee
        $response = $this->actingAs($employeeUser)->get(route('attendance.index'));
        $response->assertStatus(200);

        // 4. Check if they cannot access other people's records
        $otherUser = User::factory()->create();
        $attendanceRecord = \App\Models\Attendance::create([
            'user_id' => $otherUser->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $this->actingAs($employeeUser)->get(route('attendance.show', $attendanceRecord))->assertStatus(403);

        // 5. Check if they can access their own attendance details
        $ownAttendanceRecord = \App\Models\Attendance::create([
            'user_id' => $employeeUser->id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);
        $this->actingAs($employeeUser)->get(route('attendance.show', $ownAttendanceRecord))->assertStatus(200);

        // 6. Check if they cannot access edit attendance
        $this->actingAs($employeeUser)->get(route('attendance.edit', $ownAttendanceRecord))->assertStatus(403);
    }

    public function test_authorized_user_can_access_attendance_report()
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $adminUser = $this->superAdmin;

        // Verify that the route /attendance/report resolves and does not return a 404
        $response = $this->actingAs($adminUser)->get(route('attendance.report'));
        $this->assertNotEquals(404, $response->status());
    }

    public function test_half_day_leave_creation_and_approval()
    {
        $employeeUser = $this->employee;
        $response = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'half_day',
            'half_day_session' => 'morning',
            'from_date' => '2026-06-30',
            'to_date' => '2026-07-02',
            'reason' => 'Doctor appointment',
        ]);

        $response->assertRedirect(route('leaves.index'));

        $leave = \App\Models\Leave::where('user_id', $employeeUser->id)->where('leave_type', 'half_day')->first();
        $this->assertNotNull($leave);
        $this->assertEquals(0.5, $leave->total_days);
        $this->assertEquals('morning', $leave->half_day_session);
        $this->assertEquals('2026-06-30', $leave->from_date->toDateString());
        $this->assertEquals('2026-06-30', $leave->to_date->toDateString());

        $hrApprovalResponse = $this->actingAs($this->superAdmin)->post(route('leaves.approve-hr', $leave), [
            'comment' => 'Approved half day',
        ]);
        $hrApprovalResponse->assertRedirect();
        $attendance = \App\Models\Attendance::where('user_id', $employeeUser->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('half_day', $attendance->status);
    }

    public function test_casual_leave_limit_rules()
    {
        $employeeUser = $this->employee;

        // 1. First casual leave submission should succeed (date range should be overridden to a single date)
        $response1 = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'casual_leave',
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-03', // should be overridden to 2026-06-01
            'reason' => 'Family event',
        ]);
        $response1->assertRedirect(route('leaves.index'));

        $leave = \App\Models\Leave::where('user_id', $employeeUser->id)->where('leave_type', 'casual_leave')->first();
        $this->assertNotNull($leave);
        $this->assertEquals('2026-06-01', $leave->to_date->toDateString());
        $this->assertEquals(1.0, $leave->total_days);

        // 2. Second casual leave submission in same month should fail validation
        $response2 = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'casual_leave',
            'from_date' => '2026-06-15',
            'to_date' => '2026-06-15',
            'reason' => 'Another event',
        ]);
        $response2->assertSessionHasErrors('leave_type');
    }

    public function test_casual_leave_disabled_if_two_half_days_taken()
    {
        $employeeUser = $this->employee;

        // 1. Take first half day
        $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'half_day',
            'half_day_session' => 'morning',
            'from_date' => '2026-06-01',
            'to_date' => '2026-06-01',
            'reason' => 'Appointment 1',
        ])->assertRedirect();

        // 2. Take second half day
        $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'half_day',
            'half_day_session' => 'evening',
            'from_date' => '2026-06-02',
            'to_date' => '2026-06-02',
            'reason' => 'Appointment 2',
        ])->assertRedirect();

        // 3. Trying to apply for a casual leave should fail now because 2 half days were taken
        $response = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'casual_leave',
            'from_date' => '2026-06-10',
            'to_date' => '2026-06-10',
            'reason' => 'Some event',
        ]);
        $response->assertSessionHasErrors('leave_type');
    }

    public function test_sick_leave_limit_and_document_upload()
    {
        $employeeUser = $this->employee;

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('medical.pdf', 100);

        // 1. Apply for sick leave with medical document upload (date range should be overridden to a single date)
        $response1 = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'sick_leave',
            'from_date' => '2026-06-05',
            'to_date' => '2026-06-08', // should be overridden to 2026-06-05
            'reason' => 'Sick leave request',
            'medical_document' => $file
        ]);
        $response1->assertRedirect(route('leaves.index'));

        // Assert attachment was stored and dates/days are overridden
        $leave = \App\Models\Leave::where('user_id', $employeeUser->id)->where('leave_type', 'sick_leave')->first();
        $this->assertNotNull($leave);
        $this->assertEquals('2026-06-05', $leave->to_date->toDateString());
        $this->assertEquals(1.0, $leave->total_days);
        $this->assertCount(1, $leave->attachments);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($leave->attachments[0]);

        // 2. Applying for second sick leave should fail
        $response2 = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'sick_leave',
            'from_date' => '2026-06-20',
            'to_date' => '2026-06-21',
            'reason' => 'Flu',
        ]);
        $response2->assertSessionHasErrors('leave_type');
    }

    public function test_leave_request_date_overlap_validation()
    {
        $employeeUser = $this->employee;

        // 1. Create a pending leave request
        $leave = \App\Models\Leave::create([
            'user_id' => $employeeUser->id,
            'leave_type' => 'unpaid_leave',
            'from_date' => '2026-06-15',
            'to_date' => '2026-06-15',
            'reason' => 'Pending request',
            'status' => 'pending'
        ]);

        // 2. Try to apply again for the same date -> should fail overlap check
        $response = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'half_day',
            'half_day_session' => 'morning',
            'from_date' => '2026-06-15',
            'to_date' => '2026-06-15',
            'reason' => 'Another request on same day',
        ]);
        $response->assertSessionHasErrors('from_date');

        // 3. Reject the pending leave
        $leave->update(['status' => 'rejected']);

        // 4. Try to apply again for the same date -> should now succeed because previous is rejected
        $response2 = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'half_day',
            'half_day_session' => 'morning',
            'from_date' => '2026-06-15',
            'to_date' => '2026-06-15',
            'reason' => 'Another request on same day',
        ]);
        $response2->assertRedirect(route('leaves.index'));
    }

    public function test_leave_request_sends_direct_messages_to_tl_hr_and_admins()
    {
        $employeeUser = $this->employee;

        // 1. Create a Team Leader user
        $tlRole = Role::where('slug', 'team-leader')->first();
        $teamLeaderUser = User::factory()->create([
            'role_id' => $tlRole->id,
        ]);

        // 2. Set the teamLeaderUser as team_leader_id on employeeUser's Employee record
        $employeeRecord = \App\Models\Employee::where('user_id', $employeeUser->id)->first();
        if (!$employeeRecord) {
            $employeeRecord = \App\Models\Employee::create([
                'user_id' => $employeeUser->id,
                'employee_code' => 'EMP-TEST',
                'status' => 'active',
                'joining_date' => now(),
            ]);
        }
        $employeeRecord->update([
            'team_leader_id' => $teamLeaderUser->id,
        ]);

        // 3. Create an HR user
        $hrRole = Role::where('slug', 'hr')->first();
        $hrUser = User::factory()->create([
            'role_id' => $hrRole->id,
        ]);

        // 4. Create an Admin user (in addition to superAdmin who is already created in setUp)
        $adminRole = Role::where('slug', 'admin')->first();
        $adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Clear any direct messages in DB just in case
        \App\Models\DirectMessage::query()->truncate();

        // 5. Post a leave request (unpaid_leave for 3 days)
        $response = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'unpaid_leave',
            'from_date' => '2026-06-15',
            'to_date' => '2026-06-17',
            'reason' => 'Family event',
        ]);
        $response->assertRedirect(route('leaves.index'));

        // 6. Assert that direct messages were sent to:
        //    - teamLeaderUser
        //    - hrUser
        //    - adminUser
        //    - superAdmin
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $teamLeaderUser->id,
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $hrUser->id,
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $adminUser->id,
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $this->superAdmin->id,
        ]);

        // Check content of one of the messages
        $dm = \App\Models\DirectMessage::where('sender_id', $employeeUser->id)
            ->where('receiver_id', $teamLeaderUser->id)
            ->first();
        $this->assertNotNull($dm);
        $this->assertStringContainsString('Unpaid Leave', $dm->message);
        $this->assertStringContainsString('Family event', $dm->message);
        $this->assertStringContainsString('15-06-2026 to 17-06-2026', $dm->message);

        // 7. Approve the leave request as HR user
        $leave = \App\Models\Leave::where('user_id', $employeeUser->id)->first();
        
        // Truncate messages table to only count the new approval messages
        \App\Models\DirectMessage::query()->truncate();

        $hrApprovalResponse = $this->actingAs($hrUser)->post(route('leaves.approve-hr', $leave), [
            'comment' => 'Fully approved by HR',
        ]);
        $hrApprovalResponse->assertRedirect();

        // 8. Assert that direct messages were sent from hrUser to:
        //    - employeeUser
        //    - teamLeaderUser
        //    - adminUser
        //    - superAdmin
        // 8. Assert that direct messages were sent:
        //    - From hrUser to employeeUser
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $hrUser->id,
            'receiver_id' => $employeeUser->id,
        ]);

        //    - From employeeUser (as sender) to other managers (teamLeaderUser, adminUser, superAdmin)
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $teamLeaderUser->id,
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $adminUser->id,
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $employeeUser->id,
            'receiver_id' => $this->superAdmin->id,
        ]);

        // Check content of the message to the employee
        $dmApproval = \App\Models\DirectMessage::where('sender_id', $hrUser->id)
            ->where('receiver_id', $employeeUser->id)
            ->first();
        $this->assertNotNull($dmApproval);
        $this->assertStringContainsString('fully approved', $dmApproval->message);
        $this->assertStringContainsString('Fully approved by HR', $dmApproval->message);
    }

    public function test_leave_request_can_be_approved_by_admin()
    {
        $employeeUser = $this->employee;

        // Create the Employee record
        $employeeRecord = \App\Models\Employee::where('user_id', $employeeUser->id)->first();
        if (!$employeeRecord) {
            $employeeRecord = \App\Models\Employee::create([
                'user_id' => $employeeUser->id,
                'employee_code' => 'EMP-TEST-2',
                'status' => 'active',
                'joining_date' => now(),
            ]);
        }

        // Post a leave request
        $response = $this->actingAs($employeeUser)->post(route('leaves.store'), [
            'leave_type' => 'unpaid_leave',
            'from_date' => '2026-07-20',
            'to_date' => '2026-07-22',
            'reason' => 'Admin test leave',
        ]);
        $response->assertRedirect(route('leaves.index'));

        $leave = \App\Models\Leave::where('user_id', $employeeUser->id)->where('leave_type', 'unpaid_leave')->first();
        $this->assertNotNull($leave);

        // Approve the leave request as Admin
        $approvalResponse = $this->actingAs($this->superAdmin)->post(route('leaves.approve-hr', $leave), [
            'comment' => 'Approved by admin overrides',
        ]);
        $approvalResponse->assertRedirect();

        $leave->refresh();
        $this->assertEquals('approved', $leave->status);
        $this->assertEquals('approved', $leave->hr_status);
        $this->assertEquals($this->superAdmin->id, $leave->hr_id);
    }

    public function test_employee_dashboard_displays_leaves_correctly()
    {
        $employeeUser = $this->employee;

        // 1. Create a past approved leave (should not appear in activeApprovedLeaves)
        \App\Models\Leave::create([
            'user_id' => $employeeUser->id,
            'leave_type' => 'casual_leave',
            'from_date' => now()->subDays(10)->toDateString(),
            'to_date' => now()->subDays(8)->toDateString(),
            'total_days' => 3,
            'reason' => 'Past leave',
            'status' => 'approved',
            'hr_status' => 'approved',
        ]);

        // 2. Create an upcoming/active approved leave (should appear in activeApprovedLeaves)
        \App\Models\Leave::create([
            'user_id' => $employeeUser->id,
            'leave_type' => 'sick_leave',
            'from_date' => now()->addDays(2)->toDateString(),
            'to_date' => now()->addDays(3)->toDateString(),
            'total_days' => 2,
            'reason' => 'Upcoming leave',
            'status' => 'approved',
            'hr_status' => 'approved',
        ]);

        // 3. Create a pending leave (should appear in leaveRequests but not activeApprovedLeaves)
        \App\Models\Leave::create([
            'user_id' => $employeeUser->id,
            'leave_type' => 'unpaid_leave',
            'from_date' => now()->addDays(5)->toDateString(),
            'to_date' => now()->addDays(6)->toDateString(),
            'total_days' => 2,
            'reason' => 'Pending leave',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employeeUser)->get(route('dashboard'));
        $response->assertStatus(200);

        // Check view data
        $response->assertViewHas('leaveRequests');
        $response->assertViewHas('activeApprovedLeaves');

        $activeLeaves = $response->viewData('activeApprovedLeaves');
        $allRequests = $response->viewData('leaveRequests');

        // The upcoming sick leave should be in both lists
        $this->assertTrue($activeLeaves->contains('leave_type', 'sick_leave'));
        // The past casual leave should not be in the active list (ended)
        $this->assertFalse($activeLeaves->contains('leave_type', 'casual_leave'));
        // The pending unpaid leave should not be in the active list (not approved)
        $this->assertFalse($activeLeaves->contains('leave_type', 'unpaid_leave'));

        // All three should be in the all requests list
        $this->assertTrue($allRequests->contains('leave_type', 'sick_leave'));
        $this->assertTrue($allRequests->contains('leave_type', 'casual_leave'));
        $this->assertTrue($allRequests->contains('leave_type', 'unpaid_leave'));
    }

    public function test_employee_can_revoke_and_delete_leave_request()
    {
        $employeeUser = $this->employee;

        // 1. Create a leave request
        $leave = \App\Models\Leave::create([
            'user_id' => $employeeUser->id,
            'leave_type' => 'casual_leave',
            'from_date' => '2026-08-10',
            'to_date' => '2026-08-10',
            'total_days' => 1,
            'reason' => 'Revoke test',
            'status' => 'pending',
        ]);

        // 2. Create a corresponding attendance log
        $attendance = \App\Models\Attendance::create([
            'user_id' => $employeeUser->id,
            'date' => '2026-08-10',
            'status' => 'on_leave',
            'company_id' => $employeeUser->company_id ?: 1,
        ]);

        // 3. Create a corresponding direct message notification
        $message = \App\Models\DirectMessage::create([
            'sender_id' => $employeeUser->id,
            'receiver_id' => $this->superAdmin->id,
            'message' => "Hello, I have submitted a leave request. View here: http://127.0.0.1:8000/leaves/{$leave->id}",
            'company_id' => $employeeUser->company_id ?: 1,
        ]);

        // Assert database has them
        $this->assertDatabaseHas('leaves', ['id' => $leave->id]);
        $this->assertDatabaseHas('attendance', ['id' => $attendance->id]);
        $this->assertDatabaseHas('direct_messages', ['id' => $message->id]);

        // Revoke the leave request
        $response = $this->actingAs($employeeUser)
            ->from(route('dashboard'))
            ->delete(route('leaves.destroy', $leave));
        $response->assertRedirect(route('dashboard'));

        // Assert they are deleted from the database
        $this->assertDatabaseMissing('leaves', ['id' => $leave->id]);
        $this->assertDatabaseMissing('attendance', ['id' => $attendance->id]);
        $this->assertDatabaseMissing('direct_messages', ['id' => $message->id]);
    }
}
