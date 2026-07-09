<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendAmcReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-amc-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sends AMC renewal WhatsApp reminders at specific thresholds (40, 30, 20, 10, 5, 3, 1, 0 days remaining)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic AMC reminders check...');

        $thresholds = [40, 30, 20, 10, 5, 3, 1, 0];

        $amcs = \App\Models\ProjectAmc::whereIn('status', ['active', 'pending_renewal'])->get();

        $sentCount = 0;

        foreach ($amcs as $amc) {
            // Days remaining
            $daysRemaining = (int) max(0, today()->diffInDays($amc->end_date, false));

            if (in_array($daysRemaining, $thresholds)) {
                // Check if a reminder has already been sent for this threshold and specific end date
                $alreadySent = \App\Models\ActivityLog::where('model_type', \App\Models\ProjectAmc::class)
                    ->where('model_id', $amc->id)
                    ->where('action', 'amc_whatsapp_reminder_sent')
                    ->whereJsonContains('new_values->days_remaining', $daysRemaining)
                    ->whereJsonContains('new_values->end_date', $amc->end_date->toDateString())
                    ->exists();

                if (!$alreadySent) {
                    $this->info("Sending automated reminder for AMC ID {$amc->id} (Days remaining: {$daysRemaining})");
                    $result = $amc->sendWhatsappReminderNotification();
                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $this->error("Failed to send reminder for AMC ID {$amc->id}: " . ($result['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->line("Reminder already sent for AMC ID {$amc->id} and threshold {$daysRemaining} days.");
                }
            }
        }

        $this->info("Completed sending automatic AMC reminders. Total sent: {$sentCount}");
    }
}
