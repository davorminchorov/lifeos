<?php

namespace App\Services;

use App\Models\Investment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvestmentAnalyticsService
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    /**
     * Get comprehensive portfolio overview with all metrics
     */
    public function getPortfolioOverview(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->with(['dividends', 'transactions'])
            ->get();

        $defaultCurrency = $this->currencyService->getDefaultCurrency();

        // Convert all investments to default currency for accurate totals
        $totalValue = 0;
        $totalCost = 0;
        $totalDividends = 0;
        $totalFees = 0;

        foreach ($investments as $investment) {
            $currency = $investment->currency ?? $defaultCurrency;
            $totalValue += $this->currencyService->convertToDefault($investment->current_market_value, $currency);
            $totalCost += $this->currencyService->convertToDefault($investment->total_cost_basis, $currency);
            $totalDividends += $this->currencyService->convertToDefault($investment->total_dividends_received ?? 0, $currency);
            $totalFees += $this->currencyService->convertToDefault($investment->total_fees_paid ?? 0, $currency);
        }

        $unrealizedGainLoss = $totalValue - $totalCost;
        $unrealizedGainLossPercentage = $totalCost > 0 ? ($unrealizedGainLoss / $totalCost) * 100 : 0;
        $totalReturn = $unrealizedGainLoss + $totalDividends;
        $totalReturnPercentage = $totalCost > 0 ? ($totalReturn / $totalCost) * 100 : 0;

        return [
            'total_investments' => $investments->count(),
            'total_value' => $totalValue,
            'total_cost' => $totalCost,
            'total_dividends' => $totalDividends,
            'total_fees' => $totalFees,
            'unrealized_gain_loss' => $unrealizedGainLoss,
            'unrealized_gain_loss_percentage' => $unrealizedGainLossPercentage,
            'total_return' => $totalReturn,
            'total_return_percentage' => $totalReturnPercentage,
            'currency' => $defaultCurrency,
        ];
    }

    /**
     * Get analytics by investment type
     */
    public function getAnalyticsByType(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $defaultCurrency = $this->currencyService->getDefaultCurrency();
        $typeAnalytics = [];

        $groupedByType = $investments->groupBy('investment_type');

        foreach ($groupedByType as $type => $typeInvestments) {
            $totalValue = 0;
            $totalCost = 0;
            $totalDividends = 0;

            foreach ($typeInvestments as $investment) {
                $currency = $investment->currency ?? $defaultCurrency;
                $totalValue += $this->currencyService->convertToDefault($investment->current_market_value, $currency);
                $totalCost += $this->currencyService->convertToDefault($investment->total_cost_basis, $currency);
                $totalDividends += $this->currencyService->convertToDefault($investment->total_dividends_received ?? 0, $currency);
            }

            $gainLoss = $totalValue - $totalCost;
            $gainLossPercentage = $totalCost > 0 ? ($gainLoss / $totalCost) * 100 : 0;

            $typeAnalytics[$type] = [
                'count' => $typeInvestments->count(),
                'total_value' => $totalValue,
                'total_cost' => $totalCost,
                'total_dividends' => $totalDividends,
                'gain_loss' => $gainLoss,
                'gain_loss_percentage' => $gainLossPercentage,
                'average_return' => $typeInvestments->count() > 0 ? $gainLossPercentage : 0,
            ];
        }

        return $typeAnalytics;
    }

    /**
     * Get project investment analytics
     */
    public function getProjectInvestmentAnalytics(int $userId): array
    {
        $projectInvestments = Investment::where('user_id', $userId)
            ->where('investment_type', 'project')
            ->where('status', 'active')
            ->get();

        if ($projectInvestments->isEmpty()) {
            return [
                'total_projects' => 0,
                'total_invested' => 0,
                'total_value' => 0,
                'by_stage' => [],
                'by_type' => [],
                'by_business_model' => [],
                'active_projects' => 0,
                'completed_projects' => 0,
            ];
        }

        $defaultCurrency = $this->currencyService->getDefaultCurrency();

        $totalInvested = 0;
        $totalValue = 0;
        $byStage = [];
        $byType = [];
        $byBusinessModel = [];
        $activeProjects = 0;
        $completedProjects = 0;

        foreach ($projectInvestments as $investment) {
            $currency = $investment->project_currency ?? $investment->currency ?? $defaultCurrency;
            $amount = $investment->project_amount ?? $investment->total_cost_basis;
            $value = $investment->current_market_value;

            $totalInvested += $this->currencyService->convertToDefault($amount, $currency);
            $totalValue += $this->currencyService->convertToDefault($value, $currency);

            // Group by stage
            if ($investment->project_stage) {
                $stage = $investment->project_stage;
                if (!isset($byStage[$stage])) {
                    $byStage[$stage] = [
                        'count' => 0,
                        'total_invested' => 0,
                        'total_value' => 0,
                    ];
                }
                $byStage[$stage]['count']++;
                $byStage[$stage]['total_invested'] += $this->currencyService->convertToDefault($amount, $currency);
                $byStage[$stage]['total_value'] += $this->currencyService->convertToDefault($value, $currency);
            }

            // Group by project type
            if ($investment->project_type) {
                $type = $investment->project_type;
                if (!isset($byType[$type])) {
                    $byType[$type] = [
                        'count' => 0,
                        'total_invested' => 0,
                        'total_value' => 0,
                    ];
                }
                $byType[$type]['count']++;
                $byType[$type]['total_invested'] += $this->currencyService->convertToDefault($amount, $currency);
                $byType[$type]['total_value'] += $this->currencyService->convertToDefault($value, $currency);
            }

            // Group by business model
            if ($investment->project_business_model) {
                $model = $investment->project_business_model;
                if (!isset($byBusinessModel[$model])) {
                    $byBusinessModel[$model] = [
                        'count' => 0,
                        'total_invested' => 0,
                        'total_value' => 0,
                    ];
                }
                $byBusinessModel[$model]['count']++;
                $byBusinessModel[$model]['total_invested'] += $this->currencyService->convertToDefault($amount, $currency);
                $byBusinessModel[$model]['total_value'] += $this->currencyService->convertToDefault($value, $currency);
            }

            // Count active vs completed
            if ($investment->project_end_date) {
                if ($investment->project_end_date->isPast()) {
                    $completedProjects++;
                } else {
                    $activeProjects++;
                }
            } else {
                $activeProjects++;
            }
        }

        return [
            'total_projects' => $projectInvestments->count(),
            'total_invested' => $totalInvested,
            'total_value' => $totalValue,
            'gain_loss' => $totalValue - $totalInvested,
            'gain_loss_percentage' => $totalInvested > 0 ? (($totalValue - $totalInvested) / $totalInvested) * 100 : 0,
            'by_stage' => $byStage,
            'by_type' => $byType,
            'by_business_model' => $byBusinessModel,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
        ];
    }

    /**
     * Get top performing investments
     */
    public function getTopPerformers(int $userId, int $limit = 5): Collection
    {
        return Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get()
            ->sortByDesc(function ($investment) {
                return $investment->unrealized_gain_loss_percentage;
            })
            ->take($limit);
    }

    /**
     * Get worst performing investments
     */
    public function getWorstPerformers(int $userId, int $limit = 5): Collection
    {
        return Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get()
            ->sortBy(function ($investment) {
                return $investment->unrealized_gain_loss_percentage;
            })
            ->take($limit);
    }

    /**
     * Get dividend analytics
     */
    public function getDividendAnalytics(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->whereNotNull('total_dividends_received')
            ->where('total_dividends_received', '>', 0)
            ->with('dividends')
            ->get();

        $defaultCurrency = $this->currencyService->getDefaultCurrency();
        $totalDividends = 0;
        $dividendsByMonth = [];
        $dividendsByYear = [];

        foreach ($investments as $investment) {
            $currency = $investment->currency ?? $defaultCurrency;
            $totalDividends += $this->currencyService->convertToDefault($investment->total_dividends_received, $currency);

            // Calculate dividends by month and year
            foreach ($investment->dividends as $dividend) {
                $month = $dividend->payment_date->format('Y-m');
                $year = $dividend->payment_date->format('Y');
                $amount = $this->currencyService->convertToDefault($dividend->amount, $currency);

                if (!isset($dividendsByMonth[$month])) {
                    $dividendsByMonth[$month] = 0;
                }
                $dividendsByMonth[$month] += $amount;

                if (!isset($dividendsByYear[$year])) {
                    $dividendsByYear[$year] = 0;
                }
                $dividendsByYear[$year] += $amount;
            }
        }

        // Calculate average monthly dividend (last 12 months)
        $recentMonths = array_slice($dividendsByMonth, -12, 12, true);
        $averageMonthlyDividend = count($recentMonths) > 0 ? array_sum($recentMonths) / count($recentMonths) : 0;

        return [
            'total_dividends' => $totalDividends,
            'paying_investments' => $investments->count(),
            'dividends_by_month' => $dividendsByMonth,
            'dividends_by_year' => $dividendsByYear,
            'average_monthly_dividend' => $averageMonthlyDividend,
            'projected_annual_dividend' => $averageMonthlyDividend * 12,
        ];
    }

    /**
     * Get allocation breakdown
     */
    public function getAllocationBreakdown(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $defaultCurrency = $this->currencyService->getDefaultCurrency();
        $totalValue = 0;
        $allocationByType = [];

        foreach ($investments as $investment) {
            $currency = $investment->currency ?? $defaultCurrency;
            $value = $this->currencyService->convertToDefault($investment->current_market_value, $currency);
            $totalValue += $value;

            $type = $investment->investment_type;
            if (!isset($allocationByType[$type])) {
                $allocationByType[$type] = 0;
            }
            $allocationByType[$type] += $value;
        }

        // Calculate percentages
        $allocationPercentages = [];
        foreach ($allocationByType as $type => $value) {
            $allocationPercentages[$type] = [
                'value' => $value,
                'percentage' => $totalValue > 0 ? ($value / $totalValue) * 100 : 0,
            ];
        }

        return $allocationPercentages;
    }

    /**
     * Get risk analysis
     */
    public function getRiskAnalysis(int $userId): array
    {
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $riskBreakdown = [
            'conservative' => 0,
            'moderate' => 0,
            'aggressive' => 0,
            'unknown' => 0,
        ];

        $defaultCurrency = $this->currencyService->getDefaultCurrency();
        $totalValue = 0;

        foreach ($investments as $investment) {
            $currency = $investment->currency ?? $defaultCurrency;
            $value = $this->currencyService->convertToDefault($investment->current_market_value, $currency);
            $totalValue += $value;

            $risk = $investment->risk_tolerance ?? 'unknown';
            if (isset($riskBreakdown[$risk])) {
                $riskBreakdown[$risk] += $value;
            } else {
                $riskBreakdown['unknown'] += $value;
            }
        }

        // Calculate percentages
        $riskPercentages = [];
        foreach ($riskBreakdown as $risk => $value) {
            $riskPercentages[$risk] = [
                'value' => $value,
                'percentage' => $totalValue > 0 ? ($value / $totalValue) * 100 : 0,
            ];
        }

        return $riskPercentages;
    }

    /**
     * Get recent performance trend (last 30 days)
     */
    public function getRecentPerformanceTrend(int $userId): array
    {
        // This is a simplified version - in a real scenario, you'd track daily values
        $investments = Investment::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $totalCurrentValue = 0;
        $totalGainLoss = 0;
        $defaultCurrency = $this->currencyService->getDefaultCurrency();

        foreach ($investments as $investment) {
            $currency = $investment->currency ?? $defaultCurrency;
            $totalCurrentValue += $this->currencyService->convertToDefault($investment->current_market_value, $currency);
            $totalGainLoss += $this->currencyService->convertToDefault($investment->unrealized_gain_loss, $currency);
        }

        $trend = $totalGainLoss >= 0 ? 'up' : 'down';

        return [
            'current_value' => $totalCurrentValue,
            'change' => $totalGainLoss,
            'trend' => $trend,
        ];
    }

    /**
     * Get comprehensive analytics for all investment types
     */
    public function getComprehensiveAnalytics(int $userId): array
    {
        return [
            'overview' => $this->getPortfolioOverview($userId),
            'by_type' => $this->getAnalyticsByType($userId),
            'project_investments' => $this->getProjectInvestmentAnalytics($userId),
            'top_performers' => $this->getTopPerformers($userId),
            'worst_performers' => $this->getWorstPerformers($userId),
            'dividends' => $this->getDividendAnalytics($userId),
            'allocation' => $this->getAllocationBreakdown($userId),
            'risk_analysis' => $this->getRiskAnalysis($userId),
            'recent_trend' => $this->getRecentPerformanceTrend($userId),
        ];
    }
}
