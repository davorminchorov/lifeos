<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\Query;
use App\Subscriptions\Domain\SubscriptionStatus;

class GetActiveSubscriptions implements Query
{
    public function __construct()
    {
        // No parameters needed for this query
    }
}
