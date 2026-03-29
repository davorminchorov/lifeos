<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Subscription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QuerySubscriptions extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and list subscriptions with optional filters for name, status, or cost.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Filter by subscription name'),
            'status' => $schema->string()->description('Filter by status: active, paused, or cancelled'),
            'min_cost' => $schema->number()->description('Minimum cost'),
            'max_cost' => $schema->number()->description('Maximum cost'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Subscription::class);

        $name = $request['name'] ?? null;
        if ($name !== null) {
            $query->where('service_name', 'LIKE', '%'.$name.'%');
        }

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', $status);
        }

        $minCost = $request['min_cost'] ?? null;
        if ($minCost !== null) {
            $query->where('cost', '>=', $minCost);
        }

        $maxCost = $request['max_cost'] ?? null;
        if ($maxCost !== null) {
            $query->where('cost', '<=', $maxCost);
        }

        $subscriptions = $query->orderBy('service_name')->get();

        if ($subscriptions->isEmpty()) {
            return 'No subscriptions found matching your criteria.';
        }

        $lines = $subscriptions->map(
            fn (Subscription $s): string => sprintf(
                '- %s: %s %s/%s [%s]%s',
                $s->service_name,
                number_format((float) $s->cost, 2),
                $s->currency ?? 'MKD',
                $s->billing_cycle ?? 'month',
                $s->status,
                $s->next_billing_date ? ' — next billing: '.$s->next_billing_date->format('Y-m-d') : '',
            ),
        );

        $totalMonthlyCost = $subscriptions
            ->where('status', 'active')
            ->sum(fn (Subscription $s): float => (float) $s->monthly_cost);

        return "Found {$subscriptions->count()} subscriptions:\n"
            .$lines->implode("\n")
            ."\nTotal active monthly cost: ".number_format($totalMonthlyCost, 2);
    }
}
