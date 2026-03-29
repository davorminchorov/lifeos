<?php

use App\Http\Controllers\Api\AssistantController;
use App\Http\Controllers\Api\CurrencyController;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::get('/currency/exchange-rate', [CurrencyController::class, 'getExchangeRate'])->name('api.currency.exchange-rate');

// Assistant routes (require auth + tenant)
Route::middleware(['web', 'auth', 'tenant'])->prefix('assistant')->name('api.assistant.')->group(function () {
    Route::post('/message', [AssistantController::class, 'message'])->name('message')->middleware('throttle:30,1');
    Route::get('/suggestions', [AssistantController::class, 'suggestions'])->name('suggestions')->middleware('throttle:60,1');
    Route::get('/history', [AssistantController::class, 'history'])->name('history')->middleware('throttle:60,1');
});
