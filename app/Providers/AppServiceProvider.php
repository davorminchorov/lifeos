<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for Vite manifest not found error
        if (! file_exists(public_path('build/manifest.json')) && file_exists(public_path('build/.vite/manifest.json'))) {
            copy(public_path('build/.vite/manifest.json'), public_path('build/manifest.json'));
        }
    }
}
