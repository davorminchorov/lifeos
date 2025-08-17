<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard Routes
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Life Management Platform Routes
    // Analytics routes must come before resource routes to prevent conflicts
    Route::get('subscriptions/analytics/summary', [SubscriptionController::class, 'analyticsSummary'])->name('subscriptions.analytics.summary');
    Route::get('subscriptions/analytics/spending', [SubscriptionController::class, 'spendingAnalytics'])->name('subscriptions.analytics.spending');
    Route::get('subscriptions/analytics/category-breakdown', [SubscriptionController::class, 'categoryBreakdown'])->name('subscriptions.analytics.category-breakdown');

    Route::resource('subscriptions', SubscriptionController::class);
    Route::patch('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::patch('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::patch('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::resource('contracts', ContractController::class);
    Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
    Route::post('contracts/{contract}/renew', [ContractController::class, 'renew'])->name('contracts.renew');
    Route::post('contracts/{contract}/add-amendment', [ContractController::class, 'addAmendment'])->name('contracts.add-amendment');
    Route::resource('warranties', WarrantyController::class);
    Route::post('warranties/{warranty}/file-claim', [WarrantyController::class, 'fileClaim'])->name('warranties.file-claim');
    Route::patch('warranties/{warranty}/update-claim', [WarrantyController::class, 'updateClaim'])->name('warranties.update-claim');
    Route::post('warranties/{warranty}/transfer', [WarrantyController::class, 'transfer'])->name('warranties.transfer');
    Route::post('warranties/{warranty}/add-maintenance-reminder', [WarrantyController::class, 'addMaintenanceReminder'])->name('warranties.add-maintenance-reminder');
    Route::resource('investments', InvestmentController::class);
    Route::post('investments/{investment}/record-transaction', [InvestmentController::class, 'recordTransaction'])->name('investments.record-transaction');
    Route::post('investments/{investment}/record-buy', [InvestmentController::class, 'recordBuy'])->name('investments.record-buy');
    Route::post('investments/{investment}/record-sell', [InvestmentController::class, 'recordSell'])->name('investments.record-sell');
    Route::post('investments/{investment}/record-dividend', [InvestmentController::class, 'recordDividend'])->name('investments.record-dividend');
    Route::post('investments/{investment}/update-price', [InvestmentController::class, 'updatePrice'])->name('investments.update-price');
    Route::get('investments/goals/index', [InvestmentController::class, 'goalIndex'])->name('investments.goals.index');
    Route::post('investments/goals/store', [InvestmentController::class, 'goalStore'])->name('investments.goals.store');
    Route::patch('investments/goals/{goal}/update', [InvestmentController::class, 'goalUpdate'])->name('investments.goals.update');
    Route::delete('investments/goals/{goal}', [InvestmentController::class, 'goalDestroy'])->name('investments.goals.destroy');
    Route::get('investments/tax-reports/index', [InvestmentController::class, 'taxReportIndex'])->name('investments.tax-reports.index');
    Route::get('investments/tax-reports/capital-gains', [InvestmentController::class, 'capitalGainsReport'])->name('investments.tax-reports.capital-gains');
    Route::get('investments/tax-reports/dividend-income', [InvestmentController::class, 'dividendIncomeReport'])->name('investments.tax-reports.dividend-income');
    Route::get('investments/rebalancing/alerts', [InvestmentController::class, 'rebalancingAlerts'])->name('investments.rebalancing.alerts');
    Route::post('investments/rebalancing/recommendations', [InvestmentController::class, 'rebalancingRecommendations'])->name('investments.rebalancing.recommendations');

    Route::resource('expenses', ExpenseController::class);
    Route::get('expenses/analytics', [ExpenseController::class, 'analytics'])->name('expenses.analytics');
    Route::patch('expenses/{expense}/mark-reimbursed', [ExpenseController::class, 'markReimbursed'])->name('expenses.mark-reimbursed');
    Route::post('expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate'])->name('expenses.duplicate');
    Route::post('expenses/bulk-action', [ExpenseController::class, 'bulkAction'])->name('expenses.bulk-action');

    Route::resource('utility-bills', UtilityBillController::class);
    Route::patch('utility-bills/{utility_bill}/mark-paid', [UtilityBillController::class, 'markPaid'])->name('utility-bills.mark-paid');

    // File Management Routes
    Route::prefix('files')->name('files.')->group(function () {
        Route::post('{category}/upload', [FileUploadController::class, 'upload'])->name('upload');
        Route::get('{category}/{filename}/download', [FileUploadController::class, 'download'])->name('download');
        Route::get('{category}/{filename}/view', [FileUploadController::class, 'view'])->name('view');
        Route::get('{category}/{filename}/info', [FileUploadController::class, 'getFileInfo'])->name('info');
        Route::delete('{category}/{filename}', [FileUploadController::class, 'delete'])->name('delete');
        Route::get('types/{category?}', [FileUploadController::class, 'getAllowedTypes'])->name('types');
    });
});
