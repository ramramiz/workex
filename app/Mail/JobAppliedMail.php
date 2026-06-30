<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobAppliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    /**
     * Create a new message instance.
     *
     * @param JobApplication $application
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $vacancyTitle = $this->application->vacancy?->title ?? 'Position';
        return $this->subject('Application Received - ' . $vacancyTitle)
                    ->from('No-Replay@teamtechsoul.com', 'Techsoul')
                    ->view('emails.job_applied');
    }
}
