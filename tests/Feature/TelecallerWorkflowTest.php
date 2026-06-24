<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadAppointment;
use App\Models\LeadFollowUp;
use App\Models\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelecallerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $telecallerRole;
    private Role $employeeRole;

    private User $admin;
    private User $telecaller1;
    private User $telecaller2;
    private User $salesExecutive;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles
        $this->adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $this->telecallerRole = Role::create([
            'name' => 'Telecaller',
            'slug' => 'telecaller',
            'description' => 'Telecaller role',
            'color' => '#14b8a6'
        ]);

        $this->employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Developer',
            'color' => '#059669'
        ]);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'status' => 'active'
        ]);

        $this->telecaller1 = User::create([
            'name' => 'Telecaller One',
            'email' => 'tele1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->telecallerRole->id,
            'status' => 'active'
        ]);

        $this->telecaller2 = User::create([
            'name' => 'Telecaller Two',
            'email' => 'tele2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->telecallerRole->id,
            'status' => 'active'
        ]);

        $this->salesExecutive = User::create([
            'name' => 'Sales Executive',
            'email' => 'sales@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->employeeRole->id,
            'status' => 'active'
        ]);
    }

    public function test_telecaller_dashboard_loads_with_correct_metrics(): void
    {
        // Create some leads for telecaller1
        $lead1 = Lead::create([
            'client_name' => 'Lead One',
            'requirement' => 'Web App Dev',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $lead2 = Lead::create([
            'client_name' => 'Lead Two',
            'requirement' => 'Mobile App Dev',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'website',
            'status' => 'converted'
        ]);

        // Log a call for lead1
        LeadCall::create([
            'lead_id' => $lead1->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'customer_response' => 'Interested',
            'next_action' => 'Call again',
            'remarks' => 'Good discussion'
        ]);

        // Log a failed call for lead1
        LeadCall::create([
            'lead_id' => $lead1->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Busy',
            'customer_response' => '',
            'next_action' => 'Recall',
            'remarks' => 'Busy tone'
        ]);

        // Add follow-up for today
        LeadFollowUp::create([
            'lead_id' => $lead1->id,
            'user_id' => $this->telecaller1->id,
            'note' => 'Call back',
            'next_follow_up' => now()->toDateString(),
            'follow_up_time' => '10:00:00',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->telecaller1)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Telecaller Dashboard');
        $response->assertSee(route('leads.start-work.index'));
        $response->assertDontSee(route('tasks.index'));

        // Check if stats are passed and rendered/visible
        // Total leads = 2
        // Calls completed = 2
        // Converted leads = 1
        // Failed calls = 1
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_leads'] === 2
                && $stats['calls_completed'] === 2
                && $stats['converted_leads'] === 1
                && $stats['failed_calls'] === 1;
        });
    }

    public function test_telecaller_redirected_from_leads_index(): void
    {
        $response = $this->actingAs($this->telecaller1)->get(route('leads.index'));
        $response->assertRedirect(route('leads.start-work.index'));
    }

    public function test_telecaller_cannot_view_unassigned_lead(): void
    {
        $otherLead = Lead::create([
            'client_name' => 'Other Lead',
            'requirement' => 'SEO audit',
            'assigned_to' => $this->telecaller2->id,
            'created_by' => $this->admin->id,
            'source' => 'website',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)->get(route('leads.show', $otherLead));

        $response->assertStatus(403);
    }

    public function test_telecaller_can_log_call_activity(): void
    {
        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.calls.store', $lead), [
                'status' => 'Connected',
                'customer_response' => 'Wants quotes',
                'next_action' => 'Send estimation',
                'remarks' => 'Call was positive',
                'lead_status' => 'interested'
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('lead_calls', [
            'lead_id' => $lead->id,
            'telecaller_id' => $this->telecaller1->id,
            'status' => 'Connected',
            'customer_response' => 'Wants quotes',
            'next_action' => 'Send estimation',
            'remarks' => 'Call was positive'
        ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'interested'
        ]);
    }

    public function test_telecaller_cannot_log_call_on_unassigned_lead(): void
    {
        $otherLead = Lead::create([
            'client_name' => 'Other Lead',
            'requirement' => 'SEO audit',
            'assigned_to' => $this->telecaller2->id,
            'created_by' => $this->admin->id,
            'source' => 'website',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.calls.store', $otherLead), [
                'status' => 'Connected',
                'customer_response' => 'Wants quotes',
                'next_action' => 'Send estimation',
                'remarks' => 'Call was positive'
            ]);

        $response->assertStatus(403);
    }

    public function test_telecaller_can_log_follow_up(): void
    {
        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.follow-up', $lead), [
                'note' => 'Call back next week',
                'next_follow_up' => '2026-06-20',
                'follow_up_time' => '11:30:00'
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lead_follow_ups', [
            'lead_id' => $lead->id,
            'user_id' => $this->telecaller1->id,
            'note' => 'Call back next week',
            'next_follow_up' => '2026-06-20 00:00:00',
            'follow_up_time' => '11:30:00',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'following_up',
            'follow_up_date' => '2026-06-20 00:00:00'
        ]);
    }

    public function test_telecaller_can_book_appointment_and_sends_notifications(): void
    {
        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.appointments.store', $lead), [
                'sales_executive_id' => $this->salesExecutive->id,
                'meeting_date_time' => '2026-06-15 14:00:00',
                'type' => 'Demo',
                'notes' => 'Sales Demo request'
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lead_appointments', [
            'lead_id' => $lead->id,
            'sales_executive_id' => $this->salesExecutive->id,
            'meeting_date_time' => '2026-06-15 14:00:00',
            'type' => 'Demo',
            'notes' => 'Sales Demo request',
            'created_by' => $this->telecaller1->id,
            'status' => 'scheduled'
        ]);

        // Lead status should be updated to 'follow_up_required'
        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'follow_up_required'
        ]);

        // Sales executive should receive a notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->salesExecutive->id,
            'type' => 'appointment',
            'title' => 'New Lead Appointment Assigned'
        ]);

        // Admin (manager) should receive a notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'appointment',
            'title' => 'New Lead Appointment Booked'
        ]);
    }

    public function test_telecaller_can_update_customer_requirements(): void
    {
        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.requirements.update', $lead), [
                'service_required' => 'Custom SaaS Development',
                'estimated_budget' => 5000.00,
                'preferred_date' => '2026-07-01',
                'company_details' => 'Tech Corp, 50 employees',
                'notes' => 'Urgent requirement'
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'service_required' => 'Custom SaaS Development',
            'estimated_budget' => 5000.00,
            'preferred_date' => '2026-07-01 00:00:00',
            'company_details' => 'Tech Corp, 50 employees',
            'notes' => 'Urgent requirement'
        ]);
    }

    public function test_telecaller_performance_report_access_control(): void
    {
        // Telecaller 1 requesting their own report
        $response = $this->actingAs($this->telecaller1)
            ->get(route('reports.telecaller-performance', ['telecaller_id' => $this->telecaller1->id]));
        $response->assertStatus(200);

        // Telecaller 1 requesting Telecaller 2's report (Should be Forbidden)
        $response2 = $this->actingAs($this->telecaller1)
            ->get(route('reports.telecaller-performance', ['telecaller_id' => $this->telecaller2->id]));
        $response2->assertStatus(403);

        // Admin requesting Telecaller 1's report (Should be Allowed)
        $response3 = $this->actingAs($this->admin)
            ->get(route('reports.telecaller-performance', ['telecaller_id' => $this->telecaller1->id]));
        $response3->assertStatus(200);
        $response3->assertSee('Telecaller Performance Report');
    }

    public function test_telecaller_can_access_assigned_rooms_and_start_work(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // 1. Visit Start Work Session page (index)
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.index'));
        $response->assertStatus(200);
        $response->assertSee('Start Work Session');

        // 2. Start session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));
        $response->assertRedirect(route('leads.start-work.select-room'));

        // 3. See assigned room on select-room page
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room'));
        $response->assertStatus(200);
        $response->assertSee('Assigned Room');

        // 4. Select the room
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));
        $response->assertRedirect(route('leads.start-work.leads', $room));
        $this->assertEquals($room->id, session('active_room_work.room_id'));

        // 5. Access Leads page
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.leads', $room));
        $response->assertStatus(200);
        $response->assertSee('**********'); // Masked number

        // 6. Stop session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.stop'));
        
        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        $response->assertRedirect(route('leads.start-work.summary', [$room->id, $session->id]));
        $this->assertNull(session('active_room_work'));
    }

    public function test_telecaller_cannot_access_unassigned_rooms(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Unassigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.room', $room));
        $response->assertStatus(403);
    }

    public function test_telecaller_can_log_call_with_duration_and_redirects_to_leads(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.calls.store', $lead), [
                'status' => 'Connected',
                'customer_response' => 'Wants quotes',
                'next_action' => 'Send estimation',
                'remarks' => 'Call was positive',
                'lead_status' => 'interested',
                'duration' => 45,
                'source' => 'room_work'
            ]);

        $response->assertRedirect(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'uncalled']));
        
        $this->assertDatabaseHas('lead_calls', [
            'lead_id' => $lead->id,
            'telecaller_id' => $this->telecaller1->id,
            'status' => 'Connected',
            'duration' => 45
        ]);
    }

    public function test_telecaller_can_log_unconnected_call_without_lead_status(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.calls.store', $lead), [
                'status' => 'Busy',
                'duration' => 5,
                'source' => 'room_work'
            ]);

        $response->assertRedirect(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'uncalled']));
        
        $this->assertDatabaseHas('lead_calls', [
            'lead_id' => $lead->id,
            'telecaller_id' => $this->telecaller1->id,
            'status' => 'Busy',
            'duration' => 5
        ]);
    }

    public function test_telecaller_cannot_log_connected_call_without_lead_status(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'My Lead',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $response = $this->actingAs($this->telecaller1)
            ->post(route('leads.calls.store', $lead), [
                'status' => 'Connected',
                'duration' => 45,
                'source' => 'room_work'
            ]);

        $response->assertSessionHasErrors(['lead_status']);
    }

    public function test_telecaller_leads_view_tabs(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room 2',
            'description' => 'Test room 2',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session and select room
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));
        $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));

        // Create 2 leads in this room
        $lead1 = Lead::create([
            'client_name' => 'Client Followup Today',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new',
            'follow_up_date' => today()
        ]);

        $lead2 = Lead::create([
            'client_name' => 'Client Interested',
            'requirement' => 'Design 2',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // Fetch today_follow_up tab (default)
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'today_follow_up']));
        
        $response->assertStatus(200);
        $response->assertSee('Client Followup Today');
        $response->assertDontSee('Client Interested');

        // Fetch interested tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'interested']));
        
        $response->assertStatus(200);
        $response->assertSee('Client Interested');
        $response->assertDontSee('Client Followup Today');

        // Fetch all_contacts tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'all_contacts']));
        
        $response->assertStatus(200);
        $response->assertSee('Client Followup Today');
        $response->assertSee('Client Interested');
    }

    public function test_telecaller_leads_view_not_connected_pill(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room 4',
            'description' => 'Test room 4',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session and select room
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));
        $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));

        // Create 3 leads in this room
        $lead1 = Lead::create([
            'client_name' => 'Client Uncalled',
            'requirement' => 'Design 1',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $lead2 = Lead::create([
            'client_name' => 'Client Connected',
            'requirement' => 'Design 2',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $lead3 = Lead::create([
            'client_name' => 'Client Not Connected',
            'requirement' => 'Design 3',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        // Log connected call on lead2
        LeadCall::create([
            'lead_id' => $lead2->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'remarks' => 'called lead2'
        ]);

        // Log Switched Off call on lead3
        LeadCall::create([
            'lead_id' => $lead3->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Switched Off',
            'remarks' => 'called lead3'
        ]);

        // Fetch not_connected tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'not_connected']));
        $response->assertStatus(200);
        $response->assertSee('Client Not Connected');
        $response->assertDontSee('Client Uncalled');
        $response->assertDontSee('Client Connected');

        // Fetch all_contacts tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'all_contacts']));
        $response->assertStatus(200);
        $response->assertSee('Client Uncalled');
        $response->assertSee('Client Connected');
        $response->assertSee('Client Not Connected');
    }

    public function test_telecaller_leads_view_interested_and_today_follow_up_pills(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room 3',
            'description' => 'Test room 3',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session and select room
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));
        $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));

        // Lead 1: Interested
        $lead1 = Lead::create([
            'client_name' => 'Client Interested',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // Lead 2: Today follow up
        $lead2 = Lead::create([
            'client_name' => 'Client Followup Today',
            'requirement' => 'Design 2',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new',
            'follow_up_date' => today()
        ]);

        // Lead 3: Uncalled & New (neither interested nor follow up today)
        $lead3 = Lead::create([
            'client_name' => 'Client Regular Uncalled',
            'requirement' => 'Design 3',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        // Fetch interested tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'interested']));
        
        $response->assertStatus(200);
        $response->assertSee('Client Interested');
        $response->assertDontSee('Client Followup Today');
        $response->assertDontSee('Client Regular Uncalled');

        // Fetch today_follow_up tab
        $response = $this->actingAs($this->telecaller1)
            ->get(route('leads.start-work.leads', ['room' => $room->id, 'tab' => 'today_follow_up']));
        
        $response->assertStatus(200);
        $response->assertSee('Client Followup Today');
        $response->assertDontSee('Client Interested');
        $response->assertDontSee('Client Regular Uncalled');
        
        // Assert that the pulsing bell icon shows up on the page when there is a today's follow up
        $response->assertSee('bi-bell-fill text-danger animate-pulse');
    }

    public function test_telecaller_active_session_locks_navigation(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start', $room));

        // Attempting to access dashboard should redirect to room leads page
        $response = $this->actingAs($this->telecaller1)->get(route('dashboard'));
        $response->assertRedirect(route('leads.start-work.leads', $room));

        // Attempting to access main leads list should redirect to room leads page
        $response2 = $this->actingAs($this->telecaller1)->get(route('leads.index'));
        $response2->assertRedirect(route('leads.start-work.leads', $room));
    }

    public function test_telecaller_paused_session_allows_navigation(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start', $room));

        // Pause work session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.pause', $room));
        $response->assertRedirect();

        // Check database is updated to paused
        $this->assertDatabaseHas('lead_room_work_sessions', [
            'user_id' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'status' => 'paused'
        ]);

        // Attempting to access dashboard should now succeed (middleware is bypassed since status is paused)
        $response2 = $this->actingAs($this->telecaller1)->get(route('dashboard'));
        $response2->assertStatus(200);
    }

    public function test_telecaller_session_pausing_saves_accumulated_seconds(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Start work session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start', $room));
        
        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        // Artificially modify the started_at timestamp in database to simulate elapsed time
        $startedAt = now()->subSeconds(15);
        $session->update(['started_at' => $startedAt]);
        
        // Update Laravel session variables to keep it in sync
        session(['active_room_work' => [
            'room_id' => $room->id,
            'started_at' => $startedAt->toISOString(),
            'status' => 'active',
            'accumulated_seconds' => 0
        ]]);

        // Pause work
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.pause', $room));

        // Refresh model
        $session->refresh();
        $this->assertEquals('paused', $session->status);
        $this->assertGreaterThanOrEqual(14, $session->total_seconds);
    }

    public function test_telecaller_stop_work_aggregates_metrics_and_saves_pending(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Assigned Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'Room Lead',
            'requirement' => 'Design',
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        // Start session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start', $room));

        // Log call
        $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead), [
            'status' => 'Connected',
            'customer_response' => 'Yes',
            'next_action' => 'Follow up',
            'remarks' => 'Positive',
            'lead_status' => 'converted',
            'duration' => 20
        ]);

        // Stop work session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.stop'));
        
        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        
        $response->assertRedirect(route('leads.start-work.summary', [$room->id, $session->id]));

        $this->assertEquals('pending', $session->status);
        $this->assertEquals(1, $session->calls_count);
        $this->assertEquals(1, $session->converted_count);
        $this->assertNotNull($session->ended_at);
    }

    public function test_admin_approvals_workflow(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Test Room',
            'description' => 'Test room description',
            'created_by' => $this->admin->id
        ]);

        $session = \App\Models\LeadRoomWorkSession::create([
            'user_id' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'total_seconds' => 3600,
            'calls_count' => 10,
            'converted_count' => 2,
            'status' => 'pending'
        ]);

        // Non-admin tries to access approvals index
        $response = $this->actingAs($this->telecaller1)->get(route('admin.telecaller-sessions.index'));
        $response->assertStatus(403);

        // Admin accesses approvals index
        $response2 = $this->actingAs($this->admin)->get(route('admin.telecaller-sessions.index'));
        $response2->assertStatus(200);
        $response2->assertSee('Pending Room Work Approvals');

        // Admin approves session
        $response3 = $this->actingAs($this->admin)->post(route('admin.telecaller-sessions.approve', $session));
        $response3->assertRedirect();
        
        $session->refresh();
        $this->assertEquals('approved', $session->status);
        $this->assertEquals($this->admin->id, $session->approved_by);
        $this->assertNotNull($session->approved_at);

        // Non-admin tries to approve
        $session->update(['status' => 'pending']);
        $response4 = $this->actingAs($this->telecaller2)->post(route('admin.telecaller-sessions.approve', $session));
        $response4->assertStatus(403);
    }

    public function test_telecaller_cannot_access_unauthorized_routes(): void
    {
        $task = \App\Models\Task::create([
            'title' => 'Some developer task',
            'assigned_to' => $this->salesExecutive->id,
            'created_by' => $this->admin->id,
            'status' => 'pending',
            'priority' => 'high'
        ]);

        // Accessing tasks list should be Forbidden
        $response = $this->actingAs($this->telecaller1)->get(route('tasks.index'));
        $response->assertStatus(403);

        // Accessing specific task details should be Forbidden
        $response2 = $this->actingAs($this->telecaller1)->get(route('tasks.show', $task));
        $response2->assertStatus(403);
    }

    public function test_telecaller_can_access_chat_mailbox_and_leaves(): void
    {
        // Accessing chat index should be Allowed (200)
        $response = $this->actingAs($this->telecaller1)->get(route('chat.index'));
        $response->assertStatus(200);

        // Accessing mailbox index should be Allowed (200)
        $response2 = $this->actingAs($this->telecaller1)->get(route('mailbox.index'));
        $response2->assertStatus(200);

        // Accessing leaves index should be Allowed (200)
        $response3 = $this->actingAs($this->telecaller1)->get(route('leaves.index'));
        $response3->assertStatus(200);
    }

    public function test_telecaller_leave_management_and_attendance_sync(): void
    {
        // 1. Telecaller accesses leaves index and should see the "Apply for Leave" button
        $response = $this->actingAs($this->telecaller1)->get(route('leaves.index'));
        $response->assertStatus(200);
        $response->assertSee('Apply for Leave');

        // 2. Telecaller applies for a 2-day leave
        $fromDate = today()->toDateString();
        $toDate = today()->addDay()->toDateString();
        
        $response = $this->actingAs($this->telecaller1)->post(route('leaves.store'), [
            'leave_type' => 'sick_leave',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'reason' => 'Feeling unwell'
        ]);

        $response->assertRedirect(route('leaves.index'));

        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->telecaller1->id,
            'leave_type' => 'sick_leave',
            'from_date' => $fromDate . ' 00:00:00',
            'to_date' => $toDate . ' 00:00:00',
            'status' => 'pending'
        ]);

        $leave = \App\Models\Leave::where('user_id', $this->telecaller1->id)->first();

        // 3. HR approves the leave request
        $response = $this->actingAs($this->admin)->post(route('leaves.approve-hr', $leave), [
            'comment' => 'Approved'
        ]);

        $response->assertRedirect();

        // Check that leaves status is approved
        $leave->refresh();
        $this->assertEquals('approved', $leave->status);

        // Check that Attendance records with status 'on_leave' have been created
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->telecaller1->id,
            'date' => $fromDate . ' 00:00:00',
            'status' => 'on_leave'
        ]);
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->telecaller1->id,
            'date' => $toDate . ' 00:00:00',
            'status' => 'on_leave'
        ]);

        // 4. Telecaller cancels/deletes the approved leave, which should delete the on_leave attendance records
        $response = $this->actingAs($this->telecaller1)->delete(route('leaves.destroy', $leave));
        $response->assertRedirect(route('leaves.index'));

        // Check that leave is deleted (soft-deleted)
        $this->assertSoftDeleted('leaves', [
            'id' => $leave->id
        ]);

        // Check that attendance records with status 'on_leave' are deleted
        $this->assertDatabaseMissing('attendance', [
            'user_id' => $this->telecaller1->id,
            'date' => $fromDate . ' 00:00:00',
            'status' => 'on_leave'
        ]);
        $this->assertDatabaseMissing('attendance', [
            'user_id' => $this->telecaller1->id,
            'date' => $toDate . ' 00:00:00',
            'status' => 'on_leave'
        ]);
    }

    public function test_telecaller_start_and_stop_work_notifies_admins_and_generates_pdf_report(): void
    {
        // Fake public storage disk
        \Illuminate\Support\Facades\Storage::fake('public');

        // 1. Create a lead room and assign telecaller1 to it
        $room = \App\Models\LeadRoom::create([
            'name' => 'Support Room',
            'description' => 'Test support room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // 2. Create some leads in this room assigned to telecaller1
        $lead1 = Lead::create([
            'client_name' => 'Call Lead One',
            'client_phone' => '1234567890',
            'client_email' => 'one@client.com',
            'requirement' => 'Web Audit',
            'lead_room_id' => $room->id,
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $lead2 = Lead::create([
            'client_name' => 'Interested Lead Two',
            'client_phone' => '0987654321',
            'client_email' => 'two@client.com',
            'service_required' => 'Custom SaaS',
            'requirement' => 'Design & Dev',
            'lead_room_id' => $room->id,
            'assigned_to' => $this->telecaller1->id,
            'created_by' => $this->admin->id,
            'source' => 'website',
            'status' => 'new'
        ]);

        // 3. Telecaller starts work
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.start', $room));
        $response->assertRedirect();

        // Verify start notifications were created for Admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'work_session',
            'title' => 'Telecaller Work Started'
        ]);
        $this->assertDatabaseHas('mailbox_messages', [
            'sender_id' => $this->telecaller1->id,
            'receiver_id' => $this->admin->id,
            'subject' => 'Work Session Started: ' . $room->name . ' by ' . $this->telecaller1->name
        ]);

        // 4. Log a call on lead1 (Connected)
        $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead1), [
            'status' => 'Connected',
            'customer_response' => 'Maybe next time',
            'remarks' => 'Call was average',
            'lead_status' => 'following_up',
            'duration' => 15,
            'source' => 'room_work'
        ]);

        // 5. Log a call on lead2 (Interested status updated to interested)
        $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead2), [
            'status' => 'Connected',
            'customer_response' => 'Wants prototype',
            'remarks' => 'Highly interested',
            'lead_status' => 'interested',
            'duration' => 60,
            'source' => 'room_work'
        ]);

        // 6. Stop work
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.stop'));
        $response->assertRedirect();

        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        $this->assertNotNull($session);

        // Verify end notifications were created for Admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'work_session',
            'title' => 'Telecaller Work Session Completed'
        ]);

        $mailboxMsg = \App\Models\MailboxMessage::where('sender_id', $this->telecaller1->id)
            ->where('receiver_id', $this->admin->id)
            ->where('subject', 'like', '%Daily Call Report & Session Ended%')
            ->first();

        $this->assertNotNull($mailboxMsg);
        $this->assertNotNull($mailboxMsg->attachment_path);
        
        // Verify the file was stored on public disk
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($mailboxMsg->attachment_path);

        // 7. Verify internal mailbox fallback (officialIndex)
        // Since admin doesn't have IMAP enabled by default
        $response = $this->actingAs($this->admin)->get(route('mailbox.official.index', ['folder' => 'inbox', 'user_id' => $this->admin->id]));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'subject' => $mailboxMsg->subject,
            'sender_name' => $this->telecaller1->name
        ]);

        // 8. Verify internal mailbox detail fallback (officialShow)
        $response = $this->actingAs($this->admin)->get(route('mailbox.official.show', ['uid' => $mailboxMsg->id, 'folder' => 'inbox', 'user_id' => $this->admin->id]));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'uid' => $mailboxMsg->id,
            'subject' => $mailboxMsg->subject,
            'body' => $mailboxMsg->body
        ]);
    }

    public function test_telecaller_select_room_shows_today_follow_ups_and_summary_shows_metrics_breakdown(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Room Alpha',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'Followup Cust',
            'requirement' => 'Design Call',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new',
            'follow_up_date' => today()
        ]);

        // Start day session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));

        // Visit select-room page and check if it lists today's follow-up
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room'));
        $response->assertStatus(200);
        $response->assertSee('Room Alpha');
        $response->assertSee("Today's Follow-up", false);

        // Select the room
        $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));

        // Log one connected call (marked interested)
        $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead), [
            'status' => 'Connected',
            'lead_status' => 'interested',
            'duration' => 60,
            'source' => 'room_work'
        ]);

        // Log one not connected call
        $lead2 = Lead::create([
            'client_name' => 'Busy Cust',
            'requirement' => 'Support',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);
        $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead2), [
            'status' => 'Busy',
            'duration' => 5,
            'source' => 'room_work'
        ]);

        // Stop session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.stop'));
        
        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        
        // Visit summary and check if breakdown stats exist
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.summary', [$room->id, $session->id]));
        $response->assertStatus(200);
        
        // Assert view has data
        $response->assertViewHas('totalCalls', 2);
        $response->assertViewHas('connectedCalls', 1);
        $response->assertViewHas('notConnectedCalls', 1);
        $response->assertViewHas('interestedCount', 1);

        $response->assertSee('Total Calls Logged');
        $response->assertSee('Connected Calls');
        $response->assertSee('Interested Leads');
        $response->assertSee('Not Connected / Busy');
    }

    public function test_telecaller_virtual_followup_room_workflow(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Room Alpha',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead = Lead::create([
            'client_name' => 'Followup Cust',
            'requirement' => 'Design Call',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new',
            'follow_up_date' => today()
        ]);

        // Start day session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));

        // Visit select-room page and check if it has the Today's Follow-ups virtual card
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room'));
        $response->assertStatus(200);
        $response->assertSee("Today's Follow-up", false);

        // Join virtual room
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-followups'));
        $response->assertRedirect(route('leads.start-work.followup-leads'));
        $this->assertEquals('followups', session('active_room_work.room_id'));

        // Try to access dashboard - should be locked & redirect to follow-ups leads page
        $response = $this->actingAs($this->telecaller1)->get(route('dashboard'));
        $response->assertRedirect(route('leads.start-work.followup-leads'));

        // View followups leads list
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.followup-leads'));
        $response->assertStatus(200);
        $response->assertSee('Followup Cust');

        // Log a call inside virtual followups room
        $response = $this->actingAs($this->telecaller1)->post(route('leads.calls.store', $lead), [
            'status' => 'Connected',
            'lead_status' => 'interested',
            'duration' => 30,
            'source' => 'room_work'
        ]);
        $response->assertRedirect(route('leads.start-work.followup-leads'));

        // Verify that the lead's follow_up_date has been set to null (saved as blank)
        $lead->refresh();
        $this->assertNull($lead->follow_up_date);

        // Pause follow-up session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.pause-followups'));
        $response->assertRedirect();
        $this->assertEquals('paused', session('active_room_work.status'));

        // Resume follow-up session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.resume-followups'));
        $response->assertRedirect();
        $this->assertEquals('active', session('active_room_work.status'));

        // Stop session
        $response = $this->actingAs($this->telecaller1)->post(route('leads.start-work.stop'));
        
        $session = \App\Models\LeadRoomWorkSession::where('user_id', $this->telecaller1->id)->first();
        $response->assertRedirect(route('leads.start-work.summary', [0, $session->id]));

        // Visit summary and check if breakdown stats exist
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.summary', [0, $session->id]));
        $response->assertStatus(200);
        $response->assertSee('Total Calls Logged');
        $response->assertSee('N/A'); // Room is N/A for followups virtual room
    }

    public function test_telecaller_can_export_interested_leads_to_xls(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Room Alpha',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Lead with 91 prefix
        Lead::create([
            'client_name' => 'John Doe',
            'requirement' => 'Sales',
            'client_phone' => '919876543210',
            'client_email' => 'john@example.com',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // Lead without 91 prefix
        Lead::create([
            'client_name' => 'Jane Smith',
            'requirement' => 'Support',
            'client_phone' => '8887776665',
            'client_email' => 'jane@example.com',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // Lead with country code with plus
        Lead::create([
            'client_name' => 'Bob Marley',
            'requirement' => 'Music',
            'client_phone' => '+919999999999',
            'client_email' => 'bob@example.com',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // Start day session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));

        // Visit export route
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.interested-leads.export'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        
        // Let's capture the streamed response content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // The content should be a valid binary file, but we can verify that we can load it using PhpSpreadsheet
        $tempFile = tempnam(sys_get_temp_dir(), 'xls');
        file_put_contents($tempFile, $content);

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Check headers
        $this->assertEquals('Phone Number', $sheet->getCell('A1')->getValue());
        $this->assertEquals('Name', $sheet->getCell('B1')->getValue());
        $this->assertEquals('Email', $sheet->getCell('C1')->getValue());

        // Check first lead (919876543210 -> 9876543210)
        $this->assertEquals('9876543210', $sheet->getCell('A2')->getValue());
        $this->assertEquals('John Doe', $sheet->getCell('B2')->getValue());
        $this->assertEquals('john@example.com', $sheet->getCell('C2')->getValue());

        // Check second lead (8887776665 -> 8887776665)
        $this->assertEquals('8887776665', $sheet->getCell('A3')->getValue());
        $this->assertEquals('Jane Smith', $sheet->getCell('B3')->getValue());
        $this->assertEquals('jane@example.com', $sheet->getCell('C3')->getValue());

        // Check third lead (+919999999999 -> 9999999999)
        $this->assertEquals('9999999999', $sheet->getCell('A4')->getValue());
        $this->assertEquals('Bob Marley', $sheet->getCell('B4')->getValue());
        $this->assertEquals('bob@example.com', $sheet->getCell('C4')->getValue());

        unlink($tempFile);
    }

    public function test_admin_leads_view_rooms_by_customer_workflow(): void
    {
        $client = \App\Models\Client::create([
            'company_name' => 'BigCorp',
            'contact_person' => 'CEO',
            'email' => 'ceo@bigcorp.com',
            'phone' => '1234567890',
            'status' => 'active'
        ]);

        $room = \App\Models\LeadRoom::create([
            'name' => 'General Calling Room',
            'description' => 'Test admin room',
            'client_id' => $client->id,
            'created_by' => $this->admin->id
        ]);

        // Create a lead in this room
        $lead = Lead::create([
            'client_name' => 'Lead under BigCorp',
            'requirement' => 'Web App',
            'client_phone' => '9876543210',
            'client_email' => 'lead@bigcorp.com',
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'interested'
        ]);

        // 1. Visit /leads without parameters (Default View)
        $response = $this->actingAs($this->admin)->get(route('leads.index'));
        $response->assertStatus(200);
        $response->assertSee('BigCorp');
        $response->assertSee('General Calling Room');
        // Flat leads list should NOT be shown yet (since no room is selected)
        $response->assertDontSee('Lead under BigCorp');

        // 2. Visit /leads?room_id=X (Room View)
        $response = $this->actingAs($this->admin)->get(route('leads.index', ['room_id' => $room->id, 'tab' => 'interested']));
        $response->assertStatus(200);
        $response->assertSee('Lead under BigCorp');
        $response->assertSee('Today Follow-ups');
        $response->assertSee('Interested');
        $response->assertSee('Not Connected Leads');
        $response->assertSee('All Contacts');

        // 3. Visit /leads?type=interested (Global Filter View)
        $response = $this->actingAs($this->admin)->get(route('leads.index', ['type' => 'interested']));
        $response->assertStatus(200);
        $response->assertSee('Lead under BigCorp');
        $response->assertSee('Interested Leads');
    }

    public function test_admin_leads_view_telecallers_workflow(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'General Calling Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        // Create a session for this telecaller
        $session = \App\Models\LeadRoomWorkSession::create([
            'user_id' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'total_seconds' => 3600,
            'calls_count' => 10,
            'converted_count' => 1,
            'status' => 'approved',
        ]);

        // Visit /leads?view=telecaller
        $response = $this->actingAs($this->admin)->get(route('leads.index', ['view' => 'telecaller']));
        $response->assertStatus(200);
        $response->assertSee($this->telecaller1->name);
        $response->assertSee('General Calling Room');
        $response->assertSee('10 calls');
        $response->assertSee('1 converted');
        $response->assertSee('View Report');
    }

    public function test_telecaller_report_pdf_download_permissions(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'General Calling Room',
            'description' => 'Test room',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $session = \App\Models\LeadRoomWorkSession::create([
            'user_id' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'total_seconds' => 3600,
            'calls_count' => 5,
            'converted_count' => 0,
            'status' => 'pending',
        ]);

        // 1. Admin downloads PDF report - should succeed
        $response = $this->actingAs($this->admin)->get(route('leads.start-work.download-report', $session));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // 2. Telecaller attempts to download PDF report (even their own) - should fail with 403 Forbidden
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.download-report', $session));
        $response->assertStatus(403);
    }

    public function test_telecaller_room_selection_renders_direct_rooms_and_defaults_to_uncalled_leads(): void
    {
        $client = \App\Models\Client::create([
            'company_name' => 'CustomClientCorp',
            'contact_person' => 'John Client',
            'email' => 'john@client.com',
            'phone' => '1112223333',
            'status' => 'active'
        ]);

        $room = \App\Models\LeadRoom::create([
            'name' => 'Calling Room Direct',
            'description' => 'Test room direct',
            'client_id' => $client->id,
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $leadUncalled = Lead::create([
            'client_name' => 'Uncalled Lead Person',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $leadCalled = Lead::create([
            'client_name' => 'Already Called Lead Person',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        LeadCall::create([
            'lead_id' => $leadCalled->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'remarks' => 'called lead'
        ]);

        // Start session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));

        // Visit select-room page
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room'));
        $response->assertStatus(200);
        $response->assertSee('Calling Room Direct');
        $response->assertSee('CustomClientCorp');
        // Ensure there is NO accordion collapse client targets
        $response->assertDontSee('collapseClient_');

        // Select the room
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room-join', $room));
        $response->assertRedirect(route('leads.start-work.leads', $room));

        // Open leads page and check if it defaults to Next Call (uncalled)
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.leads', $room));
        $response->assertStatus(200);
        $response->assertSee('Next Call');
        $response->assertSee('Uncalled Lead Person');
        $response->assertDontSee('Already Called Lead Person');
        
        // Assert "NEXT CALL" badge is displayed
        $response->assertSee('NEXT CALL');
        $response->assertSee('Start Next Call');
    }

    public function test_telecaller_not_connected_leads_count_matches_today_calls_logged(): void
    {
        $room = \App\Models\LeadRoom::create([
            'name' => 'Calling Room Direct',
            'description' => 'Test room direct',
            'created_by' => $this->admin->id
        ]);
        $room->users()->attach($this->telecaller1->id);

        $lead1 = Lead::create([
            'client_name' => 'Lead Called Today Not Connected',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        $lead2 = Lead::create([
            'client_name' => 'Lead Called Yesterday Not Connected',
            'requirement' => 'Design',
            'assigned_to' => $this->telecaller1->id,
            'lead_room_id' => $room->id,
            'created_by' => $this->admin->id,
            'source' => 'direct',
            'status' => 'new'
        ]);

        // Log call yesterday on lead2
        $callYesterday = new LeadCall([
            'lead_id' => $lead2->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now()->subDay(),
            'status' => 'Busy',
            'remarks' => 'called yesterday',
        ]);
        $callYesterday->created_at = now()->subDay();
        $callYesterday->save();

        // Log call today on lead1
        $callToday = new LeadCall([
            'lead_id' => $lead1->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Busy',
            'remarks' => 'called today',
        ]);
        $callToday->created_at = now();
        $callToday->save();

        // Start session
        $this->actingAs($this->telecaller1)->post(route('leads.start-work.start-session'));

        // Visit select-room page and check if not connected calls today count is 1 (lead1 called today, not lead2)
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.select-room'));
        $response->assertStatus(200);
        $response->assertViewHas('notConnectedCalls', 1);

        // Visit not connected leads page and verify only lead1 is listed, count is 1
        $response = $this->actingAs($this->telecaller1)->get(route('leads.start-work.not-connected-leads'));
        $response->assertStatus(200);
        $response->assertViewHas('totalLeads', 1);
        $response->assertSee('Lead Called Today Not Connected');
        $response->assertDontSee('Lead Called Yesterday Not Connected');
    }
}

