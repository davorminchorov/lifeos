<?php

namespace App\Providers;

use App\Expenses\Subscribers\ExpenseEventSubscriber;
use App\JobApplications\Subscribers\JobApplicationEventSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Register individual event listeners here
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        ExpenseEventSubscriber::class,
        JobApplicationEventSubscriber::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
