<?php

namespace App\Listeners;

use App\Events\SubscriptionRenewalDue;
use App\Notifications\SubscriptionRenewalAlert;
use App\Support\NotificationDeduplicator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendSubscriptionRenewalNotification implements ShouldQueue
{
    public function handle(SubscriptionRenewalDue $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->user;
        $days = $event->days;

        try {
            $enabledChannels = $user->getEnabledNotificationChannels('subscription_renewal');

            if (empty($enabledChannels)) {
                Log::info("Skipping notification for subscription {$subscription->id} - user has disabled all channels");

                return;
            }

            // Prevent duplicate sends across jobs/listeners within the same day
            if (! NotificationDeduplicator::acquire('subscription_renewal', $user->id, 'subscription', $subscription->id, 'D'.$days)) {
                Log::info("Skipping duplicate subscription renewal notification for subscription {$subscription->id} (days {$days})");

                return;
            }

            $user->notify(new SubscriptionRenewalAlert($subscription, $days));

            Log::info("Sent renewal notification for subscription {$subscription->id} ({$subscription->service_name}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send renewal notification for subscription {$subscription->id}: {$e->getMessage()}");
        }
    }
}
