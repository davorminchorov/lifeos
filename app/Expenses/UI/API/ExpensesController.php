<?php

namespace App\Expenses\UI\API;

use App\Core\Commands\CommandBus;
use App\Expenses\Commands\CategorizeExpense;
use App\Expenses\Commands\RecordExpense;
use App\Expenses\Queries\GetExpenses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExpensesController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly GetExpenses $getExpenses,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : null;
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : null;
        $limit = (int) $request->query('limit', 10);
        $offset = (int) $request->query('offset', 0);

        $expenses = $this->getExpenses->handle(
            $categoryId,
            $startDate,
            $endDate,
            $limit,
            $offset
        );

        return response()->json($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category_id' => 'nullable|string',
            'date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expenseId = Str::uuid()->toString();

        $command = new RecordExpense(
            $expenseId,
            $request->input('description'),
            (float) $request->input('amount'),
            $request->input('category_id'),
            $request->has('date') ? Carbon::parse($request->input('date')) : null,
            $request->input('notes'),
        );

        $this->commandBus->dispatch($command);

        return response()->json(['expense_id' => $expenseId], 201);
    }

    public function categorize(Request $request, string $expenseId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $command = new CategorizeExpense(
            $expenseId,
            $request->input('category_id'),
        );

        $this->commandBus->dispatch($command);

        return response()->json(['message' => 'Expense categorized successfully']);
    }
}
