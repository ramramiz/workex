<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Company;
use App\Models\Intern;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Company $company;
    private Department $department;
    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        // Create company
        $this->company = Company::create([
            'name' => 'Techsoul Inc',
            'email' => 'info@techsoul.com',
            'status' => 'active'
        ]);

        // Create user under company
        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create department
        $this->department = Department::create([
            'name' => 'Engineering',
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);
    }

    public function test_admin_can_view_interns_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('interns.index'));

        $response->assertStatus(200);
        $response->assertViewIs('interns.index');
    }

    public function test_admin_can_register_intern()
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'name' => 'John Intern',
            'email' => 'intern@example.com',
            'phone' => '1234567890',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'photo' => $photo,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('interns.store'), $data);

        $response->assertRedirect(route('interns.index'));
        $this->assertDatabaseHas('interns', [
            'name' => 'John Intern',
            'email' => 'intern@example.com',
            'company_id' => $this->company->id,
        ]);

        $intern = Intern::where('email', 'intern@example.com')->first();
        $this->assertNotNull($intern->certificate_code);
        $this->assertStringStartsWith('TSL-', $intern->certificate_code);
        $this->assertNotNull($intern->photo);
        Storage::disk('public')->assertExists($intern->photo);
    }

    public function test_admin_can_update_intern()
    {
        $intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'active',
        ]);

        $data = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '0987654321',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'completed',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('interns.update', $intern->id), $data);

        $response->assertRedirect(route('interns.index'));
        $this->assertDatabaseHas('interns', [
            'id' => $intern->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_delete_intern()
    {
        $intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('interns.destroy', $intern->id));

        $response->assertRedirect(route('interns.index'));
        $this->assertSoftDeleted('interns', [
            'id' => $intern->id,
        ]);
    }

    public function test_admin_can_generate_certificate()
    {
        $intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'John Certificate',
            'email' => 'cert@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'completed',
            'certificate_code' => 'CERT-INT-TEST-123',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('interns.certificate', $intern->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_public_user_can_verify_valid_certificate()
    {
        $intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'John Verified',
            'email' => 'verified@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'completed',
            'certificate_code' => 'TSL-014738-X7EH89',
        ]);

        $response = $this->get(route('interns.verify.public', Intern::encryptCode($intern->certificate_code)));

        $response->assertStatus(200);
        $response->assertViewIs('interns.verify');
        $response->assertSee('Genuineness Verified');
        $response->assertSee('John Verified');
        $response->assertSee('TSLB-INT-');
    }

    public function test_public_user_sees_failure_page_for_invalid_certificate()
    {
        $response = $this->get(route('interns.verify.public', 'TS/INT/2026/INVALID'));

        $response->assertStatus(200);
        $response->assertViewIs('interns.verify');
        $response->assertSee('Verification Failed');
        $response->assertSee('TS/INT/2026/INVALID');
    }

    public function test_admin_can_download_qr_code()
    {
        $intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'John QR',
            'email' => 'qr@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'completed',
            'certificate_code' => 'TS/INT/2026/8888',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('interns.qr-code', $intern->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'image/png');
        $response->assertHeader('content-disposition', 'attachment; filename="qr-john-qr.png"');
        $this->assertNotEmpty($response->getContent());
    }
}
