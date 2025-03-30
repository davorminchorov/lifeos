<?php

namespace App\Core\EventSourcing;

use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\EventBus\SimpleEventBus;
use App\Core\EventSourcing\Projectors\ProjectorManager;
use App\Core\EventSourcing\Store\EventStore;
use App\Core\EventSourcing\Store\MySqlEventStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class EventSourcingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register event store
        $this->app->singleton(EventStore::class, function ($app) {
            return new MySqlEventStore(DB::connection());
        });

        // Register event bus
        $this->app->singleton(EventBus::class, function ($app) {
            return new SimpleEventBus();
        });

        // Register projector manager
        $this->app->singleton(ProjectorManager::class, function ($app) {
            return new ProjectorManager(
                $app->make(EventBus::class),
                $app->make(EventStore::class)
            );
        });

        // Register command bus
        $this->app->singleton(CommandBus::class, function ($app) {
            return new CommandBus($app);
        });

        // Register query bus
        $this->app->singleton(QueryBus::class, function ($app) {
            return new QueryBus($app);
        });
    }

    public function boot(): void
    {
        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }
}
