<?php

namespace App\Policies;

use App\Models\Iou;
use App\Models\User;

class IouPolicy
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
    public function view(User $user, Iou $iou): bool
    {
        return $this->belongsToUserAndTenant($user, $iou);
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
    public function update(User $user, Iou $iou): bool
    {
        return $this->belongsToUserAndTenant($user, $iou);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Iou $iou): bool
    {
        return $this->belongsToUserAndTenant($user, $iou);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Iou $iou): bool
    {
        return $this->belongsToUserAndTenant($user, $iou);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Iou $iou): bool
    {
        return $this->belongsToUserAndTenant($user, $iou);
    }

    /**
     * Check if the model belongs to the user and their current tenant.
     */
    protected function belongsToUserAndTenant(User $user, Iou $iou): bool
    {
        return $iou->user_id === $user->id
            && $iou->tenant_id === $user->current_tenant_id;
    }
}
