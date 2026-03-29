<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CancelSubscription extends TenantScopedTool
{
    public function description(): string
    {
        return 'Cancel a subscription.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()->description('Name of the subscription to cancel'),
        ];
    }

    public function handle(Request $request): string
    {
        $name = $request->get('name');

        $subscription = $this->scopedQuery(Subscription::class)
            ->where('service_name', 'LIKE', '%'.$name.'%')
            ->first();

        if (! $subscription) {
            $available = $this->scopedQuery(Subscription::class)
                ->where('status', '!=', 'cancelled')
                ->pluck('service_name')
                ->implode(', ');

            return "No subscription found matching '{$name}'. Available subscriptions: {$available}";
        }

        if ($subscription->status === 'cancelled') {
            return "'{$subscription->service_name}' is already cancelled.";
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancellation_date' => CarbonImmutable::now(),
        ]);

        return "Cancelled subscription '{$subscription->service_name}'. It was costing {$subscription->cost} {$subscription->currency} per {$subscription->billing_cycle}.";
    }
}
