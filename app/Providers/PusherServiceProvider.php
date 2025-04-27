<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class PusherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set Pusher options based on configuration
        $this->configurePusher();
    }

    /**
     * Configure Pusher with additional options if needed.
     */
    protected function configurePusher(): void
    {
        if (Config::get('broadcasting.default') !== 'pusher') {
            return;
        }

        $options = Config::get('broadcasting.connections.pusher.options', []);

        // Add any additional configuration options if needed
        $options['cluster'] = Config::get('broadcasting.connections.pusher.options.cluster', 'mt1');

        // Update the configuration
        Config::set('broadcasting.connections.pusher.options', $options);
    }
}
