<?php

namespace App\Expenses\Queries;

use Illuminate\Support\Facades\DB;

class GetCategoryDistribution
{
    public function handle(string $period = 'all'): array
    {
        $query = DB::table('category_spending')
            ->join('expense_categories', 'category_spending.category_id', '=', 'expense_categories.category_id')
            ->select([
                'expense_categories.category_id',
                'expense_categories.name',
                'expense_categories.color',
                'category_spending.total_amount',
            ]);

        // Filter by period if needed
        if ($period !== 'all') {
            // Implementation for period filtering would go here
            // This would require modifying the category_spending projection to track time periods
        }

        $categories = $query->orderBy('category_spending.total_amount', 'desc')
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'color' => $category->color ?: '#3b82f6', // Default color if not set
                    'total_amount' => $category->total_amount,
                ];
            })
            ->toArray();

        // Calculate total amount to determine percentages
        $totalAmount = array_reduce($categories, function ($carry, $item) {
            return $carry + $item['total_amount'];
        }, 0);

        // Add percentage to each category
        foreach ($categories as &$category) {
            $category['percentage'] = $totalAmount > 0
                ? round(($category['total_amount'] / $totalAmount) * 100, 1)
                : 0;
        }

        return $categories;
    }
}
