<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;

class ProjectManagementTest extends TestCase
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

    public function test_super_admin_can_download_project_template()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('projects.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="projects_import_template.xlsx"');
    }

    public function test_leader_cannot_download_project_template()
    {
        $response = $this->actingAs($this->leaderUser)
            ->get(route('projects.import.template'));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_download_project_template()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('projects.import.template'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_import_projects_excel()
    {
        // Pre-create a client that matches sample data
        $client = Client::create([
            'company_name' => 'Acme Company',
            'name' => 'Acme Contact',
            'contact_person' => 'Acme Contact Person',
            'phone' => '9876543210',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $this->adminUser->id
        ]);

        $domainReg = \App\Models\DomainRegistration::create([
            'name' => 'GoDaddy',
            'company_id' => $this->adminUser->company_id,
        ]);
        $hostingProv = \App\Models\HostingProvider::create([
            'name' => 'Hostinger',
            'company_id' => $this->adminUser->company_id,
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Project Name', 'Project Type', 'Description', 'Client Company Name',
            'Team Leader Email', 'Project Budget', 'Priority', 'Project Start Date', 'Deadline', 'Technologies',
            'Project Code', 'Project URL', 'Completed Date', 'Advance Amount', 'Balance Amount', 'Manager Email',
            'Progress Percentage', 'Notes', 'Amc Start Date', 'AMC Billing Frequency', 'AMC Value', 'AMC Due Date',
            'AMC Contract Status', 'AMC Remarks', 'Domain Provider', 'Domain Valid Till', 'Hosting Provider', 'Hosting Valid Till'
        ];
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        $row = [
            'Imported E-Commerce', 'web', 'Imported desc', 'Acme Company',
            'leader@example.com', '125000.00', 'critical', '2026-07-05', '2026-12-31', 'Laravel, React',
            'PRJ-IMP-111', 'https://imported.com', '2026-12-30', '50000.00', '20000.00', 'manager@example.com',
            '75', 'Imported notes', '2026-08-01',
            'quarterly', '15000.00', '2027-07-31', 'pending_renewal', 'My test remarks',
            'GoDaddy', '2027-07-05', 'Hostinger', '2027-07-05'
        ];

        foreach ($row as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'proj_import') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFilePath);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'projects.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Step 1: Preview the file
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.import.preview'), [
                'file' => $uploadedFile
            ]);

        $response->assertStatus(200);
        $response->assertViewIs('projects.import_preview');
        $storedTempFilePath = $response->viewData('tempFilePath');
        $this->assertNotEmpty($storedTempFilePath);

        // Step 2: Submit the import
        $responseSubmit = $this->actingAs($this->adminUser)
            ->post(route('projects.import.submit'), [
                'temp_file_path' => $storedTempFilePath
            ]);

        $responseSubmit->assertRedirect(route('projects.index'));
        $responseSubmit->assertSessionHas('success');

        $project = Project::where('name', 'Imported E-Commerce')->first();
        $this->assertNotNull($project);
        $this->assertEquals('completed_started_amc', $project->status);
        $this->assertEquals('web', $project->project_type);
        $this->assertEquals('critical', $project->priority);
        $this->assertEquals(125000.00, $project->project_value);
        $this->assertEquals('2026-07-05', $project->start_date->format('Y-m-d'));
        $this->assertEquals('2026-12-31', $project->deadline->format('Y-m-d'));
        $this->assertEquals($client->id, $project->client_id);
        $this->assertEquals($this->leaderUser->id, $project->team_leader_id);
        
        $this->assertEquals('PRJ-IMP-111', $project->project_code);
        $this->assertEquals('https://imported.com', $project->url);
        $this->assertEquals('2026-12-30', $project->completed_date->format('Y-m-d'));
        $this->assertEquals(50000.00, $project->advance_amount);
        $this->assertEquals(20000.00, $project->balance_amount);
        $this->assertEquals(75, $project->progress_percentage);
        $this->assertEquals('Imported notes', $project->notes);

        $this->assertEquals('GoDaddy', $project->domain_provider);
        $this->assertEquals($domainReg->id, $project->domain_registration_id);
        $this->assertEquals('2027-07-05', $project->domain_valid_till->format('Y-m-d'));
        $this->assertEquals($hostingProv->id, $project->hosting_provider_id);
        $this->assertEquals('2027-07-05', $project->hosting_valid_till->format('Y-m-d'));

        $this->assertDatabaseHas('project_amcs', [
            'project_id' => $project->id,
            'amount'     => 15000.00,
            'start_date' => '2026-08-01 00:00:00',
            'end_date'   => '2027-07-31 00:00:00',
            'frequency'  => 'quarterly',
            'status'     => 'pending_renewal',
            'remarks'    => 'My test remarks',
        ]);

        @unlink($tempFilePath);
        if (file_exists($storedTempFilePath)) {
            @unlink($storedTempFilePath);
        }
    }

    public function test_super_admin_can_import_and_update_existing_projects_excel()
    {
        // 1. Pre-create a client
        $client = Client::create([
            'company_name' => 'Acme Company',
            'name' => 'Acme Contact',
            'contact_person' => 'Acme Contact Person',
            'phone' => '9876543210',
            'email' => 'acme@example.com',
            'status' => 'active',
            'created_by' => $this->adminUser->id
        ]);

        // 2. Pre-create a project with the code 'PRJ-IMP-111'
        $existingProject = Project::create([
            'project_code'        => 'PRJ-IMP-111',
            'name'                => 'Old Project Name',
            'description'         => 'Old description',
            'client_id'           => $client->id,
            'team_leader_id'      => $this->leaderUser->id,
            'manager_id'          => $this->adminUser->id,
            'start_date'          => '2026-01-01',
            'deadline'            => '2026-06-30',
            'completed_date'      => null,
            'project_value'       => 50000.00,
            'advance_amount'      => 10000.00,
            'balance_amount'      => 40000.00,
            'progress_percentage' => 10,
            'notes'               => 'Old notes',
            'priority'            => 'low',
            'status'              => 'planning',
            'technologies'        => ['PHP'],
            'project_type'        => 'web',
            'url'                 => 'https://old.com',
            'created_by'          => $this->adminUser->id,
            'company_id'          => $this->adminUser->company_id,
        ]);

        $domainReg = \App\Models\DomainRegistration::create([
            'name' => 'GoDaddy',
            'company_id' => $this->adminUser->company_id,
        ]);
        $hostingProv = \App\Models\HostingProvider::create([
            'name' => 'Hostinger',
            'company_id' => $this->adminUser->company_id,
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Project Name', 'Project Type', 'Description', 'Client Company Name',
            'Team Leader Email', 'Project Budget', 'Priority', 'Project Start Date', 'Deadline', 'Technologies',
            'Project Code', 'Project URL', 'Completed Date', 'Advance Amount', 'Balance Amount', 'Manager Email',
            'Progress Percentage', 'Notes', 'Amc Start Date', 'AMC Billing Frequency', 'AMC Value', 'AMC Due Date',
            'AMC Contract Status', 'AMC Remarks', 'Domain Provider', 'Domain Valid Till', 'Hosting Provider', 'Hosting Valid Till'
        ];
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Row containing same project code PRJ-IMP-111, but updated data
        $row = [
            'Updated Project Name', 'mobile', 'Updated desc', 'Acme Company',
            'leader@example.com', '99999.00', 'high', '2026-07-01', '2026-12-31', 'Flutter, Firebase',
            'PRJ-IMP-111', 'https://updated.com', '2026-12-25', '30000.00', '69999.00', 'admin@example.com',
            '90', 'Updated notes', null,
            'annually', '0.00', null, 'active', null,
            'GoDaddy', '2027-07-05', 'Hostinger', '2027-07-05'
        ];

        foreach ($row as $colIndex => $value) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $value);
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'proj_import') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFilePath);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'projects.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Preview the file (assert exists = true is set in view data)
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.import.preview'), [
                'file' => $uploadedFile
            ]);

        $response->assertStatus(200);
        $validRows = $response->viewData('validRows');
        $this->assertCount(1, $validRows);
        $this->assertTrue($validRows[0]['exists']);

        $storedTempFilePath = $response->viewData('tempFilePath');

        // Submit the import
        $responseSubmit = $this->actingAs($this->adminUser)
            ->post(route('projects.import.submit'), [
                'temp_file_path' => $storedTempFilePath
            ]);

        $responseSubmit->assertRedirect(route('projects.index'));

        // Assert existing project was updated and not duplicated
        $this->assertEquals(1, Project::where('project_code', 'PRJ-IMP-111')->count());

        $project = Project::where('project_code', 'PRJ-IMP-111')->first();
        $this->assertEquals('Updated Project Name', $project->name);
        $this->assertEquals('mobile', $project->project_type);
        $this->assertEquals('Updated desc', $project->description);
        $this->assertEquals('high', $project->priority);
        $this->assertEquals(99999.00, $project->project_value);
        $this->assertEquals('2026-07-01', $project->start_date->format('Y-m-d'));
        $this->assertEquals('2026-12-31', $project->deadline->format('Y-m-d'));
        $this->assertEquals('https://updated.com', $project->url);
        $this->assertEquals(90, $project->progress_percentage);
        $this->assertEquals('Updated notes', $project->notes);
        $this->assertEquals('GoDaddy', $project->domain_provider);
        $this->assertEquals($domainReg->id, $project->domain_registration_id);
        $this->assertEquals('2027-07-05', $project->domain_valid_till->format('Y-m-d'));
        $this->assertEquals($hostingProv->id, $project->hosting_provider_id);
        $this->assertEquals('2027-07-05', $project->hosting_valid_till->format('Y-m-d'));

        @unlink($tempFilePath);
        if (file_exists($storedTempFilePath)) {
            @unlink($storedTempFilePath);
        }
    }

    public function test_leader_cannot_import_projects_excel()
    {
        $uploadedFile = UploadedFile::fake()->create('projects.xlsx', 100);

        $responsePreview = $this->actingAs($this->leaderUser)
            ->post(route('projects.import.preview'), [
                'file' => $uploadedFile
            ]);
        $responsePreview->assertStatus(403);

        $responseSubmit = $this->actingAs($this->leaderUser)
            ->post(route('projects.import.submit'), [
                'temp_file_path' => '/some/path/to/projects.xlsx'
            ]);
        $responseSubmit->assertStatus(403);
    }

    public function test_admin_can_create_project_with_amc()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('projects.store'), [
                'name' => 'Manual Project with AMC',
                'priority' => 'high',
                'start_date' => '2026-07-05',
                'amc_start_date' => '2026-08-01',
                'amc_end_date' => '2027-07-31',
                'amc_amount' => '35000.00',
                'amc_frequency' => 'quarterly',
                'amc_status' => 'pending_renewal',
                'amc_remarks' => 'My manual create notes',
            ]);

        $project = Project::where('name', 'Manual Project with AMC')->first();
        $this->assertNotNull($project);
        $this->assertEquals('completed_started_amc', $project->status);
        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_amcs', [
            'project_id' => $project->id,
            'amount' => 35000.00,
            'start_date' => '2026-08-01 00:00:00',
            'end_date' => '2027-07-31 00:00:00',
            'frequency' => 'quarterly',
            'status' => 'pending_renewal',
            'remarks' => 'My manual create notes',
        ]);
    }

    public function test_admin_can_edit_project_amc()
    {
        $project = Project::create([
            'project_code' => 'PRJ-TEST-1234',
            'name' => 'Edit project test',
            'priority' => 'medium',
            'status' => 'planning',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('projects.update', $project), [
                'name' => 'Edit project test updated',
                'priority' => 'medium',
                'amc_start_date' => '2026-09-01',
                'amc_end_date' => '2027-08-31',
                'amc_amount' => '45000.00',
                'amc_frequency' => 'monthly',
                'amc_status' => 'expired',
                'amc_remarks' => 'My manual edit notes',
            ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();
        $this->assertEquals('completed_started_amc', $project->status);

        $this->assertDatabaseHas('project_amcs', [
            'project_id' => $project->id,
            'amount' => 45000.00,
            'start_date' => '2026-09-01 00:00:00',
            'end_date' => '2027-08-31 00:00:00',
            'frequency' => 'monthly',
            'status' => 'expired',
            'remarks' => 'My manual edit notes',
        ]);
    }

    public function test_projects_index_displays_amc_status()
    {
        // 1. Create a project with AMC (due in 30 days)
        $projectWithAmc = Project::create([
            'project_code' => 'PRJ-AMC-1111',
            'name' => 'Project With Active AMC',
            'priority' => 'medium',
            'status' => 'planning',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        \App\Models\ProjectAmc::create([
            'project_id' => $projectWithAmc->id,
            'amount' => 50000.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'frequency' => 'annually',
            'status' => 'active',
            'company_id' => $this->adminUser->company_id,
        ]);

        // 2. Create a project without AMC
        $projectWithoutAmc = Project::create([
            'project_code' => 'PRJ-AMC-2222',
            'name' => 'Project Without AMC',
            'priority' => 'low',
            'status' => 'planning',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee('Project With Active AMC');
        $response->assertSee('Project Without AMC');
        
        // Assert AMC countdown is shown
        $response->assertSee('30 days to go');
        // Assert AMC Not Started is shown
        $response->assertSee('AMC Not Started');
    }

    public function test_admin_can_access_edit_project_page()
    {
        $project = Project::create([
            'project_code' => 'PRJ-EDIT-TEST',
            'name' => 'Project to Edit',
            'priority' => 'medium',
            'status' => 'planning',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('projects.edit', $project));

        $response->assertStatus(200);
        $response->assertViewHas('project');
        $response->assertViewHas('amc');
    }

    public function test_projects_index_filtering_tabs()
    {
        // 1. Create a working project
        $workingProject = Project::create([
            'project_code' => 'PRJ-WORK-11',
            'name' => 'Active Working Project ABC',
            'priority' => 'medium',
            'status' => 'planning',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        // 2. Create a completed project
        $completedProject = Project::create([
            'project_code' => 'PRJ-COMP-22',
            'name' => 'Done Completed Project XYZ',
            'priority' => 'low',
            'status' => 'completed',
            'project_type' => 'web',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->adminUser->company_id,
        ]);

        // Access Working tab
        $responseWorking = $this->actingAs($this->adminUser)
            ->get(route('projects.index', ['tab' => 'working']));
        $responseWorking->assertStatus(200);
        $responseWorking->assertSee('Active Working Project ABC');
        $responseWorking->assertDontSee('Done Completed Project XYZ');

        // Access Completed tab
        $responseCompleted = $this->actingAs($this->adminUser)
            ->get(route('projects.index', ['tab' => 'completed']));
        $responseCompleted->assertStatus(200);
        $responseCompleted->assertSee('Done Completed Project XYZ');
        $responseCompleted->assertDontSee('Active Working Project ABC');
    }
}
