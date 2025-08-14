<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 routes for all LifeOS modules
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Dashboard analytics
    Route::get('dashboard/overview', [DashboardController::class, 'apiOverview']);
    Route::get('dashboard/upcoming', [DashboardController::class, 'apiUpcoming']);
    Route::get('dashboard/alerts', [DashboardController::class, 'apiAlerts']);

    // Subscriptions
    Route::apiResource('subscriptions', SubscriptionController::class);
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause']);
    Route::post('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume']);
    Route::get('subscriptions/analytics/summary', [SubscriptionController::class, 'analyticsSummary']);
    Route::get('subscriptions/analytics/spending', [SubscriptionController::class, 'spendingAnalytics']);
    Route::get('subscriptions/analytics/categories', [SubscriptionController::class, 'categoryBreakdown']);

    // Contracts
    Route::apiResource('contracts', ContractController::class);
    Route::post('contracts/{contract}/renew', [ContractController::class, 'renew']);
    Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate']);
    Route::get('contracts/analytics/summary', [ContractController::class, 'analyticsSummary']);
    Route::get('contracts/analytics/expiring', [ContractController::class, 'expiringAnalytics']);
    Route::get('contracts/analytics/performance', [ContractController::class, 'performanceAnalytics']);

    // Warranties
    Route::apiResource('warranties', WarrantyController::class);
    Route::post('warranties/{warranty}/claim', [WarrantyController::class, 'createClaim']);
    Route::post('warranties/{warranty}/transfer', [WarrantyController::class, 'transfer']);
    Route::get('warranties/analytics/summary', [WarrantyController::class, 'analyticsSummary']);
    Route::get('warranties/analytics/expiring', [WarrantyController::class, 'expiringAnalytics']);
    Route::get('warranties/analytics/claims', [WarrantyController::class, 'claimsAnalytics']);

    // Investments
    Route::apiResource('investments', InvestmentController::class);
    Route::post('investments/{investment}/update-price', [InvestmentController::class, 'updatePrice']);
    Route::post('investments/{investment}/record-buy', [InvestmentController::class, 'recordBuy']);
    Route::post('investments/{investment}/record-sell', [InvestmentController::class, 'recordSell']);
    Route::post('investments/{investment}/record-dividend', [InvestmentController::class, 'recordDividend']);
    Route::get('investments/portfolio/summary', [InvestmentController::class, 'portfolioSummary']);
    Route::get('investments/analytics/summary', [InvestmentController::class, 'analyticsSummary']);
    Route::get('investments/analytics/performance', [InvestmentController::class, 'performanceAnalytics']);
    Route::get('investments/analytics/allocation', [InvestmentController::class, 'allocationAnalytics']);
    Route::get('investments/analytics/dividends', [InvestmentController::class, 'dividendAnalytics']);
    Route::get('investments/goals', [InvestmentController::class, 'goalIndex']);
    Route::post('investments/goals', [InvestmentController::class, 'goalStore']);
    Route::patch('investments/goals/{goal}', [InvestmentController::class, 'goalUpdate']);
    Route::delete('investments/goals/{goal}', [InvestmentController::class, 'goalDestroy']);
    Route::get('investments/tax-reports', [InvestmentController::class, 'taxReportIndex']);
    Route::get('investments/tax-reports/capital-gains', [InvestmentController::class, 'capitalGainsReport']);
    Route::get('investments/tax-reports/dividend-income', [InvestmentController::class, 'dividendIncomeReport']);
    Route::get('investments/rebalancing/alerts', [InvestmentController::class, 'rebalancingAlerts']);
    Route::post('investments/rebalancing/recommendations', [InvestmentController::class, 'rebalancingRecommendations']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::patch('expenses/{expense}/mark-reimbursed', [ExpenseController::class, 'markReimbursed']);
    Route::post('expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate']);
    Route::post('expenses/bulk-action', [ExpenseController::class, 'bulkAction']);
    Route::get('expenses/analytics/summary', [ExpenseController::class, 'analyticsSummary']);
    Route::get('expenses/analytics/categories', [ExpenseController::class, 'categoryBreakdown']);
    Route::get('expenses/analytics/trends', [ExpenseController::class, 'trendAnalytics']);
    Route::get('expenses/analytics/budget', [ExpenseController::class, 'budgetAnalytics']);

    // Utility Bills
    Route::apiResource('utility-bills', UtilityBillController::class);
    Route::post('utility-bills/{utilityBill}/mark-paid', [UtilityBillController::class, 'markPaid']);
    Route::get('utility-bills/analytics/summary', [UtilityBillController::class, 'analyticsSummary']);
    Route::get('utility-bills/analytics/usage', [UtilityBillController::class, 'usageAnalytics']);
    Route::get('utility-bills/analytics/costs', [UtilityBillController::class, 'costAnalytics']);
    Route::get('utility-bills/analytics/providers', [UtilityBillController::class, 'providerAnalytics']);
});
