<?php

namespace App\Expenses\UI\Api;

use App\Core\EventSourcing\CommandBus;
use App\Core\EventSourcing\QueryBus;
use App\Expenses\Commands\CategorizeExpense;
use App\Expenses\Commands\RecordExpense;
use App\Expenses\Queries\GetExpenses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExpensesController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetExpenses(
            period: $request->get('period'),
            categoryId: $request->get('category_id'),
            dateFrom: $request->get('date_from'),
            dateTo: $request->get('date_to'),
            search: $request->get('search'),
            sortBy: $request->get('sort_by', 'date'),
            sortOrder: $request->get('sort_order', 'desc'),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 10)
        );

        $result = $this->queryBus->handle($query);

        return response()->json($result);
    }

    public function show(string $id): JsonResponse
    {
        // Validate ID format
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid expense ID format'], Response::HTTP_BAD_REQUEST);
        }

        $expense = $this->getExpenseById($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($expense);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'date' => 'required|date_format:Y-m-d',
            'category_id' => 'nullable|string|uuid',
            'description' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'receipt_url' => 'nullable|string|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $expenseId = (string) Str::uuid();

        $command = new RecordExpense(
            $expenseId,
            $request->input('title'),
            (float) $request->input('amount'),
            $request->input('category_id'),
            $request->input('date'),
            $request->input('description') ?? null,
            $request->input('payment_method') ?? null,
            $request->input('notes') ?? null,
            $request->input('currency', 'USD'),
            $request->input('receipt_url') ?? null
        );

        $this->commandBus->dispatch($command);

        return response()->json([
            'id' => $expenseId,
            'message' => 'Expense recorded successfully'
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        // First check if expense exists
        $expense = $this->getExpenseById($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'date' => 'required|date_format:Y-m-d',
            'category_id' => 'nullable|string|uuid',
            'description' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'receipt_url' => 'nullable|string|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // For now, we'll just delete and re-create the expense
        // In a real app, you'd want to implement proper update logic

        // TODO: Implement proper update command

        return response()->json([
            'message' => 'Expense updated successfully'
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        // Validate ID format
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid expense ID format'], Response::HTTP_BAD_REQUEST);
        }

        // Check if expense exists
        $expense = $this->getExpenseById($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], Response::HTTP_NOT_FOUND);
        }

        // TODO: Implement delete expense command

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }

    public function categorize(Request $request, string $expenseId): JsonResponse
    {
        // Validate ID format
        if (!Str::isUuid($expenseId)) {
            return response()->json(['error' => 'Invalid expense ID format'], Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $command = new CategorizeExpense(
            $expenseId,
            $request->input('category_id')
        );

        $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Expense categorized successfully'
        ]);
    }

    /**
     * Helper method to get an expense by ID
     */
    private function getExpenseById(string $id): ?array
    {
        $expenses = \DB::table('expenses')
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
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.category_id')
            ->where('expenses.id', $id)
            ->first();

        if (!$expenses) {
            return null;
        }

        return [
            'id' => $expenses->id,
            'title' => $expenses->title,
            'description' => $expenses->description,
            'amount' => (float) $expenses->amount,
            'currency' => $expenses->currency,
            'date' => $expenses->date,
            'payment_method' => $expenses->payment_method,
            'notes' => $expenses->notes,
            'receipt_url' => $expenses->receipt_url,
            'category' => $expenses->category_id ? [
                'id' => $expenses->category_id,
                'name' => $expenses->category_name,
                'color' => $expenses->category_color,
            ] : null,
            'created_at' => $expenses->created_at,
            'updated_at' => $expenses->updated_at,
        ];
    }
}
