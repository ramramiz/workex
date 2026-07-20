<?php

namespace App\Mail;

use App\Models\EmployeeOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeOnboardingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $onboarding;
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct(EmployeeOnboarding $onboarding)
    {
        $this->onboarding = $onboarding;
        $this->link = route('employees.onboard.show', $onboarding->token);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Techsoul Cyber Solutions - Employee Onboarding Form')
                    ->view('emails.employee_onboarding_link');
    }
}
