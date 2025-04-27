<?php

namespace App\Expenses\Projections;

use App\Core\EventSourcing\Projector;
use App\Expenses\Events\ExpenseCategorized;
use App\Expenses\Events\ExpenseRecorded;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseProjector extends Projector
{
    public function handleExpenseRecorded(ExpenseRecorded $event): void
    {
        DB::table('expense_list')->insert([
            'id' => $event->expenseId,
            'amount' => $event->amount,
            'description' => $event->description,
            'category' => $event->category,
            'date' => $event->date,
            'notes' => $event->notes,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // Also update monthly summary projection
        $this->updateMonthlySummary($event->amount, $event->category, $event->date);
    }

    public function handleExpenseCategorized(ExpenseCategorized $event): void
    {
        // Get the original expense
        $expense = DB::table('expense_list')
            ->where('id', $event->expenseId)
            ->first();

        if (!$expense) {
            return;
        }

        // Update the expense with the new category
        DB::table('expense_list')
            ->where('id', $event->expenseId)
            ->update([
                'category' => $event->category,
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);

        // Update monthly summary for both old and new categories
        if ($expense->category !== $event->category) {
            // Deduct from old category count if it existed
            if ($expense->category) {
                $this->updateCategorySummary($expense->category, -$expense->amount, $expense->date);
            }

            // Add to new category
            $this->updateCategorySummary($event->category, $expense->amount, $expense->date);
        }
    }

    private function updateMonthlySummary(float $amount, ?string $category, string $date): void
    {
        $date = Carbon::parse($date);
        $yearMonth = $date->format('Y-m');

        // Check if we already have a record for this month
        $existingSummary = DB::table('expense_monthly_summary')
            ->where('year_month', $yearMonth)
            ->first();

        if ($existingSummary) {
            // Update existing summary
            DB::table('expense_monthly_summary')
                ->where('year_month', $yearMonth)
                ->update([
                    'total_amount' => $existingSummary->total_amount + $amount,
                    'count' => $existingSummary->count + 1,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
        } else {
            // Create new summary
            DB::table('expense_monthly_summary')
                ->insert([
                    'year_month' => $yearMonth,
                    'total_amount' => $amount,
                    'count' => 1,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
        }

        // Update category summary if category is not null
        if ($category) {
            $this->updateCategorySummary($category, $amount, $date);
        }
    }

    private function updateCategorySummary(string $category, float $amount, string $date): void
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;
        $yearMonth = $date->format('Y-m');

        // Check if we already have a record for this category this month
        $existingSummary = DB::table('expense_category_summary')
            ->where('year_month', $yearMonth)
            ->where('category', $category)
            ->first();

        if ($existingSummary) {
            // Update existing summary
            DB::table('expense_category_summary')
                ->where('year_month', $yearMonth)
                ->where('category', $category)
                ->update([
                    'total_amount' => $existingSummary->total_amount + $amount,
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
        } else {
            // Create new summary
            DB::table('expense_category_summary')
                ->insert([
                    'year_month' => $yearMonth,
                    'category' => $category,
                    'total_amount' => $amount,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
        }
    }
}
