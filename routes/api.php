<?php

use App\Http\Controllers\Api\CurrencyController;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::get('/currency/exchange-rate', [CurrencyController::class, 'getExchangeRate'])->name('api.currency.exchange-rate');
