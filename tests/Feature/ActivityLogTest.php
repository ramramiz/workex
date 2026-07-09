<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);
    }

    public function test_admin_can_access_activity_logs_index_with_tabs(): void
    {
        // Create sample logs
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'project_created',
            'description' => 'Created project A',
        ]);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'amc_whatsapp_reminder_sent',
            'description' => 'Sent WhatsApp reminder',
        ]);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'email_sent',
            'description' => 'Sent email to user',
        ]);

        // 1. All logs tab
        $response = $this->actingAs($this->admin)
            ->get(route('activity-logs.index', ['log_type' => 'all']));
        $response->assertStatus(200);
        $response->assertSee('Created project A');
        $response->assertSee('Sent WhatsApp reminder');
        $response->assertSee('Sent email to user');

        // 2. WhatsApp logs tab
        $response = $this->actingAs($this->admin)
            ->get(route('activity-logs.index', ['log_type' => 'whatsapp']));
        $response->assertStatus(200);
        $response->assertDontSee('Created project A');
        $response->assertSee('Sent WhatsApp reminder');
        $response->assertDontSee('Sent email to user');

        // 3. Email logs tab
        $response = $this->actingAs($this->admin)
            ->get(route('activity-logs.index', ['log_type' => 'email']));
        $response->assertStatus(200);
        $response->assertDontSee('Created project A');
        $response->assertDontSee('Sent WhatsApp reminder');
        $response->assertSee('Sent email to user');
    }

    public function test_email_sent_event_automatically_logs_activity(): void
    {
        // Trigger mail sending using default array mailer
        Mail::raw('Test email body', function($message) {
            $message->to('test@example.com')->subject('Testing Mail Event');
        });

        // Verify that an activity log entry is written for email_sent
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'email_sent',
            'description' => 'Sent email to test@example.com with subject: "Testing Mail Event"',
        ]);
    }
}
