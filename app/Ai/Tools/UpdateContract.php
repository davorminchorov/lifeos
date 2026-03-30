<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class UpdateContract extends TenantScopedTool
{
    public function description(): string
    {
        return 'Update an existing contract (status, end date, value, or notes).';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required()->description('Contract title to find'),
            'new_status' => $schema->string()->description('New status: active, expired, terminated, pending'),
            'new_end_date' => $schema->string()->description('New end date YYYY-MM-DD'),
            'new_contract_value' => $schema->number()->description('New contract value'),
            'notes' => $schema->string()->description('Updated notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $title = $request['title'] ?? null;

        $matches = $this->scopedQuery(Contract::class)
            ->where('title', 'LIKE', '%'.$title.'%')
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(Contract::class)
                ->pluck('title')
                ->implode(', ');

            return "No contract found matching '{$title}'. Available contracts: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->pluck('title')->implode(', ');

            return "Multiple contracts match '{$title}'. Please be more specific: {$names}";
        }

        $contract = $matches->first();
        $updates = [];
        $changes = [];

        $newStatus = $request['new_status'] ?? null;
        if ($newStatus !== null) {
            $validated = $this->validate(
                ['new_status' => $newStatus],
                ['new_status' => 'string|in:active,expired,terminated,pending'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['status'] = $newStatus;
            $changes[] = "status to {$newStatus}";
        }

        $newEndDate = $request['new_end_date'] ?? null;
        if ($newEndDate !== null) {
            $validated = $this->validate(
                ['new_end_date' => $newEndDate],
                ['new_end_date' => 'date'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['end_date'] = $newEndDate;
            $changes[] = "end date to {$newEndDate}";
        }

        $newValue = $request['new_contract_value'] ?? null;
        if ($newValue !== null) {
            $validated = $this->validate(
                ['new_contract_value' => $newValue],
                ['new_contract_value' => 'numeric|min:0'],
            );
            if (is_string($validated)) {
                return $validated;
            }
            $updates['contract_value'] = $newValue;
            $changes[] = 'value to '.number_format((float) $newValue, 2);
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
            return 'No changes provided. You can update: new_status, new_end_date, new_contract_value, or notes.';
        }

        $contract->update($updates);

        return "Updated contract '{$contract->title}': changed ".implode(', ', $changes).'.';
    }
}
