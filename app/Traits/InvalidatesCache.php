<?php

namespace App\Traits;

use App\Services\TenantCacheService;

/**
 * Add to any model to automatically invalidate related cache domains
 * when the model is created, updated, or deleted.
 *
 * Models should define a $cacheDomains property listing which cache
 * domains to invalidate, e.g.: protected array $cacheDomains = ['dashboard', 'expenses'];
 */
trait InvalidatesCache
{
    protected static function bootInvalidatesCache(): void
    {
        $events = ['created', 'updated', 'deleted'];

        foreach ($events as $event) {
            static::$event(function ($model) {
                $model->bustCache();
            });
        }
    }

    /**
     * Invalidate all cache domains associated with this model.
     */
    public function bustCache(): void
    {
        $domains = $this->getCacheDomains();

        if (empty($domains)) {
            return;
        }

        $cache = app(TenantCacheService::class);
        $tenantId = $this->tenant_id ?? null;
        $userId = $this->user_id ?? null;

        $cache->invalidateDomains($domains, $tenantId, $userId);
    }

    /**
     * Get the cache domains this model invalidates.
     */
    protected function getCacheDomains(): array
    {
        return $this->cacheDomains ?? [];
    }
}
