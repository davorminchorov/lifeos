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

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

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
        $today = now()->toDateString();

        $query = Warranty::with('user')
            ->where('current_status', 'active');

        if ($days === 0) {
            // Include items expiring today or already expired (still active status)
            $query->whereDate('warranty_expiration_date', '<=', $today);
        } else {
            $query->whereDate('warranty_expiration_date', $targetDate);
        }

        $warranties = $query->get();

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
