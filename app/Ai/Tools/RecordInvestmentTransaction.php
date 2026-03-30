<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class RecordInvestmentTransaction extends TenantScopedTool
{
    public function description(): string
    {
        return 'Record a buy, sell, or other transaction for an investment.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'investment_name' => $schema->string()->required()->description('Investment name or symbol to find'),
            'transaction_type' => $schema->string()->required()->description('One of: buy, sell, dividend_reinvestment, transfer_in, transfer_out'),
            'quantity' => $schema->number()->required()->description('Number of shares or units'),
            'price_per_share' => $schema->number()->required()->description('Price per share or unit'),
            'fees' => $schema->number()->description('Transaction fees, defaults to 0'),
            'transaction_date' => $schema->string()->description('YYYY-MM-DD, defaults to today'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $name = $request['investment_name'] ?? null;

        $matches = $this->scopedQuery(Investment::class)
            ->where(function ($q) use ($name) {
                $q->where('name', 'LIKE', '%'.$name.'%')
                    ->orWhere('symbol_identifier', 'LIKE', '%'.$name.'%');
            })
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(Investment::class)
                ->pluck('name')
                ->implode(', ');

            return "No investment found matching '{$name}'. Available investments: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->map(fn (Investment $i) => $i->symbol_identifier ? "{$i->name} ({$i->symbol_identifier})" : $i->name)->implode(', ');

            return "Multiple investments match '{$name}'. Please be more specific: {$names}";
        }

        $investment = $matches->first();

        $transactionDate = $request['transaction_date'] ?? date('Y-m-d');
        $fees = $request['fees'] ?? 0;
        $quantity = $request['quantity'] ?? null;
        $pricePerShare = $request['price_per_share'] ?? null;

        $data = [
            'transaction_type' => $request['transaction_type'] ?? null,
            'quantity' => $quantity,
            'price_per_share' => $pricePerShare,
            'fees' => $fees,
            'transaction_date' => $transactionDate,
            'notes' => $request['notes'] ?? null,
        ];

        $validated = $this->validate($data, [
            'transaction_type' => 'required|string|in:buy,sell,dividend_reinvestment,transfer_in,transfer_out',
            'quantity' => 'required|numeric|min:0.00000001',
            'price_per_share' => 'required|numeric|min:0',
            'fees' => 'numeric|min:0',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:10000',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        $totalAmount = (float) $validated['quantity'] * (float) $validated['price_per_share'];

        InvestmentTransaction::create([
            'tenant_id' => $this->tenantId,
            'investment_id' => $investment->id,
            'transaction_type' => $validated['transaction_type'],
            'quantity' => $validated['quantity'],
            'price_per_share' => $validated['price_per_share'],
            'total_amount' => $totalAmount,
            'fees' => $validated['fees'],
            'transaction_date' => $validated['transaction_date'],
            'currency' => $investment->currency ?? 'MKD',
            'notes' => $validated['notes'],
        ]);

        $investmentName = $investment->symbol_identifier
            ? "{$investment->name} ({$investment->symbol_identifier})"
            : $investment->name;

        return sprintf(
            'Recorded %s: %s shares of %s at %s (%s total, %s fees) on %s.',
            $validated['transaction_type'],
            number_format((float) $validated['quantity'], 4),
            $investmentName,
            number_format((float) $validated['price_per_share'], 2),
            number_format($totalAmount, 2),
            number_format((float) $validated['fees'], 2),
            $validated['transaction_date'],
        );
    }
}
