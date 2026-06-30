<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class PublicJobVacancyController extends Controller
{
    public function show($token)
    {
        $vacancy = JobVacancy::where('token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        return view('careers.show', compact('vacancy'));
    }

    public function apply(Request $request, $token)
    {
        $vacancy = JobVacancy::where('token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|string|max:50',
            'dob' => 'required|date',
            'qualification' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'state' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'home_town' => 'required|string|max:255',
            'experience_years' => 'required|string|max:255',
            'salary_expectation' => 'required|string|max:255',
            'ready_to_relocate' => 'required|string|max:50',
            'linkedin_id' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB limit
            'cover_letter' => 'nullable|string|max:5000',
        ]);

        $path = $request->file('resume')->store('resumes', 'public');

        $application = JobApplication::create([
            'company_id' => $vacancy->company_id,
            'job_vacancy_id' => $vacancy->id,
            'name' => $data['name'],
            'gender' => $data['gender'],
            'dob' => $data['dob'],
            'qualification' => $data['qualification'],
            'email' => $data['email'],
            'state' => $data['state'],
            'district' => $data['district'],
            'home_town' => $data['home_town'],
            'experience_years' => $data['experience_years'],
            'salary_expectation' => $data['salary_expectation'],
            'ready_to_relocate' => $data['ready_to_relocate'],
            'linkedin_id' => $data['linkedin_id'],
            'phone' => $data['phone'],
            'resume_path' => $path,
            'cover_letter' => $data['cover_letter'],
            'status' => 'pending',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($application->email)->send(new \App\Mail\JobAppliedMail($application));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Job application confirmation mail failed: ' . $e->getMessage());
        }

        return redirect()->route('careers.success', $vacancy->token);
    }

    public function success($token)
    {
        $vacancy = JobVacancy::where('token', $token)->firstOrFail();
        return view('careers.success', compact('vacancy'));
    }
}
