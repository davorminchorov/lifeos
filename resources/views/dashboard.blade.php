@extends('layouts.app')

@section('title', 'Dashboard - LifeOS')

@section('header')
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        Dashboard
    </h1>
    <p class="mt-2 text-gray-600 dark:text-gray-400">
        Welcome to your personal life management platform
    </p>
@endsection

@section('content')
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Subscriptions Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Subscriptions</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['active_subscriptions'] ?? 0 }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('subscriptions.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Monthly cost:</span>
                    <span class="font-medium text-gray-900 dark:text-white">${{ number_format($stats['monthly_subscription_cost'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Contracts Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Contracts</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['active_contracts'] ?? 0 }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('contracts.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Expiring soon:</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $stats['contracts_expiring_soon'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Investments Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Portfolio Value</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">${{ number_format($stats['portfolio_value'] ?? 0, 2) }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('investments.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Total return:</span>
                    <span class="font-medium {{ ($stats['total_return'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($stats['total_return'] ?? 0) >= 0 ? '+' : '' }}${{ number_format($stats['total_return'] ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    @if(isset($alerts) && count($alerts) > 0)
        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Alerts & Notifications</h2>
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($alerts as $alert)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($alert['type'] === 'warning')
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($alert['type'] === 'info')
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $alert['title'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $alert['message'] }}</p>
                                    </div>
                                </div>
                                @if(isset($alert['action_url']))
                                    <div class="flex-shrink-0">
                                        <a href="{{ $alert['action_url'] }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                            {{ $alert['action_text'] ?? 'View' }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Recent Activity & Quick Access -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Expenses -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Expenses</h3>
                    <a href="{{ route('expenses.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">View all</a>
                </div>
                @if(isset($recent_expenses) && count($recent_expenses) > 0)
                    <div class="space-y-3">
                        @foreach($recent_expenses as $expense)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $expense->description }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $expense->category }} • {{ $expense->expense_date->format('M j, Y') }}</p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($expense->amount, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No recent expenses found.</p>
                @endif
            </div>
        </div>

        <!-- Upcoming Bills -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Upcoming Bills</h3>
                    <a href="{{ route('utility-bills.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">View all</a>
                </div>
                @if(isset($upcoming_bills) && count($upcoming_bills) > 0)
                    <div class="space-y-3">
                        @foreach($upcoming_bills as $bill)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $bill->service_provider }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($bill->utility_type) }} • Due {{ $bill->due_date->format('M j, Y') }}</p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($bill->bill_amount, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No upcoming bills found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('subscriptions.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white dark:bg-blue-900 dark:text-blue-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Subscription
                    </h3>
                </div>
            </a>

            <a href="{{ route('expenses.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white dark:bg-green-900 dark:text-green-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Expense
                    </h3>
                </div>
            </a>

            <a href="{{ route('contracts.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-700 ring-4 ring-white dark:bg-purple-900 dark:text-purple-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Contract
                    </h3>
                </div>
            </a>

            <a href="{{ route('warranties.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-700 ring-4 ring-white dark:bg-yellow-900 dark:text-yellow-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Warranty
                    </h3>
                </div>
            </a>

            <a href="{{ route('investments.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-indigo-50 text-indigo-700 ring-4 ring-white dark:bg-indigo-900 dark:text-indigo-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Investment
                    </h3>
                </div>
            </a>

            <a href="{{ route('utility-bills.create') }}" class="group relative bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-red-50 text-red-700 ring-4 ring-white dark:bg-red-900 dark:text-red-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Utility Bill
                    </h3>
                </div>
            </a>
        </div>
    </div>
@endsection
