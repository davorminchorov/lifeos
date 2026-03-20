<?php

namespace App\Ai\Tools\Subscriptions;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Subscription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddSubscriptionTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Add a new subscription. Use when the user mentions a recurring service payment like Netflix, Spotify, gym membership, etc.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'service_name' => $schema->string()->description('Name of the subscription service (e.g. Netflix, Spotify, GitHub)')->required(),
            'cost' => $schema->number()->description('Cost per billing cycle as a decimal')->required(),
            'currency' => $schema->string()->description('Currency code (default: MKD)'),
            'billing_cycle' => $schema->string()->description('Billing cycle: monthly, yearly, weekly')->required(),
            'category' => $schema->string()->description('Category: streaming, music, software, fitness, cloud, gaming, news, other'),
            'description' => $schema->string()->description('Optional description'),
        ];
    }

    public function handle(Request $request): string
    {
        $subscription = Subscription::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => $this->userId(),
            'service_name' => $request['service_name'],
            'cost' => $request['cost'],
            'currency' => $request['currency'] ?? $this->defaultCurrency(),
            'billing_cycle' => $request['billing_cycle'],
            'category' => $request['category'] ?? 'other',
            'description' => $request['description'] ?? null,
            'start_date' => now()->toDateString(),
            'next_billing_date' => now()->toDateString(),
            'auto_renewal' => true,
            'status' => 'active',
        ]);

        $formatted = $this->formatAmount($subscription->cost, $subscription->currency);

        return "Subscription created: {$subscription->service_name} — {$formatted}/{$subscription->billing_cycle}";
    }
}
