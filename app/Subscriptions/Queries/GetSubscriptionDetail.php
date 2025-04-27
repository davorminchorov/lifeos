<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\Query;

class GetSubscriptionDetail implements Query
{
    public function __construct(
        public readonly string $subscriptionId
    ) {}
}
