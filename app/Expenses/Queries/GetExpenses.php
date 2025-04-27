<?php

namespace App\Expenses\Queries;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetExpenses
{
    public function handle(?string $categoryId = null, ?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10, int $offset = 0): array
    {
        $query = DB::table('expenses');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $total = $query->count();

        $expenses = $query->orderBy('date', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($expense) {
                return (array) $expense;
            })
            ->toArray();

        return [
            'data' => $expenses,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ],
        ];
    }
}
