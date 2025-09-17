<?php

namespace App\Listeners;

use App\Events\ContractNotificationDue;
use App\Notifications\ContractExpirationAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendContractExpirationNotification implements ShouldQueue
{
    public function handle(ContractNotificationDue $event): void
    {
        $contract = $event->contract;
        $user = $contract->user;
        $days = $event->days;
        $isNoticeAlert = $event->isNoticeAlert;

        try {
            $enabledChannels = $user->getEnabledNotificationChannels('contract_expiration');

            if (empty($enabledChannels)) {
                Log::info("Skipping notification for contract {$contract->id} - user has disabled all channels");

                return;
            }

            $user->notify(new ContractExpirationAlert($contract, $days, $isNoticeAlert));

            $alertType = $isNoticeAlert ? 'notice period' : 'expiration';
            Log::info("Sent {$alertType} notification for contract {$contract->id} ({$contract->title}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send notification for contract {$contract->id}: {$e->getMessage()}");
        }
    }
}
