<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up tenant context for testing.
     * Creates a tenant, sets it as the user's current tenant, and authenticates the user.
     */
    protected function setupTenantContext(?User $user = null, ?Tenant $tenant = null): array
    {
        $user = $user ?? User::factory()->create();
        $tenant = $tenant ?? Tenant::factory()->create(['owner_id' => $user->id]);

        $user->current_tenant_id = $tenant->id;
        $user->save();

        $this->actingAs($user);

        return ['user' => $user, 'tenant' => $tenant];
    }
}
