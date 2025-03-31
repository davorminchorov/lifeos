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

        <!-- Inline Tailwind Styles - Temporary Fix -->
        <style>
            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                background-color: #F8FAFC;
                color: #1E293B;
            }
            h1 {
                font-size: 2.25rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }
            .container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1rem;
            }
            .login-box {
                width: 100%;
                max-width: 28rem;
                background: white;
                padding: 2rem;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            .form-group {
                margin-bottom: 1rem;
            }
            .form-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                margin-bottom: 0.25rem;
                color: #475569;
            }
            .form-control {
                width: 100%;
                padding: 0.5rem 0.75rem;
                border: 1px solid #e2e8f0;
                border-radius: 0.375rem;
                font-size: 0.875rem;
            }
            .btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                font-weight: 500;
                text-align: center;
                border-radius: 0.375rem;
                transition: background-color 0.15s ease-in-out;
                cursor: pointer;
            }
            .btn-primary {
                background-color: #0F766E;
                color: white;
                border: none;
            }
            .btn-primary:hover {
                background-color: #0e6a63;
            }
            .btn-block {
                display: block;
                width: 100%;
            }
        </style>

        <!-- Debug Info -->
        <meta name="is-local" content="{{ app()->environment('local') ? 'true' : 'false' }}">
        <meta name="app-url" content="{{ config('app.url') }}">

        <!-- Direct CSS reference -->
        <link rel="stylesheet" href="{{ asset('build/assets/styles-IHzpTDQ5.css') }}">

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
    <body class="font-sans antialiased bg-gray-50 text-slate-800">
        <div id="app"></div>
    </body>
</html>
