<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\QueryHandler;
use App\Subscriptions\Domain\SubscriptionStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetActiveSubscriptionsHandler implements QueryHandler
{
    public function handle(GetActiveSubscriptions $query): Collection
    {
        return DB::table('subscriptions')
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->orderBy('name')
            ->get();
    }
}
