<?php

namespace App\Expenses\Queries;

use Illuminate\Support\Facades\DB;

class GetCategorySpending
{
    public function handle(): array
    {
        $categories = DB::table('category_spending')
            ->join('expense_categories', 'category_spending.category_id', '=', 'expense_categories.category_id')
            ->select([
                'category_spending.category_id',
                'expense_categories.name',
                'category_spending.total_amount',
            ])
            ->orderBy('category_spending.total_amount', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'total_amount' => $category->total_amount,
                ];
            })
            ->toArray();

        return $categories;
    }
}
