<?php

namespace App\Policies;

use App\Models\CycleMenu;
use App\Models\User;

class CycleMenuPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    public function view(User $user, CycleMenu $cycleMenu): bool
    {
        return $this->belongsToUserAndTenant($user, $cycleMenu);
    }

    public function create(User $user): bool
    {
        return $user->current_tenant_id !== null;
    }

    public function update(User $user, CycleMenu $cycleMenu): bool
    {
        return $this->belongsToUserAndTenant($user, $cycleMenu);
    }

    public function delete(User $user, CycleMenu $cycleMenu): bool
    {
        return $this->belongsToUserAndTenant($user, $cycleMenu);
    }

    /**
     * Check if the model belongs to the user and their current tenant.
     */
    protected function belongsToUserAndTenant(User $user, CycleMenu $cycleMenu): bool
    {
        return $cycleMenu->user_id === $user->id
            && $cycleMenu->tenant_id === $user->current_tenant_id;
    }
}
