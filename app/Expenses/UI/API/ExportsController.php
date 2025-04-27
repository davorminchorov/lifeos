<?php

namespace App\Expenses\UI\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExportsController
{
    public function exportExpenses(Request $request): Response
    {
        // Get query parameters
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : null;
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : null;
        $categoryId = $request->query('category_id');

        // Build query
        $query = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.category_id')
            ->select([
                'expenses.expense_id',
                'expenses.description',
                'expenses.amount',
                'expense_categories.name as category_name',
                'expenses.date',
                'expenses.notes',
                'expenses.created_at',
            ]);

        if ($startDate) {
            $query->where('expenses.date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('expenses.date', '<=', $endDate);
        }

        if ($categoryId) {
            $query->where('expenses.category_id', $categoryId);
        }

        $expenses = $query->orderBy('expenses.date', 'desc')->get();

        // Create CSV content
        $headers = [
            'ID',
            'Description',
            'Amount',
            'Category',
            'Date',
            'Notes',
            'Created At',
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($expenses as $expense) {
            $row = [
                $expense->expense_id,
                '"' . str_replace('"', '""', $expense->description) . '"',
                $expense->amount,
                '"' . str_replace('"', '""', $expense->category_name ?? 'Uncategorized') . '"',
                $expense->date,
                '"' . str_replace('"', '""', $expense->notes ?? '') . '"',
                $expense->created_at,
            ];
            $csvContent .= implode(',', $row) . "\n";
        }

        // Set filename
        $fileName = 'expenses_export_' . Carbon::now()->format('Y-m-d') . '.csv';

        // Return response
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$fileName\"");
    }
}
