<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JobVacancy;
use App\Models\JobApplication;
use App\Models\Department;
use App\Models\Company;

try {
    // 1. Get default company
    $company = Company::first();
    if (!$company) {
        throw new Exception("No company found. Please run seeders first.");
    }
    echo "Found Company: " . $company->name . " (ID: " . $company->id . ")\n";

    // Mock authentication to bypass BelongsToCompany global scope when testing
    $adminUser = \App\Models\User::where('role_id', 1)->first(); // super-admin
    if ($adminUser) {
        auth()->login($adminUser);
        echo "Logged in test user: " . $adminUser->name . "\n";
    }

    // 2. Create test department if none exists
    $department = Department::first();
    if (!$department) {
        $department = Department::create([
            'name' => 'Testing Dept',
            'description' => 'For testing purposes',
            'status' => 'active',
            'company_id' => $company->id
        ]);
        echo "Created test department: " . $department->name . "\n";
    } else {
        echo "Using Department: " . $department->name . "\n";
    }

    // 3. Register vacancy
    $vacancy = JobVacancy::create([
        'title' => 'Software Engineer (Test)',
        'department_id' => $department->id,
        'description' => 'Test Job Description details.',
        'requirements' => 'Test Job Requirements details.',
        'location' => 'Remote',
        'job_type' => 'Full-time',
        'status' => 'active',
        'company_id' => $company->id
    ]);

    echo "Registered Vacancy:\n";
    echo " - Title: " . $vacancy->title . "\n";
    echo " - Token: " . $vacancy->token . "\n";
    echo " - Company ID: " . $vacancy->company_id . "\n";

    if (empty($vacancy->token)) {
        throw new Exception("Failed: Vacancy token was not auto-generated!");
    }
    echo "Success: Token generated successfully!\n";

    // 4. Register Candidate Application
    $application = JobApplication::create([
        'company_id' => $company->id,
        'job_vacancy_id' => $vacancy->id,
        'name' => 'John Candidate',
        'email' => 'john.candidate@example.com',
        'phone' => '1234567890',
        'resume_path' => 'resumes/mock_resume.pdf',
        'cover_letter' => 'I would love to join your team.',
        'status' => 'pending'
    ]);

    echo "Registered Candidate Application:\n";
    echo " - Candidate Name: " . $application->name . "\n";
    echo " - Vacancy ID: " . $application->job_vacancy_id . "\n";
    echo " - Status: " . $application->status . "\n";

    // 5. Clean up test records
    $application->delete();
    $vacancy->delete();
    echo "Cleaned up test records successfully.\n";

    echo "\n=== ALL programmatic checks passed successfully! ===\n";

} catch (Exception $e) {
    echo "Verification Error: " . $e->getMessage() . "\n";
    exit(1);
}
