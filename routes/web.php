<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Diagnostic route to see all configuration
Route::get('/diagnostic', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'app_name' => config('app.name'),
        'inertia_version' => \Inertia\Inertia::getVersion(),
        'available_pages' => [
            'Test',
            'Authentication/UI/Simple',
            'Authentication/UI/Login',
            'Dashboard/UI/Dashboard',
            'Authentication/UI/NewTest'
        ],
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'asset_url' => config('app.asset_url'),
        'manifest_exists' => file_exists(public_path('build/manifest.json')),
    ]);
});

Route::get('/', function () {
    // Output both views to check which one works
    if (request()->has('test')) {
        return Inertia::render('Test');
    }

    if (request()->has('debug')) {
        // For debugging - output plain HTML
        return '<h1>Debug Mode</h1><p>This is a direct response without Inertia.</p>';
    }

    // Revert to login component
    return Inertia::render('Authentication/UI/Login');
});

// Test routes for each component
Route::get('/test-simple', function () {
    return Inertia::render('Authentication/UI/Simple');
})->name('test-simple');

Route::get('/test-test', function () {
    return Inertia::render('Test');
})->name('test-test');

Route::get('/test-login', function () {
    return Inertia::render('Authentication/UI/Login');
})->name('test-login');

Route::get('/test-new', function () {
    return Inertia::render('Authentication/UI/NewTest');
})->name('test-new');

// Authentication routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard route (protected)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/UI/Dashboard');
    })->name('dashboard');
});
