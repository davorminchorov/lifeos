<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Get the current tenant for the authenticated user.
     */
    public function current(): ?Tenant
    {
        if (! auth()->check() || ! auth()->user()->current_tenant_id) {
            return null;
        }

        return Tenant::find(auth()->user()->current_tenant_id);
    }

    /**
     * Get the current tenant ID.
     */
    public function currentId(): ?int
    {
        return auth()->check() ? auth()->user()->current_tenant_id : null;
    }

    /**
     * Create a new tenant for a user.
     */
    public function createTenant(User $user, string $name): Tenant
    {
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'owner_id' => $user->id,
        ]);

        // Add the owner as a member with admin role
        $tenant->members()->attach($user->id, ['role' => 'admin']);

        // Set as current tenant if user doesn't have one
        if (! $user->current_tenant_id) {
            $user->current_tenant_id = $tenant->id;
            $user->save();
        }

        return $tenant;
    }

    /**
     * Add a user to a tenant.
     */
    public function addMember(Tenant $tenant, User $user, string $role = 'member'): void
    {
        if (! $tenant->members()->where('user_id', $user->id)->exists()) {
            $tenant->members()->attach($user->id, ['role' => $role]);
        }
    }

    /**
     * Remove a user from a tenant.
     */
    public function removeMember(Tenant $tenant, User $user): void
    {
        $tenant->members()->detach($user->id);

        // If this was the user's current tenant, clear it
        if ($user->current_tenant_id === $tenant->id) {
            $user->current_tenant_id = null;
            $user->save();
        }
    }

    /**
     * Switch the current user's tenant.
     */
    public function switchTenant(Tenant $tenant): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->switchTenant($tenant);
    }

    /**
     * Check if a user has access to a tenant.
     */
    public function hasAccess(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->where('tenants.id', $tenant->id)->exists()
            || $user->ownedTenants()->where('id', $tenant->id)->exists();
    }

    /**
     * Check if a user is the owner of a tenant.
     */
    public function isOwner(User $user, Tenant $tenant): bool
    {
        return $tenant->owner_id === $user->id;
    }

    /**
     * Get the user's role in a tenant.
     */
    public function getUserRole(User $user, Tenant $tenant): ?string
    {
        if ($this->isOwner($user, $tenant)) {
            return 'owner';
        }

        $member = $tenant->members()->where('user_id', $user->id)->first();

        return $member ? $member->pivot->role : null;
    }
}
