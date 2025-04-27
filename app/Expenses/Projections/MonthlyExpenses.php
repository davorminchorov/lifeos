<?php

namespace App\Expenses\Projections;

use App\Core\Projections\Projection;
use App\Expenses\Events\ExpenseRecorded;
use Illuminate\Support\Facades\DB;

class MonthlyExpenses extends Projection
{
    public function __construct()
    {
        $this->handlesEvents([
            ExpenseRecorded::class => 'onExpenseRecorded',
        ]);
    }

    protected function onExpenseRecorded(ExpenseRecorded $event): void
    {
        $month = $event->date->format('Y-m');

        DB::table('monthly_expenses')
            ->updateOrInsert([
                'month' => $month,
            ], [
                'total_amount' => DB::raw('COALESCE(total_amount, 0) + ' . $event->amount),
                'expense_count' => DB::raw('COALESCE(expense_count, 0) + 1'),
                'updated_at' => now(),
            ]);
    }

    public static function getTables(): array
    {
        return [
            'monthly_expenses' => [
                'month' => 'string',
                'total_amount' => 'float',
                'expense_count' => 'integer',
                'updated_at' => 'datetime',
            ],
        ];
    }
}
