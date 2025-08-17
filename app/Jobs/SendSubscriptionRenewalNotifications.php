<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\SubscriptionRenewalAlert;
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
        private array $notificationDays = [7, 3, 1, 0]
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting subscription renewal notification job');

        foreach ($this->notificationDays as $days) {
            $this->sendNotificationsForDay($days);
        }

        Log::info('Completed subscription renewal notification job');
    }

    /**
     * Send notifications for subscriptions due in specific days.
     */
    private function sendNotificationsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();

        $subscriptions = Subscription::with('user')
            ->where('status', 'active')
            ->whereDate('next_billing_date', $targetDate)
            ->get();

        Log::info("Found {$subscriptions->count()} subscriptions due in {$days} days");

        foreach ($subscriptions as $subscription) {
            try {
                $subscription->user->notify(
                    new SubscriptionRenewalAlert($subscription, $days)
                );

                Log::info("Sent renewal notification for subscription {$subscription->id} ({$subscription->service_name}) to user {$subscription->user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send renewal notification for subscription {$subscription->id}: {$e->getMessage()}");
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
