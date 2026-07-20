<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\InternOnboarding;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\OnboardingLinkMail;

class InternOnboardingController extends Controller
{
    /**
     * Display a listing of onboarding entries for HR.
     */
    public function index(Request $request)
    {
        $onboardings = InternOnboarding::with(['intern.department', 'intern.designation'])
            ->whereHas('intern', function ($query) use ($request) {
                $query->whereNull('certificate_code');

                if ($request->filled('search')) {
                    $query->where(function($q) use ($request) {
                        $q->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%");
                    });
                }
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        $departments = Department::where('status', 'active')->with('designations')->get();
        $teamLeaders = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();

        return view('interns.onboardings.index', compact('onboardings', 'departments', 'teamLeaders'));
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
            'sector' => 'required|string|in:Techsoul Technologies,Techsoul IT Solutions,Techsoul Solar',
            'joining_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:joining_date',
            'mentor_supervisor' => 'nullable|string|max:255',
        ]);

        // Create the intern record with pending_onboarding status
        $intern = Intern::create([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'sector' => $request->sector,
            'joining_date' => $request->joining_date,
            'end_date' => $request->end_date,
            'status' => 'pending_onboarding',
        ]);

        // Generate onboarding record
        $onboarding = InternOnboarding::create([
            'intern_id' => $intern->id,
            'token' => Str::random(32),
            'status' => 'pending',
            'sector' => $request->sector,
            'mentor_supervisor' => $request->mentor_supervisor,
        ]);

        \App\Models\ActivityLog::log('intern_onboarding_generated', "Generated onboarding link for: {$request->name}");

        return redirect()->route('interns.onboardings.index')->with('success', 'Onboarding link generated successfully!');
    }

    /**
     * Email the onboarding link to the intern.
     */
    public function sendEmail(InternOnboarding $onboarding)
    {
        $onboarding->load('intern');
        
        try {
            Mail::to($onboarding->intern->email)->send(new OnboardingLinkMail($onboarding));
            
            \App\Models\ActivityLog::log('intern_onboarding_email_sent', "Emailed onboarding link to: {$onboarding->intern->email}");
            
            return back()->with('success', 'Onboarding link emailed successfully!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Onboarding email fail: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Check SMTP settings. Error: ' . $e->getMessage());
        }
    }

    /**
     * Show the public onboarding form.
     */
    public function showForm($token)
    {
        $onboarding = InternOnboarding::where('token', $token)->with(['intern.department', 'intern.designation'])->firstOrFail();

        if ($onboarding->status === 'completed') {
            return view('interns.onboardings.success', [
                'onboarding' => $onboarding,
                'message' => 'Your onboarding is complete, and your profile is activated! Thank you.'
            ]);
        }

        return view('interns.onboardings.form', compact('onboarding'));
    }

