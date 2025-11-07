@extends('layouts.app')

@section('title', 'Spending Analytics - Subscriptions - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Spending Analytics
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Detailed breakdown of your subscription spending patterns
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
    <div class="space-y-8">
        <!-- Spending Trend -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Spending Trend
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Current and projected subscription costs
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                            ${{ number_format($analytics['spending_trend']['current_month'], 2) }}
                        </div>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                            Current Month
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            ${{ number_format($analytics['spending_trend']['projected_year'], 2) }}
                        </div>
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                            Projected Year
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown by Billing Cycle -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Breakdown by Billing Cycle
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    How your subscriptions are distributed by billing frequency
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                @if($analytics['monthly_breakdown']->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Billing Cycle
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Count
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Total Cost
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                        Monthly Equivalent
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                @foreach($analytics['monthly_breakdown'] as $cycle => $data)
                                    <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] capitalize">
                                                {{ $cycle }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $data['count'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                ${{ number_format($data['total_cost'], 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                ${{ number_format($data['monthly_equivalent'], 2) }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-8 text-center">
                        <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No active subscriptions found.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Expenses -->
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Top 5 Expenses
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Your most expensive subscriptions by monthly cost
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                @if($analytics['top_expenses']->count() > 0)
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['top_expenses'] as $subscription)
                            <li class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $subscription->service_name }}
                                            </div>
                                            <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                {{ $subscription->category }}
                                                @if($subscription->billing_cycle)
                                                    • {{ ucfirst($subscription->billing_cycle) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            ${{ number_format($subscription->monthly_cost, 2) }}/month
                                        </div>
                                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                            ${{ $subscription->currency }} {{ number_format($subscription->cost, 2) }}/{{ $subscription->billing_cycle }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-8 text-center">
                        <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No active subscriptions found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
