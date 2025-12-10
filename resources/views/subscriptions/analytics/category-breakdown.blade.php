@extends('layouts.app')

@section('title', 'Category Breakdown - Subscriptions - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Category Breakdown
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Analysis of subscription spending by category
            </p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('subscriptions.index') }}" variant="secondary">Back to Subscriptions</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-8">
        @if($categories->count() > 0)
            <!-- Category Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $categoryData)
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] capitalize">
                                    {{ $categoryData['category'] }}
                                </h3>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                    {{ $categoryData['percentage'] }}%
                                </span>
                            </div>
                            <div class="mt-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Subscriptions</span>
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $categoryData['count'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Monthly Cost</span>
                                    <span class="text-sm font-medium text-[color:var(--color-success-600)] dark:text-green-400">
                                        ${{ number_format($categoryData['monthly_cost'], 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Yearly Cost</span>
                                    <span class="text-sm font-medium text-[color:var(--color-info-600)] dark:text-blue-400">
                                        ${{ number_format($categoryData['yearly_cost'], 2) }}
                                    </span>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full h-2">
                                    <div class="bg-[color:var(--color-info-600)] h-2 rounded-full" style="width: {{ $categoryData['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Detailed Table -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Category Details
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Comprehensive breakdown of spending by category
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Count
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Monthly Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Yearly Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Percentage
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                @php
                                    $sortedCategories = $categories->sortByDesc('monthly_cost');
                                @endphp
                                @foreach($sortedCategories as $categoryData)
                                    <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] capitalize">
                                                {{ $categoryData['category'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $categoryData['count'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]">
                                                ${{ number_format($categoryData['monthly_cost'], 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-500)]">
                                                ${{ number_format($categoryData['yearly_cost'], 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mr-2">
                                                    {{ $categoryData['percentage'] }}%
                                                </div>
                                                <div class="w-16 bg-[color:var(--color-primary-300)] dark:bg-[color:var(--color-dark-400)] rounded-full h-2">
                                                    <div class="bg-[color:var(--color-info-600)] h-2 rounded-full" style="width: {{ $categoryData['percentage'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Summary
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Overall category statistics
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ $categories->count() }}
                            </div>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                Total Categories
                            </p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-[color:var(--color-success-600)] dark:text-green-400">
                                ${{ number_format($categories->sum('monthly_cost'), 2) }}
                            </div>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                Total Monthly
                            </p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-[color:var(--color-info-600)] dark:text-blue-400">
                                ${{ number_format($categories->sum('yearly_cost'), 2) }}
                            </div>
                            <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                Total Yearly
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                        <svg class="h-6 w-6 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No active subscriptions</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Get started by adding your first subscription.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)]">
                            Add Subscription
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
