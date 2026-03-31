<?php

namespace App\Listeners;

use App\Events\BudgetThresholdCrossed;
use App\Notifications\BudgetThresholdAlert;
use App\Support\NotificationDeduplicator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendBudgetThresholdNotification implements ShouldQueue
{
    public function handle(BudgetThresholdCrossed $event): void
    {
        $budget = $event->budget;
        $user = $budget->user;

        try {
            $enabledChannels = $user->getEnabledNotificationChannels('budget_threshold');

            if (empty($enabledChannels)) {
                Log::info("Skipping notification for budget {$budget->id} - user has disabled all channels");

                return;
            }

            if (! NotificationDeduplicator::acquire('budget_threshold', $user->id, 'budget', $budget->id, $event->direction)) {
                Log::info("Skipping duplicate budget threshold notification for budget {$budget->id} ({$event->direction})");

                return;
            }

            $user->notify(new BudgetThresholdAlert($budget, $event->direction));

            Log::info("Sent budget threshold notification for budget {$budget->id} ({$budget->category}) to user {$user->email} via channels: ".implode(', ', $enabledChannels));
        } catch (\Exception $e) {
            Log::error("Failed to send notification for budget {$budget->id}: {$e->getMessage()}");
        }
    }
}
