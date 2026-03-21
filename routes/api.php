<?php

use App\Http\Controllers\Api\CurrencyController;
use App\Telegram\TelegramBotController;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::get('/currency/exchange-rate', [CurrencyController::class, 'getExchangeRate'])->name('api.currency.exchange-rate');

// Telegram Bot Webhook
Route::post('/telegram/webhook', [TelegramBotController::class, 'handle'])
    ->middleware('telegram.auth')
    ->name('telegram.webhook');
