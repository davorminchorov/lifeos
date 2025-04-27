<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Subscriptions\UI\Api\SubscriptionController;

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

// Subscription routes (protected by auth middleware)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::put('/subscriptions/{id}', [SubscriptionController::class, 'update']);
    Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{id}/payments', [SubscriptionController::class, 'recordPayment']);
    Route::get('/upcoming-payments', [SubscriptionController::class, 'upcomingPayments']);
});

// Expenses routes
Route::group(['prefix' => 'expenses', 'namespace' => 'App\Expenses\UI\API'], function () {
    Route::get('/', 'ExpensesController@index');
    Route::post('/', 'ExpensesController@store');
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
