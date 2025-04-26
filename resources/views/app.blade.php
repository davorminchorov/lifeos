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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Inline Tailwind Styles - Temporary Fix -->
        <style>
            body {
                font-family: 'Inter', sans-serif;
                margin: 0;
                padding: 0;
                background-color: #F9FAFB;
                color: #1E293B;
            }
            .container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1.5rem;
                background: linear-gradient(
                    135deg,
                    rgba(240, 249, 255, 0.4) 0%,
                    rgba(249, 250, 251, 1) 100%
                );
            }

            @media (min-width: 768px) {
                .container {
                    background-image:
                        radial-gradient(circle at 0% 0%, rgba(56, 189, 248, 0.08), transparent 25%),
                        radial-gradient(circle at 100% 100%, rgba(20, 184, 166, 0.08), transparent 25%),
                        linear-gradient(
                            135deg,
                            rgba(240, 249, 255, 0.4) 0%,
                            rgba(249, 250, 251, 1) 100%
                        );
                    padding: 2rem;
                }
            }
        </style>

        <!-- Debug Info -->
        <meta name="is-local" content="{{ app()->environment('local') ? 'true' : 'false' }}">
        <meta name="app-url" content="{{ config('app.url') }}">

        <!-- Direct CSS reference -->
        <link rel="stylesheet" href="{{ asset('build/assets/styles-Dx-XYl0p.css') }}">

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
