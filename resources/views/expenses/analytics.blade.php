@extends('layouts.app')

@section('title', 'Expense Analytics - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Expense Analytics
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Insights and reports on your spending patterns
            </p>
        </div>
        <a href="{{ route('expenses.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium">
            Back to Expenses
        </a>
    </div>
@endsection

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-info-500)] rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Total Expenses</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($analytics['total_expenses']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-success-500)] rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Total Amount</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">MKD {{ number_format($analytics['total_amount'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-info-500)] rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Average Expense</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">MKD {{ number_format($analytics['average_expense'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-warning-500)] rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Tax Deductible</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">MKD {{ number_format($analytics['tax_deductible_total'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Category Breakdown -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Spending by Category</h3>

                @if($analytics['category_breakdown']->count() > 0)
                    <div class="space-y-4">
                        @foreach($analytics['category_breakdown'] as $category)
                            @php
                                $percentage = $analytics['total_amount'] > 0 ? ($category->total_amount / $analytics['total_amount']) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $category->category }}</span>
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">MKD {{ number_format($category->total_amount, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full h-2">
                                    <div class="bg-[color:var(--color-accent-500)] h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                    {{ $category->count }} expenses
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-center py-8">No data available</p>
                @endif
            </div>
        </div>

        <!-- Expense Type Breakdown -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Business vs Personal</h3>

                @if($analytics['type_breakdown']->count() > 0)
                    <div class="space-y-4">
                        @foreach($analytics['type_breakdown'] as $type)
                            @php
                                $percentage = $analytics['total_amount'] > 0 ? ($type->total_amount / $analytics['total_amount']) * 100 : 0;
                                $color = $type->expense_type === 'business' ? 'bg-[color:var(--color-info-500)]' : 'bg-[color:var(--color-success-500)]';
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ ucfirst($type->expense_type) }}</span>
                                    <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">MKD {{ number_format($type->total_amount, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="w-full bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full h-2">
                                    <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mt-1">
                                    {{ $type->count }} expenses
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-center py-8">No data available</p>
                @endif
            </div>
        </div>

        <!-- Monthly Spending Trends -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Monthly Spending Trends</h3>

                @if($analytics['monthly_spending']->count() > 0)
                    <div class="space-y-3">
                        @foreach($analytics['monthly_spending']->take(6) as $month)
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ \Carbon\Carbon::create($month->year, $month->month)->format('M Y') }}
                                </span>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        ${{ number_format($month->total_amount, 2) }}
                                    </div>
                                    <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $month->count }} expenses
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-center py-8">No data available</p>
                @endif
            </div>
        </div>

        <!-- Top Merchants -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Top Merchants</h3>

                @if($analytics['top_merchants']->count() > 0)
                    <div class="space-y-3">
                        @foreach($analytics['top_merchants'] as $index => $merchant)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-600)]">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $merchant->merchant }}</p>
                                        <p class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $merchant->count }} transactions</p>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    ${{ number_format($merchant->total_amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-center py-8">No merchant data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Filter Analytics</h3>

            <form method="GET" action="{{ route('expenses.analytics') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] dark:bg-[color:var(--color-dark-100)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium">
                        Update Analytics
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
