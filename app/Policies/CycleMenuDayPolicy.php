<?php

namespace App\Policies;

use App\Models\CycleMenuDay;
use App\Models\User;

class CycleMenuDayPolicy
{
    public function update(User $user, CycleMenuDay $day): bool
    {
        return $this->belongsToUserAndTenant($user, $day);
    }

    /**
     * Check if the model belongs to the user and their current tenant.
     */
    protected function belongsToUserAndTenant(User $user, CycleMenuDay $day): bool
    {
        return $day->menu->user_id === $user->id
            && $day->tenant_id === $user->current_tenant_id;
    }
}
