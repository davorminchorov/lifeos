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
        // Inter-module events for due/expiring entities
        \App\Events\SubscriptionRenewalDue::class => [
            \App\Listeners\SendSubscriptionRenewalNotification::class,
        ],
        \App\Events\ContractNotificationDue::class => [
            \App\Listeners\SendContractExpirationNotification::class,
        ],
        \App\Events\UtilityBillDueSoon::class => [
            \App\Listeners\SendUtilityBillDueNotification::class,
        ],
        \App\Events\WarrantyExpirationDue::class => [
            \App\Listeners\SendWarrantyExpirationNotification::class,
        ],
    ];
}
