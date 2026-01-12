<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Browserless API Key
    |--------------------------------------------------------------------------
    |
    | This is your Browserless.io API key for accessing their browser
    | automation service.
    |
    */
    'api_key' => env('BROWSERLESS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Browserless API Endpoint
    |--------------------------------------------------------------------------
    |
    | The base URL for the Browserless API. You can use the cloud service
    | or self-hosted instance.
    |
    */
    'api_endpoint' => env('BROWSERLESS_API_ENDPOINT', 'https://chrome.browserless.io'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for a crawl operation to complete.
    |
    */
    'timeout' => env('BROWSERLESS_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Investor Portal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Macedonian investor portal crawling.
    |
    */
    'investor_portal' => [
        'url' => env('INVESTOR_PORTAL_URL', 'https://investor.wvpfondovi.mk/frmPrijava.aspx'),
        'username' => env('INVESTOR_PORTAL_USERNAME'),
        'password' => env('INVESTOR_PORTAL_PASSWORD'),
        'dashboard_url' => env('INVESTOR_PORTAL_DASHBOARD_URL', 'https://investor.wvpfondovi.mk/frmPocetna.aspx'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed crawl attempts.
    |
    */
    'retry' => [
        'max_attempts' => env('BROWSERLESS_RETRY_ATTEMPTS', 3),
        'delay_seconds' => env('BROWSERLESS_RETRY_DELAY', 60),
    ],
];
