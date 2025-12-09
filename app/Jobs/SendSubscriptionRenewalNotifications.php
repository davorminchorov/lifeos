<?php

namespace App\Jobs;

use App\Events\SubscriptionRenewalDue;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionRenewalNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private ?array $notificationDays = null
    ) {
        $this->notificationDays = $notificationDays ?? [7, 3, 1, 0];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting subscription renewal notification job');

        foreach ($this->notificationDays as $days) {
            $this->dispatchEventsForDay($days);
        }

        Log::info('Completed subscription renewal notification job');
    }

    /**
     * Dispatch events for subscriptions due in specific days.
     */
    private function dispatchEventsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today = now()->toDateString();

        $query = Subscription::with('user')
            ->where('status', 'active');

        if ($days === 0) {
            // Include items due today or overdue (covers cases where next_billing_date advanced earlier)
            $query->whereDate('next_billing_date', '<=', $today);
        } else {
            $query->whereDate('next_billing_date', $targetDate);
        }

        $subscriptions = $query->get();

        Log::info("Found {$subscriptions->count()} subscriptions due in {$days} days");

        foreach ($subscriptions as $subscription) {
            try {
                event(new SubscriptionRenewalDue($subscription, $days));
            } catch (\Exception $e) {
                Log::error("Failed to dispatch SubscriptionRenewalDue for subscription {$subscription->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Subscription renewal notification job failed: '.$exception->getMessage());
    }
}
