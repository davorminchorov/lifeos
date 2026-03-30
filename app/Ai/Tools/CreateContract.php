<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateContract extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new contract for tracking agreements and their terms.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required()->description('Contract title'),
            'counterparty' => $schema->string()->required()->description('Name of the other party'),
            'contract_type' => $schema->string()->required()->description('Type (e.g. service, employment, lease, subscription, NDA, insurance)'),
            'start_date' => $schema->string()->required()->description('YYYY-MM-DD start date'),
            'end_date' => $schema->string()->description('YYYY-MM-DD end date'),
            'contract_value' => $schema->number()->description('Total contract value'),
            'notice_period_days' => $schema->integer()->description('Notice period in days before termination'),
            'auto_renewal' => $schema->boolean()->description('Whether contract auto-renews, defaults to false'),
            'status' => $schema->string()->description('One of: active, pending. Defaults to active'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $data = [
            'title' => $request['title'] ?? null,
            'counterparty' => $request['counterparty'] ?? null,
            'contract_type' => $request['contract_type'] ?? null,
            'start_date' => $request['start_date'] ?? null,
            'end_date' => $request['end_date'] ?? null,
            'contract_value' => $request['contract_value'] ?? null,
            'notice_period_days' => $request['notice_period_days'] ?? null,
            'auto_renewal' => $request['auto_renewal'] ?? false,
            'status' => $request['status'] ?? 'active',
            'notes' => $request['notes'] ?? null,
        ];

        $validated = $this->validate($data, [
            'title' => 'required|string|max:255',
            'counterparty' => 'required|string|max:255',
            'contract_type' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'nullable|numeric|min:0',
            'notice_period_days' => 'nullable|integer|min:0',
            'auto_renewal' => 'boolean',
            'status' => 'string|in:active,pending',
            'notes' => 'nullable|string|max:10000',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        Contract::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            ...$validated,
        ]);

        $endInfo = $validated['end_date'] ? " to {$validated['end_date']}" : ' (no end date)';
        $valueInfo = $validated['contract_value'] ? ', value: '.number_format((float) $validated['contract_value'], 2) : '';

        return sprintf(
            "Created contract '%s' with %s: %s, %s%s%s.",
            $validated['title'],
            $validated['counterparty'],
            $validated['contract_type'],
            $validated['start_date'],
            $endInfo,
            $valueInfo,
        );
    }
}
