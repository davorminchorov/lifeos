<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Projections\SubscriptionList;
use Illuminate\Support\Facades\DB;

class GetTotalSubscriptionsCount
{
    public function handle(): int
    {
        return SubscriptionList::count();
    }
}
