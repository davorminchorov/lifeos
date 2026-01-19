<?php

namespace App\Policies;

use App\Models\CycleMenuItem;
use App\Models\User;

class CycleMenuItemPolicy
{
    public function create(User $user): bool
    {
        return $user->current_tenant_id !== null;
    }

    public function update(User $user, CycleMenuItem $item): bool
    {
        return $this->belongsToUserAndTenant($user, $item);
    }

    public function delete(User $user, CycleMenuItem $item): bool
    {
        return $this->belongsToUserAndTenant($user, $item);
    }

    /**
     * Check if the model belongs to the user and their current tenant.
     */
    protected function belongsToUserAndTenant(User $user, CycleMenuItem $item): bool
    {
        return $item->user_id === $user->id
            && $item->tenant_id === $user->current_tenant_id;
    }
}
