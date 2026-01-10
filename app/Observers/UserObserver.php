<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * Automatically create default notification preferences for new users.
     */
    public function created(User $user): void
    {
        $user->createDefaultNotificationPreferences();
    }
}
