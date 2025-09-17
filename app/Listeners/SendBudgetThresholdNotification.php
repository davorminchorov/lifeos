<?php

namespace App\Listeners;

use App\Events\BudgetThresholdCrossed;
use App\Models\UserNotificationPreference;
use App\Notifications\BudgetThresholdAlert;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBudgetThresholdNotification implements ShouldQueue
{
    public function handle(BudgetThresholdCrossed $event): void
    {
        $budget = $event->budget;
        $user = $budget->user;

        // Load preference; fallback to defaults if not set
        $pref = UserNotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('notification_type', 'budget_threshold')
            ->first();

        $channels = $pref?->getEnabledChannels() ?? (UserNotificationPreference::getDefaultPreferences()['budget_threshold']['email_enabled']
            ? ['mail', 'database']
            : ['database']);

        if (empty($channels)) {
            return; // nothing to send
        }

        $user->notify((new BudgetThresholdAlert($budget, $event->direction))->onQueue('notifications')->via($channels));
    }
}
