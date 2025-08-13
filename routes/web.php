<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

// Dashboard Routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Life Management Platform Routes
Route::resource('subscriptions', SubscriptionController::class);
Route::patch('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
Route::patch('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
Route::patch('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

Route::resource('contracts', ContractController::class);
Route::resource('warranties', WarrantyController::class);
Route::resource('investments', InvestmentController::class);

Route::resource('expenses', ExpenseController::class);
Route::patch('expenses/{expense}/mark-reimbursed', [ExpenseController::class, 'markReimbursed'])->name('expenses.mark-reimbursed');

Route::resource('utility-bills', UtilityBillController::class);
Route::patch('utility-bills/{utility_bill}/mark-paid', [UtilityBillController::class, 'markPaid'])->name('utility-bills.mark-paid');