    /**
     * Submit the public onboarding form.
     */
    public function submitForm(Request $request, $token)
    {
        $onboarding = InternOnboarding::where('token', $token)->with('intern')->firstOrFail();

        // Validation for the form fields
        $request->validate([
            // Section 1
            'preferred_name' => 'nullable|string|max:255',
            'gender' => 'required|string|in:Male,Female,Other',
            'dob' => 'required|date',
            'blood_group' => 'nullable|string|max:10',
            'aadhaar_number' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'current_address' => 'required|string',
            'pin_code' => 'required|string|max:10',

            // Section 2
            'college_name' => 'required|string|max:255',
            'university_board' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'branch_specialization' => 'required|string|max:255',
            'current_semester_year' => 'nullable|string|max:100',
            'college_roll_number' => 'nullable|string|max:100',
            'expected_completion_year' => 'nullable|string|max:10',

            // Section 3
            'parent_name' => 'required|string|max:255',
            'parent_relationship' => 'required|string|max:100',
            'parent_phone' => 'required|string|max:20',
            'parent_occupation' => 'nullable|string|max:255',
            'parent_address' => 'nullable|string',

            // Section 4
            'emergency_contact_person' => 'required|string|max:255',
            'emergency_relationship' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'emergency_alternate_phone' => 'nullable|string|max:20',

            // Section 5
            'internship_type' => 'required|string',
            'internship_type_other' => 'nullable|required_if:internship_type,Other|string|max:255',
            'internship_mode' => 'required|string|in:On-site,Hybrid,Remote',
            'internship_duration' => 'required|string',
            'internship_duration_other' => 'nullable|required_if:internship_duration,Other|string|max:255',
            'areas_of_interest' => 'required|array',
            'areas_of_interest_other' => 'nullable|string|max:255',

            // Section 6
            'programming_languages' => 'nullable|array',
            'programming_languages_other' => 'nullable|string|max:255',
            'design_tools' => 'nullable|array',
            'design_tools_other' => 'nullable|string|max:255',
            'completed_projects' => 'nullable|string',

            // Section 10
            'learning_expectations' => 'required|string',
            'career_goal' => 'required|string',

            // Section 11
            'declaration_accepted' => 'required|accepted',
            'signature_name' => 'nullable|string|max:255',
            'signature_date' => 'nullable|date',

            // Photo and Document Checklist
            'photo' => 'required|image|max:2048',
            'doc_aadhaar' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_resume' => 'required|file|mimes:pdf,docx,doc|max:5120',
            'doc_college_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_bonafide' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_request_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_project_details' => 'nullable|file|mimes:pdf,docx,doc,txt|max:5120',
            'doc_prev_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Upload Photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('intern_photos', 'public');
            $onboarding->intern->update([
                'photo' => $photoPath,
                'phone' => $request->phone,
            ]);
        }

        // Upload other documents
        $documentTypes = [
            'doc_aadhaar' => 'Aadhaar Card',
            'doc_resume' => 'Resume / CV',
            'doc_college_id' => 'College ID Card',
            'doc_bonafide' => 'Bonafide Certificate',
            'doc_request_letter' => 'Internship Request Letter',
            'doc_project_details' => 'Academic Project Details',
            'doc_prev_certificate' => 'Previous Internship Certificates',
        ];

        foreach ($documentTypes as $inputName => $title) {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $path = $file->store('documents', 'public');

                Document::create([
                    'uploaded_by' => null, // Public upload
                    'documentable_type' => Intern::class,
                    'documentable_id' => $onboarding->intern_id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'title' => $title,
                ]);
            }
        }

        // Save form fields to InternOnboarding
        $data = $request->except(['photo', 'doc_aadhaar', 'doc_resume', 'doc_college_id', 'doc_bonafide', 'doc_request_letter', 'doc_project_details', 'doc_prev_certificate', 'phone']);
        $data['status'] = 'submitted';
        $data['signature_name'] = $request->input('signature_name') ?: $onboarding->intern->name;
        $data['signature_date'] = $request->input('signature_date') ?: now();
        
        $onboarding->update($data);

        \App\Models\ActivityLog::log('intern_onboarding_submitted', "Onboarding form submitted by intern: {$onboarding->intern->name}");

        return redirect()->route('interns.onboard.success', $token);
    }

    /**
     * Show success page after form submission.
     */
    public function successPage($token)
    {
        $onboarding = InternOnboarding::where('token', $token)->firstOrFail();
        return view('interns.onboardings.success', compact('onboarding'));
    }

    /**
     * Review the submitted form (HR / Admin).
     */
    public function review(InternOnboarding $onboarding)
    {
        $onboarding->load(['intern.department', 'intern.designation', 'intern.uploadedDocuments']);
        $teamLeaders = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        return view('interns.onboardings.review', compact('onboarding', 'teamLeaders'));
    }

    /**
     * Approve the onboarding submission.
     */
    public function approve(Request $request, InternOnboarding $onboarding)
    {
        $request->validate([
            'company_access_requirements' => 'nullable|array',
            'company_access_other' => 'nullable|string|max:255',
            'assets_issued' => 'nullable|array',
            'assets_remarks' => 'nullable|string',
            'office_use_domain' => 'nullable|string|max:255',
            'office_use_mentor_assigned' => 'nullable|string|max:255',
            'office_use_certificate_eligible' => 'required|string|in:yes,no',
            'office_use_hr_signature' => 'nullable|string|max:255',
            'office_use_mentor_signature' => 'nullable|string|max:255',
            'office_use_management_approval' => 'nullable|string|max:255',
        ]);

        $intern = $onboarding->intern;

        // Update onboarding record
        $onboarding->update([
            'status' => 'completed',
            'company_access_requirements' => $request->company_access_requirements,
            'company_access_other' => $request->company_access_other,
            'assets_issued' => $request->assets_issued,
            'assets_remarks' => $request->assets_remarks,
            'office_use_domain' => $request->office_use_domain,
            'office_use_mentor_assigned' => $request->office_use_mentor_assigned,
            'office_use_certificate_eligible' => $request->office_use_certificate_eligible === 'yes',
            'office_use_hr_signature' => $request->office_use_hr_signature,
            'office_use_mentor_signature' => $request->office_use_mentor_signature,
            'office_use_management_approval' => $request->office_use_management_approval,
        ]);

        \App\Models\ActivityLog::log('intern_onboarding_approved', "Approved and completed onboarding for intern: {$intern->name}");

        return redirect()->route('interns.onboardings.index')->with('success', 'Intern onboarding approved successfully!');
    }

