<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Lead;
use App\Models\LeadRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\UploadedFile;

class LeadRoomImportTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $telecallerRole;
    private User $admin;
    private User $telecaller1;
    private User $telecaller2;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_admin_can_manage_rooms_and_assign_telecallers(): void
    {
        $client = \App\Models\Client::create([
            'company_name' => 'Acme Corp',
            'contact_person' => 'John Client',
            'email' => 'acme@example.com',
            'phone' => '1234567890',
            'status' => 'active',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('lead-rooms.store'), [
                'client_id' => $client->id,
                'name' => 'SaaS Campaign',
                'description' => 'SaaS outbound leads'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_rooms', [
            'client_id' => $client->id,
            'name' => 'SaaS Campaign',
            'description' => 'SaaS outbound leads',
            'created_by' => $this->admin->id
        ]);

        $room = LeadRoom::first();

        // Assign telecaller1 to this room
        $assignResponse = $this->actingAs($this->admin)
            ->post(route('lead-rooms.assign', $room), [
                'telecaller_ids' => [$this->telecaller1->id]
            ]);

        $assignResponse->assertRedirect();
        $this->assertDatabaseHas('lead_room_user', [
            'lead_room_id' => $room->id,
            'user_id' => $this->telecaller1->id
        ]);
        $this->assertDatabaseMissing('lead_room_user', [
            'lead_room_id' => $room->id,
            'user_id' => $this->telecaller2->id
        ]);
    }

    public function test_telecaller_cannot_manage_rooms(): void
    {
        $response = $this->actingAs($this->telecaller1)
            ->get(route('lead-rooms.index'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->telecaller1)
            ->post(route('lead-rooms.store'), [
                'name' => 'Should Fail',
            ]);
        $response2->assertStatus(403);
    }

    public function test_telecaller_room_scoping_isolation(): void
    {
        $roomA = LeadRoom::create(['name' => 'Room A', 'created_by' => $this->admin->id]);
        $roomB = LeadRoom::create(['name' => 'Room B', 'created_by' => $this->admin->id]);

        // Assign telecaller1 to Room A, telecaller2 to Room B
        $roomA->users()->sync([$this->telecaller1->id]);
        $roomB->users()->sync([$this->telecaller2->id]);

        // Create leads in both rooms
        $leadInA = Lead::create([
            'client_name' => 'Lead In Room A',
            'client_phone' => '1111111111',
            'lead_room_id' => $roomA->id,
            'requirement' => 'CRM app',
            'source' => 'direct',
            'status' => 'new'
        ]);

        $leadInB = Lead::create([
            'client_name' => 'Lead In Room B',
            'client_phone' => '2222222222',
            'lead_room_id' => $roomB->id,
            'requirement' => 'ERP development',
            'source' => 'website',
            'status' => 'new'
        ]);

        // Telecaller 1 index check: should be redirected to start work index
        $response = $this->actingAs($this->telecaller1)->get(route('leads.index'));
        $response->assertRedirect(route('leads.start-work.index'));

        // Telecaller 1 show check: should be able to view Lead in Room A, but get 403 for Room B
        $showResponseA = $this->actingAs($this->telecaller1)->get(route('leads.show', $leadInA));
        $showResponseA->assertStatus(200);

        $showResponseB = $this->actingAs($this->telecaller1)->get(route('leads.show', $leadInB));
        $showResponseB->assertStatus(403);
    }

    public function test_excel_template_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leads.import.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_excel_import_flow_preview_and_submission(): void
    {
        $client = \App\Models\Client::create([
            'company_name' => 'Acme Corp',
            'contact_person' => 'John Client',
            'email' => 'acme@example.com',
            'phone' => '1234567890',
            'status' => 'active',
            'created_by' => $this->admin->id
        ]);

        $room = LeadRoom::create([
            'client_id' => $client->id,
            'name' => 'Import Room',
            'created_by' => $this->admin->id
        ]);

        // Create mock excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Write Headers
        $sheet->setCellValue('A1', 'Client Name');
        $sheet->setCellValue('B1', 'Client Phone');
        $sheet->setCellValue('C1', 'Client Email');
        $sheet->setCellValue('D1', 'Location');
        $sheet->setCellValue('E1', 'Business Type');
        $sheet->setCellValue('F1', 'Source');
        $sheet->setCellValue('G1', 'Requirement');
        $sheet->setCellValue('H1', 'Estimated Budget');
        $sheet->setCellValue('I1', 'Notes');

        // Row 2: Valid
        $sheet->setCellValue('A2', 'Valid Client');
        $sheet->setCellValue('B2', '9998887776');
        $sheet->setCellValue('C2', 'valid@example.com');
        $sheet->setCellValue('D2', 'Delhi');
        $sheet->setCellValue('E2', 'Agency');
        $sheet->setCellValue('F2', 'whatsapp');
        $sheet->setCellValue('G2', 'SaaS App');
        $sheet->setCellValue('H2', '12000');
        $sheet->setCellValue('I2', 'High priority');

        // Row 3: Invalid (Missing Phone)
        $sheet->setCellValue('A3', 'Invalid Client');
        $sheet->setCellValue('B3', '');
        $sheet->setCellValue('C3', 'invalid@example.com');

        // Save mock file to temporary path
        $writer = new Xlsx($spreadsheet);
        $tempPath = tempnam(storage_path('app'), 'lead_import_test_xlsx');
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile(
            $tempPath,
            'leads_test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Upload and Preview
        $previewResponse = $this->actingAs($this->admin)
            ->post(route('leads.import.preview'), [
                'file' => $uploadedFile,
                'lead_room_id' => $room->id
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('leads.import_preview');
        $previewResponse->assertViewHas('validRows', function ($rows) {
            return count($rows) === 1 && $rows[0]['client_name'] === 'Valid Client';
        });
        $previewResponse->assertViewHas('invalidRows', function ($rows) {
            return count($rows) === 1 && $rows[0]['client_name'] === 'Invalid Client';
        });

        // Get the temp file path passed in preview
        $tempStoredPath = $previewResponse->viewData('tempFilePath');

        // Submit the preview data to final import
        $submitResponse = $this->actingAs($this->admin)
            ->post(route('leads.import.submit'), [
                'temp_file_path' => $tempStoredPath,
                'lead_room_id' => $room->id
            ]);

        $submitResponse->assertRedirect(route('leads.index'));

        // Check if database has the imported lead under the room
        $this->assertDatabaseHas('leads', [
            'client_id' => $client->id,
            'client_name' => 'Valid Client',
            'client_phone' => '9998887776',
            'client_email' => 'valid@example.com',
            'location' => 'Delhi',
            'business_type' => 'Agency',
            'source' => 'whatsapp',
            'requirement' => 'SaaS App',
            'estimated_budget' => 12000,
            'lead_room_id' => $room->id,
            'assigned_to' => null
        ]);

        // Check that the invalid lead was NOT imported
        $this->assertDatabaseMissing('leads', [
            'client_name' => 'Invalid Client'
        ]);

        // Check that the temp file has been cleaned up/deleted
        $this->assertFalse(file_exists($tempStoredPath));
    }

    public function test_admin_can_view_lead_rooms_counts(): void
    {
        $room = LeadRoom::create(['name' => 'Metrics Room', 'created_by' => $this->admin->id]);

        // Lead 1: Uncalled
        Lead::create([
            'client_name' => 'Uncalled Lead',
            'client_phone' => '1111111111',
            'lead_room_id' => $room->id,
            'requirement' => 'CRM app',
            'source' => 'direct',
            'status' => 'new'
        ]);

        // Lead 2: Contacted but not interested
        $lead2 = Lead::create([
            'client_name' => 'Contacted Lead',
            'client_phone' => '2222222222',
            'lead_room_id' => $room->id,
            'requirement' => 'CRM app',
            'source' => 'direct',
            'status' => 'following_up'
        ]);

        \App\Models\LeadCall::create([
            'lead_id' => $lead2->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'customer_response' => 'Discussing requirements',
            'remarks' => 'Connected call'
        ]);

        // Lead 3: Contacted and interested
        $lead3 = Lead::create([
            'client_name' => 'Interested Lead',
            'client_phone' => '3333333333',
            'lead_room_id' => $room->id,
            'requirement' => 'CRM app',
            'source' => 'direct',
            'status' => 'interested'
        ]);

        \App\Models\LeadCall::create([
            'lead_id' => $lead3->id,
            'telecaller_id' => $this->telecaller1->id,
            'call_date_time' => now(),
            'status' => 'Connected',
            'customer_response' => 'Wants product demo',
            'remarks' => 'Interested client'
        ]);

        // Hit the room list index
        $response = $this->actingAs($this->admin)->get(route('lead-rooms.index'));

        $response->assertStatus(200);

        // Check view data
        $response->assertViewHas('rooms', function ($rooms) use ($room) {
            $matchedRoom = $rooms->firstWhere('id', $room->id);
            return $matchedRoom &&
                $matchedRoom->leads_count === 3 &&
                $matchedRoom->contacted_leads_count === 2 &&
                $matchedRoom->interested_leads_count === 1;
        });

        // Assert that counts are visible in HTML output
        $response->assertSee('3'); // Total Leads
        $response->assertSee('2'); // Contacted
        $response->assertSee('1'); // Interested
    }
}
