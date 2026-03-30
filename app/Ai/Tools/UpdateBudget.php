<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Budget;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class UpdateBudget extends TenantScopedTool
{
    public function description(): string
    {
        return 'Update an existing budget (amount, threshold, active status, or notes).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->required()->description('Budget category to find'),
            'new_amount' => $schema->number()->description('New budget amount'),
            'new_alert_threshold' => $schema->integer()->description('New alert threshold percentage (1-100)'),
            'is_active' => $schema->boolean()->description('Set active or inactive'),
            'notes' => $schema->string()->description('Updated notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $category = $request['category'] ?? null;

        $matches = $this->scopedQuery(Budget::class)
            ->where('category', 'LIKE', '%'.$category.'%')
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(Budget::class)
                ->pluck('category')
                ->implode(', ');

            return "No budget found matching '{$category}'. Available budgets: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->pluck('category')->implode(', ');

            return "Multiple budgets match '{$category}'. Please be more specific: {$names}";
        }

        $budget = $matches->first();
        $updates = [];
        $changes = [];

        $newAmount = $request['new_amount'] ?? null;
        if ($newAmount !== null) {
            $validated = $this->validate(
                ['new_amount' => $newAmount],
                ['new_amount' => 'numeric|min:0.01|max:99999999'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['amount'] = $newAmount;
            $changes[] = "amount to {$newAmount}";
        }

        $newThreshold = $request['new_alert_threshold'] ?? null;
        if ($newThreshold !== null) {
            $validated = $this->validate(
                ['new_alert_threshold' => $newThreshold],
                ['new_alert_threshold' => 'integer|min:1|max:100'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['alert_threshold'] = $newThreshold;
            $changes[] = "alert threshold to {$newThreshold}%";
        }

        $isActive = $request['is_active'] ?? null;
        if ($isActive !== null) {
            $updates['is_active'] = (bool) $isActive;
            $changes[] = $isActive ? 'activated' : 'deactivated';
        }

        $notes = $request['notes'] ?? null;
        if ($notes !== null) {
            $validated = $this->validate(
                ['notes' => $notes],
                ['notes' => 'nullable|string|max:10000'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['notes'] = $notes;
            $changes[] = 'notes';
        }

        if ($updates === []) {
            return 'No changes provided. You can update: new_amount, new_alert_threshold, is_active, or notes.';
        }

        $budget->update($updates);

        return "Updated budget '{$budget->category}': changed ".implode(', ', $changes).'.';
    }
}
