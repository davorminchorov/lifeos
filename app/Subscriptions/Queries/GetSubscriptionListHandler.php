<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\QueryHandler;
use Illuminate\Support\Facades\DB;

class GetSubscriptionListHandler implements QueryHandler
{
    public function handle(GetSubscriptionList $query): array
    {
        $subscriptionsQuery = DB::table('subscriptions');

        // Apply filters
        if ($query->status) {
            $subscriptionsQuery->where('status', $query->status);
        }

        if ($query->category) {
            $subscriptionsQuery->where('category', $query->category);
        }

        if ($query->search) {
            $searchTerm = "%{$query->search}%";
            $subscriptionsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                    ->orWhere('description', 'LIKE', $searchTerm);
            });
        }

        // Get the count before pagination
        $total = $subscriptionsQuery->count();

        // Apply sorting
        $subscriptionsQuery->orderBy($query->sortBy, $query->sortDirection);

        // Apply pagination
        $subscriptions = $subscriptionsQuery
            ->skip(($query->page - 1) * $query->perPage)
            ->take($query->perPage)
            ->get();

        // Get associated upcoming payments
        $subscriptionIds = $subscriptions->pluck('id')->toArray();
        $upcomingPayments = DB::table('upcoming_payments')
            ->whereIn('subscription_id', $subscriptionIds)
            ->get()
            ->keyBy('subscription_id');

        // Format subscriptions with next payment date
        $formattedSubscriptions = $subscriptions->map(function ($subscription) use ($upcomingPayments) {
            $upcomingPayment = $upcomingPayments->get($subscription->id);

            return [
                'id' => $subscription->id,
                'name' => $subscription->name,
                'description' => $subscription->description,
                'amount' => (float) $subscription->amount,
                'currency' => $subscription->currency,
                'billing_cycle' => $subscription->billing_cycle,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'status' => $subscription->status,
                'website' => $subscription->website,
                'category' => $subscription->category,
                'next_payment_date' => $upcomingPayment ? $upcomingPayment->expected_date : null,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
            ];
        })->toArray();

        return [
            'data' => $formattedSubscriptions,
            'meta' => [
                'current_page' => $query->page,
                'per_page' => $query->perPage,
                'total' => $total,
                'last_page' => ceil($total / $query->perPage),
            ],
        ];
    }
}
