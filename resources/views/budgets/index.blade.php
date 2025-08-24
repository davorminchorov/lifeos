@extends('layouts.app')

@section('title', 'Budget Management - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Budget Management
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Set and track your spending limits by category
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('budgets.analytics') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                View Analytics
            </a>
            <a href="{{ route('budgets.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Create Budget
            </a>
        </div>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Budgets</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $summaryStats['total_budgets'] }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Budgeted</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">MKD {{ number_format($summaryStats['total_budgeted'], 2) }}</dd>
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
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">MKD {{ number_format($summaryStats['total_spent'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 {{ $summaryStats['budgets_exceeded'] > 0 ? 'bg-red-500' : 'bg-green-500' }} rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($summaryStats['budgets_exceeded'] > 0)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                {{ $summaryStats['budgets_exceeded'] > 0 ? 'Over Budget' : 'On Track' }}
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $summaryStats['overall_utilization'] }}%
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('budgets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period</label>
                    <select name="period" id="period" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Periods</option>
                        <option value="monthly" {{ request('period') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('period') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ request('period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Filter Budgets
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Budgets Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($budgets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Budget Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Spent / Remaining</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($budgets as $budget)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $budget->category }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $budget->currency }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($budget->budget_period === 'monthly') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($budget->budget_period === 'quarterly') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($budget->budget_period === 'yearly') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst($budget->budget_period) }}
                                        </span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $budget->start_date->format('M j') }} - {{ $budget->end_date->format('M j, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($budget->amount, 2) }}
                                        </div>
                                        @if($budget->alert_threshold < 100)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Alert at {{ $budget->alert_threshold }}%
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $spent = $budget->getCurrentSpending();
                                            $remaining = $budget->getRemainingAmount();
                                        @endphp
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ number_format($spent, 2) }}</span> spent
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($remaining, 2) }} remaining
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $utilization = $budget->getUtilizationPercentage();
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div class="flex justify-between text-sm mb-1">
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $utilization }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                    <div class="h-2 rounded-full
                                                        @if($utilization >= 100) bg-red-600
                                                        @elseif($utilization >= $budget->alert_threshold) bg-yellow-600
                                                        @else bg-green-600 @endif"
                                                        style="width: {{ min($utilization, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $status = $budget->getStatus() @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($status === 'exceeded') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($status === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                            @if($status === 'exceeded') Over Budget
                                            @elseif($status === 'warning') Warning
                                            @else On Track @endif
                                        </span>
                                        @if(!$budget->is_active)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                    Inactive
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('budgets.show', $budget) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</a>
                                            <a href="{{ route('budgets.edit', $budget) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Edit</a>
                                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('Are you sure you want to delete this budget?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $budgets->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286M30 14a6 6 0 11-12 0 6 6 0 0112 0zm12 6a4 4 0 11-8 0 4 4 0 018 0zm-28 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No budgets found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first budget.</p>
                    <div class="mt-6">
                        <a href="{{ route('budgets.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Budget
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
