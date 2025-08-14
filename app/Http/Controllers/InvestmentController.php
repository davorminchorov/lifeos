<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentRequest;
use App\Http\Requests\UpdateInvestmentRequest;
use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Investment::query()->with('user');

        // Filter by investment type
        if ($request->has('investment_type')) {
            $query->byType($request->investment_type);
        }

        // Filter by risk tolerance
        if ($request->has('risk_tolerance')) {
            $query->byRiskTolerance($request->risk_tolerance);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by account broker
        if ($request->has('account_broker')) {
            $query->where('account_broker', $request->account_broker);
        }

        // Search by name or symbol
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('symbol_identifier', 'like', '%' . $search . '%');
            });
        }

        // Sort by purchase date by default
        $sortBy = $request->get('sort_by', 'purchase_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $investments = $query->paginate($request->get('per_page', 15));

        if ($request->expectsJson()) {
            return InvestmentResource::collection($investments);
        }

        return view('investments.index', compact('investments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('investments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvestmentRequest $request)
    {
        $investment = Investment::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Investment created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investment $investment)
    {
        $investment->load('user');

        if (request()->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return view('investments.show', compact('investment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investment $investment)
    {
        return view('investments.edit', compact('investment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvestmentRequest $request, Investment $investment)
    {
        $investment->update($request->validated());

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Investment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investment $investment)
    {
        $investment->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Investment deleted successfully']);
        }

        return redirect()->route('investments.index')
            ->with('success', 'Investment deleted successfully!');
    }

    /**
     * Record a dividend payment.
     */
    public function recordDividend(Request $request, Investment $investment)
    {
        $request->validate([
            'dividend_amount' => 'required|numeric|min:0',
            'dividend_date' => 'nullable|date',
        ]);

        $dividendAmount = $request->dividend_amount;
        $newTotal = $investment->total_dividends_received + $dividendAmount;

        // Add transaction to history
        $transactionHistory = $investment->transaction_history ?? [];
        $transactionHistory[] = [
            'date' => $request->get('dividend_date', now()->toDateString()),
            'type' => 'dividend',
            'amount' => $dividendAmount,
            'description' => 'Dividend payment received',
        ];

        $investment->update([
            'total_dividends_received' => $newTotal,
            'transaction_history' => $transactionHistory,
        ]);

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Dividend recorded successfully!');
    }

    /**
     * Update current market value.
     */
    public function updatePrice(Request $request, Investment $investment)
    {
        $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $investment->update([
            'current_value' => $request->current_value,
            'last_price_update' => now(),
        ]);

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Price updated successfully!');
    }

    /**
     * Record a buy transaction.
     */
    public function recordBuy(Request $request, Investment $investment)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'fees' => 'nullable|numeric|min:0',
            'transaction_date' => 'nullable|date',
        ]);

        $quantity = $request->quantity;
        $pricePerUnit = $request->price_per_unit;
        $fees = $request->get('fees', 0);

        // Update investment totals
        $newQuantity = $investment->quantity + $quantity;
        $newTotalFees = $investment->total_fees_paid + $fees;

        // Add transaction to history
        $transactionHistory = $investment->transaction_history ?? [];
        $transactionHistory[] = [
            'date' => $request->get('transaction_date', now()->toDateString()),
            'type' => 'buy',
            'quantity' => $quantity,
            'price' => $pricePerUnit,
            'fees' => $fees,
            'total_cost' => ($quantity * $pricePerUnit) + $fees,
        ];

        $investment->update([
            'quantity' => $newQuantity,
            'total_fees_paid' => $newTotalFees,
            'transaction_history' => $transactionHistory,
        ]);

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Buy transaction recorded successfully!');
    }

    /**
     * Record a sell transaction.
     */
    public function recordSell(Request $request, Investment $investment)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0|max:' . $investment->quantity,
            'price_per_unit' => 'required|numeric|min:0',
            'fees' => 'nullable|numeric|min:0',
            'transaction_date' => 'nullable|date',
        ]);

        $quantity = $request->quantity;
        $pricePerUnit = $request->price_per_unit;
        $fees = $request->get('fees', 0);

        // Update investment totals
        $newQuantity = $investment->quantity - $quantity;
        $newTotalFees = $investment->total_fees_paid + $fees;

        // Add transaction to history
        $transactionHistory = $investment->transaction_history ?? [];
        $transactionHistory[] = [
            'date' => $request->get('transaction_date', now()->toDateString()),
            'type' => 'sell',
            'quantity' => $quantity,
            'price' => $pricePerUnit,
            'fees' => $fees,
            'total_proceeds' => ($quantity * $pricePerUnit) - $fees,
        ];

        // Update status if all shares sold
        $status = $newQuantity <= 0 ? 'sold' : $investment->status;

        $investment->update([
            'quantity' => $newQuantity,
            'total_fees_paid' => $newTotalFees,
            'transaction_history' => $transactionHistory,
            'status' => $status,
        ]);

        if ($request->expectsJson()) {
            return new InvestmentResource($investment);
        }

        return redirect()->route('investments.show', $investment)
            ->with('success', 'Sell transaction recorded successfully!');
    }

    /**
     * Get portfolio summary.
     */
    public function portfolioSummary(Request $request)
    {
        $investments = Investment::active()->get();

        $summary = [
            'total_investments' => $investments->count(),
            'total_cost_basis' => $investments->sum('total_cost_basis'),
            'current_market_value' => $investments->sum('current_market_value'),
            'total_dividends' => $investments->sum('total_dividends_received'),
            'total_fees' => $investments->sum('total_fees_paid'),
            'unrealized_gain_loss' => $investments->sum('unrealized_gain_loss'),
            'total_return' => $investments->sum('total_return'),
        ];

        $summary['unrealized_gain_loss_percentage'] = $summary['total_cost_basis'] > 0
            ? ($summary['unrealized_gain_loss'] / $summary['total_cost_basis']) * 100
            : 0;

        $summary['total_return_percentage'] = $summary['total_cost_basis'] > 0
            ? ($summary['total_return'] / $summary['total_cost_basis']) * 100
            : 0;

        if ($request->expectsJson()) {
            return response()->json($summary);
        }

        return view('investments.portfolio', compact('summary', 'investments'));
    }

    /**
     * Get analytics summary for investments.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        $investments = Investment::where('user_id', $userId)->get();
        $activeInvestments = $investments->where('status', 'active');

        $summary = [
            'total_investments' => $investments->count(),
            'active_investments' => $activeInvestments->count(),
            'sold_investments' => $investments->where('status', 'sold')->count(),
            'total_invested' => $activeInvestments->sum('total_cost_basis'),
            'current_value' => $activeInvestments->sum('current_market_value'),
            'total_dividends' => $activeInvestments->sum('total_dividends_received'),
            'total_fees' => $investments->sum('total_fees_paid'),
            'unrealized_gain_loss' => $activeInvestments->sum('unrealized_gain_loss'),
            'realized_gain_loss' => $investments->where('status', 'sold')->sum('realized_gain_loss'),
        ];

        $summary['portfolio_performance'] = $summary['total_invested'] > 0
            ? round(($summary['unrealized_gain_loss'] / $summary['total_invested']) * 100, 2)
            : 0;

        return response()->json(['data' => $summary]);
    }

    /**
     * Get performance analytics for investments.
     */
    public function performanceAnalytics(Request $request)
    {
        $userId = auth()->id();

        $investments = Investment::where('user_id', $userId)->get();
        $activeInvestments = $investments->where('status', 'active');

        $analytics = [
            'best_performers' => $activeInvestments->sortByDesc('unrealized_gain_loss_percentage')->take(5)->values(),
            'worst_performers' => $activeInvestments->sortBy('unrealized_gain_loss_percentage')->take(5)->values(),
            'performance_distribution' => [
                'gainers' => $activeInvestments->where('unrealized_gain_loss', '>', 0)->count(),
                'losers' => $activeInvestments->where('unrealized_gain_loss', '<', 0)->count(),
                'neutral' => $activeInvestments->where('unrealized_gain_loss', '=', 0)->count(),
            ],
            'risk_assessment' => [
                'high_risk' => $activeInvestments->where('risk_level', 'high')->count(),
                'medium_risk' => $activeInvestments->where('risk_level', 'medium')->count(),
                'low_risk' => $activeInvestments->where('risk_level', 'low')->count(),
            ],
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get allocation analytics for investments.
     */
    public function allocationAnalytics(Request $request)
    {
        $userId = auth()->id();

        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $totalValue = $investments->sum('current_market_value');

        $analytics = [
            'by_investment_type' => $investments->groupBy('investment_type')->map(function ($group) use ($totalValue) {
                $groupValue = $group->sum('current_market_value');
                return [
                    'count' => $group->count(),
                    'value' => $groupValue,
                    'percentage' => $totalValue > 0 ? round(($groupValue / $totalValue) * 100, 2) : 0,
                ];
            }),
            'by_account_broker' => $investments->groupBy('account_broker')->map(function ($group) use ($totalValue) {
                $groupValue = $group->sum('current_market_value');
                return [
                    'count' => $group->count(),
                    'value' => $groupValue,
                    'percentage' => $totalValue > 0 ? round(($groupValue / $totalValue) * 100, 2) : 0,
                ];
            }),
            'largest_holdings' => $investments->sortByDesc('current_market_value')->take(10)->values(),
            'diversification_score' => $this->calculateDiversificationScore($investments),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get dividend analytics for investments.
     */
    public function dividendAnalytics(Request $request)
    {
        $userId = auth()->id();

        $investments = Investment::where('user_id', $userId)
            ->where('total_dividends_received', '>', 0)
            ->get();

        $analytics = [
            'total_dividend_income' => $investments->sum('total_dividends_received'),
            'dividend_paying_investments' => $investments->count(),
            'average_dividend_yield' => $investments->count() > 0 ? $investments->avg('dividend_yield') : 0,
            'top_dividend_payers' => $investments->sortByDesc('total_dividends_received')->take(5)->values(),
            'monthly_dividend_trend' => $this->calculateMonthlyDividendTrend($investments),
            'dividend_growth_investments' => $investments->where('dividend_growth_rate', '>', 0)->count(),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Calculate diversification score based on allocation spread.
     */
    private function calculateDiversificationScore($investments)
    {
        if ($investments->count() === 0) return 0;

        $totalValue = $investments->sum('current_market_value');
        $typeAllocations = $investments->groupBy('investment_type');

        // Calculate concentration risk
        $maxConcentration = $typeAllocations->map(function ($group) use ($totalValue) {
            return $totalValue > 0 ? ($group->sum('current_market_value') / $totalValue) * 100 : 0;
        })->max();

        // Score based on diversification (lower concentration = higher score)
        return $maxConcentration < 20 ? 100 : max(0, 100 - ($maxConcentration - 20) * 2);
    }

    /**
     * Calculate monthly dividend trend from transaction history.
     */
    private function calculateMonthlyDividendTrend($investments)
    {
        $monthlyTrend = [];

        foreach ($investments as $investment) {
            $dividendHistory = $investment->dividend_history ?? [];

            foreach ($dividendHistory as $dividend) {
                $month = date('Y-m', strtotime($dividend['date']));
                $monthlyTrend[$month] = ($monthlyTrend[$month] ?? 0) + $dividend['amount'];
            }
        }

        return collect($monthlyTrend)->sortKeys()->take(12);
    }

    /**
     * Display investment goals.
     */
    public function goalIndex(Request $request)
    {
        $userId = auth()->id();
        $goals = collect([
            // This would typically come from a goals table/model
            // For now, returning placeholder data structure
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => $goals]);
        }

        return view('investments.goals.index', compact('goals'));
    }

    /**
     * Store a new investment goal.
     */
    public function goalStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date|after:today',
            'description' => 'nullable|string|max:1000',
        ]);

        // This would typically create a new goal record
        // For now, returning success response
        $goal = [
            'id' => uniqid(),
            'user_id' => auth()->id(),
            'title' => $request->title,
            'target_amount' => $request->target_amount,
            'target_date' => $request->target_date,
            'description' => $request->description,
            'current_progress' => 0,
            'created_at' => now(),
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $goal], 201);
        }

        return redirect()->route('investments.goals.index')
            ->with('success', 'Investment goal created successfully!');
    }

    /**
     * Update an investment goal.
     */
    public function goalUpdate(Request $request, $goalId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'nullable|date|after:today',
            'description' => 'nullable|string|max:1000',
        ]);

        // This would typically update the goal record
        // For now, returning success response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Goal updated successfully']);
        }

        return redirect()->route('investments.goals.index')
            ->with('success', 'Investment goal updated successfully!');
    }

    /**
     * Delete an investment goal.
     */
    public function goalDestroy($goalId)
    {
        // This would typically delete the goal record
        // For now, returning success response
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Goal deleted successfully']);
        }

        return redirect()->route('investments.goals.index')
            ->with('success', 'Investment goal deleted successfully!');
    }

    /**
     * Display tax reports index.
     */
    public function taxReportIndex(Request $request)
    {
        $userId = auth()->id();

        // Get basic tax summary data
        $taxSummary = [
            'total_realized_gains' => 0,
            'total_realized_losses' => 0,
            'total_dividend_income' => 0,
            'tax_year' => $request->get('tax_year', date('Y')),
        ];

        $soldInvestments = Investment::where('user_id', $userId)
            ->where('status', 'sold')
            ->get();

        $activeInvestments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $taxSummary['total_realized_gains'] = $soldInvestments->where('realized_gain_loss', '>', 0)->sum('realized_gain_loss');
        $taxSummary['total_realized_losses'] = abs($soldInvestments->where('realized_gain_loss', '<', 0)->sum('realized_gain_loss'));
        $taxSummary['total_dividend_income'] = $activeInvestments->sum('total_dividends_received');

        if ($request->expectsJson()) {
            return response()->json(['data' => $taxSummary]);
        }

        return view('investments.tax-reports.index', compact('taxSummary'));
    }

    /**
     * Generate capital gains report.
     */
    public function capitalGainsReport(Request $request)
    {
        $userId = auth()->id();
        $taxYear = $request->get('tax_year', date('Y'));

        $soldInvestments = Investment::where('user_id', $userId)
            ->where('status', 'sold')
            ->get();

        $capitalGains = $soldInvestments->map(function ($investment) {
            return [
                'investment_name' => $investment->name,
                'symbol' => $investment->symbol_identifier,
                'purchase_date' => $investment->purchase_date,
                'sale_date' => $investment->sale_date,
                'purchase_price' => $investment->purchase_price,
                'sale_price' => $investment->sale_price,
                'quantity' => $investment->quantity_sold ?? $investment->quantity,
                'cost_basis' => $investment->total_cost_basis,
                'proceeds' => $investment->sale_proceeds,
                'gain_loss' => $investment->realized_gain_loss,
                'holding_period' => $investment->holding_period_days > 365 ? 'Long-term' : 'Short-term',
            ];
        });

        $report = [
            'tax_year' => $taxYear,
            'total_short_term_gains' => $capitalGains->where('holding_period', 'Short-term')->where('gain_loss', '>', 0)->sum('gain_loss'),
            'total_short_term_losses' => abs($capitalGains->where('holding_period', 'Short-term')->where('gain_loss', '<', 0)->sum('gain_loss')),
            'total_long_term_gains' => $capitalGains->where('holding_period', 'Long-term')->where('gain_loss', '>', 0)->sum('gain_loss'),
            'total_long_term_losses' => abs($capitalGains->where('holding_period', 'Long-term')->where('gain_loss', '<', 0)->sum('gain_loss')),
            'transactions' => $capitalGains,
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $report]);
        }

        return view('investments.tax-reports.capital-gains', compact('report'));
    }

    /**
     * Generate dividend income report.
     */
    public function dividendIncomeReport(Request $request)
    {
        $userId = auth()->id();
        $taxYear = $request->get('tax_year', date('Y'));

        $investments = Investment::where('user_id', $userId)
            ->where('total_dividends_received', '>', 0)
            ->get();

        $dividendReport = $investments->map(function ($investment) {
            return [
                'investment_name' => $investment->name,
                'symbol' => $investment->symbol_identifier,
                'total_dividends' => $investment->total_dividends_received,
                'qualified_dividends' => $investment->qualified_dividends ?? $investment->total_dividends_received * 0.8, // Estimate
                'non_qualified_dividends' => $investment->non_qualified_dividends ?? $investment->total_dividends_received * 0.2, // Estimate
                'dividend_history' => $investment->dividend_history ?? [],
            ];
        });

        $report = [
            'tax_year' => $taxYear,
            'total_dividend_income' => $dividendReport->sum('total_dividends'),
            'total_qualified_dividends' => $dividendReport->sum('qualified_dividends'),
            'total_non_qualified_dividends' => $dividendReport->sum('non_qualified_dividends'),
            'dividend_details' => $dividendReport,
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $report]);
        }

        return view('investments.tax-reports.dividend-income', compact('report'));
    }

    /**
     * Get rebalancing alerts.
     */
    public function rebalancingAlerts(Request $request)
    {
        $userId = auth()->id();
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $totalValue = $investments->sum('current_market_value');
        $alerts = [];

        // Check for concentration risk
        $typeAllocations = $investments->groupBy('investment_type');
        foreach ($typeAllocations as $type => $typeInvestments) {
            $typeValue = $typeInvestments->sum('current_market_value');
            $percentage = $totalValue > 0 ? ($typeValue / $totalValue) * 100 : 0;

            if ($percentage > 30) {
                $alerts[] = [
                    'type' => 'concentration_risk',
                    'severity' => 'high',
                    'message' => "High concentration in {$type} ({$percentage}%). Consider diversifying.",
                    'recommendation' => "Reduce allocation to {$type} to below 30%",
                ];
            }
        }

        // Check for underperforming investments
        $underperformers = $investments->where('unrealized_gain_loss_percentage', '<', -20);
        foreach ($underperformers as $investment) {
            $alerts[] = [
                'type' => 'underperformance',
                'severity' => 'medium',
                'message' => "{$investment->name} is down {$investment->unrealized_gain_loss_percentage}%",
                'recommendation' => "Review and consider rebalancing or cutting losses",
            ];
        }

        // Check for no recent rebalancing
        $lastRebalance = $request->get('last_rebalance_date');
        if (!$lastRebalance || now()->diffInMonths($lastRebalance) > 6) {
            $alerts[] = [
                'type' => 'rebalance_due',
                'severity' => 'low',
                'message' => 'Portfolio has not been rebalanced in over 6 months',
                'recommendation' => 'Consider reviewing and rebalancing your portfolio allocation',
            ];
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $alerts]);
        }

        return view('investments.rebalancing.alerts', compact('alerts'));
    }

    /**
     * Generate rebalancing recommendations.
     */
    public function rebalancingRecommendations(Request $request)
    {
        $request->validate([
            'target_allocation' => 'required|array',
            'target_allocation.*.type' => 'required|string',
            'target_allocation.*.percentage' => 'required|numeric|min:0|max:100',
        ]);

        $userId = auth()->id();
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $totalValue = $investments->sum('current_market_value');
        $currentAllocations = $investments->groupBy('investment_type')->map(function ($group) use ($totalValue) {
            return [
                'current_value' => $group->sum('current_market_value'),
                'current_percentage' => $totalValue > 0 ? ($group->sum('current_market_value') / $totalValue) * 100 : 0,
            ];
        });

        $recommendations = [];
        foreach ($request->target_allocation as $target) {
            $currentAllocation = $currentAllocations->get($target['type'], ['current_value' => 0, 'current_percentage' => 0]);
            $targetValue = ($target['percentage'] / 100) * $totalValue;
            $difference = $targetValue - $currentAllocation['current_value'];

            if (abs($difference) > ($totalValue * 0.05)) { // 5% threshold
                $recommendations[] = [
                    'investment_type' => $target['type'],
                    'current_percentage' => $currentAllocation['current_percentage'],
                    'target_percentage' => $target['percentage'],
                    'current_value' => $currentAllocation['current_value'],
                    'target_value' => $targetValue,
                    'difference' => $difference,
                    'action' => $difference > 0 ? 'buy' : 'sell',
                    'amount' => abs($difference),
                ];
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => $recommendations]);
        }

        return view('investments.rebalancing.recommendations', compact('recommendations'));
    }
}
