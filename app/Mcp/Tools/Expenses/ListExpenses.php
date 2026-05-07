<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Expenses;

use App\Mcp\Tools\AbstractTool;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListExpenses extends AbstractTool
{
    protected string $name = 'expenses.list';

    protected string $description = 'List expenses for the authenticated tenant. Supports filters on date range, category, merchant, and amount.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()->description('Inclusive lower bound (YYYY-MM-DD).'),
            'to' => $schema->string()->description('Inclusive upper bound (YYYY-MM-DD).'),
            'category' => $schema->string()->description('Match expense category (substring, case-insensitive).'),
            'merchant' => $schema->string()->description('Match merchant (substring, case-insensitive).'),
            'min_amount' => $schema->number()->description('Minimum amount in the expense currency.'),
            'max_amount' => $schema->number()->description('Maximum amount in the expense currency.'),
            'limit' => $schema->integer()->description('Max rows to return (default 50, max 200).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 50), 1), 200);

        $query = Expense::query()->orderByDesc('expense_date');

        if ($from = $request->get('from')) {
            $query->where('expense_date', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->where('expense_date', '<=', $to);
        }

        if ($category = $request->get('category')) {
            $query->where('category', 'LIKE', '%'.$category.'%');
        }

        if ($merchant = $request->get('merchant')) {
            $query->where('merchant', 'LIKE', '%'.$merchant.'%');
        }

        if (($min = $request->get('min_amount')) !== null) {
            $query->where('amount', '>=', $min);
        }

        if (($max = $request->get('max_amount')) !== null) {
            $query->where('amount', '<=', $max);
        }

        $items = $query->limit($limit)->get([
            'id',
            'expense_date',
            'amount',
            'currency',
            'merchant',
            'category',
            'subcategory',
            'description',
            'payment_method',
            'status',
        ])->map(fn (Expense $e): array => [
            'id' => $e->id,
            'expense_date' => $e->expense_date?->toDateString(),
            'amount' => (float) $e->amount,
            'currency' => $e->currency,
            'merchant' => $e->merchant,
            'category' => $e->category,
            'subcategory' => $e->subcategory,
            'description' => $e->description,
            'payment_method' => $e->payment_method,
            'status' => $e->status,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'limit' => $limit,
            'items' => $items,
        ]);
    }
}
