@extends('layouts.app')

@section('title', 'Subscription Analytics Summary - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Analytics Summary
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Overview of your subscription metrics and spending
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('subscriptions.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] dark:bg-[color:var(--color-dark-400)] dark:hover:bg-[color:var(--color-dark-500)] text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Subscriptions
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Total Subscriptions Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Total Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $summary['total_subscriptions'] }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        All your subscriptions
                    </p>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Active Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                        {{ $summary['active_subscriptions'] }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        Currently active
                    </p>
                </div>
            </div>
        </div>

        <!-- Cancelled Subscriptions Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Cancelled Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">
                        {{ $summary['cancelled_subscriptions'] }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        No longer active
                    </p>
                </div>
            </div>
        </div>

        <!-- Paused Subscriptions Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Paused Subscriptions
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-[color:var(--color-warning-400)]">
                        {{ $summary['paused_subscriptions'] }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        Temporarily paused
                    </p>
                </div>
            </div>
        </div>

        <!-- Due Soon Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Due Soon
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $summary['due_soon'] }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        Next 7 days
                    </p>
                </div>
            </div>
        </div>

        <!-- Monthly Spending Card -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Monthly Spending
                </h3>
                <div class="mt-4">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        ${{ number_format($summary['monthly_spending'], 2) }}
                    </div>
                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                        Per month (active only)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Yearly Spending Summary -->
    <div class="mt-8">
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Yearly Spending Projection
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Based on current active subscriptions
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="px-4 py-5 sm:px-6">
                    <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 text-center">
                        ${{ number_format($summary['yearly_spending'], 2) }}
                    </div>
                    <p class="text-center text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-2">
                        Estimated annual cost for all active subscriptions
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
