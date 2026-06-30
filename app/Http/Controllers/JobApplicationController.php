<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use App\Mail\InterviewScheduleMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class JobApplicationController extends Controller
{
    public function scheduleInterview(Request $request)
    {
        $request->validate([
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'exists:job_applications,id',
            'interview_date' => 'required|date|after_or_equal:today',
            'interview_time' => 'required',
            'interview_venue' => 'required|string|max:1000',
        ]);

        $applications = JobApplication::whereIn('id', $request->candidate_ids)->get();
        $sentCount = 0;

        foreach ($applications as $app) {
            try {
                Mail::to($app->email)->send(new InterviewScheduleMail(
                    $app,
                    $request->interview_date,
                    $request->interview_time,
                    $request->interview_venue
                ));
                
                // Update candidate status and save interview details
                $app->update([
                    'status' => 'interview_scheduled',
                    'interview_date' => $request->interview_date,
                    'interview_time' => $request->interview_time,
                    'interview_venue' => $request->interview_venue,
                ]);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Failed sending interview call to candidate ' . $app->id . ': ' . $e->getMessage());
            }
        }

        if ($sentCount === 0) {
            return back()->withErrors(['error' => 'Failed to send interview invitation emails. Please check your mail logs.']);
        }

        return back()->with('success', 'Interview call letters successfully sent to ' . $sentCount . ' candidate(s)!');
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,interview_scheduled,accepted,rejected',
        ]);

        $application->update(['status' => $request->status]);

        $statusLabel = str_replace('_', ' ', ucfirst($request->status));
        return back()->with('success', 'Candidate application status updated to ' . $statusLabel . '!');
    }

    public function destroy(JobApplication $application)
    {
        $application->delete();
        return back()->with('success', 'Candidate application deleted successfully!');
    }
}
