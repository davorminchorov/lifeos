<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\UtilityBill;
use App\Observers\ExpenseObserver;
use App\Observers\UtilityBillObserver;
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
        Expense::observe(ExpenseObserver::class);
        UtilityBill::observe(UtilityBillObserver::class);
    }
}
