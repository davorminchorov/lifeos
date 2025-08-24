<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Expense;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of budgets.
     */
    public function index(Request $request)
    {
        $query = Budget::where('user_id', auth()->id())
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('budget_period', $request->period);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $budgets = $query->paginate(15);

        // Get expense categories for filter dropdown
        $categories = Expense::where('user_id', auth()->id())
            ->distinct()
            ->pluck('category')
            ->sort();

        // Calculate summary statistics
        $summaryStats = $this->calculateSummaryStats();

        return view('budgets.index', compact('budgets', 'categories', 'summaryStats'));
    }

    /**
     * Show the form for creating a new budget.
     */
    public function create()
    {
        // Get expense categories from user's existing expenses
        $categories = Expense::where('user_id', auth()->id())
            ->distinct()
            ->pluck('category')
            ->sort();

        // Get currency options
        $currencies = $this->currencyService->getCurrencyOptions();

        return view('budgets.create', compact('categories', 'currencies'));
    }

    /**
     * Store a newly created budget in storage.
     */
    public function store(StoreBudgetRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        // Set dates based on period if not custom
        if ($validated['budget_period'] !== 'custom') {
            $dates = $this->calculatePeriodDates($validated['budget_period']);
            $validated['start_date'] = $dates['start_date'];
            $validated['end_date'] = $dates['end_date'];
        }

        $budget = Budget::create($validated);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    /**
     * Display the specified budget.
     */
    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);

        // Get detailed spending for this budget period
        $expenses = $budget->user->expenses()
            ->where('category', $budget->category)
            ->whereBetween('expense_date', [$budget->start_date, $budget->end_date])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Calculate daily spending trends
        $dailySpending = $expenses->groupBy(function ($expense) {
            return $expense->expense_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        })->sortKeys();

        // Calculate projected spending
        $daysElapsed = now()->diffInDays($budget->start_date) + 1;
        $totalDays = $budget->start_date->diffInDays($budget->end_date) + 1;
        $projectedSpending = $daysElapsed > 0 ?
            ($budget->getCurrentSpending() / $daysElapsed) * $totalDays : 0;

        return view('budgets.show', compact('budget', 'expenses', 'dailySpending', 'projectedSpending'));
    }

    /**
     * Show the form for editing the specified budget.
     */
    public function edit(Budget $budget)
    {
        $this->authorize('update', $budget);

        // Get expense categories
        $categories = Expense::where('user_id', auth()->id())
            ->distinct()
            ->pluck('category')
            ->sort();

        // Get currency options
        $currencies = $this->currencyService->getCurrencyOptions();

        return view('budgets.edit', compact('budget', 'categories', 'currencies'));
    }

    /**
     * Update the specified budget in storage.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $validated = $request->validated();

        // Set dates based on period if not custom
        if ($validated['budget_period'] !== 'custom') {
            $dates = $this->calculatePeriodDates($validated['budget_period']);
            $validated['start_date'] = $dates['start_date'];
            $validated['end_date'] = $dates['end_date'];
        }

        $budget->update($validated);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    /**
     * Remove the specified budget from storage.
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    /**
     * Display budget analytics.
     */
    public function analytics(Request $request)
    {
        $userId = auth()->id();

        // Get current month's active budgets
        $activeBudgets = Budget::where('user_id', $userId)
            ->active()
            ->current()
            ->get();

        // Calculate category spending vs budgets
        $categoryAnalysis = $activeBudgets->map(function ($budget) {
            return [
                'category' => $budget->category,
                'budget_amount' => $budget->amount,
                'spent_amount' => $budget->getCurrentSpending(),
                'remaining_amount' => $budget->getRemainingAmount(),
                'utilization_percentage' => $budget->getUtilizationPercentage(),
                'status' => $budget->getStatus(),
                'days_remaining' => now()->diffInDays($budget->end_date, false),
            ];
        });

        // Budget performance trends
        $monthlyTrends = Budget::where('user_id', $userId)
            ->where('start_date', '>=', now()->subMonths(12))
            ->get()
            ->groupBy(function ($budget) {
                return $budget->start_date->format('Y-m');
            })
            ->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'total_budgeted' => $group->sum('amount'),
                    'total_spent' => $group->sum(function ($budget) {
                        return $budget->getCurrentSpending();
                    }),
                    'budgets_count' => $group->count(),
                    'exceeded_count' => $group->where('status', 'exceeded')->count(),
                ];
            })->values();

        $analytics = [
            'total_budgeted' => $activeBudgets->sum('amount'),
            'total_spent' => $activeBudgets->sum(function ($budget) {
                return $budget->getCurrentSpending();
            }),
            'budgets_on_track' => $activeBudgets->where('status', 'on_track')->count(),
            'budgets_warning' => $activeBudgets->where('status', 'warning')->count(),
            'budgets_exceeded' => $activeBudgets->where('status', 'exceeded')->count(),
            'category_analysis' => $categoryAnalysis,
            'monthly_trends' => $monthlyTrends,
        ];

        return view('budgets.analytics', compact('analytics'));
    }

    /**
     * Calculate period start and end dates.
     */
    private function calculatePeriodDates($period)
    {
        $now = now();

        switch ($period) {
            case 'monthly':
                return [
                    'start_date' => $now->startOfMonth()->toDateString(),
                    'end_date' => $now->endOfMonth()->toDateString(),
                ];
            case 'quarterly':
                return [
                    'start_date' => $now->startOfQuarter()->toDateString(),
                    'end_date' => $now->endOfQuarter()->toDateString(),
                ];
            case 'yearly':
                return [
                    'start_date' => $now->startOfYear()->toDateString(),
                    'end_date' => $now->endOfYear()->toDateString(),
                ];
            default:
                return [
                    'start_date' => $now->toDateString(),
                    'end_date' => $now->addMonth()->toDateString(),
                ];
        }
    }

    /**
     * Calculate summary statistics for dashboard.
     */
    private function calculateSummaryStats()
    {
        $userId = auth()->id();

        $activeBudgets = Budget::where('user_id', $userId)
            ->active()
            ->current()
            ->get();

        $totalBudgeted = $activeBudgets->sum('amount');
        $totalSpent = $activeBudgets->sum(function ($budget) {
            return $budget->getCurrentSpending();
        });

        return [
            'total_budgets' => $activeBudgets->count(),
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => max(0, $totalBudgeted - $totalSpent),
            'overall_utilization' => $totalBudgeted > 0 ?
                round(($totalSpent / $totalBudgeted) * 100, 2) : 0,
            'budgets_exceeded' => $activeBudgets->filter(function ($budget) {
                return $budget->isExceeded();
            })->count(),
        ];
    }
}
