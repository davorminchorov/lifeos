<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Ai\Tools\Request;

class CreateExpense extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new expense record for tracking spending.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'amount' => $schema->number()->required()->description('Expense amount'),
            'category' => $schema->string()->required()->description('Expense category (e.g. food, transport, utilities)'),
            'merchant' => $schema->string()->description('Store or vendor name'),
            'description' => $schema->string()->description('Description of the expense'),
            'date' => $schema->string()->description('YYYY-MM-DD format, defaults to today'),
            'currency' => $schema->string()->description('3-letter code, defaults to MKD'),
        ];
    }

    public function handle(Request $request): string
    {
        $date = $request['date'] ?? date('Y-m-d');
        $currency = $request['currency'] ?? 'MKD';
        $merchant = $request['merchant'] ?? null;
        $description = $request['description'] ?? $merchant;

        $data = [
            'amount' => $request['amount'] ?? null,
            'currency' => $currency,
            'category' => $request['category'] ?? null,
            'expense_date' => $date,
            'description' => $description,
            'merchant' => $merchant,
        ];

        $rules = Arr::only([
            'amount' => 'required|numeric|min:1|max:99999999',
            'currency' => 'nullable|string|size:3',
            'category' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'description' => 'required|string|max:65535',
            'merchant' => 'nullable|string|max:255',
        ], array_keys($data));

        $validated = $this->validate($data, $rules);

        if (is_string($validated)) {
            return $validated;
        }

        Expense::create([
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            ...$validated,
        ]);

        $merchantDisplay = $validated['merchant'] ?? 'Unknown';

        return "Created expense: {$validated['amount']} {$validated['currency']} at {$merchantDisplay} ({$validated['category']}) on {$validated['expense_date']}";
    }
}
