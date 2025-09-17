<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\ExpenseSaved::class => [
            \App\Listeners\EvaluateBudgetOnExpenseSaved::class,
        ],
        \App\Events\BudgetThresholdCrossed::class => [
            \App\Listeners\SendBudgetThresholdNotification::class,
        ],
    ];
}
