<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TenantPolicy
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
    public function view(User $user, Tenant $tenant): bool
    {
        return $this->hasAccess($user, $tenant);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tenant $tenant): bool
    {
        return $this->isOwner($user, $tenant);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->isOwner($user, $tenant);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tenant $tenant): bool
    {
        return $this->isOwner($user, $tenant);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tenant $tenant): bool
    {
        return $this->isOwner($user, $tenant);
    }

    /**
     * Determine whether the user can add members to the tenant.
     */
    public function addMember(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can remove members from the tenant.
     */
    public function removeMember(User $user, Tenant $tenant): bool
    {
        return $this->isOwnerOrAdmin($user, $tenant);
    }

    /**
     * Determine whether the user can switch to this tenant.
     */
    public function switch(User $user, Tenant $tenant): bool
    {
        return $this->hasAccess($user, $tenant);
    }

    /**
     * Check if the user has access to the tenant.
     */
    protected function hasAccess(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists()
            || $user->ownedTenants()->where('id', $tenant->id)->exists();
    }

    /**
     * Check if the user is the owner of the tenant.
     */
    protected function isOwner(User $user, Tenant $tenant): bool
    {
        return $tenant->owner_id === $user->id;
    }

    /**
     * Check if the user is the owner or an admin of the tenant.
     */
    protected function isOwnerOrAdmin(User $user, Tenant $tenant): bool
    {
        if ($this->isOwner($user, $tenant)) {
            return true;
        }

        $member = $tenant->members()->where('user_id', $user->id)->first();

        return $member && $member->pivot->role === 'admin';
    }
}
