<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Budget;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateBudget extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new budget for tracking spending in a category.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->required()->description('Budget category (should match expense categories)'),
            'amount' => $schema->number()->required()->description('Budget amount limit'),
            'budget_period' => $schema->string()->required()->description('One of: weekly, monthly, quarterly, yearly'),
            'start_date' => $schema->string()->description('YYYY-MM-DD, defaults to start of current period'),
            'end_date' => $schema->string()->description('YYYY-MM-DD, defaults to end of current period'),
            'currency' => $schema->string()->description('3-letter code, defaults to MKD'),
            'alert_threshold' => $schema->integer()->description('Alert at this utilization percentage, defaults to 80'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $now = CarbonImmutable::now();
        $period = $request['budget_period'] ?? 'monthly';

        $startDate = $request['start_date'] ?? null;
        $endDate = $request['end_date'] ?? null;

        if ($startDate === null || $endDate === null) {
            [$defaultStart, $defaultEnd] = match ($period) {
                'weekly' => [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()],
                'quarterly' => [$now->firstOfQuarter()->toDateString(), $now->lastOfQuarter()->toDateString()],
                'yearly' => [$now->startOfYear()->toDateString(), $now->endOfYear()->toDateString()],
                default => [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()],
            };
            $startDate = $startDate ?? $defaultStart;
            $endDate = $endDate ?? $defaultEnd;
        }

        $data = [
            'category' => $request['category'] ?? null,
            'amount' => $request['amount'] ?? null,
            'budget_period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'currency' => $request['currency'] ?? 'MKD',
            'alert_threshold' => $request['alert_threshold'] ?? 80,
            'notes' => $request['notes'] ?? null,
        ];

        $validated = $this->validate($data, [
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'budget_period' => 'required|string|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'currency' => 'nullable|string|size:3',
            'alert_threshold' => 'integer|min:1|max:100',
            'notes' => 'nullable|string|max:10000',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        Budget::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'is_active' => true,
            ...$validated,
        ]);

        return sprintf(
            "Created %s budget for '%s': %s %s from %s to %s.",
            $validated['budget_period'],
            $validated['category'],
            number_format((float) $validated['amount'], 2),
            $validated['currency'],
            $validated['start_date'],
            $validated['end_date'],
        );
    }
}
