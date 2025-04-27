<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Projections\SubscriptionList;

class GetActiveSubscriptionsCount
{
    public function handle(): int
    {
        return SubscriptionList::where('status', 'active')->count();
    }
}
