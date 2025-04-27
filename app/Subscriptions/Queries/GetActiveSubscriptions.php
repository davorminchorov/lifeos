<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Domain\SubscriptionStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetActiveSubscriptions
{
    public function __invoke(): Collection
    {
        return DB::table('subscriptions_read_model')
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->get();
    }
}
