<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // If user doesn't have a current tenant, try to set one
        if (! $user->current_tenant_id) {
            $firstTenant = $user->tenants()->first();

            if ($firstTenant) {
                $user->current_tenant_id = $firstTenant->id;
                $user->save();
            } elseif ($user->ownedTenants()->exists()) {
                $firstOwnedTenant = $user->ownedTenants()->first();
                $user->current_tenant_id = $firstOwnedTenant->id;
                $user->save();
            }
        }

        // Verify user has access to current tenant
        if ($user->current_tenant_id) {
            $hasAccess = $user->tenants()->where('tenants.id', $user->current_tenant_id)->exists()
                || $user->ownedTenants()->where('id', $user->current_tenant_id)->exists();

            if (! $hasAccess) {
                $user->current_tenant_id = null;
                $user->save();

                return redirect()->route('tenant.select')
                    ->with('error', 'You do not have access to the selected tenant.');
            }
        }

        return $next($request);
    }
}
