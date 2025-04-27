<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Subscriptions\UI\Api\SubscriptionController;
use App\UtilityBills\UI\Api\UtilityBillsController;
use App\Dashboard\UI\Api\DashboardController;
use App\Investments\UI\Api\InvestmentController;
use App\JobApplications\UI\Api\JobApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Password reset routes
Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

// Subscription routes (protected by auth middleware)
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard route
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::put('/subscriptions/{id}', [SubscriptionController::class, 'update']);
    Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{id}/payments', [SubscriptionController::class, 'recordPayment']);
    Route::get('/upcoming-payments', [SubscriptionController::class, 'upcomingPayments']);

    // Utility Bills routes
    Route::get('/utility-bills', [UtilityBillsController::class, 'index']);
    Route::post('/utility-bills', [UtilityBillsController::class, 'store']);
    Route::get('/utility-bills/{id}', [UtilityBillsController::class, 'show']);
    Route::put('/utility-bills/{id}', [UtilityBillsController::class, 'update']);
    Route::post('/utility-bills/{id}/pay', [UtilityBillsController::class, 'pay']);
    Route::post('/utility-bills/{id}/remind', [UtilityBillsController::class, 'scheduleReminder']);
    Route::get('/utility-bills/pending', [UtilityBillsController::class, 'pendingBills']);
    Route::get('/utility-bills/reminders', [UtilityBillsController::class, 'upcomingReminders']);
    Route::get('/utility-bills/payments', [UtilityBillsController::class, 'paymentHistory']);

    // Investment routes
    Route::get('/investments', [InvestmentController::class, 'index']);
    Route::post('/investments', [InvestmentController::class, 'store']);
    Route::get('/investments/{id}', [InvestmentController::class, 'show']);
    Route::post('/investments/{id}/transactions', [InvestmentController::class, 'recordTransaction']);
    Route::post('/investments/{id}/valuations', [InvestmentController::class, 'updateValuation']);
    Route::get('/investments/{id}/performance', [InvestmentController::class, 'getPerformance']);
    Route::get('/portfolio/summary', [InvestmentController::class, 'getPortfolioSummary']);

    // Job Application routes
    Route::get('/job-applications', [JobApplicationController::class, 'index']);
    Route::post('/job-applications', [JobApplicationController::class, 'store']);
    Route::get('/job-applications/{id}', [JobApplicationController::class, 'show']);
    Route::put('/job-applications/{id}', [JobApplicationController::class, 'update']);
    Route::post('/job-applications/{id}/interviews', [JobApplicationController::class, 'scheduleInterview']);
    Route::post('/job-applications/{id}/outcome', [JobApplicationController::class, 'recordOutcome']);
    Route::get('/active-applications', [JobApplicationController::class, 'activeApplications']);
    Route::get('/interview-schedule', [JobApplicationController::class, 'interviewSchedule']);
});

// Expenses routes
Route::group(['prefix' => 'expenses', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/', 'ExpensesController@index');
    Route::get('/{id}', 'ExpensesController@show');
    Route::post('/', 'ExpensesController@store');
    Route::put('/{id}', 'ExpensesController@update');
    Route::delete('/{id}', 'ExpensesController@destroy');
    Route::post('/{expenseId}/categorize', 'ExpensesController@categorize');
});

// Budgets routes
Route::group(['prefix' => 'budgets', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/', 'BudgetsController@index');
    Route::post('/', 'BudgetsController@store');
});

// Categories routes
Route::group(['prefix' => 'categories', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/', 'CategoriesController@index');
    Route::post('/', 'CategoriesController@store');
    Route::put('/{categoryId}', 'CategoriesController@update');
    Route::delete('/{categoryId}', 'CategoriesController@destroy');
});

// Expense Reports routes
Route::group(['prefix' => 'expense-reports', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/monthly-summary', 'ReportsController@monthlySummary');
    Route::get('/category-distribution', 'ReportsController@categoryDistribution');
});

// Expense Exports routes
Route::group(['prefix' => 'expense-exports', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/csv', 'ExportsController@exportExpenses');
});

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/subscriptions-summary', App\Dashboard\UI\Api\SubscriptionSummaryController::class);
    Route::get('/utility-bills-summary', App\Dashboard\UI\Api\UtilityBillsSummaryController::class);
    Route::get('/job-applications-summary', App\Dashboard\UI\Api\JobApplicationsSummaryController::class);
});

// File attachment routes
Route::prefix('files')->group(function () {
    Route::post('/upload', [App\Core\Http\Controllers\FileController::class, 'upload']);
    Route::get('/entity', [App\Core\Http\Controllers\FileController::class, 'getFiles']);
    Route::get('/{id}', [App\Core\Http\Controllers\FileController::class, 'show']);
    Route::get('/{id}/download', [App\Core\Http\Controllers\FileController::class, 'download']);
    Route::delete('/{id}', [App\Core\Http\Controllers\FileController::class, 'delete']);
});
