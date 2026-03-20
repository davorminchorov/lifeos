<?php

namespace App\Ai\Tools\Expenses;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddExpenseTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Add a new expense entry. Use this when the user mentions spending money, buying something, or paying for something.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'merchant' => $schema->string()->description('The merchant or store name (e.g. Vero, Ramstor, Uber, Netflix)')->required(),
            'amount' => $schema->number()->description('The amount spent as a decimal (e.g. 2800.00)')->required(),
            'currency' => $schema->string()->description('Currency code (default: MKD). Common: MKD, EUR, USD'),
            'category' => $schema->string()->description('Expense category: groceries, dining, transport, entertainment, utilities, health, shopping, education, travel, other')->required(),
            'description' => $schema->string()->description('Optional description or notes'),
            'expense_date' => $schema->string()->description('Date in Y-m-d format. Defaults to today.'),
            'payment_method' => $schema->string()->description('Payment method: cash, card, bank_transfer, other'),
        ];
    }

    public function handle(Request $request): string
    {
        $expense = Expense::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => $this->userId(),
            'merchant' => $request['merchant'],
            'amount' => $request['amount'],
            'currency' => $request['currency'] ?? $this->defaultCurrency(),
            'category' => $request['category'],
            'description' => $request['description'] ?? null,
            'expense_date' => $request['expense_date'] ?? now()->toDateString(),
            'payment_method' => $request['payment_method'] ?? null,
            'expense_type' => 'personal',
            'status' => 'completed',
        ]);

        $formatted = $this->formatAmount($expense->amount, $expense->currency);

        return "Expense created: {$expense->merchant} — {$formatted} ({$expense->category}) on {$expense->expense_date->format('M j, Y')}";
    }
}
