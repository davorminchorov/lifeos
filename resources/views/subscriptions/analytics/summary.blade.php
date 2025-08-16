@extends('layouts.app')

@section('title', 'Subscription Analytics Summary - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Analytics Summary
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Overview of your subscription metrics and spending
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('subscriptions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Subscriptions
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Total Subscriptions Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Total Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $summary['total_subscriptions'] }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        All your subscriptions
                    </p>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Active Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $summary['active_subscriptions'] }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Currently active
                    </p>
                </div>
            </div>
        </div>

        <!-- Cancelled Subscriptions Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Cancelled Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">
                        {{ $summary['cancelled_subscriptions'] }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        No longer active
                    </p>
                </div>
            </div>
        </div>

        <!-- Paused Subscriptions Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Paused Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $summary['paused_subscriptions'] }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Temporarily paused
                    </p>
                </div>
            </div>
        </div>

        <!-- Due Soon Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Due Soon
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $summary['due_soon'] }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Next 7 days
                    </p>
                </div>
            </div>
        </div>

        <!-- Monthly Spending Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Monthly Spending
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        ${{ number_format($summary['monthly_spending'], 2) }}
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Per month (active only)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Spending Summary -->
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Yearly Spending Projection
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Based on current active subscriptions
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:px-6">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 text-center">
                        ${{ number_format($summary['yearly_spending'], 2) }}
                    </div>
                    <p class="text-center text-gray-500 dark:text-gray-400 mt-2">
                        Estimated annual cost for all active subscriptions
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
