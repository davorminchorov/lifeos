<?php

namespace App\Subscriptions\Queries;

use App\Subscriptions\Projections\SubscriptionList;
use Illuminate\Support\Facades\DB;

class GetMonthlyCost
{
    public function handle(): float
    {
        $subscriptions = SubscriptionList::where('status', 'active')->get();

        $monthlyCost = 0.0;

        foreach ($subscriptions as $subscription) {
            switch ($subscription->billing_cycle) {
                case 'monthly':
                    $monthlyCost += $subscription->amount;
                    break;
                case 'quarterly':
                    $monthlyCost += $subscription->amount / 3;
                    break;
                case 'annually':
                    $monthlyCost += $subscription->amount / 12;
                    break;
                default:
                    $monthlyCost += $subscription->amount;
            }
        }

        return round($monthlyCost, 2);
    }
}
