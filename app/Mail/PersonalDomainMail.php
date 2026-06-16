<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonalDomainMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $bodyContent;
    public $attachmentPath;
    public $attachmentName;

    public function __construct(string $subject, string $bodyContent, ?string $attachmentPath = null, ?string $attachmentName = null)
    {
        $this->subject = $subject;
        $this->bodyContent = $bodyContent;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    public function build()
    {
        $mail = $this->html($this->bodyContent)
            ->subject($this->subject);

        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $mail->attach($this->attachmentPath, [
                'as' => $this->attachmentName,
            ]);
        }

        return $mail;
    }
}
