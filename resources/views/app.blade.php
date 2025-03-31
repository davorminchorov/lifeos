<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>LifeOS - Personal Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Debug Info -->
        <meta name="is-local" content="{{ app()->environment('local') ? 'true' : 'false' }}">
        <meta name="app-url" content="{{ config('app.url') }}">

        <!-- Scripts and Styles -->
        @php
        $manifestPath = public_path('build/.vite/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            echo "<!-- Manifest found at correct path: " . count($manifest) . " entries -->";
        } else {
            echo "<!-- Manifest not found at: $manifestPath -->";

            // Check alternative location
            $altPath = public_path('build/manifest.json');
            if (file_exists($altPath)) {
                echo "<!-- But found at: $altPath -->";
            }
        }
        @endphp

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    </head>
    <body class="font-sans antialiased bg-[#F8FAFC] text-[#1E293B]">
        <div id="app"></div>
    </body>
</html>
