<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'LifeOS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono&display=swap" rel="stylesheet">

        <!-- Debug Info -->
        <meta name="asset-url" content="{{ asset('build/manifest.json') }}">
        <meta name="is-local" content="{{ app()->environment('local') ? 'true' : 'false' }}">
        <meta name="app-url" content="{{ config('app.url') }}">

        <!-- Direct Asset Links (For Production & When Vite Server is Down) -->
        @production
            <link rel="stylesheet" href="{{ asset('build/assets/app-C6fcpKUa.css') }}">
            <script src="{{ asset('build/assets/app-BE1fph-U.js') }}" defer></script>
        @else
            @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @endproduction

        @routes
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
