<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'LifeOS - Personal Life Management Platform')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-50)]">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-100)] border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                LifeOS
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Dashboard
                            </a>
                            <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('subscriptions.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Subscriptions
                            </a>
                            <a href="{{ route('contracts.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('contracts.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Contracts
                            </a>
                            <a href="{{ route('warranties.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('warranties.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Warranties
                            </a>
                            <a href="{{ route('investments.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('investments.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Investments
                            </a>
                            <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('expenses.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Expenses
                            </a>
                            <a href="{{ route('utility-bills.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('utility-bills.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Utility Bills
                            </a>
                        </div>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <div class="flex items-center">
                        <button id="theme-toggle" type="button" class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-200)] focus:outline-none focus:ring-4 focus:ring-[color:var(--color-primary-300)] dark:focus:ring-[color:var(--color-dark-300)] rounded-lg text-sm p-2.5 transition-colors duration-200">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L13.09 8.26L20 9L14 14.74L15.18 21.02L10 18L4.82 21.02L6 14.74L0 9L6.91 8.26L10 2Z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Dashboard</a>
                    <a href="{{ route('subscriptions.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('subscriptions.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Subscriptions</a>
                    <a href="{{ route('contracts.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('contracts.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Contracts</a>
                    <a href="{{ route('warranties.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('warranties.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Warranties</a>
                    <a href="{{ route('investments.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('investments.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Investments</a>
                    <a href="{{ route('expenses.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('expenses.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Expenses</a>
                    <a href="{{ route('utility-bills.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('utility-bills.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Utility Bills</a>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        @hasSection('header')
            <header class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-100)] shadow-sm border-b border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('header')
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-[color:var(--color-success-50)] border border-[color:var(--color-success-500)] text-[color:var(--color-success-600)] px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-[color:var(--color-danger-50)] border border-[color:var(--color-danger-500)] text-[color:var(--color-danger-600)] px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-[color:var(--color-danger-50)] border border-[color:var(--color-danger-500)] text-[color:var(--color-danger-600)] px-4 py-3 rounded-lg relative" role="alert">
                    <div class="font-bold">Please correct the following errors:</div>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Dark mode script -->
    <script>
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
            document.documentElement.classList.add('dark');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
            document.documentElement.classList.remove('dark');
        }

        const themeToggleBtn = document.getElementById('theme-toggle');

        themeToggleBtn.addEventListener('click', function() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }

            // if NOT set via local storage previously
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });
    </script>
</body>
</html>
