<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Subscription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class UpdateSubscription extends TenantScopedTool
{
    public function description(): string
    {
        return 'Update an existing subscription (cost, status, or notes).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()->description('Subscription name to find'),
            'new_cost' => $schema->number()->description('New cost amount'),
            'new_status' => $schema->string()->description('New status: active, paused, or cancelled'),
            'notes' => $schema->string()->description('Updated notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $name = $request['name'];

        $subscription = $this->scopedQuery(Subscription::class)
            ->where('service_name', 'LIKE', '%'.$name.'%')
            ->first();

        if (! $subscription) {
            $available = $this->scopedQuery(Subscription::class)
                ->pluck('service_name')
                ->implode(', ');

            return "No subscription found matching '{$name}'. Available subscriptions: {$available}";
        }

        $updates = [];
        $changes = [];

        $newCost = $request['new_cost'];
        if ($newCost !== null) {
            $validated = $this->validate(
                ['new_cost' => $newCost],
                ['new_cost' => 'numeric|min:0|max:99999999'],
            );

            if (is_string($validated)) {
                return $validated;
            }

            $updates['cost'] = $newCost;
            $changes[] = "cost to {$newCost}";
        }

        $newStatus = $request['new_status'];
        if ($newStatus !== null) {
            $validated = $this->validate(
                ['new_status' => $newStatus],
                ['new_status' => 'string|in:active,paused,cancelled'],
            );

            if (is_string($validated)) {
                return $validated;
            }

            $updates['status'] = $newStatus;
            $changes[] = "status to {$newStatus}";
        }

        $notes = $request['notes'];
        if ($notes !== null) {
            $updates['notes'] = $notes;
            $changes[] = 'notes';
        }

        if ($updates === []) {
            return 'No changes provided. You can update: new_cost, new_status, or notes.';
        }

        $subscription->update($updates);

        return "Updated '{$subscription->service_name}': changed ".implode(', ', $changes).'.';
    }
}
