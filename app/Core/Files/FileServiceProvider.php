<?php

namespace App\Core\Files;

use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileService::class, function ($app) {
            return new FileService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Run storage link command if not already
        if (!file_exists(public_path('storage'))) {
            $this->app->make('files')->link(
                storage_path('app/public'), public_path('storage')
            );
        }
    }
}
