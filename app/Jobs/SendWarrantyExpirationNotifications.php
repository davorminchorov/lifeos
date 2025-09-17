<?php

namespace App\Jobs;

use App\Events\WarrantyExpirationDue;
use App\Models\Warranty;
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
            $this->dispatchEventsForDay($days);
        }

        Log::info('Completed warranty expiration notification job');
    }

    /**
     * Dispatch events for warranties expiring in specific days.
     */
    private function dispatchEventsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();

        $warranties = Warranty::with('user')
            ->where('current_status', 'active')
            ->whereDate('warranty_expiration_date', $targetDate)
            ->get();

        Log::info("Found {$warranties->count()} warranties expiring in {$days} days");

        foreach ($warranties as $warranty) {
            try {
                event(new WarrantyExpirationDue($warranty, $days));
            } catch (\Exception $e) {
                Log::error("Failed to dispatch WarrantyExpirationDue for warranty {$warranty->id}: {$e->getMessage()}");
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
