<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectAmc;
use App\Models\ProjectAmcLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAmcManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teamLeader;
    private User $employee;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $tlRole = Role::create([
            'name' => 'Team Leader',
            'slug' => 'team-leader',
            'description' => 'Leader',
            'color' => '#10b981'
        ]);

        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Standard employee',
            'color' => '#6366f1'
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $this->teamLeader = User::create([
            'name' => 'Leader User',
            'email' => 'tl@example.com',
            'password' => bcrypt('password'),
            'role_id' => $tlRole->id,
            'status' => 'active'
        ]);

        $this->employee = User::create([
            'name' => 'Standard Employee',
            'email' => 'emp@example.com',
            'password' => bcrypt('password'),
            'role_id' => $employeeRole->id,
            'status' => 'active'
        ]);

        $client = Client::create([
            'name' => 'Acme Inc',
            'contact_person' => 'John Client',
            'phone' => '1234567890',
            'email' => 'acme@example.com',
            'company_name' => 'Acme Company',
            'status' => 'active',
            'created_by' => $this->admin->id
        ]);

        $this->project = Project::create([
            'project_code'   => 'PRJ-001',
            'name'           => 'Acme Web Application',
            'client_id'      => $client->id,
            'team_leader_id' => $this->teamLeader->id,
            'start_date'     => '2026-01-01',
            'deadline'       => '2026-12-31',
            'project_value'  => 150000.00,
            'priority'       => 'high',
            'status'         => 'development',
            'created_by'     => $this->admin->id,
        ]);
    }

    public function test_admin_can_access_project_amc_index_and_create_amc(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('project-amcs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('project_amcs.index');

        $amcData = [
            'project_id' => $this->project->id,
            'amount'     => 30000.00,
            'start_date' => '2026-07-04',
            'end_date'   => '2027-07-03',
            'frequency'  => 'annually',
            'status'     => 'active',
            'remarks'    => 'Contract signed',
        ];

        $createResponse = $this->actingAs($this->admin)
            ->post(route('project-amcs.store'), $amcData);

        $createResponse->assertRedirect(route('project-amcs.index'));
        $this->assertDatabaseHas('project_amcs', [
            'project_id' => $this->project->id,
            'amount'     => 30000.00,
            'status'     => 'active',
        ]);
    }

    public function test_team_leader_can_view_amcs_but_cannot_create_amc(): void
    {
        // View AMC page
        $response = $this->actingAs($this->teamLeader)
            ->get(route('project-amcs.index'));

        $response->assertStatus(200);

        // Attempt to create AMC -> fails with 403 (restricted writeOnly check)
        $amcData = [
            'project_id' => $this->project->id,
            'amount'     => 30000.00,
            'start_date' => '2026-07-04',
            'end_date'   => '2027-07-03',
            'frequency'  => 'annually',
            'status'     => 'active',
        ];

        $createResponse = $this->actingAs($this->teamLeader)
            ->post(route('project-amcs.store'), $amcData);

        $createResponse->assertStatus(403);
    }

    public function test_employee_cannot_access_project_amcs(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('project-amcs.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_log_renewal_payment(): void
    {
        $amc = ProjectAmc::create([
            'project_id' => $this->project->id,
            'amount'     => 50000.00,
            'start_date' => '2026-07-04',
            'end_date'   => '2027-07-03',
            'frequency'  => 'annually',
            'status'     => 'active',
        ]);

        $logData = [
            'payment_date' => '2026-07-05',
            'amount_paid'  => 25000.00,
            'payment_mode' => 'Bank Transfer',
            'reference_no' => 'REF-AMC-101',
            'remarks'      => 'Half payment paid',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('project-amcs.logs.store', $amc), $logData);

        $response->assertRedirect(route('project-amcs.show', $amc));
        $this->assertDatabaseHas('project_amc_logs', [
            'project_amc_id' => $amc->id,
            'amount_paid'    => 25000.00,
            'reference_no'   => 'REF-AMC-101',
        ]);
    }

    public function test_admin_can_download_amc_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('project-amcs.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="project_amcs_import_template.xlsx"');
    }

    public function test_admin_can_import_amc_excel(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = ['Project Code', 'Project Name', 'AMC Amount', 'Start Date', 'End Date', 'Frequency', 'Status', 'Remarks'];
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        $row = [$this->project->project_code, 'ignored name', '45000.00', '2026-07-04', '2027-07-03', 'annually', 'active', 'Imported contract'];
        foreach ($row as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_import') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFilePath);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFilePath,
            'import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Step 1: Preview
        $response = $this->actingAs($this->admin)
            ->post(route('project-amcs.import.preview'), [
                'file' => $uploadedFile
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('project_amcs.import_preview');
        $storedTempFilePath = $response->viewData('tempFilePath');
        $this->assertNotEmpty($storedTempFilePath);

        // Step 2: Submit
        $responseSubmit = $this->actingAs($this->admin)
            ->post(route('project-amcs.import.submit'), [
                'temp_file_path' => $storedTempFilePath
            ]);

        $responseSubmit->assertRedirect(route('project-amcs.index'));
        $responseSubmit->assertSessionHas('success');
        
        $this->assertDatabaseHas('project_amcs', [
            'project_id' => $this->project->id,
            'amount'     => 45000.00,
            'frequency'  => 'annually',
            'status'     => 'active',
        ]);

        @unlink($tempFilePath);
        if (file_exists($storedTempFilePath)) {
            @unlink($storedTempFilePath);
        }
    }

    public function test_team_leader_cannot_download_amc_template(): void
    {
        $response = $this->actingAs($this->teamLeader)
            ->get(route('project-amcs.import.template'));

        $response->assertStatus(403);
    }

    public function test_team_leader_cannot_import_amc_excel(): void
    {
        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->create('amc_import.xlsx', 100);

        $responsePreview = $this->actingAs($this->teamLeader)
            ->post(route('project-amcs.import.preview'), [
                'file' => $uploadedFile
            ]);
        $responsePreview->assertStatus(403);

        $responseSubmit = $this->actingAs($this->teamLeader)
            ->post(route('project-amcs.import.submit'), [
                'temp_file_path' => '/some/path/to/amc_import.xlsx'
            ]);
        $responseSubmit->assertStatus(403);
    }
}
