<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUtilityBillRequest;
use App\Http\Requests\UpdateUtilityBillRequest;
use App\Models\UtilityBill;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilityBillController extends Controller
{
    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UtilityBill::query()->with('user');

        // Filter by utility type
        if ($request->has('utility_type')) {
            $query->byType($request->utility_type);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by service provider
        if ($request->has('service_provider')) {
            $query->where('service_provider', $request->service_provider);
        }

        // Filter pending bills
        if ($request->has('pending')) {
            $query->pending();
        }

        // Filter overdue bills
        if ($request->has('overdue')) {
            $query->overdue();
        }

        // Filter bills due soon
        if ($request->has('due_soon')) {
            $days = $request->get('due_soon', 7);
            $query->dueSoon($days);
        }

        // Filter current month bills
        if ($request->has('current_month')) {
            $query->currentMonth();
        }

        // Filter bills over budget threshold
        if ($request->has('over_budget')) {
            $query->whereNotNull('budget_alert_threshold')
                ->whereRaw('bill_amount > budget_alert_threshold');
        }

        // Search by service provider or service address
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_provider', 'like', '%'.$search.'%')
                    ->orWhere('service_address', 'like', '%'.$search.'%')
                    ->orWhere('account_number', 'like', '%'.$search.'%');
            });
        }

        // Sort by due date by default
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $utilityBills = $query->paginate($request->get('per_page', 15));

        if ($request->expectsJson()) {
            return UtilityBillResource::collection($utilityBills);
        }

        return view('utility-bills.index', compact('utilityBills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('utility-bills.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUtilityBillRequest $request)
    {
        $utilityBill = UtilityBill::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        if ($request->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Utility bill created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UtilityBill $utilityBill)
    {
        $utilityBill->load('user');

        if (request()->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return view('utility-bills.show', compact('utilityBill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UtilityBill $utilityBill)
    {
        return view('utility-bills.edit', compact('utilityBill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUtilityBillRequest $request, UtilityBill $utilityBill)
    {
        $utilityBill->update($request->validated());

        if ($request->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Utility bill updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UtilityBill $utilityBill)
    {
        $utilityBill->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Utility bill deleted successfully']);
        }

        return redirect()->route('utility-bills.index')
            ->with('success', 'Utility bill deleted successfully!');
    }

    /**
     * Mark bill as paid.
     */
    public function markPaid(Request $request, UtilityBill $utilityBill)
    {
        $request->validate([
            'payment_date' => 'nullable|date',
        ]);

        $utilityBill->update([
            'payment_status' => 'paid',
            'payment_date' => $request->get('payment_date', now()),
        ]);

        if ($request->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Bill marked as paid successfully!');
    }

    /**
     * Mark bill as disputed.
     */
    public function markDisputed(Request $request, UtilityBill $utilityBill)
    {
        $request->validate([
            'dispute_reason' => 'nullable|string|max:1000',
        ]);

        $utilityBill->update([
            'payment_status' => 'disputed',
            'notes' => $utilityBill->notes."\n\nDispute: ".$request->get('dispute_reason', 'No reason provided'),
        ]);

        if ($request->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Bill marked as disputed successfully!');
    }

    /**
     * Add meter reading.
     */
    public function addMeterReading(Request $request, UtilityBill $utilityBill)
    {
        $request->validate([
            'current_reading' => 'required|numeric',
            'previous_reading' => 'nullable|numeric',
            'reading_date' => 'nullable|date',
            'photo_path' => 'nullable|string|max:255',
        ]);

        $meterReadings = $utilityBill->meter_readings ?? [];
        $meterReadings[] = [
            'current' => $request->current_reading,
            'previous' => $request->previous_reading,
            'date' => $request->get('reading_date', now()->toDateString()),
            'photo' => $request->photo_path,
            'recorded_at' => now()->toDateTimeString(),
        ];

        $utilityBill->update(['meter_readings' => $meterReadings]);

        if ($request->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Meter reading added successfully!');
    }

    /**
     * Get utility usage analytics.
     */
    public function analytics(Request $request)
    {
        $query = UtilityBill::query();

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('bill_period_start', [$request->start_date, $request->end_date]);
        } else {
            // Default to last 12 months
            $query->where('bill_period_start', '>=', now()->subYear());
        }

        // Utility type breakdown
        $typeBreakdown = (clone $query)
            ->select('utility_type', DB::raw('SUM(bill_amount) as total_amount'), DB::raw('COUNT(*) as count'), DB::raw('AVG(bill_amount) as average_amount'))
            ->groupBy('utility_type')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Monthly spending trends
        $monthlySpending = (clone $query)
            ->select(
                DB::raw('strftime("%Y", bill_period_start) as year'),
                DB::raw('strftime("%m", bill_period_start) as month'),
                DB::raw('SUM(bill_amount) as total_amount'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(bill_amount) as average_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Service provider comparison
        $providerComparison = (clone $query)
            ->select('service_provider', 'utility_type', DB::raw('SUM(bill_amount) as total_amount'), DB::raw('COUNT(*) as count'), DB::raw('AVG(bill_amount) as average_amount'))
            ->groupBy('service_provider', 'utility_type')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Usage efficiency (bills with usage data)
        $usageEfficiency = (clone $query)
            ->whereNotNull('usage_amount')
            ->whereNotNull('usage_unit')
            ->select('utility_type', DB::raw('AVG(bill_amount / usage_amount) as avg_cost_per_unit'), DB::raw('SUM(usage_amount) as total_usage'))
            ->groupBy('utility_type')
            ->get();

        // Overdue and payment status summary
        $paymentSummary = (clone $query)
            ->select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(bill_amount) as total_amount'))
            ->groupBy('payment_status')
            ->get();

        // Bills over budget threshold
        $overBudget = (clone $query)
            ->whereNotNull('budget_alert_threshold')
            ->whereRaw('bill_amount > budget_alert_threshold')
            ->count();

        $analytics = [
            'total_bills' => $query->count(),
            'total_amount' => $query->sum('bill_amount'),
            'average_bill' => $query->avg('bill_amount'),
            'type_breakdown' => $typeBreakdown,
            'monthly_spending' => $monthlySpending,
            'provider_comparison' => $providerComparison,
            'usage_efficiency' => $usageEfficiency,
            'payment_summary' => $paymentSummary,
            'over_budget_count' => $overBudget,
        ];

        if ($request->expectsJson()) {
            return response()->json($analytics);
        }

        return view('utility-bills.analytics', compact('analytics'));
    }

    /**
     * Duplicate a utility bill.
     */
    public function duplicate(UtilityBill $utilityBill)
    {
        $newBill = $utilityBill->replicate();

        // Update dates for next billing period
        $newBill->bill_period_start = $utilityBill->bill_period_end->addDay();
        $newBill->bill_period_end = $newBill->bill_period_start->copy()->addMonth()->subDay();
        $newBill->due_date = $newBill->bill_period_end->copy()->addWeeks(2);

        // Reset payment status
        $newBill->payment_status = 'pending';
        $newBill->payment_date = null;
        $newBill->bill_amount = null; // Will need to be updated
        $newBill->usage_amount = null; // Will need to be updated

        $newBill->save();

        if (request()->expectsJson()) {
            return new UtilityBillResource($newBill);
        }

        return redirect()->route('utility-bills.show', $newBill)
            ->with('success', 'Utility bill duplicated successfully! Please update the amount and usage.');
    }

    /**
     * Update usage history.
     */
    public function updateUsageHistory(UtilityBill $utilityBill)
    {
        $usageHistory = $utilityBill->usage_history ?? [];

        if ($utilityBill->usage_amount) {
            $usageHistory[] = [
                'period' => $utilityBill->bill_period_start->format('Y-m'),
                'usage' => $utilityBill->usage_amount,
                'cost' => $utilityBill->bill_amount,
                'rate' => $utilityBill->rate_per_unit,
                'updated_at' => now()->toDateString(),
            ];

            // Keep only last 24 months of history
            if (count($usageHistory) > 24) {
                $usageHistory = array_slice($usageHistory, -24);
            }

            $utilityBill->update(['usage_history' => $usageHistory]);
        }

        if (request()->expectsJson()) {
            return new UtilityBillResource($utilityBill);
        }

        return redirect()->route('utility-bills.show', $utilityBill)
            ->with('success', 'Usage history updated successfully!');
    }

    /**
     * Get analytics summary for utility bills.
     */
    public function analyticsSummary(Request $request)
    {
        $userId = auth()->id();

        // Get bills and convert amounts to MKD
        $currentMonthBills = UtilityBill::where('user_id', $userId)
            ->whereMonth('bill_period_start', now()->month)
            ->whereYear('bill_period_start', now()->year)
            ->get();

        $currentYearBills = UtilityBill::where('user_id', $userId)
            ->whereYear('bill_period_start', now()->year)
            ->get();

        $overdueBills = UtilityBill::where('user_id', $userId)
            ->where('payment_status', 'overdue')
            ->get();

        // Convert amounts to MKD
        $currentMonthTotalMKD = 0;
        foreach ($currentMonthBills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $currentMonthTotalMKD += $amountInMKD;
        }

        $currentYearTotalMKD = 0;
        foreach ($currentYearBills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $currentYearTotalMKD += $amountInMKD;
        }

        $overdueAmountMKD = 0;
        foreach ($overdueBills as $bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $overdueAmountMKD += $amountInMKD;
        }

        $summary = [
            'total_bills' => UtilityBill::where('user_id', $userId)->count(),
            'current_month_total' => $currentMonthTotalMKD,
            'current_month_count' => $currentMonthBills->count(),
            'current_year_total' => $currentYearTotalMKD,
            'current_year_count' => $currentYearBills->count(),
            'average_monthly_cost' => $currentYearTotalMKD / max(now()->month, 1),
            'pending_bills' => UtilityBill::where('user_id', $userId)
                ->where('payment_status', 'pending')
                ->count(),
            'overdue_bills' => UtilityBill::where('user_id', $userId)
                ->where('payment_status', 'overdue')
                ->count(),
            'overdue_amount' => $overdueAmountMKD,
            'disputed_bills' => UtilityBill::where('user_id', $userId)
                ->where('payment_status', 'disputed')
                ->count(),
            'budget_alerts' => UtilityBill::where('user_id', $userId)
                ->whereNotNull('budget_alert_threshold')
                ->whereRaw('bill_amount > budget_alert_threshold')
                ->count(),
        ];

        return response()->json(['data' => $summary]);
    }

    /**
     * Get usage analytics for utility bills.
     */
    public function usageAnalytics(Request $request)
    {
        $userId = auth()->id();

        $bills = UtilityBill::where('user_id', $userId)
            ->whereNotNull('usage_amount')
            ->where('bill_period_start', '>=', now()->subYear())
            ->get();

        $usageByType = $bills->groupBy('utility_type')->map(function ($group, $type) {
            return [
                'utility_type' => $type,
                'total_usage' => $group->sum('usage_amount'),
                'average_usage' => $group->avg('usage_amount'),
                'usage_unit' => $group->first()->usage_unit,
                'bill_count' => $group->count(),
                'seasonal_variation' => $this->calculateSeasonalVariation($group),
                'efficiency_trend' => $this->calculateEfficiencyTrend($group),
            ];
        });

        $analytics = [
            'usage_by_type' => $usageByType->values(),
            'highest_usage_months' => $this->getHighestUsageMonths($bills),
            'conservation_opportunities' => $this->identifyConservationOpportunities($bills),
            'usage_comparison' => $this->compareUsageYearOverYear($bills),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get cost analytics for utility bills.
     */
    public function costAnalytics(Request $request)
    {
        $userId = auth()->id();

        $bills = UtilityBill::where('user_id', $userId)
            ->where('bill_period_start', '>=', now()->subMonths(24))
            ->get();

        // Convert amounts to MKD first
        $billsWithMKD = $bills->map(function ($bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $bill->bill_amount_mkd = $amountInMKD;

            return $bill;
        });

        $costByType = $billsWithMKD->groupBy('utility_type')->map(function ($group, $type) {
            return [
                'utility_type' => $type,
                'total_cost' => $group->sum('bill_amount_mkd'),
                'average_cost' => $group->avg('bill_amount_mkd'),
                'cost_per_unit' => $group->whereNotNull('usage_amount')->avg('rate_per_unit'),
                'cost_trend' => $this->calculateCostTrend($group),
                'seasonal_cost_variation' => $this->calculateSeasonalCostVariation($group),
            ];
        });

        $analytics = [
            'cost_by_type' => $costByType->values(),
            'monthly_cost_trends' => $this->getMonthlyTrends($billsWithMKD, 'bill_amount_mkd'),
            'cost_spikes' => $this->identifyCostSpikes($billsWithMKD),
            'budget_performance' => $this->analyzeBudgetPerformance($billsWithMKD),
            'rate_changes' => $this->detectRateChanges($billsWithMKD),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get provider analytics for utility bills.
     */
    public function providerAnalytics(Request $request)
    {
        $userId = auth()->id();

        $bills = UtilityBill::where('user_id', $userId)
            ->where('bill_period_start', '>=', now()->subYear())
            ->get();

        // Convert amounts to MKD first
        $billsWithMKD = $bills->map(function ($bill) {
            $currency = $bill->currency ?? config('currency.default', 'MKD');
            $amountInMKD = $this->currencyService->convertToDefault($bill->bill_amount, $currency);
            $bill->bill_amount_mkd = $amountInMKD;

            return $bill;
        });

        $providerPerformance = $billsWithMKD->groupBy(['utility_type', 'service_provider'])
            ->map(function ($typeGroup, $type) {
                return $typeGroup->map(function ($providerGroup, $provider) use ($type) {
                    return [
                        'utility_type' => $type,
                        'service_provider' => $provider,
                        'total_cost' => $providerGroup->sum('bill_amount_mkd'),
                        'average_cost' => $providerGroup->avg('bill_amount_mkd'),
                        'bill_count' => $providerGroup->count(),
                        'reliability_score' => $this->calculateReliabilityScore($providerGroup),
                        'cost_efficiency' => $this->calculateCostEfficiency($providerGroup),
                        'customer_service_rating' => $providerGroup->whereNotNull('customer_service_rating')
                            ->avg('customer_service_rating'),
                    ];
                });
            })->flatten(1);

        $analytics = [
            'provider_performance' => $providerPerformance->values(),
            'cost_comparison' => $this->compareProviderCosts($billsWithMKD),
            'switching_opportunities' => $this->identifySwitchingOpportunities($billsWithMKD),
            'contract_renewals' => $this->getUpcomingContractRenewals($billsWithMKD),
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Calculate seasonal variation in usage.
     */
    private function calculateSeasonalVariation($bills)
    {
        $seasonal = $bills->groupBy(function ($bill) {
            return $bill->bill_period_start->format('n'); // Month number
        })->map(function ($group, $month) {
            return [
                'month' => $month,
                'average_usage' => $group->avg('usage_amount'),
                'count' => $group->count(),
            ];
        });

        $overallAverage = $bills->avg('usage_amount');

        return $seasonal->map(function ($data) use ($overallAverage) {
            $data['variation_percentage'] = $overallAverage > 0
                ? round((($data['average_usage'] - $overallAverage) / $overallAverage) * 100, 2)
                : 0;

            return $data;
        });
    }

    /**
     * Calculate efficiency trend over time.
     */
    private function calculateEfficiencyTrend($bills)
    {
        return $bills->sortBy('bill_period_start')
            ->groupBy(function ($bill) {
                return $bill->bill_period_start->format('Y-m');
            })
            ->map(function ($group, $period) {
                return [
                    'period' => $period,
                    'cost_per_unit' => $group->avg('rate_per_unit'),
                    'usage' => $group->sum('usage_amount'),
                ];
            })->values();
    }

    /**
     * Get highest usage months.
     */
    private function getHighestUsageMonths($bills)
    {
        return $bills->sortByDesc('usage_amount')->take(5)
            ->map(function ($bill) {
                return [
                    'period' => $bill->bill_period_start->format('Y-m'),
                    'utility_type' => $bill->utility_type,
                    'usage' => $bill->usage_amount,
                    'cost' => $bill->bill_amount,
                ];
            })->values();
    }

    /**
     * Identify conservation opportunities.
     */
    private function identifyConservationOpportunities($bills)
    {
        $opportunities = [];

        foreach ($bills->groupBy('utility_type') as $type => $typeBills) {
            $averageUsage = $typeBills->avg('usage_amount');
            $highUsageBills = $typeBills->where('usage_amount', '>', $averageUsage * 1.2);

            if ($highUsageBills->count() > 0) {
                $opportunities[] = [
                    'utility_type' => $type,
                    'potential_savings' => $highUsageBills->sum('bill_amount') * 0.15, // Estimated 15% savings
                    'high_usage_periods' => $highUsageBills->count(),
                    'recommendation' => $this->getConservationRecommendation($type),
                ];
            }
        }

        return $opportunities;
    }

    /**
     * Compare usage year over year.
     */
    private function compareUsageYearOverYear($bills)
    {
        $currentYear = $bills->where('bill_period_start', '>=', now()->startOfYear());
        $previousYear = $bills->whereBetween('bill_period_start', [
            now()->subYear()->startOfYear(),
            now()->subYear()->endOfYear(),
        ]);

        return [
            'current_year_usage' => $currentYear->sum('usage_amount'),
            'previous_year_usage' => $previousYear->sum('usage_amount'),
            'change_percentage' => $this->calculatePercentageChange(
                $previousYear->sum('usage_amount'),
                $currentYear->sum('usage_amount')
            ),
        ];
    }

    /**
     * Get conservation recommendation based on utility type.
     */
    private function getConservationRecommendation($type)
    {
        $recommendations = [
            'electricity' => 'Consider LED lighting, programmable thermostats, and energy-efficient appliances',
            'gas' => 'Improve insulation, service heating systems, and consider smart thermostats',
            'water' => 'Install low-flow fixtures, fix leaks promptly, and consider water-efficient appliances',
            'internet' => 'Review data usage patterns and consider plan optimization',
        ];

        return $recommendations[$type] ?? 'Monitor usage patterns and consider efficiency improvements';
    }

    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange($old, $new)
    {
        return $old > 0 ? round((($new - $old) / $old) * 100, 2) : 0;
    }

    /**
     * Calculate cost trend over time.
     */
    private function calculateCostTrend($bills)
    {
        return $bills->sortBy('bill_period_start')
            ->groupBy(function ($bill) {
                return $bill->bill_period_start->format('Y-m');
            })
            ->map(function ($group, $period) {
                return [
                    'period' => $period,
                    'total_cost' => $group->sum('bill_amount'),
                    'average_cost' => $group->avg('bill_amount'),
                ];
            })->values();
    }

    /**
     * Calculate seasonal cost variation.
     */
    private function calculateSeasonalCostVariation($bills)
    {
        return $bills->groupBy(function ($bill) {
            return $bill->bill_period_start->format('n'); // Month number
        })->map(function ($group, $month) {
            return [
                'month' => $month,
                'average_cost' => $group->avg('bill_amount'),
                'count' => $group->count(),
            ];
        })->values();
    }

    /**
     * Get monthly trends for specified field.
     */
    private function getMonthlyTrends($bills, $field)
    {
        return $bills->groupBy(function ($bill) {
            return $bill->bill_period_start->format('Y-m');
        })->map(function ($group, $period) use ($field) {
            return [
                'period' => $period,
                'value' => $group->sum($field),
                'count' => $group->count(),
            ];
        })->sortKeys()->values();
    }

    /**
     * Identify cost spikes in bills.
     */
    private function identifyCostSpikes($bills)
    {
        $averageCost = $bills->avg('bill_amount');
        $threshold = $averageCost * 1.5; // 50% above average

        return $bills->where('bill_amount', '>', $threshold)
            ->sortByDesc('bill_amount')
            ->take(5)
            ->map(function ($bill) use ($averageCost) {
                return [
                    'period' => $bill->bill_period_start->format('Y-m'),
                    'utility_type' => $bill->utility_type,
                    'amount' => $bill->bill_amount,
                    'spike_percentage' => round((($bill->bill_amount - $averageCost) / $averageCost) * 100, 2),
                ];
            })->values();
    }

    /**
     * Analyze budget performance.
     */
    private function analyzeBudgetPerformance($bills)
    {
        $budgetedBills = $bills->whereNotNull('budget_alert_threshold');

        return [
            'total_budgeted' => $budgetedBills->sum('budget_alert_threshold'),
            'total_actual' => $budgetedBills->sum('bill_amount'),
            'over_budget_count' => $budgetedBills->where('bill_amount', '>', function ($bill) {
                return $bill->budget_alert_threshold;
            })->count(),
            'budget_variance' => $budgetedBills->sum('budget_alert_threshold') - $budgetedBills->sum('bill_amount'),
        ];
    }

    /**
     * Detect rate changes in bills.
     */
    private function detectRateChanges($bills)
    {
        return $bills->whereNotNull('rate_per_unit')
            ->groupBy('utility_type')
            ->map(function ($group, $type) {
                $sorted = $group->sortBy('bill_period_start');
                $rateChanges = [];

                foreach ($sorted as $index => $bill) {
                    if ($index > 0) {
                        $previous = $sorted->slice($index - 1, 1)->first();
                        if ($bill->rate_per_unit != $previous->rate_per_unit) {
                            $rateChanges[] = [
                                'period' => $bill->bill_period_start->format('Y-m'),
                                'old_rate' => $previous->rate_per_unit,
                                'new_rate' => $bill->rate_per_unit,
                                'change_percentage' => $this->calculatePercentageChange(
                                    $previous->rate_per_unit,
                                    $bill->rate_per_unit
                                ),
                            ];
                        }
                    }
                }

                return [
                    'utility_type' => $type,
                    'rate_changes' => $rateChanges,
                ];
            })->values();
    }

    /**
     * Compare provider costs.
     */
    private function compareProviderCosts($bills)
    {
        return $bills->groupBy('utility_type')->map(function ($typeBills, $type) {
            return $typeBills->groupBy('service_provider')->map(function ($providerBills, $provider) use ($type) {
                return [
                    'utility_type' => $type,
                    'provider' => $provider,
                    'average_cost' => $providerBills->avg('bill_amount'),
                    'total_cost' => $providerBills->sum('bill_amount'),
                    'bill_count' => $providerBills->count(),
                ];
            })->values();
        })->flatten(1);
    }

    /**
     * Identify switching opportunities.
     */
    private function identifySwitchingOpportunities($bills)
    {
        // This would integrate with market rate data in a real implementation
        return $bills->groupBy('utility_type')->map(function ($typeBills, $type) {
            $currentAverage = $typeBills->avg('bill_amount');
            $marketAverage = $currentAverage * 0.85; // Assume 15% potential savings

            if ($currentAverage > $marketAverage) {
                return [
                    'utility_type' => $type,
                    'current_average' => $currentAverage,
                    'market_average' => $marketAverage,
                    'potential_annual_savings' => ($currentAverage - $marketAverage) * 12,
                    'recommendation' => 'Consider comparing rates from other providers',
                ];
            }

            return null;
        })->filter()->values();
    }

    /**
     * Get upcoming contract renewals.
     */
    private function getUpcomingContractRenewals($bills)
    {
        // This would integrate with contract data in a real implementation
        return $bills->whereNotNull('contract_end_date')
            ->where('contract_end_date', '<=', now()->addMonths(3))
            ->map(function ($bill) {
                return [
                    'utility_type' => $bill->utility_type,
                    'provider' => $bill->service_provider,
                    'contract_end_date' => $bill->contract_end_date,
                    'days_until_renewal' => now()->diffInDays($bill->contract_end_date),
                ];
            })->values();
    }

    /**
     * Calculate reliability score based on bill consistency.
     */
    private function calculateReliabilityScore($bills)
    {
        // Simple reliability score based on billing consistency
        $expectedBills = 12; // Assume monthly billing
        $actualBills = $bills->count();
        $onTimePayments = $bills->where('payment_status', 'paid')->count();

        return min(100, (($actualBills / $expectedBills) * 0.5 + ($onTimePayments / $actualBills) * 0.5) * 100);
    }

    /**
     * Calculate cost efficiency for provider.
     */
    private function calculateCostEfficiency($bills)
    {
        $avgCostPerUnit = $bills->whereNotNull('rate_per_unit')->avg('rate_per_unit');
        // This would compare against market rates in a real implementation
        $marketRate = $avgCostPerUnit * 1.1; // Assume current rate is 10% below market average

        return $avgCostPerUnit < $marketRate ? 'efficient' : 'expensive';
    }
}
