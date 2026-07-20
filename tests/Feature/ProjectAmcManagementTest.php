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

        $response->assertStatus(404);
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

    public function test_amc_renewals_and_expired_show_on_admin_dashboard(): void
    {
        // 1. Create an active AMC that expires in 10 days (should show)
        ProjectAmc::create([
            'project_id' => $this->project->id,
            'amount'     => 10000.00,
            'start_date' => now()->subDays(355),
            'end_date'   => now()->addDays(10),
            'frequency'  => 'annually',
            'status'     => 'active',
            'company_id' => $this->admin->company_id,
        ]);

        // 2. Create an active AMC that has already passed end_date (should show as expired)
        ProjectAmc::create([
            'project_id' => $this->project->id,
            'amount'     => 20000.00,
            'start_date' => now()->subDays(370),
            'end_date'   => now()->subDays(5),
            'frequency'  => 'annually',
            'status'     => 'active', // starts active, but dynamic expiry should change it to expired
            'company_id' => $this->admin->company_id,
        ]);

        // 3. Create an active AMC that expires in 30 days (should NOT show)
        ProjectAmc::create([
            'project_id' => $this->project->id,
            'amount'     => 30000.00,
            'start_date' => now()->subDays(335),
            'end_date'   => now()->addDays(30),
            'frequency'  => 'annually',
            'status'     => 'active',
            'company_id' => $this->admin->company_id,
        ]);

        // Access dashboard as admin
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Check if the expiring and expired AMCs are visible in the view data
        $upcomingAmcs = $response->viewData('upcomingAmcs');
        $this->assertNotNull($upcomingAmcs);
        
        $amounts = $upcomingAmcs->pluck('amount')->map(fn($val) => (float)$val)->toArray();
        // Should contain 10000 (expiring in 10 days) and 20000 (expired 5 days ago)
        $this->assertContains(10000.00, $amounts);
        $this->assertContains(20000.00, $amounts);
        
        // Should NOT contain 30000 (expires in 30 days)
        $this->assertNotContains(30000.00, $amounts);
        
        // Assert the database has updated the second AMC status to expired
        $this->assertDatabaseHas('project_amcs', [
            'amount' => 20000.00,
            'status' => 'expired'
        ]);
    }

    public function test_amc_of_soft_deleted_project_does_not_throw_exception_on_admin_dashboard(): void
    {
        // 1. Create a project
        $projectToSoftDelete = Project::create([
            'project_code'   => 'PRJ-DEL-999',
            'name'           => 'Project to Soft Delete',
            'client_id'      => $this->project->client_id,
            'team_leader_id' => $this->teamLeader->id,
            'start_date'     => '2026-01-01',
            'deadline'       => '2026-12-31',
            'project_value'  => 150000.00,
            'priority'       => 'high',
            'status'         => 'development',
            'created_by'     => $this->admin->id,
        ]);

        // 2. Create an expired AMC for it
        ProjectAmc::create([
            'project_id' => $projectToSoftDelete->id,
            'amount'     => 99000.00,
            'start_date' => now()->subDays(370),
            'end_date'   => now()->subDays(5),
            'frequency'  => 'annually',
            'status'     => 'active',
            'company_id' => $this->admin->company_id,
        ]);

        // 3. Soft delete the project
        $projectToSoftDelete->delete();

        // 4. Access dashboard as admin
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        // Should return 200 (not 500 error)
        $response->assertStatus(200);

        // Check that the AMC associated with the soft-deleted project is NOT shown on the dashboard
        $upcomingAmcs = $response->viewData('upcomingAmcs');
        $this->assertNotNull($upcomingAmcs);
        
        $amounts = $upcomingAmcs->pluck('amount')->map(fn($val) => (float)$val)->toArray();
        $this->assertNotContains(99000.00, $amounts);
    }

    public function test_admin_can_send_whatsapp_amc_reminder(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://bhashsms.com/api/sendmsgutil.php*' => \Illuminate\Support\Facades\Http::response('Success', 200)
        ]);

        $amc = ProjectAmc::create([
            'project_id'   => $this->project->id,
            'amount'       => 12000.00,
            'start_date'   => now(),
            'end_date'     => now()->addYear(),
            'frequency'    => 'annually',
            'status'       => 'active',
            'service_type' => 'Domain',
            'company_id'   => $this->admin->company_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('project-amcs.send-whatsapp-reminder', $amc));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'WhatsApp reminder sent successfully!');

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bhashsms.com/api/sendmsgutil.php') &&
                $request['user'] === 'Techsoul_BW' &&
                $request['phone'] === '1234567890' &&
                $request['text'] === 'pending_renewal' &&
                str_contains($request['Params'], 'Acme Company') &&
                str_contains($request['Params'], 'domain') &&
                str_contains($request['Params'], 'Acme Web Application');
        });
    }

    public function test_admin_can_send_whatsapp_amc_reminder_using_custom_alert_phone(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://bhashsms.com/api/sendmsgutil.php*' => \Illuminate\Support\Facades\Http::response('Success', 200)
        ]);

        $amc = ProjectAmc::create([
            'project_id'   => $this->project->id,
            'amount'       => 12000.00,
            'start_date'   => now(),
            'end_date'     => now()->addYear(),
            'frequency'    => 'annually',
            'status'       => 'active',
            'alert_phone'  => '9988776655',
            'alert_email'  => 'custom@example.com',
            'service_type' => 'Domain',
            'company_id'   => $this->admin->company_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('project-amcs.send-whatsapp-reminder', $amc));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'WhatsApp reminder sent successfully!');

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bhashsms.com/api/sendmsgutil.php') &&
                $request['user'] === 'Techsoul_BW' &&
                $request['phone'] === '9988776655' &&
                $request['text'] === 'pending_renewal' &&
                str_contains($request['Params'], 'Acme Company') &&
                str_contains($request['Params'], 'domain') &&
                str_contains($request['Params'], 'Acme Web Application');
        });
    }

    public function test_automated_reminder_command_sends_reminders_at_thresholds(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://bhashsms.com/api/sendmsgutil.php*' => \Illuminate\Support\Facades\Http::response('Success', 200)
        ]);

        // Create an AMC contract set to expire in exactly 30 days
        $amc = ProjectAmc::create([
            'project_id'   => $this->project->id,
            'amount'       => 15000.00,
            'start_date'   => now()->subDays(335),
            'end_date'     => now()->addDays(30), // exactly 30 days remaining
            'frequency'    => 'annually',
            'status'       => 'active',
            'service_type' => 'Server',
            'company_id'   => $this->admin->company_id,
        ]);

        // Run the artisan command
        $this->artisan('app:send-amc-reminders')
            ->assertSuccessful();

        // Verify reminder API was hit
        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bhashsms.com/api/sendmsgutil.php') &&
                $request['phone'] === '1234567890' &&
                str_contains($request['Params'], 'server') &&
                str_contains($request['Params'], '30'); // 30 days remaining
        });

        // Reset fake request history
        \Illuminate\Support\Facades\Http::fake([
            'https://bhashsms.com/api/sendmsgutil.php*' => \Illuminate\Support\Facades\Http::response('Success', 200)
        ]);

        // Run the command again to ensure it does not send duplicate reminder (not sended automatically send check)
        $this->artisan('app:send-amc-reminders')
            ->assertSuccessful();

        // Verify NO HTTP requests were sent this time
        \Illuminate\Support\Facades\Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'bhashsms.com/api/sendmsgutil.php');
        });
    }

    public function test_admin_sends_whatsapp_amc_deletion_notice_when_amc_renewal_date_completed(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://bhashsms.com/api/sendmsgutil.php*' => \Illuminate\Support\Facades\Http::response('Success', 200)
        ]);

        // Create an AMC contract that is already expired (end date in the past)
        $amc = ProjectAmc::create([
            'project_id'   => $this->project->id,
            'amount'       => 15000.00,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subDays(2), // expired 2 days ago
            'frequency'    => 'annually',
            'status'       => 'active',
            'service_type' => 'Domain',
            'company_id'   => $this->admin->company_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('project-amcs.send-whatsapp-reminder', $amc));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'WhatsApp reminder sent successfully!');

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bhashsms.com/api/sendmsgutil.php') &&
                $request['user'] === 'Techsoul_BW' &&
                $request['phone'] === '1234567890' &&
                $request['text'] === 'amcdeletion_notice' &&
                str_contains($request['Params'], 'Acme Company') &&
                str_contains($request['Params'], 'domain') &&
                str_contains($request['Params'], 'Acme Web Application');
        });
    }

    public function test_amc_show_page_displays_critical_warning_when_expired(): void
    {
        $amc = ProjectAmc::create([
            'project_id'   => $this->project->id,
            'amount'       => 15000.00,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subDays(2), // expired 2 days ago
            'frequency'    => 'annually',
            'status'       => 'active',
            'service_type' => 'Domain',
            'company_id'   => $this->admin->company_id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('project-amcs.show', $amc));

        $response->assertStatus(200);
        $response->assertSee('Critical Warning: AMC Renewal Completed!');
        $response->assertSee('Service deletion notice messages are currently active for this client.');
    }
}
