<?php

namespace App\Expenses\UI\Api;

use App\Expenses\Commands\CategorizeExpense;
use App\Expenses\Commands\RecordExpense;
use App\Expenses\Queries\GetExpenses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExpensesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $period = $request->query('period', 'month');
        $category = $request->query('category');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $expenses = app(GetExpenses::class)->handle($period, $category, $startDate, $endDate);

        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expenseId = (string) Str::uuid();

        $command = new RecordExpense(
            $expenseId,
            $request->amount,
            $request->description,
            $request->category,
            $request->date,
            $request->notes ?? null
        );

        $this->dispatchCommand($command);

        return response()->json([
            'id' => $expenseId,
            'message' => 'Expense recorded successfully'
        ], 201);
    }

    public function categorize(Request $request, string $expenseId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $command = new CategorizeExpense(
            $expenseId,
            $request->category
        );

        $this->dispatchCommand($command);

        return response()->json([
            'message' => 'Expense categorized successfully'
        ]);
    }

    protected function dispatchCommand($command): void
    {
        // This would use your command bus implementation
        // For example, with Laravel's default dispatcher:
        dispatch($command);
    }
}
