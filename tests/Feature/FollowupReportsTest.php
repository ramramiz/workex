<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Lead;
use App\Models\LeadRoom;
use App\Models\LeadCall;
use App\Models\LeadRoomWorkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class FollowupReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $telecallerUser;
    protected LeadRoom $leadRoom;
    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & settings
        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->telecallerUser = User::factory()->create([
            'role_id' => $telecallerRole->id,
        ]);

        $this->leadRoom = LeadRoom::create([
            'name' => 'AIMS Kottakkal',
            'status' => 'active',
        ]);

        $this->leadRoom->users()->attach($this->telecallerUser->id);

        $this->lead = Lead::create([
            'client_name' => 'Jane Smith',
            'client_phone' => '9876543210',
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'new',
            'source' => 'manual',
            'requirement' => 'Need a CRM software',
        ]);
    }

    public function test_followup_call_is_logged_correctly_with_flag()
    {
        $response = $this->actingAs($this->telecallerUser)->post(route('leads.calls.store', $this->lead), [
            'status' => 'Connected',
            'lead_status' => 'interested',
            'customer_response' => 'Wants next week callback',
            'is_followup' => '1',
            'duration' => 20,
        ]);

        $response->assertRedirect();
        
        $call = LeadCall::first();
        $this->assertNotNull($call);
        $this->assertTrue($call->is_followup);
    }

    public function test_followup_calls_group_separately_on_live_status()
    {
        $session = LeadRoomWorkSession::create([
            'user_id' => $this->telecallerUser->id,
            'lead_room_id' => $this->leadRoom->id,
            'status' => 'active',
            'started_at' => now(),
            'total_seconds' => 0,
        ]);

        LeadCall::create([
            'lead_id' => $this->lead->id,
            'telecaller_id' => $this->telecallerUser->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'is_followup' => false,
            'duration' => 10,
        ]);

        LeadCall::create([
            'lead_id' => $this->lead->id,
            'telecaller_id' => $this->telecallerUser->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'is_followup' => true,
            'duration' => 15,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.data'));
        $response->assertStatus(200);

        $employees = $response->json('employees');
        $telecallerData = collect($employees)->firstWhere('id', $this->telecallerUser->id);

        $this->assertNotNull($telecallerData);
        $completedWork = $telecallerData['completed_work'];
        
        $this->assertCount(2, $completedWork);
        
        $normalRoom = collect($completedWork)->firstWhere('room_id', $this->leadRoom->id);
        $followupsRoom = collect($completedWork)->firstWhere('room_id', 'followups');

        $this->assertNotNull($normalRoom);
        $this->assertNotNull($followupsRoom);

        $this->assertEquals("Today's Follow-ups", $followupsRoom['title']);
        $this->assertEquals(1, $normalRoom['called_count']);
        $this->assertEquals(1, $followupsRoom['called_count']);
    }

    public function test_followup_room_details_page_loads_and_filters_correctly()
    {
        LeadCall::create([
            'lead_id' => $this->lead->id,
            'telecaller_id' => $this->telecallerUser->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'is_followup' => false,
            'duration' => 10,
        ]);

        LeadCall::create([
            'lead_id' => $this->lead->id,
            'telecaller_id' => $this->telecallerUser->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'is_followup' => true,
            'duration' => 15,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('live-status.telecaller-room-calls', [
            'user' => $this->telecallerUser->id,
            'room' => 'followups'
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('calls');
        $calls = $response->viewData('calls');
        $this->assertCount(1, $calls);
        $this->assertTrue($calls->first()->is_followup);
        $this->assertEquals("Today's Follow-ups", $response->viewData('roomName'));
    }
}
