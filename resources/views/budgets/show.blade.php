@extends('layouts.app')

@section('title', $budget->category . ' Budget - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                {{ $budget->category }} Budget
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ ucfirst($budget->budget_period) }} budget from {{ $budget->start_date->format('M j, Y') }} to {{ $budget->end_date->format('M j, Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('budgets.edit', $budget) }}" class="bg-[color:var(--color-success-600)] hover:bg-[color:var(--color-success-600)] text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit Budget
            </a>
            <a href="{{ route('budgets.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] dark:bg-[color:var(--color-dark-400)] dark:hover:bg-[color:var(--color-dark-500)] text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Budgets
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Budget Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Budget Amount -->
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
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Budget Amount</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($budget->amount, 2) }} {{ $budget->currency }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Spent -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-[color:var(--color-warning-500)] rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Amount Spent</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($budget->getCurrentSpending(), 2) }} {{ $budget->currency }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Amount -->
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
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Remaining</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($budget->getRemainingAmount(), 2) }} {{ $budget->currency }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Status -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @php $status = $budget->getStatus() @endphp
                        <div class="w-8 h-8
                            @if($status === 'exceeded') bg-[color:var(--color-danger-500)]
                            @elseif($status === 'warning') bg-[color:var(--color-warning-500)]
                            @else bg-[color:var(--color-success-500)] @endif
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
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Status</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $budget->getUtilizationPercentage() }}%
                                @if($status === 'exceeded')
                                    <span class="text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">Over Budget</span>
                                @elseif($status === 'warning')
                                    <span class="text-sm text-yellow-600 dark:text-[color:var(--color-warning-400)]">Warning</span>
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
        <div class="lg:col-span-2 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Budget Progress</h3>

                @php $utilization = $budget->getUtilizationPercentage() @endphp
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $utilization }}% Used</span>
                        <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            {{ now()->diffInDays($budget->end_date, false) }} days remaining
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="h-4 rounded-full transition-all duration-300
                            @if($utilization >= 100) bg-[color:var(--color-danger-600)]
                            @elseif($utilization >= $budget->alert_threshold) bg-[color:var(--color-warning-500)]
                            @else bg-[color:var(--color-success-600)] @endif"
                            style="width: {{ min($utilization, 100) }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ number_format($budget->getCurrentSpending(), 2) }}
                        </div>
                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Spent</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ number_format($budget->getRemainingAmount(), 2) }}
                        </div>
                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Remaining</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ number_format($projectedSpending, 2) }}
                        </div>
                        <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Projected Total</div>
                    </div>
                </div>

                @if($projectedSpending > $budget->amount)
                    <div class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-[color:var(--color-danger-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-6">Budget Details</h3>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Period</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ ucfirst($budget->budget_period) }}
                            @if($budget->budget_period === 'custom')
                                <br>{{ $budget->start_date->format('M j, Y') }} - {{ $budget->end_date->format('M j, Y') }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Alert Threshold</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $budget->alert_threshold }}%</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($budget->is_active) bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-success-900)] dark:text-[color:var(--color-success-200)]
                                @else bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)] @endif">
                                {{ $budget->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Rollover</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $budget->rollover_unused ? 'Enabled' : 'Disabled' }}
                        </dd>
                    </div>

                    @if($budget->notes)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Notes</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $budget->notes }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Created</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $budget->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Spending Breakdown -->
    @if($expenses->count() > 0)
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Related Expenses ({{ $expenses->count() }})
                    </h3>
                    <a href="{{ route('expenses.index', ['category' => $budget->category, 'start_date' => $budget->start_date->format('Y-m-d'), 'end_date' => $budget->end_date->format('Y-m-d')]) }}"
                       class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-300)] text-sm font-medium">
                        View All →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Merchant</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($expenses->take(10) as $expense)
                                <tr class="hover:bg-[color:var(--color-primary-50)] dark:hover:bg-[color:var(--color-dark-100)]">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $expense->expense_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $expense->description }}</div>
                                        @if($expense->tags)
                                            <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $expense->tags }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $expense->merchant ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
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
                           class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] shadow-sm text-sm font-medium rounded-md text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-50)] dark:hover:bg-[color:var(--color-dark-100)]">
                            View All {{ $expenses->count() }} Expenses
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No expenses yet</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No expenses have been recorded for this budget period.</p>
                    <div class="mt-6">
                        <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
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
