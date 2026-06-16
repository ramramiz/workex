<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\LeadRoom;
use App\Models\LeadRoomWorkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Cache;

class LiveStatusTelecallerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $telecallerUser;
    protected LeadRoom $leadRoom;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $regularAdminRole = Role::where('slug', 'admin')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create regular admin user
        $this->regularAdminUser = User::factory()->create([
            'role_id' => $regularAdminRole->id,
        ]);

        // Create telecaller user
        $this->telecallerUser = User::factory()->create([
            'role_id' => $telecallerRole->id,
        ]);

        // Create a lead room
        $this->leadRoom = LeadRoom::create([
            'name' => 'Test Lead Room',
            'status' => 'active',
        ]);
    }

    public function test_telecaller_appears_on_live_status_board_with_active_session()
    {
        // Initially no active work session
        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);
        
        $employees = $response->json('employees');
        $telecallerData = collect($employees)->firstWhere('id', $this->telecallerUser->id);
        
        $this->assertNotNull($telecallerData);
        $this->assertEquals('not_started', $telecallerData['status']);
        $this->assertEquals('Not Started', $telecallerData['status_label']);
        $this->assertNull($telecallerData['current_task']);

        // Create an active lead room work session for the telecaller
        $session = LeadRoomWorkSession::create([
            'user_id' => $this->telecallerUser->id,
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'active',
            'started_at' => now(),
            'total_seconds' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);
        
        $employees = $response->json('employees');
        $telecallerData = collect($employees)->firstWhere('id', $this->telecallerUser->id);
        
        $this->assertNotNull($telecallerData);
        $this->assertEquals('working', $telecallerData['status']);
        $this->assertEquals('Working', $telecallerData['status_label']);
        $this->assertEquals('Calling Session Active', $telecallerData['current_task']);
        $this->assertEquals('Room: Test Lead Room', $telecallerData['current_project']);
    }

    public function test_telecaller_shows_current_call_information_from_cache()
    {
        // Create active work session
        $session = LeadRoomWorkSession::create([
            'user_id' => $this->telecallerUser->id,
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'active',
            'started_at' => now(),
            'total_seconds' => 0,
        ]);

        // Put current call in Cache
        $startedAt = now()->timestamp;
        Cache::put('user_current_call_' . $this->telecallerUser->id, [
            'name' => 'John Doe',
            'phone' => '+1234567890',
            'started_at' => $startedAt,
        ], 60);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);
        
        $employees = $response->json('employees');
        $telecallerData = collect($employees)->firstWhere('id', $this->telecallerUser->id);
        
        $this->assertNotNull($telecallerData);
        $this->assertEquals('working', $telecallerData['status']);
        $this->assertEquals('Calling: John Doe (+1234567890)', $telecallerData['current_task']);
        $this->assertEquals(0, $telecallerData['calls_count']);
    }

    public function test_telecaller_shows_session_call_count()
    {
        // Create active work session
        $session = LeadRoomWorkSession::create([
            'user_id' => $this->telecallerUser->id,
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'active',
            'started_at' => now()->subMinutes(10),
            'total_seconds' => 0,
        ]);

        // Create a Lead
        $lead = \App\Models\Lead::create([
            'client_name' => 'Jane Smith',
            'client_phone' => '9876543210',
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'new',
            'source' => 'manual',
            'requirement' => 'Need a CRM software',
        ]);

        // Log a Call during session
        \App\Models\LeadCall::create([
            'lead_id' => $lead->id,
            'telecaller_id' => $this->telecallerUser->id,
            'call_date_time' => now(),
            'status' => 'connected',
            'customer_response' => 'interested',
            'remarks' => 'Good response',
            'duration' => 45,
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);

        $employees = $response->json('employees');
        $telecallerData = collect($employees)->firstWhere('id', $this->telecallerUser->id);

        $this->assertNotNull($telecallerData);
        $this->assertEquals(1, $telecallerData['calls_count']);
    }

    public function test_non_super_admin_cannot_access_live_status()
    {
        // Telecaller tries to access live status
        $response = $this->actingAs($this->telecallerUser)->get(route('live-status'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->telecallerUser)->get(route('live-status.data'));
        $response2->assertStatus(403);

        // Regular admin tries to access live status
        $response3 = $this->actingAs($this->regularAdminUser)->get(route('live-status'));
        $response3->assertStatus(403);

        $response4 = $this->actingAs($this->regularAdminUser)->get(route('live-status.data'));
        $response4->assertStatus(403);

        // Super admin CAN access
        $response5 = $this->actingAs($this->adminUser)->get(route('live-status'));
        $response5->assertStatus(200);

        $response6 = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response6->assertStatus(200);
    }

    public function test_non_super_admin_cannot_access_approvals()
    {
        // Telecaller tries to access approvals index
        $response = $this->actingAs($this->telecallerUser)->get(route('admin.telecaller-sessions.index'));
        $response->assertStatus(403);

        // Regular admin tries to access approvals index
        $response2 = $this->actingAs($this->regularAdminUser)->get(route('admin.telecaller-sessions.index'));
        $response2->assertStatus(403);

        // Super admin CAN access
        $response3 = $this->actingAs($this->adminUser)->get(route('admin.telecaller-sessions.index'));
        $response3->assertStatus(200);
    }
}

