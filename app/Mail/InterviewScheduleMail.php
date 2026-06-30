<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $date;
    public $time;
    public $venue;

    /**
     * Create a new message instance.
     *
     * @param JobApplication $application
     * @param string $date
     * @param string $time
     * @param string $venue
     */
    public function __construct(JobApplication $application, string $date, string $time, string $venue)
    {
        $this->application = $application;
        $this->date = $date;
        $this->time = $time;
        $this->venue = $venue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $vacancyTitle = $this->application->vacancy?->title ?? 'Position';
        return $this->subject('Interview Invitation - ' . $vacancyTitle)
                    ->from('No-Replay@teamtechsoul.com', 'Techsoul')
                    ->view('emails.interview_schedule');
    }
}
