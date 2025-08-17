<?php

namespace App\Jobs;

use App\Models\Warranty;
use App\Notifications\WarrantyExpirationAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWarrantyExpirationNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $notificationDays = [30, 14, 7, 1, 0]
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting warranty expiration notification job');

        foreach ($this->notificationDays as $days) {
            $this->sendNotificationsForDay($days);
        }

        Log::info('Completed warranty expiration notification job');
    }

    /**
     * Send notifications for warranties expiring in specific days.
     */
    private function sendNotificationsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();

        $warranties = Warranty::with('user')
            ->where('current_status', 'active')
            ->whereDate('warranty_expiration_date', $targetDate)
            ->get();

        Log::info("Found {$warranties->count()} warranties expiring in {$days} days");

        foreach ($warranties as $warranty) {
            try {
                $warranty->user->notify(
                    new WarrantyExpirationAlert($warranty, $days)
                );

                Log::info("Sent expiration notification for warranty {$warranty->id} ({$warranty->product_name}) to user {$warranty->user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send expiration notification for warranty {$warranty->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Warranty expiration notification job failed: '.$exception->getMessage());
    }
}