    /**
     * Generate certificate and activate intern profile.
     */
    public function generateCertificate(InternOnboarding $onboarding)
    {
        $intern = $onboarding->intern;

        if (!$intern) {
            return back()->with('error', 'Associated intern record not found.');
        }

        // Generate Certificate Code if not set
        if (empty($intern->certificate_code)) {
            do {
                $sequence = str_pad(14737 + $intern->id, 6, '0', STR_PAD_LEFT);
                $randomStr = strtoupper(Str::random(6));
                $code = "TSL-{$sequence}-{$randomStr}";
            } while (Intern::where('certificate_code', $code)->exists());
            
            $intern->certificate_code = $code;
        }

        // Activate intern status
        $intern->status = 'active';
        $intern->save();

        \App\Models\ActivityLog::log('intern_certificate_generated', "Generated certificate code and activated intern: {$intern->name}");

        return redirect()->route('interns.index')->with('success', 'Certificate generated and intern profile activated!');
    }

    /**
     * Reject the onboarding submission (request revisions).
     */
    public function reject(Request $request, InternOnboarding $onboarding)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $onboarding->update([
            'status' => 'pending',
            'assets_remarks' => 'Revision Requested: ' . $request->remarks, // Temporarily store feedback here
        ]);

        \App\Models\ActivityLog::log('intern_onboarding_rejected', "Requested revisions for onboarding: {$onboarding->intern->name}");

