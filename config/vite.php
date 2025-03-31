<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Manifest Location
    |--------------------------------------------------------------------------
    |
    | This value sets the path to the Vite manifest file.
    | If a path begins with a slash, it will be considered as an absolute path.
    | If a path does not begin with a slash, it will be considered as a path relative to the base path.
    |
    */

    'manifest_path' => public_path('build/.vite/manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | Vite Build Path
    |--------------------------------------------------------------------------
    |
    | The directory where your Vite built files are located.
    | This is used to generate the correct URLs when using Vite's Hot Module Replacement.
    |
    */

    'build_path' => 'build',

    /*
    |--------------------------------------------------------------------------
    | Development Server
    |--------------------------------------------------------------------------
    |
    | This value determines the address the Vite dev server is running at.
    | Leave this as null if you do not use a development server.
    |
    */

    'dev_url' => env('VITE_DEV_SERVER_URL', 'http://localhost:5173'),
];
