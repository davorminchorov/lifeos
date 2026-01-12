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
                        @auth
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Dashboard
                            </a>

                            <!-- Finance Dropdown -->
                            <div class="relative inline-flex items-center nav-dropdown">
                                <button type="button" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs(['budgets.*', 'expenses.*', 'subscriptions.*', 'utility-bills.*']) ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200" aria-haspopup="true" aria-expanded="false" aria-controls="menu-finance">
                                    Finance
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <div id="menu-finance" class="dropdown-menu hidden absolute top-full left-0 mt-2 w-56 rounded-lg shadow-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] ring-1 ring-black/50 z-50" role="menu">
                                    <div class="py-1">
                                        <a href="{{ route('budgets.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('budgets.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Budgets</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Financial planning</div>
                                        </a>
                                        <a href="{{ route('expenses.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('expenses.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Expenses</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Track spending</div>
                                        </a>
                                        <a href="{{ route('subscriptions.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('subscriptions.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Subscriptions</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Recurring payments</div>
                                        </a>
                                        <a href="{{ route('utility-bills.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('utility-bills.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Utility Bills</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Manage utilities</div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Assets & Liabilities Dropdown -->
                            <div class="relative inline-flex items-center nav-dropdown">
                                <button type="button" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs(['investments.*', 'project-investments.*', 'contracts.*', 'warranties.*', 'ious.*']) ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200" aria-haspopup="true" aria-expanded="false" aria-controls="menu-assets">
                                    Assets & Liabilities
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                                <div id="menu-assets" class="dropdown-menu hidden absolute top-full left-0 mt-2 w-56 rounded-lg shadow-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] ring-1 ring-black/50 z-50" role="menu">
                                    <div class="py-1">
                                        <a href="{{ route('investments.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('investments.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Investments</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Portfolio tracking</div>
                                        </a>
                                        <a href="{{ route('project-investments.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('project-investments.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Project Investments</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Startups & projects</div>
                                        </a>
                                        <a href="{{ route('contracts.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('contracts.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Contracts</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Manage agreements</div>
                                        </a>
                                        <a href="{{ route('warranties.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('warranties.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">Warranties</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Product coverage</div>
                                        </a>
                                        <a href="{{ route('ious.index') }}" role="menuitem" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200 {{ request()->routeIs('ious.*') ? 'bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)]' : '' }}">
                                            <div class="font-medium">IOUs</div>
                                            <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Debts & loans</div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Job Applications -->
                            <a href="{{ route('job-applications.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('job-applications.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Job Applications
                            </a>

                            <!-- Cycle Menu -->
                            <a href="{{ route('cycle-menus.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('cycle-menus.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Cycle Menu
                            </a>

                            <!-- Holidays -->
                            <a href="{{ route('holidays.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('holidays.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]' : 'border-transparent text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">
                                Holidays
                            </a>
                        </div>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    @auth
                    <div class="flex items-center sm:hidden">
                        <button type="button" id="mobile-menu-button" class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-primary-600)] dark:hover:text-[color:var(--color-dark-400)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-200)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[color:var(--color-accent-500)] p-2 rounded-md" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Hamburger icon -->
                            <svg id="menu-icon" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Close icon -->
                            <svg id="close-icon" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endauth

                    <!-- User Menu & Dark Mode Toggle -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <!-- User Dropdown -->
                            <div class="relative">
                                <button id="user-menu-button" type="button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)] transition-colors duration-200" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 rounded-full bg-[color:var(--color-accent-600)] flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="ml-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ Auth::user()->name }}</span>
                                    <svg class="ml-1 h-4 w-4 text-[color:var(--color-primary-400)]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div id="user-menu" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] ring-1 ring-black/50 divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)] focus:outline-none z-50">
                                    <div class="py-1">
                                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">Profile</a>
                                        <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">Settings</a>
                                        <a href="{{ route('currency.index') }}" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">Currency Rates</a>
                                        <a href="{{ route('currency.freelance-rate-calculator') }}" class="block px-4 py-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">Freelance Rate Calculator</a>
                                    </div>
                                    <div class="py-1">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-[color:var(--color-danger-600)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] transition-colors duration-200">
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Login Links -->
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('login') }}" class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] hover:text-[color:var(--color-accent-600)] transition-colors duration-200">Sign in</a>
                            </div>
                        @endauth

                        <!-- Dark Mode Toggle -->
                        <button id="theme-toggle" type="button" class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-200)] focus:outline-none focus:ring-4 focus:ring-[color:var(--color-primary-300)] dark:focus:ring-[color:var(--color-dark-300)] rounded-lg text-sm p-2.5 transition-colors duration-200">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L13.09 8.26L20 9L14 14.74L15.18 21.02L10 18L4.82 21.02L6 14.74L0 9L6.91 8.26L10 2Z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            @auth
            @php
                $financeOpen = request()->routeIs(['budgets.*', 'expenses.*', 'subscriptions.*', 'utility-bills.*']);
                $assetsOpen = request()->routeIs(['investments.*', 'project-investments.*', 'contracts.*', 'warranties.*', 'ious.*']);
            @endphp
            <div id="mobile-menu" class="sm:hidden hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Dashboard</a>

                    <!-- Finance Group -->
                    <div class="mobile-nav-group">
                        <button type="button" class="mobile-group-toggle w-full flex justify-between items-center pl-3 pr-4 py-2 border-l-4 border-transparent text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-base font-semibold">
                            Finance
                            <svg class="h-5 w-5 transform transition-transform duration-200 {{ $financeOpen ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div class="mobile-group-items {{ $financeOpen ? '' : 'hidden' }}">
                            <a href="{{ route('budgets.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('budgets.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Budgets</a>
                            <a href="{{ route('expenses.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('expenses.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Expenses</a>
                            <a href="{{ route('subscriptions.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('subscriptions.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Subscriptions</a>
                            <a href="{{ route('utility-bills.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('utility-bills.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Utility Bills</a>
                        </div>
                    </div>

                    <!-- Assets & Liabilities Group -->
                    <div class="mobile-nav-group">
                        <button type="button" class="mobile-group-toggle w-full flex justify-between items-center pl-3 pr-4 py-2 border-l-4 border-transparent text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-base font-semibold">
                            Assets & Liabilities
                            <svg class="h-5 w-5 transform transition-transform duration-200 {{ $assetsOpen ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div class="mobile-group-items {{ $assetsOpen ? '' : 'hidden' }}">
                            <a href="{{ route('investments.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('investments.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Investments</a>
                            <a href="{{ route('project-investments.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('project-investments.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Project Investments</a>
                            <a href="{{ route('contracts.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('contracts.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Contracts</a>
                            <a href="{{ route('warranties.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('warranties.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">Warranties</a>
                            <a href="{{ route('ious.index') }}" class="block pl-8 pr-4 py-2 border-l-4 {{ request()->routeIs('ious.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-sm font-medium transition-colors duration-200">IOUs</a>
                        </div>
                    </div>

                    <a href="{{ route('job-applications.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('job-applications.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Job Applications</a>

                    <a href="{{ route('cycle-menus.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('cycle-menus.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Cycle Menu</a>

                    <a href="{{ route('holidays.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('holidays.*') ? 'border-[color:var(--color-accent-500)] text-[color:var(--color-accent-600)] bg-[color:var(--color-accent-50)]' : 'border-transparent text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] hover:bg-[color:var(--color-primary-200)] hover:border-[color:var(--color-primary-400)]' }} text-base font-medium transition-colors duration-200">Holidays</a>
                </div>
            </div>
            @endauth
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
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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

        // User menu dropdown functionality
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');

        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', function() {
                userMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }

        // Mobile menu toggle functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';

                mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.classList.toggle('hidden');
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
            });

            // Close mobile menu when screen size changes to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 640) { // sm breakpoint
                    mobileMenu.classList.add('hidden');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Accessible desktop dropdowns (single controller)
        (() => {
            const dropdowns = Array.from(document.querySelectorAll('.nav-dropdown'));
            let open = null; // {button, menu}

            function closeOpen() {
                if (!open) return;
                open.menu.classList.add('hidden');
                open.button.setAttribute('aria-expanded', 'false');
                open = null;
            }

            function openMenu(button, menu) {
                // Close others
                closeOpen();
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
                open = { button, menu };
                // Focus first item
                const firstItem = menu.querySelector('[role="menuitem"], a, button');
                if (firstItem) firstItem.focus({ preventScroll: true });
            }

            // Wire buttons
            dropdowns.forEach(dd => {
                const button = dd.querySelector('button[aria-controls]');
                const menuId = button?.getAttribute('aria-controls');
                const menu = menuId ? document.getElementById(menuId) : null;
                if (!button || !menu) return;

                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (open && open.menu === menu) {
                        closeOpen();
                    } else {
                        openMenu(button, menu);
                    }
                });

                // Keyboard on button
                button.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        if (open && open.menu === menu) return;
                        openMenu(button, menu);
                    }
                });

                // Keyboard within menu
                menu.addEventListener('keydown', (e) => {
                    const items = Array.from(menu.querySelectorAll('[role="menuitem"], a, button')).filter(i => !i.disabled);
                    const idx = items.indexOf(document.activeElement);
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        closeOpen();
                        button.focus();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        items[(idx + 1) % items.length]?.focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        items[(idx - 1 + items.length) % items.length]?.focus();
                    } else if (e.key === 'Home') {
                        e.preventDefault();
                        items[0]?.focus();
                    } else if (e.key === 'End') {
                        e.preventDefault();
                        items[items.length - 1]?.focus();
                    }
                });
            });

            // Outside click and Esc
            document.addEventListener('click', (e) => {
                if (!open) return;
                const { button, menu } = open;
                if (!button.contains(e.target) && !menu.contains(e.target)) {
                    closeOpen();
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeOpen();
            });

            // Close on resize to desktop/mobile transitions
            window.addEventListener('resize', closeOpen);
        })();

        // Mobile navigation group toggle functionality
        document.querySelectorAll('.mobile-group-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const group = this.parentElement;
                const items = group.querySelector('.mobile-group-items');
                const icon = this.querySelector('svg');

                if (items && icon) {
                    items.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
