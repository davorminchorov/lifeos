<?php

namespace App\Expenses\UI\API;

use App\Core\Commands\CommandBus;
use App\Expenses\Commands\SetBudget;
use App\Expenses\Queries\GetBudgetSummary;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BudgetsController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly GetBudgetSummary $getBudgetSummary,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $budgets = $this->getBudgetSummary->handle($categoryId);

        return response()->json(['data' => $budgets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $budgetId = Str::uuid()->toString();

        $command = new SetBudget(
            $budgetId,
            $request->input('category_id'),
            (float) $request->input('amount'),
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('end_date')),
            $request->input('notes'),
        );

        $this->commandBus->dispatch($command);

        return response()->json(['budget_id' => $budgetId], 201);
    }
}
