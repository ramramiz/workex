<?php

namespace App\Mail;

use App\Models\InternOnboarding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnboardingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $onboarding;
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct(InternOnboarding $onboarding)
    {
        $this->onboarding = $onboarding;
        $this->link = route('interns.onboard.show', $onboarding->token);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Techsoul Cyber Solutions - Intern Onboarding Form')
                    ->view('emails.onboarding_link');
    }
}
