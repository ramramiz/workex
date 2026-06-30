<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use App\Models\Department;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::with('department')
            ->withCount('applications')
            ->latest()
            ->paginate(20);

        return view('job-vacancies.index', compact('vacancies'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->get();
        return view('job-vacancies.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'job_type' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        JobVacancy::create($data);

        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy registered successfully!');
    }

    public function edit(JobVacancy $jobVacancy)
    {
        $departments = Department::where('status', 'active')->get();
        return view('job-vacancies.edit', compact('jobVacancy', 'departments'));
    }

    public function update(Request $request, JobVacancy $jobVacancy)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'job_type' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $jobVacancy->update($data);

        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy updated successfully!');
    }

    public function destroy(JobVacancy $jobVacancy)
    {
        $jobVacancy->delete();
        return redirect()->route('job-vacancies.index')->with('success', 'Job vacancy deleted successfully!');
    }

    public function applications(JobVacancy $jobVacancy)
    {
        $applications = $jobVacancy->applications()->latest()->paginate(20);
        return view('job-vacancies.applications', compact('jobVacancy', 'applications'));
    }
}
