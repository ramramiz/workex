<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;

class ClientImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $leaderUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->first();
        $leaderRole = Role::where('slug', 'team-leader')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@example.com'
        ]);

        $this->leaderUser = User::factory()->create([
            'role_id' => $leaderRole->id,
            'email' => 'leader@example.com'
        ]);

        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email' => 'employee@example.com'
        ]);
    }

    public function test_super_admin_can_download_client_template()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('clients.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="clients_import_template.xlsx"');
    }

    public function test_leader_cannot_download_client_template()
    {
        $response = $this->actingAs($this->leaderUser)
            ->get(route('clients.import.template'));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_download_client_template()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('clients.import.template'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_import_clients_excel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Company Name', 'Contact Person', 'Email', 'Phone', 'Address', 'City', 'State', 'Country', 'GST Number', 'Website', 'Notes', 'Status'
        ];
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Row 2: valid new client
        $row2 = [
            'Test Company Ltd', 'Jane Contact', 'jane@example.com', '9876543210', '456 Lane', 'Mumbai', 'MH', 'India', '27ABCDE1234F1Z1', 'www.test.com', 'Valid notes', 'active'
        ];

        foreach ($row2 as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        // Row 3: duplicate email (should be skipped)
        $row3 = [
            'Duplicate Company', 'Jane Duplicate', 'jane@example.com', '0000000000', '', '', '', '', '', '', '', 'active'
        ];

        foreach ($row3 as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '3', $value);
        }

        // Row 4: invalid format/missing field (should be skipped)
        $row4 = [
            '', 'Missing Company', 'missing@example.com', '', '', '', '', '', '', '', '', 'active'
        ];

        foreach ($row4 as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '4', $value);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'client_import') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFilePath);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'clients.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Step 1: Preview the file
        $response = $this->actingAs($this->adminUser)
            ->post(route('clients.import.preview'), [
                'file' => $uploadedFile
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('clients.import_preview');
        $storedTempFilePath = $response->viewData('tempFilePath');
        $this->assertNotEmpty($storedTempFilePath);

        // Step 2: Submit the import
        $responseSubmit = $this->actingAs($this->adminUser)
            ->post(route('clients.import.submit'), [
                'temp_file_path' => $storedTempFilePath
            ]);

        $responseSubmit->assertRedirect(route('clients.index'));
        $responseSubmit->assertSessionHas('success');

        $client = Client::where('email', 'jane@example.com')->first();
        $this->assertNotNull($client);
        $this->assertEquals('Test Company Ltd', $client->company_name);
        $this->assertEquals('Jane Contact', $client->contact_person);
        $this->assertEquals('9876543210', $client->phone);
        $this->assertEquals('456 Lane', $client->address);
        $this->assertEquals('Mumbai', $client->city);
        $this->assertEquals('MH', $client->state);
        $this->assertEquals('India', $client->country);
        $this->assertEquals('27ABCDE1234F1Z1', $client->gst_number);
        $this->assertEquals('www.test.com', $client->website);
        $this->assertEquals('Valid notes', $client->notes);
        $this->assertEquals('active', $client->status);

        // Verify duplicate was NOT created
        $duplicateCount = Client::where('email', 'jane@example.com')->count();
        $this->assertEquals(1, $duplicateCount);

        @unlink($tempFilePath);
        if (file_exists($storedTempFilePath)) {
            @unlink($storedTempFilePath);
        }
    }

    public function test_leader_cannot_import_clients_excel()
    {
        $uploadedFile = UploadedFile::fake()->create('clients.xlsx', 100);

        $responsePreview = $this->actingAs($this->leaderUser)
            ->post(route('clients.import.preview'), [
                'file' => $uploadedFile
            ]);
        $responsePreview->assertStatus(403);

        $responseSubmit = $this->actingAs($this->leaderUser)
            ->post(route('clients.import.submit'), [
                'temp_file_path' => '/some/path/to/clients.xlsx'
            ]);
        $responseSubmit->assertStatus(403);
    }
}
