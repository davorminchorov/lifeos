<?php

namespace App\Listeners;

use App\Events\UtilityBillDueSoon;
use App\Notifications\UtilityBillDueAlert;
use App\Support\NotificationDeduplicator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendUtilityBillDueNotification implements ShouldQueue
{
    public function handle(UtilityBillDueSoon $event): void
    {
        $bill = $event->utilityBill;
        $user = $bill->user;
        $days = $event->days;

        try {
            $enabledChannels = $user->getEnabledNotificationChannels('utility_bill_due');

            if (empty($enabledChannels)) {
                Log::info("Skipping notification for utility bill {$bill->id} - user has disabled all channels");

                return;
            }

            // Prevent duplicate sends within the same day
            if (! NotificationDeduplicator::acquire('utility_bill_due', $user->id, 'utility_bill', $bill->id, 'D'.$days)) {
                Log::info("Skipping duplicate utility bill notification for bill {$bill->id} (days {$days})");

                return;
            }

            $user->notify(new UtilityBillDueAlert($bill, $days));

            Log::info("Sent utility bill due notification for bill {$bill->id} ({$bill->provider}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send notification for utility bill {$bill->id}: {$e->getMessage()}");
        }
    }
}
