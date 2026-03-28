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
        return $this->belongsToUserTenant($user, $item);
    }

    public function delete(User $user, CycleMenuItem $item): bool
    {
        return $this->belongsToUserTenant($user, $item);
    }

    /**
     * Check if the item belongs to the user's current tenant.
     */
    protected function belongsToUserTenant(User $user, CycleMenuItem $item): bool
    {
        return $item->tenant_id === $user->current_tenant_id;
    }
}