        return redirect()->route('interns.onboardings.index')->with('success', 'Revision request sent to the intern!');
    }

    /**
     * View completed/printable onboarding form.
     */
    public function viewCompletedForm(InternOnboarding $onboarding)
    {
        $onboarding->load(['intern.department', 'intern.designation', 'intern.uploadedDocuments']);
        return view('interns.onboardings.view', compact('onboarding'));
    }

    /**
     * Show the edit form for the onboarding details.
     */
    public function edit(InternOnboarding $onboarding)
    {
        $onboarding->load(['intern.department', 'intern.designation']);
        $departments = Department::where('status', 'active')->get();
        $teamLeaders = \App\Models\User::whereHas('role', fn($q) => $q->where('slug', 'team-leader'))->where('status', 'active')->get();
        return view('interns.onboardings.edit', compact('onboarding', 'departments', 'teamLeaders'));
    }

    /**
     * Update the onboarding and intern details.
     */
    public function update(Request $request, InternOnboarding $onboarding)
    {
        $request->validate([
            // Core Intern
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'department_id' => 'required|exists:departments,id',
            'joining_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:joining_date',
            'sector' => 'required|string|in:Techsoul Technologies,Techsoul IT Solutions,Techsoul Solar',
            
            // Section 1
            'gender' => 'nullable|string|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'aadhaar_number' => 'nullable|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'current_address' => 'nullable|string',
            'pin_code' => 'nullable|string|max:10',

            // Section 2
            'college_name' => 'nullable|string|max:255',
            'university_board' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
            'branch_specialization' => 'nullable|string|max:255',
            'current_semester_year' => 'nullable|string|max:100',
            'college_roll_number' => 'nullable|string|max:100',
            'expected_completion_year' => 'nullable|string|max:10',

            // Section 3
            'parent_name' => 'nullable|string|max:255',
            'parent_relationship' => 'nullable|string|max:100',
            'parent_phone' => 'nullable|string|max:20',
            'parent_address' => 'nullable|string',

            // Section 4
            'emergency_contact_person' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_alternate_phone' => 'nullable|string|max:20',

            // Section 5
            'internship_type' => 'nullable|string',
            'internship_type_other' => 'nullable|string|max:255',
            'internship_mode' => 'nullable|string',
            'internship_duration' => 'nullable|string',
            'internship_duration_other' => 'nullable|string|max:255',
            'areas_of_interest' => 'nullable|array',
            'areas_of_interest_other' => 'nullable|string|max:255',

            // Section 6
            'programming_languages' => 'nullable|array',
            'programming_languages_other' => 'nullable|string|max:255',
            'design_tools' => 'nullable|array',
            'design_tools_other' => 'nullable|string|max:255',
            'completed_projects' => 'nullable|string',

            // Section 8
            'company_access_requirements' => 'nullable|array',
            'company_access_other' => 'nullable|string|max:255',

            // Section 9
            'assets_issued' => 'nullable|array',
            'assets_remarks' => 'nullable|string',

            // Section 10
            'learning_expectations' => 'nullable|string',
            'career_goal' => 'nullable|string',

            // Section 11
            'signature_date' => 'nullable|date',

            // Office Use
            'office_use_domain' => 'nullable|string|max:255',
            'office_use_mentor_assigned' => 'nullable|string|max:255',
            'office_use_certificate_eligible' => 'nullable|string|in:yes,no',
        ]);

        $intern = $onboarding->intern;
        
        // Update intern model
        $intern->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'joining_date' => $request->joining_date,
            'end_date' => $request->end_date,
            'sector' => $request->sector,
            'phone' => $request->phone ?? $intern->phone,
        ]);

        // Update onboarding model
        $onboarding->update([
            'sector' => $request->sector,
            'mentor_supervisor' => $request->mentor_supervisor,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'blood_group' => $request->blood_group,
            'aadhaar_number' => $request->aadhaar_number,
            'alternate_mobile' => $request->alternate_mobile,
            'current_address' => $request->current_address,
            'pin_code' => $request->pin_code,
            'college_name' => $request->college_name,
            'university_board' => $request->university_board,
            'course' => $request->course,
            'branch_specialization' => $request->branch_specialization,
            'current_semester_year' => $request->current_semester_year,
            'college_roll_number' => $request->college_roll_number,
            'expected_completion_year' => $request->expected_completion_year,
            'parent_name' => $request->parent_name,
            'parent_relationship' => $request->parent_relationship,
            'parent_phone' => $request->parent_phone,
            'parent_address' => $request->parent_address,
            'emergency_contact_person' => $request->emergency_contact_person,
            'emergency_relationship' => $request->emergency_relationship,
            'emergency_phone' => $request->emergency_phone,
            'emergency_alternate_phone' => $request->emergency_alternate_phone,
            'internship_type' => $request->internship_type,
            'internship_type_other' => $request->internship_type_other,
            'internship_mode' => $request->internship_mode,
            'internship_duration' => $request->internship_duration,
            'internship_duration_other' => $request->internship_duration_other,
            'areas_of_interest' => $request->areas_of_interest,
            'areas_of_interest_other' => $request->areas_of_interest_other,
            'programming_languages' => $request->programming_languages,
            'programming_languages_other' => $request->programming_languages_other,
            'design_tools' => $request->design_tools,
            'design_tools_other' => $request->design_tools_other,
            'completed_projects' => $request->completed_projects,
            'company_access_requirements' => $request->company_access_requirements,
            'company_access_other' => $request->company_access_other,
            'assets_issued' => $request->assets_issued,
            'assets_remarks' => $request->assets_remarks,
            'learning_expectations' => $request->learning_expectations,
            'career_goal' => $request->career_goal,
            'signature_date' => $request->signature_date,
            'office_use_domain' => $request->office_use_domain,
            'office_use_mentor_assigned' => $request->office_use_mentor_assigned,
            'office_use_certificate_eligible' => $request->office_use_certificate_eligible === 'yes',
        ]);

        \App\Models\ActivityLog::log('intern_onboarding_updated', "Updated onboarding details for intern: {$intern->name}");

        return redirect()->route('interns.onboardings.index')->with('success', 'Onboarding details updated successfully!');
    }

    /**
     * Delete onboarding and associated intern.
     */
    public function destroy(InternOnboarding $onboarding)
    {
        $intern = $onboarding->intern;
        
        // Clean up documents on disk
        if ($intern) {
            foreach ($intern->uploadedDocuments as $document) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }
            
            if ($intern->photo && Storage::disk('public')->exists($intern->photo)) {
                Storage::disk('public')->delete($intern->photo);
            }
            
            $intern->delete();
        }

        $onboarding->delete();

        \App\Models\ActivityLog::log('intern_onboarding_deleted', "Deleted onboarding record for: " . ($intern->name ?? 'Unknown'));

        return redirect()->route('interns.onboardings.index')->with('success', 'Onboarding entry deleted successfully!');
    }
}
