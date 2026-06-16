<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Webklex\PHPIMAP\ClientManager;

class SendPersonalMail implements ShouldQueue
{
    use Queueable;

    public int $userId;
    public string $toEmail;
    public ?string $ccEmail;
    public string $subject;
    public string $body;
    public ?string $attachmentPath;
    public ?string $attachmentName;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $userId,
        string $toEmail,
        ?string $ccEmail,
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ) {
        $this->userId = $userId;
        $this->toEmail = $toEmail;
        $this->ccEmail = $ccEmail;
        $this->subject = $subject;
        $this->body = $body;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error("SendPersonalMail: User ID {$this->userId} not found.");
            return;
        }

        // Configure dynamic SMTP transporter
        $smtpHost = $user->mailbox_smtp_host ?: $user->mailbox_imap_host;
        $smtpPort = $user->mailbox_smtp_port ?: '465';
        $smtpEncryption = $user->mailbox_smtp_encryption ?: 'ssl';
        if ($smtpEncryption === 'none') {
            $smtpEncryption = null;
        }
        $smtpUsername = $user->mailbox_smtp_username ?: $user->mailbox_imap_username;
        $smtpPassword = $user->mailbox_smtp_password ?: $user->mailbox_imap_password;

        if (empty($smtpHost) || empty($smtpUsername) || empty($smtpPassword)) {
            Log::error("SendPersonalMail: SMTP configuration is incomplete for User ID {$this->userId}");
            return;
        }

        config()->set('mail.mailers.personal_smtp', [
            'transport' => 'smtp',
            'host' => $smtpHost,
            'port' => $smtpPort,
            'encryption' => $smtpEncryption,
            'username' => $smtpUsername,
            'password' => $smtpPassword,
            'timeout' => 30,
        ]);

        try {
            $realPath = null;
            if ($this->attachmentPath && Storage::disk('local')->exists($this->attachmentPath)) {
                $realPath = Storage::disk('local')->path($this->attachmentPath);
            }

            $mailable = new \App\Mail\PersonalDomainMail($this->subject, $this->body, $realPath, $this->attachmentName);
            $mailable->from($smtpUsername, $user->name);

            $mailer = Mail::mailer('personal_smtp')->to($this->toEmail);

            if (!empty($this->ccEmail)) {
                $ccAddresses = array_filter(array_map('trim', explode(',', $this->ccEmail)));
                if (!empty($ccAddresses)) {
                    $mailer = $mailer->cc($ccAddresses);
                }
            }

            $sentMessage = $mailer->send($mailable);

            // Clean up temporary attachment
            if ($this->attachmentPath) {
                try {
                    Storage::disk('local')->delete($this->attachmentPath);
                } catch (\Exception $ex) {
                    Log::warning("SendPersonalMail: Failed to delete temp attachment: " . $ex->getMessage());
                }
            }

            // Append sent message to IMAP Sent folder
            try {
                $host = $user->mailbox_imap_host;
                $port = $user->mailbox_imap_port ?: '993';
                $encryption = $user->mailbox_imap_encryption ?: 'ssl';
                $username = $user->mailbox_imap_username;
                $password = $user->mailbox_imap_password;

                if (!empty($host) && !empty($username) && !empty($password)) {
                    $cm = new ClientManager();
                    $client = $cm->make([
                        'host'          => $host,
                        'port'          => $port,
                        'encryption'    => $encryption,
                        'validate_cert' => false,
                        'username'      => $username,
                        'password'      => $password,
                        'protocol'      => 'imap'
                    ]);
                    $client->connect();

                    // Resolve folder path
                    $cacheKey = "mailbox_folder_{$user->id}_sent";
                    $sentFolderPath = Cache::remember($cacheKey, 300, function () use ($client) {
                        try {
                            $folders = $client->getFolders(false);
                            foreach ($folders as $folder) {
                                $name = strtolower($folder->name);
                                $path = strtolower($folder->path);
                                if (str_contains($name, 'sent') || str_contains($path, 'sent')) {
                                    return $folder->path;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("IMAP getFolders failed in job: " . $e->getMessage());
                        }
                        return 'Sent';
                    });

                    $sentFolder = $client->getFolder($sentFolderPath);
                    if (!$sentFolder) {
                        $sentFolder = $client->getFolder('Sent');
                    }

                    if ($sentFolder && $sentMessage) {
                        $rawMime = $sentMessage->toString();
                        $sentFolder->appendMessage($rawMime, ['\Seen']);

                        // Clear cached message list for Sent folder so the new message will be retrieved on next refresh
                        Cache::forget("mailbox_msgs_{$user->id}_sent");
                    }
                }
            } catch (\Exception $imapEx) {
                Log::warning("SendPersonalMail: Failed to append sent message to IMAP Sent folder: " . $imapEx->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("SendPersonalMail Job failed: " . $e->getMessage());
            throw $e;
        }
    }
}
