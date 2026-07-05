<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Company;
use App\Models\Intern;
use App\Models\Employee;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;
    private Company $company;
    private Department $department;
    private Role $adminRole;
    private Role $employeeRole;
    private Intern $intern;
    private Employee $employee;

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

        $this->employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'description' => 'Standard user',
            'color' => '#3b82f6'
        ]);

        // Create company
        $this->company = Company::create([
            'name' => 'Techsoul Inc',
            'email' => 'info@techsoul.com',
            'status' => 'active'
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->adminRole->id,
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create regular employee user
        $this->regularUser = User::create([
            'name' => 'Regular Employee',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'role_id' => $this->employeeRole->id,
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create department
        $this->department = Department::create([
            'name' => 'Engineering',
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create intern
        $this->intern = Intern::create([
            'company_id' => $this->company->id,
            'name' => 'John Intern',
            'email' => 'intern@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'active',
        ]);

        // Create employee
        $this->employee = Employee::create([
            'user_id' => $this->regularUser->id,
            'employee_code' => 'EMP-001',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'status' => 'active',
        ]);
    }

    public function test_admin_can_upload_document_for_intern()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('contract.pdf', 500); // 500 KB

        $data = [
            'document' => $file,
            'title' => 'Intern Agreement',
            'documentable_type' => 'intern',
            'documentable_id' => $this->intern->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('documents.store'), $data);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('documents', [
            'title' => 'Intern Agreement',
            'documentable_type' => Intern::class,
            'documentable_id' => $this->intern->id,
            'file_name' => 'contract.pdf',
        ]);

        $document = Document::first();
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_admin_can_upload_document_for_employee()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('resume.docx', 800); // 800 KB

        $data = [
            'document' => $file,
            'title' => 'Employee Resume',
            'documentable_type' => 'employee',
            'documentable_id' => $this->employee->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('documents.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'title' => 'Employee Resume',
            'documentable_type' => Employee::class,
            'documentable_id' => $this->employee->id,
            'file_name' => 'resume.docx',
        ]);

        $document = Document::first();
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_admin_can_download_document()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('contract.pdf', 500);
        $path = Storage::disk('public')->putFile('documents', $file);

        $document = Document::create([
            'uploaded_by' => $this->admin->id,
            'documentable_type' => Intern::class,
            'documentable_id' => $this->intern->id,
            'file_name' => 'contract.pdf',
            'file_path' => $path,
            'file_size' => 500 * 1024,
            'title' => 'Agreement',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('documents.download', $document));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=contract.pdf');
    }

    public function test_admin_can_view_document()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('contract.pdf', 500);
        $path = Storage::disk('public')->putFile('documents', $file);

        $document = Document::create([
            'uploaded_by' => $this->admin->id,
            'documentable_type' => Intern::class,
            'documentable_id' => $this->intern->id,
            'file_name' => 'contract.pdf',
            'file_path' => $path,
            'file_size' => 500 * 1024,
            'title' => 'Agreement',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('documents.view', $document));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'inline; filename="contract.pdf"');
    }

    public function test_admin_can_delete_document()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('contract.pdf', 500);
        $path = Storage::disk('public')->putFile('documents', $file);

        $document = Document::create([
            'uploaded_by' => $this->admin->id,
            'documentable_type' => Intern::class,
            'documentable_id' => $this->intern->id,
            'file_name' => 'contract.pdf',
            'file_path' => $path,
            'file_size' => 500 * 1024,
            'title' => 'Agreement',
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->admin)
            ->delete(route('documents.destroy', $document));

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_regular_user_cannot_upload_or_delete_document()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('hacker.pdf', 100);

        // Try to upload
        $data = [
            'document' => $file,
            'title' => 'Malicious File',
            'documentable_type' => 'intern',
            'documentable_id' => $this->intern->id,
        ];

        $response = $this->actingAs($this->regularUser)
            ->post(route('documents.store'), $data);

        $response->assertStatus(403); // Forbidden by role middleware
        $this->assertDatabaseEmpty('documents');

        // Create document for delete test
        $path = Storage::disk('public')->putFile('documents', $file);
        $document = Document::create([
            'uploaded_by' => $this->admin->id,
            'documentable_type' => Intern::class,
            'documentable_id' => $this->intern->id,
            'file_name' => 'hacker.pdf',
            'file_path' => $path,
            'file_size' => 100 * 1024,
            'title' => 'Agreement',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->delete(route('documents.destroy', $document));

        $response->assertStatus(403);
        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    public function test_admin_can_link_google_drive_folder_for_employee()
    {
        $data = [
            'name' => 'Updated Regular Employee',
            'email' => 'employee@example.com',
            'department_id' => $this->department->id,
            'joining_date' => now()->format('Y-m-d'),
            'google_drive_link' => 'https://drive.google.com/drive/folders/1abc123xyz',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('employees.update', $this->employee), $data);

        $response->assertRedirect();
        
        $this->employee->refresh();
        $this->assertEquals('https://drive.google.com/drive/folders/1abc123xyz', $this->employee->google_drive_link);
    }
}
