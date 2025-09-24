<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class NotificationDeduplicator
{
    /**
     * Returns true if the notification should be sent (i.e., not sent already for this key),
     * and acquires a short-lived lock to prevent duplicates from concurrent paths.
     *
     * The deduplication key is composed of: user, type, entity, entity id, variant, and current date.
     * A default TTL of 26 hours prevents duplicates for the same calendar day across timezones and worker delays.
     */
    public static function acquire(string $type, int $userId, string $entityType, string|int $entityId, string $variant = '', int $ttlMinutes = 26 * 60): bool
    {
        $today = CarbonImmutable::now()->toDateString();
        $variantPart = $variant !== '' ? ":{$variant}" : '';
        $key = "notify:dedup:{$today}:{$type}:u{$userId}:{$entityType}:{$entityId}{$variantPart}";

        // Cache::add only succeeds if the key does not exist yet
        return Cache::add($key, 1, now()->addMinutes($ttlMinutes));
    }
}
