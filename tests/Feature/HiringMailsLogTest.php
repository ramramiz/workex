<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\InterviewScheduleMail;
use Tests\TestCase;

class HiringMailsLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Company $company;
    private Department $department;
    private JobVacancy $vacancy;
    private JobApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent real mail dispatch
        Mail::fake();

        // Seed roles
        $adminRole = Role::create([
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
            'role_id' => $adminRole->id,
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create department
        $this->department = Department::create([
            'name' => 'Engineering',
            'company_id' => $this->company->id,
            'status' => 'active'
        ]);

        // Create vacancy
        $this->vacancy = JobVacancy::create([
            'title' => 'Software Engineer',
            'department_id' => $this->department->id,
            'description' => 'Test description',
            'requirements' => 'Test requirements',
            'location' => 'Remote',
            'job_type' => 'Full-time',
            'status' => 'active',
            'company_id' => $this->company->id
        ]);

        // Create job application
        $this->application = JobApplication::create([
            'company_id' => $this->company->id,
            'job_vacancy_id' => $this->vacancy->id,
            'name' => 'John Candidate',
            'email' => 'candidate@example.com',
            'phone' => '1234567890',
            'resume_path' => 'resumes/mock_resume.pdf',
            'cover_letter' => 'Test cover letter',
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_schedule_interview_and_creates_mail_log(): void
    {
        $data = [
            'candidate_ids' => [$this->application->id],
            'interview_date' => now()->addDays(2)->format('Y-m-d'),
            'interview_time' => '14:00',
            'interview_venue' => 'Zoom Link Here',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('job-applications.schedule-interview'), $data);

        $response->assertRedirect();
        
        // Assert email was sent
        Mail::assertSent(InterviewScheduleMail::class, function ($mail) {
            return $mail->hasTo('candidate@example.com');
        });

        // Assert database has log
        $this->assertDatabaseHas('hiring_mail_logs', [
            'candidate_name' => 'John Candidate',
            'candidate_email' => 'candidate@example.com',
            'vacancy_title' => 'Software Engineer',
            'interview_venue' => 'Zoom Link Here',
            'sent_by' => $this->admin->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_admin_can_view_mail_logs(): void
    {
        // Log an entry manually first
        \App\Models\HiringMailLog::create([
            'company_id' => $this->company->id,
            'job_application_id' => $this->application->id,
            'candidate_name' => 'Jane Candidate',
            'candidate_email' => 'jane@example.com',
            'vacancy_title' => 'QA Lead',
            'subject' => 'Interview Invitation - QA Lead',
            'interview_date' => now()->addDays(3)->format('Y-m-d'),
            'interview_time' => '11:00',
            'interview_venue' => 'Office B',
            'sent_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('job-vacancies.mail-logs'));

        $response->assertStatus(200);
        $response->assertViewIs('job-vacancies.mail-logs');
        $response->assertSee('Jane Candidate');
        $response->assertSee('jane@example.com');
        $response->assertSee('QA Lead');
    }

    public function test_vacancy_has_salary_note_fields(): void
    {
        $vacancy = JobVacancy::create([
            'title' => 'Hardware Tester',
            'department_id' => $this->department->id,
            'description' => 'Test description',
            'requirements' => 'Test requirements',
            'location' => 'Remote',
            'job_type' => 'Full-time',
            'status' => 'active',
            'salary_note' => 'For Hardware Tester, we will provide a salary in the range of 15,000 to 20,000.',
            'company_id' => $this->company->id
        ]);

        $this->assertEquals('For Hardware Tester, we will provide a salary in the range of 15,000 to 20,000.', $vacancy->salary_note);

        $response = $this->get(route('careers.vacancy.show', $vacancy->token));
        $response->assertStatus(200);
        $response->assertSee('For Hardware Tester, we will provide a salary in the range of 15,000 to 20,000.');
    }
}
