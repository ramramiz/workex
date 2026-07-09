<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $unconfirmedAlert = \App\Models\AppAlert::whereHas('users', function ($q) {
                    $q->where('user_id', auth()->id())
                      ->whereNull('confirmed_at');
                })->latest()->first();
                
                $view->with('unconfirmedAlert', $unconfirmedAlert);
            }
        });

        // Log all outgoing emails
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSent::class,
            function (\Illuminate\Mail\Events\MessageSent $event) {
                try {
                    $to = collect($event->message->getTo())->map(fn($address) => $address->getAddress())->implode(', ');
                    $subject = $event->message->getSubject();
                    $body = $event->message->getHtmlBody() ?: $event->message->getTextBody();
                    
                    $attachments = [];
                    if (method_exists($event->message, 'getAttachments')) {
                        foreach ($event->message->getAttachments() as $part) {
                            $attachments[] = $part->getFilename() ?: 'Attachment';
                        }
                    }
                    
                    \App\Models\ActivityLog::create([
                        'user_id'     => auth()->id(),
                        'action'      => 'email_sent',
                        'description' => "Sent email to {$to} with subject: \"{$subject}\"",
                        'ip_address'  => request()->ip(),
                        'user_agent'  => request()->userAgent(),
                        'old_values'  => [],
                        'new_values'  => [
                            'to'          => $to,
                            'subject'     => $subject,
                            'body'        => $body,
                            'attachments' => $attachments,
                        ],
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to log sent email activity: ' . $e->getMessage());
                }
            }
        );
    }
}
