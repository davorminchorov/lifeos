<?php

namespace App\Jobs;

use App\Events\SubscriptionRenewalDue;
use App\Models\Subscription;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionRenewalNotifications implements ShouldQueue
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
        private ?array $notificationDays = null
    ) {
        // Legacy support: Allow specific days to be passed for backward compatibility
        // If null, will use each user's individual preferences
        $this->notificationDays = $notificationDays;
    }

    /**
     * Execute the job.
     *
     * This job now processes notifications per user, respecting their individual
     * notification preferences for days and channels.
     */
    public function handle(): void
    {
        Log::info('Starting subscription renewal notification job');

        if ($this->notificationDays !== null) {
            // Legacy mode: Use system-wide notification days (for backward compatibility)
            Log::info('Running in legacy mode with system-wide notification days: '.implode(', ', $this->notificationDays));
            $this->handleLegacyMode();
        } else {
            // New mode: Respect individual user preferences
            Log::info('Running in user-centric mode with individual user preferences');
            $this->handleUserCentricMode();
        }

        Log::info('Completed subscription renewal notification job');
    }

    /**
     * Handle notifications using user-centric approach.
     *
     * Processes each user's subscriptions according to their notification preferences.
     */
    private function handleUserCentricMode(): void
    {
        // Get all users who have active subscriptions
        // Note: withoutGlobalScope is needed because this job runs in queue context without auth
        $userIds = Subscription::withoutGlobalScope(TenantScope::class)
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->get();

        Log::info("Processing notifications for {$users->count()} users with active subscriptions");

        foreach ($users as $user) {
            $this->processNotificationsForUser($user);
        }
    }

    /**
     * Handle notifications using legacy system-wide days.
     *
     * Maintains backward compatibility with old behavior.
     */
    private function handleLegacyMode(): void
    {
        foreach ($this->notificationDays as $days) {
            $this->dispatchEventsForDay($days);
        }
    }

    /**
     * Process notifications for a specific user based on their preferences.
     */
    private function processNotificationsForUser(User $user): void
    {
        // Get user's enabled notification channels
        $enabledChannels = $user->getEnabledNotificationChannels('subscription_renewal');

        // Skip if user has disabled all notification channels
        if (empty($enabledChannels)) {
            Log::info("Skipping user {$user->id} ({$user->email}) - all notification channels disabled");

            return;
        }

        // Get user's preferred notification days
        $notificationDays = $user->getNotificationDays('subscription_renewal');

        Log::info("Processing user {$user->id} ({$user->email}) with notification days: ".implode(', ', $notificationDays));

        // Process notifications for each of the user's preferred days
        foreach ($notificationDays as $days) {
            $this->dispatchEventsForUserAndDay($user, $days);
        }
    }

    /**
     * Dispatch events for a specific user's subscriptions due in specific days.
     */
    private function dispatchEventsForUserAndDay(User $user, int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today = now()->toDateString();

        // Query subscriptions for this specific user
        // Note: withoutGlobalScope is needed because this job runs in queue context without auth
        $query = Subscription::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->where('status', 'active');

        if ($days === 0) {
            // Include items due today or overdue
            $query->whereDate('next_billing_date', '<=', $today);
        } else {
            $query->whereDate('next_billing_date', $targetDate);
        }

        $subscriptions = $query->get();

        if ($subscriptions->count() > 0) {
            Log::info("Found {$subscriptions->count()} subscriptions for user {$user->id} due in {$days} days");
        }

        // Dispatch event for each subscription
        foreach ($subscriptions as $subscription) {
            try {
                event(new SubscriptionRenewalDue($subscription, $days));
            } catch (\Exception $e) {
                Log::error("Failed to dispatch SubscriptionRenewalDue for subscription {$subscription->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Dispatch events for subscriptions due in specific days (legacy method).
     *
     * This method is kept for backward compatibility when using system-wide notification days.
     */
    private function dispatchEventsForDay(int $days): void
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today = now()->toDateString();

        // Note: withoutGlobalScope is needed because this job runs in queue context without auth
        $query = Subscription::withoutGlobalScope(TenantScope::class)
            ->with('user')
            ->where('status', 'active');

        if ($days === 0) {
            // Include items due today or overdue
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
