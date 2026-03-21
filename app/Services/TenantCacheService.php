<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TenantCacheService
{
    /**
     * Default cache TTL in seconds (15 minutes).
     */
    protected int $defaultTtl = 900;

    /**
     * Get a tenant-scoped cache key.
     */
    public function key(string $domain, string $identifier, ?int $tenantId = null, ?int $userId = null): string
    {
        $tenantId = $tenantId ?? $this->currentTenantId();
        $userId = $userId ?? auth()->id();

        return "t:{$tenantId}:u:{$userId}:{$domain}:{$identifier}";
    }

    /**
     * Remember a value in tenant-scoped cache.
     */
    public function remember(string $domain, string $identifier, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->key($domain, $identifier);
        $ttl = $ttl ?? $this->defaultTtl;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Forget all cache keys for a domain within the current tenant/user scope.
     * Uses a tag-like registry approach since not all cache drivers support tags.
     */
    public function invalidateDomain(string $domain, ?int $tenantId = null, ?int $userId = null): void
    {
        $registryKey = $this->registryKey($domain, $tenantId, $userId);
        $keys = Cache::get($registryKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($registryKey);
    }

    /**
     * Invalidate multiple domains at once.
     */
    public function invalidateDomains(array $domains, ?int $tenantId = null, ?int $userId = null): void
    {
        foreach ($domains as $domain) {
            $this->invalidateDomain($domain, $tenantId, $userId);
        }
    }

    /**
     * Remember with automatic registry tracking for invalidation.
     */
    public function tracked(string $domain, string $identifier, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->key($domain, $identifier);
        $ttl = $ttl ?? $this->defaultTtl;

        $this->registerKey($domain, $cacheKey);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Register a cache key under a domain for later invalidation.
     */
    protected function registerKey(string $domain, string $cacheKey): void
    {
        $registryKey = $this->registryKey($domain);
        $keys = Cache::get($registryKey, []);

        if (! in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put($registryKey, $keys, 86400); // Registry lives 24h
        }
    }

    /**
     * Get the registry key for a domain.
     */
    protected function registryKey(string $domain, ?int $tenantId = null, ?int $userId = null): string
    {
        $tenantId = $tenantId ?? $this->currentTenantId();
        $userId = $userId ?? auth()->id();

        return "t:{$tenantId}:u:{$userId}:_registry:{$domain}";
    }

    /**
     * Get the current tenant ID.
     */
    protected function currentTenantId(): ?int
    {
        return auth()->check() ? auth()->user()->current_tenant_id : null;
    }
}
