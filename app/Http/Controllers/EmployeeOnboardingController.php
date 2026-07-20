<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeOnboarding;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Mail\EmployeeOnboardingLinkMail;
use App\Mail\WelcomeEmployeeMail;

class EmployeeOnboardingController extends Controller
{
    /**
     * Display a listing of onboarding entries for HR.
     */
    public function index(Request $request)
    {
        $onboardings = EmployeeOnboarding::with(['department', 'designation', 'teamLeader', 'role'])
            ->when($request->filled('search'), function($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(15);

        $departments = Department::where('status', 'active')->with('designations')->get();
        $roles = Role::all();
        $teamLeaders = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();

        return view('employees.onboardings.index', compact('onboardings', 'departments', 'roles', 'teamLeaders'));
    }

    /**
     * Generate an onboarding link.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'team_leader_id' => 'nullable|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'sector' => 'required|string',
        ]);

        // Generate onboarding record
        $onboarding = EmployeeOnboarding::create([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'team_leader_id' => $request->team_leader_id,
            'role_id' => $request->role_id,
            'salary' => $request->salary,
            'joining_date' => $request->joining_date,
            'sector' => $request->sector,
            'token' => Str::random(32),
            'status' => 'pending',
        ]);

        \App\Models\ActivityLog::log('employee_onboarding_generated', "Generated onboarding link for: {$request->name}");

        return redirect()->route('employees.onboardings.index')->with('success', 'Onboarding link generated successfully!');
    }

    /**
     * Email the onboarding link to the employee.
     */
    public function sendEmail(EmployeeOnboarding $onboarding)
    {
        try {
            Mail::to($onboarding->email)->send(new EmployeeOnboardingLinkMail($onboarding));
            
            \App\Models\ActivityLog::log('employee_onboarding_email_sent', "Emailed onboarding link to: {$onboarding->email}");
            
            return back()->with('success', 'Onboarding link emailed successfully!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Employee onboarding email fail: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Check SMTP settings. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show the public onboarding form.
     */
    public function showForm($token)
    {
        $onboarding = EmployeeOnboarding::where('token', $token)->with(['department', 'designation', 'teamLeader'])->firstOrFail();

        if (in_array($onboarding->status, ['submitted', 'completed'])) {
            $msg = $onboarding->status === 'completed'
                ? 'Your onboarding is complete, and your profile is activated! Thank you.'
                : 'Your onboarding form and uploaded documents have been received successfully.';
            
            return view('employees.onboardings.success', [
                'onboarding' => $onboarding,
                'message' => $msg
            ]);
        }

        return view('employees.onboardings.form', compact('onboarding'));
    }

    /**
     * Submit the public onboarding form.
     */
    public function submitForm(Request $request, $token)
    {
        $onboarding = EmployeeOnboarding::where('token', $token)->firstOrFail();

        // Validation for form fields
        $request->validate([
            // Section 1: Personal Info
            'gender' => 'required|string|in:Male,Female,Other',
            'dob' => 'required|date',
            'blood_group' => 'nullable|string|max:10',
            'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
            'nationality' => 'required|string|max:100',
            'aadhaar_number' => 'required|string|max:20',
            'pan_number' => 'required|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'driving_license_number' => 'nullable|string|max:50',

            // Section 2: Contact Details
            'phone' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'personal_email' => 'required|email|max:255',
            'current_address' => 'required|string',
            'current_pin_code' => 'required|string|max:10',
            'permanent_address' => 'nullable|string',
            'permanent_pin_code' => 'nullable|string|max:10',
            'same_as_current' => 'nullable|boolean',

            // Section 3: Emergency Contact Details
            'emergency_contact_person' => 'required|string|max:255',
            'emergency_relationship' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'emergency_alternate_phone' => 'nullable|string|max:20',
            'emergency_address' => 'nullable|string',

            // Section 5: Educational Qualifications
            'education_qualifications' => 'required|array',

            // Section 6: Professional Details
            'total_experience' => 'required|string|max:50',
            'prev_employer' => 'nullable|string|max:255',
            'prev_designation' => 'nullable|string|max:255',
            'prev_duration' => 'nullable|string|max:100',
            'prev_reason_for_leaving' => 'nullable|string',
            'skills' => 'required|array',
            'skills_other' => 'nullable|string|max:255',

            // Section 7: Bank Details
            'bank_account_holder' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:100',
            'bank_ifsc' => 'required|string|max:20',
            'bank_upi' => 'nullable|string|max:100',

            // Section 8: PF / ESI Details
            'uan_number' => 'nullable|string|max:50',
            'pf_number' => 'nullable|string|max:50',
            'esi_number' => 'nullable|string|max:50',

            // Section 12: Medical Information
            'medical_condition' => 'nullable|string',
            'medical_allergies' => 'nullable|string',
            'medical_medication' => 'nullable|string',

            // Section 13: Declaration
            'declaration_accepted' => 'required|accepted',
            'code_of_conduct_accepted' => 'required|accepted',

            // Document Checklist Files
            'photo' => 'required|image|max:2048',
            'doc_aadhaar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_pan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_resume' => 'required|file|mimes:pdf,docx,doc|max:5120',
            'doc_education' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_experience' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_relieving' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_salary' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_bank_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_passport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_driving' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Upload files and create Documents
        $documentTypes = [
            'photo' => 'Passport Size Photo',
            'doc_aadhaar' => 'Aadhaar Card',
            'doc_pan' => 'PAN Card',
            'doc_resume' => 'Resume / CV',
            'doc_education' => 'Educational Certificates',
            'doc_experience' => 'Experience Certificates',
            'doc_relieving' => 'Relieving Letter',
            'doc_salary' => 'Salary Slip (Last 3 Months)',
            'doc_bank_proof' => 'Bank Passbook / Cancelled Cheque',
            'doc_passport' => 'Passport Copy',
            'doc_driving' => 'Driving License',
        ];

        foreach ($documentTypes as $inputName => $title) {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                
                // Delete old document of the same title if it exists to avoid duplicates
                $oldDoc = Document::where('documentable_type', EmployeeOnboarding::class)
                    ->where('documentable_id', $onboarding->id)
                    ->where('title', $title)
                    ->first();
                if ($oldDoc) {
                    if (Storage::disk('public')->exists($oldDoc->file_path)) {
                        Storage::disk('public')->delete($oldDoc->file_path);
                    }
                    $oldDoc->delete();
                }

                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $path = $file->store('documents', 'public');

                Document::create([
                    'uploaded_by' => null, // Public upload
                    'documentable_type' => EmployeeOnboarding::class,
                    'documentable_id' => $onboarding->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'title' => $title,
                ]);
            }
        }

        // Save form fields
        $fields = $request->except([
            'photo', 'doc_aadhaar', 'doc_pan', 'doc_resume', 'doc_education', 
            'doc_experience', 'doc_relieving', 'doc_salary', 'doc_bank_proof', 
            'doc_passport', 'doc_driving'
        ]);
        $fields['status'] = 'submitted';
        $fields['same_as_current'] = $request->has('same_as_current');

        $onboarding->update($fields);

        \App\Models\ActivityLog::log('employee_onboarding_submitted', "Onboarding form submitted by employee: {$onboarding->name}");

        return redirect()->route('employees.onboard.success', $token);
    }

    /**
     * Show success page after form submission.
     */
    public function successPage($token)
    {
        $onboarding = EmployeeOnboarding::where('token', $token)->firstOrFail();
        return view('employees.onboardings.success', compact('onboarding'));
    }

    /**
     * Review the submitted form (HR / Admin).
     */
    public function review(EmployeeOnboarding $onboarding)
    {
        $onboarding->load(['department', 'designation', 'teamLeader', 'role']);
        $uploadedDocs = Document::where('documentable_type', EmployeeOnboarding::class)
            ->where('documentable_id', $onboarding->id)
            ->get();
        return view('employees.onboardings.review', compact('onboarding', 'uploadedDocs'));
    }

    /**
     * Approve the onboarding submission.
     */
    public function approve(Request $request, EmployeeOnboarding $onboarding)
    {
        $request->validate([
            'company_access_requirements' => 'nullable|array',
            'company_access_other' => 'nullable|string|max:255',
            'assets_issued' => 'nullable|array',
            'assets_remarks' => 'nullable|string',
            'official_email' => 'required|email|unique:users,email',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'salary' => 'required|numeric|min:0',
            'employment_type' => 'required|string|in:Permanent,Probation,Contract,Internship',
            'approved_by' => 'nullable|string|max:255',
            'hr_signature' => 'nullable|string|max:255',
            'management_signature' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. Generate a secure random password
            $plainPassword = Str::random(12);

            // 2. Create User Account
            $userData = [
                'name' => $onboarding->name,
                'email' => $request->official_email,
                'password' => Hash::make($plainPassword),
                'role_id' => $onboarding->role_id,
                'status' => 'active',
                'email_verified_at' => now(),
            ];

            // Set avatar if candidate uploaded photo
            $photoDoc = Document::where('documentable_type', EmployeeOnboarding::class)
                ->where('documentable_id', $onboarding->id)
                ->where('title', 'Passport Size Photo')
                ->first();
            
            if ($photoDoc && Storage::disk('public')->exists($photoDoc->file_path)) {
                // Copy photo to avatars folder
                $avatarPath = 'avatars/' . basename($photoDoc->file_path);
                Storage::disk('public')->copy($photoDoc->file_path, $avatarPath);
                $userData['avatar'] = $avatarPath;
            }

            $user = User::create($userData);

            // 3. Create or Update Employee Profile (handles model boot event auto-creation)
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $request->employee_code,
                    'department_id' => $onboarding->department_id,
                    'designation_id' => $onboarding->designation_id,
                    'team_leader_id' => $onboarding->team_leader_id,
                    'phone' => $onboarding->phone,
                    'personal_email' => $onboarding->personal_email,
                    'joining_date' => $onboarding->joining_date,
                    'salary' => $request->salary,
                    'is_applicable_for_salary' => true,
                    'salary_type' => 'monthly',
                    'work_type' => in_array('VPN Access', $request->company_access_requirements ?? []) ? 'remote' : 'office',
                    'address' => $onboarding->current_address,
                    'blood_group' => substr($onboarding->blood_group, 0, 5),
                    'status' => 'active',
                ]
            );

            // 4. Update and Relink Uploaded Documents to the Employee model
            Document::where('documentable_type', EmployeeOnboarding::class)
                ->where('documentable_id', $onboarding->id)
                ->update([
                    'documentable_type' => Employee::class,
                    'documentable_id' => $employee->id,
                ]);

            // 5. Update Onboarding status to completed
            $onboarding->update([
                'status' => 'completed',
                'employee_id' => $employee->id,
                'company_access_requirements' => $request->company_access_requirements,
                'company_access_other' => $request->company_access_other,
                'assets_issued' => $request->assets_issued,
                'assets_remarks' => $request->assets_remarks,
                'approved_by' => $request->approved_by,
                'hr_signature' => $request->hr_signature,
                'management_signature' => $request->management_signature,
                'employment_type' => $request->employment_type,
                'official_email' => $request->official_email,
                'employee_code' => $request->employee_code,
                'salary' => $request->salary,
            ]);

            // 6. Send welcome email with credentials
            try {
                Mail::to($user->email)->send(new WelcomeEmployeeMail($user, $plainPassword));
                if ($onboarding->personal_email) {
                    Mail::to($onboarding->personal_email)->send(new WelcomeEmployeeMail($user, $plainPassword));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to send welcome credentials to employee: " . $e->getMessage());
            }

            DB::commit();

            \App\Models\ActivityLog::log('employee_onboarding_approved', "Approved and activated employee profile: {$user->name}");

            return redirect()->route('employees.onboardings.index')->with('success', 'Employee onboarding approved and profile activated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Employee approval error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Failed to approve onboarding. Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject the onboarding submission (request revisions).
     */
    public function reject(Request $request, EmployeeOnboarding $onboarding)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $onboarding->update([
            'status' => 'pending',
            'assets_remarks' => 'Revision Requested: ' . $request->remarks, // Temporarily store feedback here
        ]);

        \App\Models\ActivityLog::log('employee_onboarding_rejected', "Requested revisions for onboarding: {$onboarding->name}");

        return redirect()->route('employees.onboardings.index')->with('success', 'Revision request sent to the candidate!');
    }

    /**
     * View completed/printable onboarding form.
     */
    public function viewCompletedForm(EmployeeOnboarding $onboarding)
    {
        $onboarding->load(['department', 'designation', 'teamLeader', 'role']);
        
        $uploadedDocs = Document::where('documentable_type', Employee::class)
            ->where('documentable_id', $onboarding->employee_id)
            ->get();
            
        if ($uploadedDocs->isEmpty()) {
            $uploadedDocs = Document::where('documentable_type', EmployeeOnboarding::class)
                ->where('documentable_id', $onboarding->id)
                ->get();
        }

        return view('employees.onboardings.view', compact('onboarding', 'uploadedDocs'));
    }

    /**
     * Show the edit form for the onboarding details.
     */
    public function edit(EmployeeOnboarding $onboarding)
    {
        $onboarding->load(['department', 'designation', 'teamLeader', 'role']);
        $departments = Department::where('status', 'active')->get();
        $roles = Role::all();
        $teamLeaders = User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        return view('employees.onboardings.edit', compact('onboarding', 'departments', 'roles', 'teamLeaders'));
    }

    /**
     * Update the onboarding details.
     */
    public function update(Request $request, EmployeeOnboarding $onboarding)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'role_id' => 'required|exists:roles,id',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'sector' => 'required|string',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'marital_status' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'current_address' => 'nullable|string',
            'current_pin_code' => 'nullable|string|max:10',
        ]);

        $data = $request->all();
        $data['same_as_current'] = $request->has('same_as_current');

        $onboarding->update($data);

        \App\Models\ActivityLog::log('employee_onboarding_updated', "Updated onboarding details for: {$onboarding->name}");

        return redirect()->route('employees.onboardings.index')->with('success', 'Onboarding details updated successfully!');
    }

    /**
     * Delete onboarding and files.
     */
    public function destroy(EmployeeOnboarding $onboarding)
    {
        // Clean up documents on disk
        $documents = Document::where('documentable_type', EmployeeOnboarding::class)
            ->where('documentable_id', $onboarding->id)
            ->get();

        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        $name = $onboarding->name;
        $onboarding->delete();

        \App\Models\ActivityLog::log('employee_onboarding_deleted', "Deleted onboarding record for: {$name}");

        return redirect()->route('employees.onboardings.index')->with('success', 'Onboarding entry deleted successfully!');
    }
}
