@extends('layouts.app')

@section('title', 'Budget Analytics - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Budget Analytics
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Insights and performance analysis of your budgets
            </p>
        </div>
        <a href="{{ route('budgets.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Back to Budgets
        </a>
    </div>
@endsection

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Budgeted</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">MKD {{ number_format($analytics['total_budgeted'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Spent</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">MKD {{ number_format($analytics['total_spent'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Budgets On Track</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $analytics['budgets_on_track'] }} / {{ $analytics['budgets_on_track'] + $analytics['budgets_warning'] + $analytics['budgets_exceeded'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 {{ $analytics['budgets_exceeded'] > 0 ? 'bg-red-500' : 'bg-yellow-500' }} rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($analytics['budgets_exceeded'] > 0)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ $analytics['budgets_exceeded'] > 0 ? 'Over Budget' : 'Warnings' }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $analytics['budgets_exceeded'] > 0 ? $analytics['budgets_exceeded'] : $analytics['budgets_warning'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Category Analysis -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Budget Performance by Category</h3>

                @if($analytics['category_analysis']->count() > 0)
                    <div class="space-y-4">
                        @foreach($analytics['category_analysis'] as $category)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $category['category'] }}</span>
                                    <div class="text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($category['spent_amount'], 2) }} / {{ number_format($category['budget_amount'], 2) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                            ({{ $category['utilization_percentage'] }}%)
                                        </span>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    @php
                                        $percentage = min($category['utilization_percentage'], 100);
                                        $colorClass = 'bg-green-600';
                                        if ($category['status'] === 'exceeded') {
                                            $colorClass = 'bg-red-600';
                                        } elseif ($category['status'] === 'warning') {
                                            $colorClass = 'bg-yellow-600';
                                        }
                                    @endphp
                                    <div class="{{ $colorClass }} h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                </div>

                                <div class="flex justify-between items-center mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($category['status'] === 'exceeded') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($category['status'] === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                        @if($category['status'] === 'exceeded') Over Budget
                                        @elseif($category['status'] === 'warning') Warning
                                        @else On Track @endif
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($category['remaining_amount'], 2) }} remaining
                                        @if($category['days_remaining'] > 0)
                                            • {{ $category['days_remaining'] }} days left
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No active budgets found</p>
                @endif
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Budget Performance Trends</h3>

                @if($analytics['monthly_trends']->count() > 0)
                    <div class="space-y-3">
                        @foreach($analytics['monthly_trends']->take(6) as $trend)
                            @php
                                $utilizationRate = $trend['total_budgeted'] > 0 ? round(($trend['total_spent'] / $trend['total_budgeted']) * 100, 1) : 0;
                            @endphp
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $trend['month'])->format('M Y') }}
                                </span>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($trend['total_spent'], 2) }} / {{ number_format($trend['total_budgeted'], 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $utilizationRate }}% utilized
                                        @if($trend['exceeded_count'] > 0)
                                            • {{ $trend['exceeded_count'] }} exceeded
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1 dark:bg-gray-700">
                                <div class="h-1 rounded-full transition-all duration-300
                                    @if($utilizationRate >= 100) bg-red-600
                                    @elseif($utilizationRate >= 80) bg-yellow-600
                                    @else bg-green-600 @endif"
                                    style="width: {{ min($utilizationRate, 100) }}%"></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No trend data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Budget Status Summary -->
    <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Budget Status Summary</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- On Track Budgets -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $analytics['budgets_on_track'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Budgets On Track</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @php
                                $totalBudgets = $analytics['budgets_on_track'] + $analytics['budgets_warning'] + $analytics['budgets_exceeded'];
                                $onTrackPercentage = $totalBudgets > 0 ? ($analytics['budgets_on_track'] / $totalBudgets) * 100 : 0;
                            @endphp
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $onTrackPercentage }}%"></div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($onTrackPercentage, 1) }}%</div>
                    </div>
                </div>

                <!-- Warning Budgets -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $analytics['budgets_warning'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Budgets at Warning</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @php
                                $warningPercentage = $totalBudgets > 0 ? ($analytics['budgets_warning'] / $totalBudgets) * 100 : 0;
                            @endphp
                            <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $warningPercentage }}%"></div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($warningPercentage, 1) }}%</div>
                    </div>
                </div>

                <!-- Exceeded Budgets -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ $analytics['budgets_exceeded'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Budgets Exceeded</div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @php
                                $exceededPercentage = $totalBudgets > 0 ? ($analytics['budgets_exceeded'] / $totalBudgets) * 100 : 0;
                            @endphp
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ $exceededPercentage }}%"></div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ number_format($exceededPercentage, 1) }}%</div>
                    </div>
                </div>
            </div>

            <!-- Overall Performance -->
            @if($analytics['total_budgeted'] > 0)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Overall Budget Performance</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                You've spent {{ number_format(($analytics['total_spent'] / $analytics['total_budgeted']) * 100, 1) }}% of your total budget
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ number_format($analytics['total_spent'], 2) }} / {{ number_format($analytics['total_budgeted'], 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">MKD</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
