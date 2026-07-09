<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationMarkAllReadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Standard employee',
            'color' => '#6366f1'
        ]);

        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active'
        ]);
    }

    public function test_get_method_not_allowed_on_mark_all_read(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('notifications.mark-all-read'));

        $response->assertStatus(405);
    }

    public function test_post_method_marks_all_notifications_as_read(): void
    {
        // Create unread notifications
        AppNotification::create([
            'user_id' => $this->user->id,
            'type' => 'task_assigned',
            'title' => 'Test 1',
            'message' => 'Message 1',
            'read_at' => null,
        ]);

        AppNotification::create([
            'user_id' => $this->user->id,
            'type' => 'task_assigned',
            'title' => 'Test 2',
            'message' => 'Message 2',
            'read_at' => null,
        ]);

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }
}
