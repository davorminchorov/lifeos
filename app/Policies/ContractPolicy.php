<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contract $contract): bool
    {
        return $this->belongsToUserAndTenant($user, $contract);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->current_tenant_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contract $contract): bool
    {
        return $this->belongsToUserAndTenant($user, $contract);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contract $contract): bool
    {
        return $this->belongsToUserAndTenant($user, $contract);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Contract $contract): bool
    {
        return $this->belongsToUserAndTenant($user, $contract);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Contract $contract): bool
    {
        return $this->belongsToUserAndTenant($user, $contract);
    }

    /**
     * Check if the model belongs to the user and their current tenant.
     */
    protected function belongsToUserAndTenant(User $user, Contract $contract): bool
    {
        return $contract->user_id === $user->id
            && $contract->tenant_id === $user->current_tenant_id;
    }
}
