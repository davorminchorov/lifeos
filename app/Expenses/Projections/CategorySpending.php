<?php

namespace App\Expenses\Projections;

use App\Core\Projections\Projection;
use App\Expenses\Events\ExpenseCategorized;
use App\Expenses\Events\ExpenseRecorded;
use Illuminate\Support\Facades\DB;

class CategorySpending extends Projection
{
    public function __construct()
    {
        $this->handlesEvents([
            ExpenseRecorded::class => 'onExpenseRecorded',
            ExpenseCategorized::class => 'onExpenseCategorized',
        ]);
    }

    protected function onExpenseRecorded(ExpenseRecorded $event): void
    {
        if (!$event->categoryId) {
            return;
        }

        $this->updateCategorySpending($event->categoryId, $event->amount);
    }

    protected function onExpenseCategorized(ExpenseCategorized $event): void
    {
        // Get the expense details
        $expense = DB::table('expenses')->where('expense_id', $event->expenseId)->first();

        if (!$expense) {
            return;
        }

        // If there was a previous category, reduce its total
        if ($event->previousCategoryId) {
            $this->updateCategorySpending($event->previousCategoryId, -$expense->amount);
        }

        // Add to the new category
        $this->updateCategorySpending($event->categoryId, $expense->amount);
    }

    private function updateCategorySpending(string $categoryId, float $amount): void
    {
        DB::table('category_spending')
            ->updateOrInsert([
                'category_id' => $categoryId,
            ], [
                'total_amount' => DB::raw('COALESCE(total_amount, 0) + ' . $amount),
                'updated_at' => now(),
            ]);
    }

    public static function getTables(): array
    {
        return [
            'category_spending' => [
                'category_id' => 'string',
                'total_amount' => 'float',
                'updated_at' => 'datetime',
            ],
        ];
    }
}
