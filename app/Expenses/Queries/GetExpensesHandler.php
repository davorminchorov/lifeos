<?php

namespace App\Expenses\Queries;

use App\Core\EventSourcing\QueryHandler;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetExpensesHandler implements QueryHandler
{
    public function handle(GetExpenses $query): array
    {
        $expensesQuery = DB::table('expenses')
            ->select([
                'expenses.id',
                'expenses.title',
                'expenses.description',
                'expenses.amount',
                'expenses.currency',
                'expenses.date',
                'expenses.payment_method',
                'expenses.notes',
                'expenses.receipt_url',
                'expenses.category_id',
                'expenses.created_at',
                'expenses.updated_at',
                'expense_categories.name as category_name',
                'expense_categories.color as category_color',
            ])
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.category_id');

        // Apply filters
        if ($query->categoryId) {
            $expensesQuery->where('expenses.category_id', $query->categoryId);
        }

        if ($query->search) {
            $searchTerm = "%{$query->search}%";
            $expensesQuery->where(function ($q) use ($searchTerm) {
                $q->where('expenses.title', 'LIKE', $searchTerm)
                  ->orWhere('expenses.description', 'LIKE', $searchTerm)
                  ->orWhere('expense_categories.name', 'LIKE', $searchTerm);
            });
        }

        if ($query->dateFrom) {
            $expensesQuery->where('expenses.date', '>=', $query->dateFrom);
        }

        if ($query->dateTo) {
            $expensesQuery->where('expenses.date', '<=', $query->dateTo);
        }

        // If no explicit date range is provided but period is, calculate date range based on period
        if (!$query->dateFrom && !$query->dateTo && $query->period) {
            $now = Carbon::now();
            $startDate = null;
            $endDate = $now->format('Y-m-d');

            switch ($query->period) {
                case 'week':
                    $startDate = $now->copy()->subWeek()->format('Y-m-d');
                    break;
                case 'month':
                    $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                    break;
                case 'quarter':
                    $startDate = $now->copy()->subMonths(3)->format('Y-m-d');
                    break;
                case 'year':
                    $startDate = $now->copy()->subYear()->format('Y-m-d');
                    break;
                case 'all':
                    // No date filtering needed
                    break;
                default:
                    // Default to month if invalid period
                    $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            }

            if ($startDate) {
                $expensesQuery->where('expenses.date', '>=', $startDate);
            }
            if ($endDate && $query->period !== 'all') {
                $expensesQuery->where('expenses.date', '<=', $endDate);
            }
        }

        // Get the count before pagination
        $total = $expensesQuery->count();

        // Apply sorting
        $sortBy = $query->sortBy ?? 'date';
        $sortOrder = $query->sortOrder ?? 'desc';
        $expensesQuery->orderBy("expenses.{$sortBy}", $sortOrder);

        // If sorting by category name, add a secondary sort
        if ($sortBy === 'category_id') {
            $expensesQuery->orderBy('expense_categories.name', $sortOrder);
        }

        // Apply pagination
        $perPage = $query->perPage ?? 10;
        $currentPage = $query->page ?? 1;
        $expenses = $expensesQuery
            ->skip(($currentPage - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format expenses data for frontend
        $formattedExpenses = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'title' => $expense->title,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'currency' => $expense->currency,
                'date' => $expense->date,
                'payment_method' => $expense->payment_method,
                'notes' => $expense->notes,
                'receipt_url' => $expense->receipt_url,
                'category' => $expense->category_id ? [
                    'id' => $expense->category_id,
                    'name' => $expense->category_name,
                    'color' => $expense->category_color,
                ] : null,
                'created_at' => $expense->created_at,
                'updated_at' => $expense->updated_at,
            ];
        })->toArray();

        return [
            'data' => $formattedExpenses,
            'meta' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ]
        ];
    }
}
