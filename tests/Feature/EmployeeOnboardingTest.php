<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeOnboarding;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $employeeRole;
    private Department $department;
    private Designation $designation;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create standard roles
        Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $this->employeeRole = Role::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee']);
        Role::firstOrCreate(['slug' => 'team-leader'], ['name' => 'Team Leader']);

        // Create admin user
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        // Create department & designation
        $this->department = Department::create([
            'name' => 'Engineering',
            'status' => 'active',
        ]);

        $this->designation = Designation::create([
            'department_id' => $this->department->id,
            'name' => 'Software Engineer',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_onboardings_index()
    {
        $response = $this->actingAs($this->admin)->get(route('employees.onboardings.index'));
        $response->assertStatus(200);
        $response->assertViewIs('employees.onboardings.index');
    }

    public function test_admin_can_generate_onboarding_link()
    {
        $data = [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'role_id' => $this->employeeRole->id,
            'joining_date' => now()->addDays(7)->toDateString(),
            'salary' => 45000,
            'sector' => 'Techsoul Technologies',
        ];

        $response = $this->actingAs($this->admin)->post(route('employees.onboardings.generate'), $data);

        $response->assertRedirect(route('employees.onboardings.index'));
        $this->assertDatabaseHas('employee_onboardings', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_public_user_can_view_onboarding_form()
    {
        $onboarding = EmployeeOnboarding::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'role_id' => $this->employeeRole->id,
            'salary' => 45000,
            'joining_date' => now()->toDateString(),
            'sector' => 'Techsoul Technologies',
            'token' => 'test-token-123',
            'status' => 'pending',
        ]);

        $response = $this->get(route('employees.onboard.show', $onboarding->token));

        $response->assertStatus(200);
        $response->assertViewIs('employees.onboardings.form');
    }

    public function test_public_user_can_submit_onboarding_form()
    {
        $onboarding = EmployeeOnboarding::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'role_id' => $this->employeeRole->id,
            'salary' => 45000,
            'joining_date' => now()->toDateString(),
            'sector' => 'Techsoul Technologies',
            'token' => 'test-token-123',
            'status' => 'pending',
        ]);

        $data = [
            'gender' => 'Female',
            'dob' => '1998-05-15',
            'blood_group' => 'A+',
            'marital_status' => 'Single',
            'nationality' => 'Indian',
            'phone' => '9876543210',
            'alternate_mobile' => '9876543211',
            'personal_email' => 'alice@example.com',
            'current_address' => '123 Main St, Bangalore',
            'current_pin_code' => '560001',
            'same_as_current' => 1,
            'aadhaar_number' => '123456789012',
            'pan_number' => 'ABCDE1234F',
            'emergency_contact_person' => 'Bob Johnson',
            'emergency_relationship' => 'Father',
            'emergency_phone' => '9876543222',
            'education_qualifications' => [
                ['qualification' => 'SSLC', 'institution' => 'High School', 'board_university' => 'State Board', 'year_passed' => '2016', 'percentage' => '90%'],
                ['qualification' => 'Plus Two', 'institution' => 'Junior College', 'board_university' => 'State Board', 'year_passed' => '2018', 'percentage' => '85%'],
                ['qualification' => 'Degree', 'institution' => 'RV College', 'board_university' => 'VTU', 'year_passed' => '2020', 'percentage' => '8.5 CGPA']
            ],
            'total_experience' => '2 Years',
            'prev_employer' => 'Old Tech Corp',
            'prev_designation' => 'Junior Dev',
            'prev_duration' => '2020-2022',
            'prev_reason_for_leaving' => 'Better prospects',
            'skills' => ['PHP', 'Laravel', 'JavaScript'],
            'bank_account_holder' => 'Alice Johnson',
            'bank_name' => 'SBI',
            'bank_branch' => 'MGRoad',
            'bank_account_number' => '100020003000',
            'bank_ifsc' => 'SBIN0001234',
            'declaration_accepted' => 1,
            'code_of_conduct_accepted' => 1,

            // Files
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'doc_aadhaar' => UploadedFile::fake()->create('aadhaar.pdf', 100),
            'doc_pan' => UploadedFile::fake()->create('pan.pdf', 100),
            'doc_resume' => UploadedFile::fake()->create('cv.pdf', 120),
            'doc_education' => UploadedFile::fake()->create('certificates.pdf', 200),
            'doc_bank_proof' => UploadedFile::fake()->create('passbook.pdf', 100),
        ];

        $response = $this->post(route('employees.onboard.submit', $onboarding->token), $data);

        $response->assertRedirect(route('employees.onboard.success', $onboarding->token));
        
        $onboarding->refresh();
        $this->assertEquals('submitted', $onboarding->status);
        
        // Assert documents are saved in DB pointing to EmployeeOnboarding
        $this->assertDatabaseHas('documents', [
            'documentable_type' => EmployeeOnboarding::class,
            'documentable_id' => $onboarding->id,
            'title' => 'Aadhaar Card',
        ]);
    }

    public function test_admin_can_request_revision()
    {
        $onboarding = EmployeeOnboarding::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'role_id' => $this->employeeRole->id,
            'salary' => 45000,
            'joining_date' => now()->toDateString(),
            'sector' => 'Techsoul Technologies',
            'token' => 'test-token-123',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)->post(route('employees.onboardings.reject', $onboarding->id), [
            'remarks' => 'Please upload a clearer copy of your PAN Card.',
        ]);

        $response->assertRedirect(route('employees.onboardings.index'));
        
        $onboarding->refresh();
        $this->assertEquals('pending', $onboarding->status);
        $this->assertStringContainsString('PAN Card', $onboarding->assets_remarks);
    }

    public function test_admin_can_approve_and_activate_employee()
    {
        $onboarding = EmployeeOnboarding::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'role_id' => $this->employeeRole->id,
            'salary' => 45000,
            'joining_date' => now()->toDateString(),
            'sector' => 'Techsoul Technologies',
            'token' => 'test-token-123',
            'status' => 'submitted',
            
            // Basic personal answers
            'phone' => '9876543210',
            'personal_email' => 'alice.personal@example.com',
            'current_address' => 'Bangalore',
            'blood_group' => 'A+',
        ]);

        // Upload a dummy document
        Document::create([
            'documentable_type' => EmployeeOnboarding::class,
            'documentable_id' => $onboarding->id,
            'file_name' => 'aadhaar.pdf',
            'file_path' => 'documents/aadhaar.pdf',
            'file_size' => 1024,
            'title' => 'Aadhaar Card',
        ]);

        $approvalData = [
            'company_access_requirements' => ['Official Email ID', 'ERP Login'],
            'assets_issued' => ['Laptop', 'Charger'],
            'official_email' => 'alice.official@techsoulcybersolutions.com',
            'employee_code' => 'EMP-0999',
            'salary' => 45000,
            'employment_type' => 'Permanent',
            'approved_by' => 'HR Manager',
        ];

        $response = $this->actingAs($this->admin)->post(route('employees.onboardings.approve', $onboarding->id), $approvalData);

        $response->assertRedirect(route('employees.onboardings.index'));
        
        // Assert user account was created
        $this->assertDatabaseHas('users', [
            'name' => 'Alice Johnson',
            'email' => 'alice.official@techsoulcybersolutions.com',
            'role_id' => $this->employeeRole->id,
            'status' => 'active',
        ]);

        $user = User::where('email', 'alice.official@techsoulcybersolutions.com')->first();
        
        // Assert employee profile was created
        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'employee_code' => 'EMP-0999',
            'salary' => 45000,
            'status' => 'active',
        ]);

        $employee = Employee::where('user_id', $user->id)->first();

        // Assert documents were transferred to Employee model
        $this->assertDatabaseHas('documents', [
            'documentable_type' => Employee::class,
            'documentable_id' => $employee->id,
            'title' => 'Aadhaar Card',
        ]);

        // Assert onboarding is complete
        $onboarding->refresh();
        $this->assertEquals('completed', $onboarding->status);
        $this->assertEquals($employee->id, $onboarding->employee_id);
    }
}
