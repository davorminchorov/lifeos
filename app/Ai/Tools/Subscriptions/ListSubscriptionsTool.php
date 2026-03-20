<?php

namespace App\Ai\Tools\Subscriptions;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Subscription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListSubscriptionsTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'List active subscriptions and their costs. Use when the user asks about their subscriptions, recurring payments, or monthly costs.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: active, paused, cancelled. Default: active'),
        ];
    }

    public function handle(Request $request): string
    {
        $status = $request['status'] ?? 'active';

        $subscriptions = Subscription::where('tenant_id', $this->tenantId())
            ->where('status', $status)
            ->orderBy('next_billing_date')
            ->get();

        if ($subscriptions->isEmpty()) {
            return "No {$status} subscriptions found.";
        }

        $totalMonthly = $subscriptions->sum(fn ($s) => $s->monthly_cost);

        $lines = ['Active Subscriptions:'];

        foreach ($subscriptions as $sub) {
            $amount = $this->formatAmount($sub->cost, $sub->currency);
            $next = $sub->next_billing_date ? $sub->next_billing_date->format('M j') : 'N/A';
            $lines[] = "- {$sub->service_name}: {$amount}/{$sub->billing_cycle} (next: {$next})";
        }

        $lines[] = 'Monthly total: '.$this->formatAmount($totalMonthly);
        $lines[] = 'Yearly total: '.$this->formatAmount($totalMonthly * 12);

        return implode("\n", $lines);
    }
}
