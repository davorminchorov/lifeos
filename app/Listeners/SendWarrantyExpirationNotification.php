<?php

namespace App\Listeners;

use App\Events\WarrantyExpirationDue;
use App\Notifications\WarrantyExpirationAlert;
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

            $user->notify(new WarrantyExpirationAlert($warranty, $days));

            Log::info("Sent warranty expiration notification for warranty {$warranty->id} ({$warranty->product_name}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send notification for warranty {$warranty->id}: {$e->getMessage()}");
        }
    }
}
