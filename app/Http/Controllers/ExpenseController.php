<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::query()->with('user');

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by expense type
        if ($request->has('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        }

        // Filter current month
        if ($request->has('current_month')) {
            $query->currentMonth();
        }

        // Filter current year
        if ($request->has('current_year')) {
            $query->currentYear();
        }

        // Filter tax deductible
        if ($request->has('tax_deductible')) {
            $query->taxDeductible();
        }

        // Filter recurring expenses
        if ($request->has('recurring')) {
            $query->recurring();
        }

        // Search by description or merchant
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhere('merchant', 'like', '%'.$search.'%');
            });
        }

        // Sort by expense date by default
        $sortBy = $request->get('sort_by', 'expense_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $expenses = $query->paginate($request->get('per_page', 15));

        if ($request->expectsJson()) {
            return ExpenseResource::collection($expenses);
        }

        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        if ($request->expectsJson()) {
            return new ExpenseResource($expense);
        }

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $expense->load('user');

        if (request()->expectsJson()) {
            return new ExpenseResource($expense);
        }

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        if ($request->expectsJson()) {
            return new ExpenseResource($expense);
        }

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Expense deleted successfully']);
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    /**
     * Mark expense as reimbursed.
     */
    public function markReimbursed(Expense $expense)
    {
        $expense->update(['status' => 'reimbursed']);

        if (request()->expectsJson()) {
            return new ExpenseResource($expense);
        }

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense marked as reimbursed!');
    }

    /**
     * Get expense analytics/reports.
     */
    public function analytics(Request $request)
    {
        $query = Expense::query();

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inDateRange($request->start_date, $request->end_date);
        } else {
            // Default to current year
            $query->currentYear();
        }

        // Category breakdown
        $categoryBreakdown = (clone $query)
            ->select('category', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Monthly spending trends
        $monthlySpending = (clone $query)
            ->select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Business vs Personal breakdown
        $typeBreakdown = (clone $query)
            ->select('expense_type', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('expense_type')
            ->get();

        // Tax deductible summary
        $taxDeductibleTotal = (clone $query)->taxDeductible()->sum('amount');

        // Top merchants
        $topMerchants = (clone $query)
            ->whereNotNull('merchant')
            ->select('merchant', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('merchant')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        $analytics = [
            'total_expenses' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'average_expense' => $query->avg('amount'),
            'category_breakdown' => $categoryBreakdown,
            'monthly_spending' => $monthlySpending,
            'type_breakdown' => $typeBreakdown,
            'tax_deductible_total' => $taxDeductibleTotal,
            'top_merchants' => $topMerchants,
        ];

        if ($request->expectsJson()) {
            return response()->json($analytics);
        }

        return view('expenses.analytics', compact('analytics'));
    }

    /**
     * Duplicate an expense.
     */
    public function duplicate(Expense $expense)
    {
        $newExpense = $expense->replicate();
        $newExpense->expense_date = now()->toDateString();
        $newExpense->status = 'pending';
        $newExpense->save();

        if (request()->expectsJson()) {
            return new ExpenseResource($newExpense);
        }

        return redirect()->route('expenses.show', $newExpense)
            ->with('success', 'Expense duplicated successfully!');
    }

    /**
     * Bulk operations on expenses.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:expenses,id',
            'action' => 'required|in:delete,mark_reimbursed,change_category,change_status',
            'category' => 'required_if:action,change_category',
            'status' => 'required_if:action,change_status|in:pending,confirmed,reimbursed',
        ]);

        $expenses = Expense::whereIn('id', $request->expense_ids)->get();
        $count = $expenses->count();

        switch ($request->action) {
            case 'delete':
                Expense::whereIn('id', $request->expense_ids)->delete();
                $message = "{$count} expenses deleted successfully!";
                break;

            case 'mark_reimbursed':
                Expense::whereIn('id', $request->expense_ids)->update(['status' => 'reimbursed']);
                $message = "{$count} expenses marked as reimbursed!";
                break;

            case 'change_category':
                Expense::whereIn('id', $request->expense_ids)->update(['category' => $request->category]);
                $message = "{$count} expenses moved to {$request->category} category!";
                break;

            case 'change_status':
                Expense::whereIn('id', $request->expense_ids)->update(['status' => $request->status]);
                $message = "{$count} expenses status changed to {$request->status}!";
                break;
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('expenses.index')->with('success', $message);
    }

    /**
     * Get analytics summary for expenses.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        $currentMonthExpenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        $currentYearExpenses = Expense::where('user_id', $userId)
            ->whereYear('expense_date', now()->year);

        $summary = [
            'total_expenses' => Expense::where('user_id', $userId)->count(),
            'current_month_total' => $currentMonthExpenses->sum('amount'),
            'current_month_count' => $currentMonthExpenses->count(),
            'current_year_total' => $currentYearExpenses->sum('amount'),
            'current_year_count' => $currentYearExpenses->count(),
            'average_monthly_spending' => $currentYearExpenses->sum('amount') / max(now()->month, 1),
            'business_expenses' => Expense::where('user_id', $userId)
                ->where('expense_type', 'business')
                ->sum('amount'),
            'personal_expenses' => Expense::where('user_id', $userId)
                ->where('expense_type', 'personal')
                ->sum('amount'),
            'tax_deductible_total' => Expense::where('user_id', $userId)
                ->where('tax_deductible', true)
                ->sum('amount'),
            'pending_reimbursements' => Expense::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('expense_type', 'business')
                ->sum('amount'),
        ];

        return response()->json(['data' => $summary]);
    }

    /**
     * Get category breakdown for expenses.
     */
    public function categoryBreakdown(Request $request)
    {
        $userId = auth()->id();

        $dateRange = $request->get('months', 12);
        $startDate = now()->subMonths($dateRange);

        $categories = Expense::where('user_id', $userId)
            ->where('expense_date', '>=', $startDate)
            ->select('category', 'subcategory', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category', 'subcategory')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->groupBy('category')
            ->map(function ($group, $category) {
                $categoryTotal = $group->sum('total_amount');

                return [
                    'category' => $category,
                    'total_amount' => $categoryTotal,
                    'total_count' => $group->sum('count'),
                    'subcategories' => $group->map(function ($item) {
                        return [
                            'subcategory' => $item->subcategory,
                            'amount' => $item->total_amount,
                            'count' => $item->count,
                        ];
                    })->values(),
                ];
            });

        $totalAmount = $categories->sum('total_amount');

        $categoriesWithPercentage = $categories->map(function ($item) use ($totalAmount) {
            $item['percentage'] = $totalAmount > 0 ? round(($item['total_amount'] / $totalAmount) * 100, 2) : 0;

            return $item;
        });

        return response()->json(['data' => $categoriesWithPercentage->values()]);
    }

    /**
     * Get trend analytics for expenses.
     */
    public function trendAnalytics(Request $request)
    {
        $userId = auth()->id();

        $months = $request->get('months', 12);
        $startDate = now()->subMonths($months);

        $monthlyTrends = Expense::where('user_id', $userId)
            ->where('expense_date', '>=', $startDate)
            ->select(
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(amount) as average_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $weeklyTrends = Expense::where('user_id', $userId)
            ->where('expense_date', '>=', now()->subWeeks(8))
            ->select(
                DB::raw('YEARWEEK(expense_date) as year_week'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year_week')
            ->orderBy('year_week', 'asc')
            ->get();

        $analytics = [
            'monthly_trends' => $monthlyTrends,
            'weekly_trends' => $weeklyTrends,
            'growth_rate' => $this->calculateGrowthRate($monthlyTrends),
            'seasonal_patterns' => $this->calculateSeasonalPatterns($monthlyTrends),
            'spending_velocity' => $this->calculateSpendingVelocity($userId),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get budget analytics for expenses.
     */
    public function budgetAnalytics(Request $request)
    {
        $userId = auth()->id();

        // This would typically integrate with a budgets table, but for now we'll use estimates
        $currentMonthExpenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->get();

        $categorySpending = $currentMonthExpenses->groupBy('category')->map(function ($group, $category) {
            return [
                'category' => $category,
                'spent' => $group->sum('amount'),
                'count' => $group->count(),
                // These would come from a budgets table in a real implementation
                'budget' => $this->getEstimatedBudget($category),
                'remaining' => max(0, $this->getEstimatedBudget($category) - $group->sum('amount')),
            ];
        })->map(function ($item) {
            $item['percentage_used'] = $item['budget'] > 0 ?
                round(($item['spent'] / $item['budget']) * 100, 2) : 0;
            $item['status'] = $item['percentage_used'] > 100 ? 'over_budget' :
                ($item['percentage_used'] > 80 ? 'warning' : 'on_track');

            return $item;
        });

        $analytics = [
            'total_budget' => $categorySpending->sum('budget'),
            'total_spent' => $categorySpending->sum('spent'),
            'total_remaining' => $categorySpending->sum('remaining'),
            'categories_over_budget' => $categorySpending->where('status', 'over_budget')->count(),
            'categories_warning' => $categorySpending->where('status', 'warning')->count(),
            'category_breakdown' => $categorySpending->values(),
            'projected_monthly_total' => $this->calculateProjectedMonthlyTotal($currentMonthExpenses),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Calculate growth rate between months.
     */
    private function calculateGrowthRate($monthlyTrends)
    {
        if ($monthlyTrends->count() < 2) {
            return 0;
        }

        $current = $monthlyTrends->last()->total_amount;
        $previous = $monthlyTrends->slice(-2, 1)->first()->total_amount;

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0;
    }

    /**
     * Calculate seasonal spending patterns.
     */
    private function calculateSeasonalPatterns($monthlyTrends)
    {
        $patterns = $monthlyTrends->groupBy('month')->map(function ($group, $month) {
            return [
                'month' => $month,
                'average_spending' => $group->avg('total_amount'),
                'count' => $group->count(),
            ];
        });

        return $patterns->values();
    }

    /**
     * Calculate spending velocity (expenses per week).
     */
    private function calculateSpendingVelocity($userId)
    {
        $recentExpenses = Expense::where('user_id', $userId)
            ->where('expense_date', '>=', now()->subWeeks(4))
            ->count();

        return round($recentExpenses / 4, 1);
    }

    /**
     * Get estimated budget for category (placeholder - would come from budgets table).
     */
    private function getEstimatedBudget($category)
    {
        $budgetEstimates = [
            'Food & Dining' => 800,
            'Transportation' => 400,
            'Shopping' => 300,
            'Entertainment' => 200,
            'Bills & Utilities' => 600,
            'Health & Fitness' => 150,
            'Travel' => 500,
        ];

        return $budgetEstimates[$category] ?? 250;
    }

    /**
     * Calculate projected monthly total based on current spending.
     */
    private function calculateProjectedMonthlyTotal($currentMonthExpenses)
    {
        $dayOfMonth = now()->day;
        $daysInMonth = now()->daysInMonth;
        $currentTotal = $currentMonthExpenses->sum('amount');

        return $dayOfMonth > 0 ? round(($currentTotal / $dayOfMonth) * $daysInMonth, 2) : 0;
    }
}
