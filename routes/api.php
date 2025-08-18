<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// API Routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard API endpoints
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('api.dashboard.chart-data');
});
