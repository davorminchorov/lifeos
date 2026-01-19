<?php

namespace App\Listeners;

use App\Events\WarrantyExpirationDue;
use App\Notifications\WarrantyExpirationAlert;
use App\Support\NotificationDeduplicator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendWarrantyExpirationNotification implements ShouldQueue
{
    public function handle(WarrantyExpirationDue $event): void
    {
        $warranty = $event->warranty;
        $user = $warranty->user;
        $days = $event->days;

        try {
            $enabledChannels = $user->getEnabledNotificationChannels('warranty_expiration');

            if (empty($enabledChannels)) {
                Log::info("Skipping notification for warranty {$warranty->id} - user has disabled all channels");

                return;
            }

            // Prevent duplicate sends across jobs/listeners within the same day
            if (! NotificationDeduplicator::acquire('warranty_expiration', $user->id, 'warranty', $warranty->id, 'D'.$days)) {
                Log::info("Skipping duplicate warranty expiration notification for warranty {$warranty->id} (days {$days})");

                return;
            }

            $user->notify(new WarrantyExpirationAlert($warranty, $days));

            Log::info("Sent warranty expiration notification for warranty {$warranty->id} ({$warranty->product_name}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send notification for warranty {$warranty->id}: {$e->getMessage()}");
        }
    }
}
