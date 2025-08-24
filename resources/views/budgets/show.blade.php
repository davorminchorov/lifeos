@extends('layouts.app')

@section('title', $budget->category . ' Budget - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $budget->category }} Budget
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ ucfirst($budget->budget_period) }} budget from {{ $budget->start_date->format('M j, Y') }} to {{ $budget->end_date->format('M j, Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('budgets.edit', $budget) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit Budget
            </a>
            <a href="{{ route('budgets.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Budgets
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Budget Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Budget Amount -->
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Budget Amount</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($budget->amount, 2) }} {{ $budget->currency }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Spent -->
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Amount Spent</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($budget->getCurrentSpending(), 2) }} {{ $budget->currency }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Amount -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Remaining</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($budget->getRemainingAmount(), 2) }} {{ $budget->currency }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Status -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @php $status = $budget->getStatus() @endphp
                        <div class="w-8 h-8
                            @if($status === 'exceeded') bg-red-500
                            @elseif($status === 'warning') bg-yellow-500
                            @else bg-green-500 @endif
                            rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($status === 'exceeded')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                @elseif($status === 'warning')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Status</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $budget->getUtilizationPercentage() }}%
                                @if($status === 'exceeded')
                                    <span class="text-sm text-red-600 dark:text-red-400">Over Budget</span>
                                @elseif($status === 'warning')
                                    <span class="text-sm text-yellow-600 dark:text-yellow-400">Warning</span>
                                @else
                                    <span class="text-sm text-green-600 dark:text-green-400">On Track</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar and Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Budget Progress -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-6">Budget Progress</h3>

                @php $utilization = $budget->getUtilizationPercentage() @endphp
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-700 dark:text-gray-300">{{ $utilization }}% Used</span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ now()->diffInDays($budget->end_date, false) }} days remaining
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="h-4 rounded-full transition-all duration-300
                            @if($utilization >= 100) bg-red-600
                            @elseif($utilization >= $budget->alert_threshold) bg-yellow-600
                            @else bg-green-600 @endif"
                            style="width: {{ min($utilization, 100) }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($budget->getCurrentSpending(), 2) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Spent</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($budget->getRemainingAmount(), 2) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Remaining</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($projectedSpending, 2) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Projected Total</div>
                    </div>
                </div>

                @if($projectedSpending > $budget->amount)
                    <div class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Budget Alert</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>Your projected spending ({{ number_format($projectedSpending, 2) }} {{ $budget->currency }}) exceeds your budget by {{ number_format($projectedSpending - $budget->amount, 2) }} {{ $budget->currency }}.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Budget Details -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-6">Budget Details</h3>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ ucfirst($budget->budget_period) }}
                            @if($budget->budget_period === 'custom')
                                <br>{{ $budget->start_date->format('M j, Y') }} - {{ $budget->end_date->format('M j, Y') }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alert Threshold</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $budget->alert_threshold }}%</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($budget->is_active) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                {{ $budget->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rollover</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $budget->rollover_unused ? 'Enabled' : 'Disabled' }}
                        </dd>
                    </div>

                    @if($budget->notes)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $budget->notes }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $budget->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Spending Breakdown -->
    @if($expenses->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Related Expenses ({{ $expenses->count() }})
                    </h3>
                    <a href="{{ route('expenses.index', ['category' => $budget->category, 'start_date' => $budget->start_date->format('Y-m-d'), 'end_date' => $budget->end_date->format('Y-m-d')]) }}"
                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                        View All →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Merchant</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($expenses->take(10) as $expense)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $expense->expense_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->description }}</div>
                                        @if($expense->tags)
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $expense->tags }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $expense->merchant ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($expense->amount, 2) }} {{ $expense->currency }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($expenses->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('expenses.index', ['category' => $budget->category]) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            View All {{ $expenses->count() }} Expenses
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No expenses yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No expenses have been recorded for this budget period.</p>
                    <div class="mt-6">
                        <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Expense
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
