<?php

namespace App\Subscriptions\Domain;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::CANCELLED => 'Cancelled',
            self::PAUSED => 'Paused',
        };
    }
}
