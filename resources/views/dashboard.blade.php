@extends('layouts.app')

@section('title', 'Dashboard - LifeOS')

@section('header')
    <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
        Dashboard
    </h1>
    <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
        Welcome to your personal life management platform
    </p>
@endsection

@section('content')
    <!-- Analytics Insights Section -->
    @if(isset($insights))


        <!-- Advanced Analytics Dashboard -->
        <div x-data="chartControls()" class="mb-8">
            <!-- Chart Controls -->
            <div class="mb-6 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg p-4 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Advanced Analytics Dashboard
                        </h2>
                        <div class="flex items-center space-x-2">
                            <svg class="h-4 w-4 text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span class="text-sm text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-400)]">Interactive Charts</span>
                        </div>
                    </div>

                    <!-- Period Selector -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Period:</label>
                        <select x-model="selectedPeriod" @change="changePeriod(selectedPeriod)"
                                class="rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-sm">
                            <option value="3months">3 Months</option>
                            <option value="6months" selected>6 Months</option>
                            <option value="1year">1 Year</option>
                            <option value="2years">2 Years</option>
                        </select>

                        <!-- Export Dropdown -->
                        <div class="ml-4 relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" :disabled="isExporting"
                                    class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] disabled:bg-[color:var(--color-primary-400)] text-white text-sm px-4 py-2 rounded-md transition-colors flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isExporting">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" x-show="isExporting">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isExporting ? 'Exporting...' : 'Export'"></span>
                                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isExporting">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-md shadow-lg border border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] z-50">
                                <div class="py-1">
                                    <button @click="exportData('pdf'); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-[color:var(--color-danger-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        Export as PDF
                                    </button>
                                    <button @click="exportData('excel'); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)] flex items-center">
                                        <svg class="h-4 w-4 mr-2 text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Export as Excel (CSV)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Spending Trends Chart -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] chart-container" data-chart-type="spending">
                    <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <h3 class="text-md font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex items-center">
                            <svg class="h-4 w-4 mr-2 text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Spending Trends
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="spendingTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown Chart -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] chart-container" data-chart-type="categories">
                    <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <h3 class="text-md font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex items-center">
                            <svg class="h-4 w-4 mr-2 text-[color:var(--color-accent-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                            </svg>
                            Spending by Category
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="categoryBreakdownChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extended Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Portfolio Performance Chart -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] chart-container" data-chart-type="portfolio">
                    <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <h3 class="text-md font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex items-center">
                            <svg class="h-4 w-4 mr-2 text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Investment Portfolio Performance
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="portfolioPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Comparison Radar Chart -->
                <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] chart-container" data-chart-type="comparison">
                    <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <h3 class="text-md font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] flex items-center">
                            <svg class="h-4 w-4 mr-2 text-[color:var(--color-info-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Monthly Category Comparison
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <canvas id="monthlyComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Subscriptions Stats -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-accent-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Active Subscriptions</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['active_subscriptions'] ?? 0 }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('subscriptions.index') }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-5 py-3">
                <div class="text-sm">
                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Monthly cost:</span>
                    <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['monthly_subscription_cost_formatted'] ?? 'MKD 0.00' }}</span>
                </div>
            </div>
        </div>

        <!-- Contracts Stats -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-success-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Active Contracts</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['active_contracts'] ?? 0 }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('contracts.index') }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-5 py-3">
                <div class="text-sm">
                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Expiring soon:</span>
                    <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['contracts_expiring_soon'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Investments Stats -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-[color:var(--color-warning-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Portfolio Value</dt>
                            <dd class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $stats['portfolio_value_formatted'] ?? 'MKD 0.00' }}</dd>
                        </dl>
                    </div>
                    <div class="ml-5">
                        <a href="{{ route('investments.index') }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">View all</a>
                    </div>
                </div>
            </div>
            <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-5 py-3">
                <div class="text-sm">
                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total return:</span>
                    <span class="font-medium {{ ($stats['total_return'] ?? 0) >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                        {{ ($stats['total_return'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['total_return_formatted'] ?? 'MKD 0.00' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    @if(isset($alerts) && count($alerts) > 0)
        <div class="mb-8">
            <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Alerts & Notifications</h2>
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-md border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <ul class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                    @foreach($alerts as $alert)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($alert['type'] === 'warning')
                                            <svg class="h-5 w-5 text-[color:var(--color-warning-500)]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($alert['type'] === 'info')
                                            <svg class="h-5 w-5 text-[color:var(--color-info-500)]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-[color:var(--color-danger-500)]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $alert['title'] }}</p>
                                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $alert['message'] }}</p>
                                    </div>
                                </div>
                                @if(isset($alert['action_url']))
                                    <div class="flex-shrink-0">
                                        <a href="{{ $alert['action_url'] }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">
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
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Recent Expenses</h3>
                    <a href="{{ route('expenses.index') }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">View all</a>
                </div>
                @if(isset($recent_expenses) && count($recent_expenses) > 0)
                    <div class="space-y-3">
                        @foreach($recent_expenses as $expense)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] truncate">{{ $expense->description }}</p>
                                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $expense->category }} • {{ $expense->expense_date->format('M j, Y') }}</p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $expense->formatted_amount_mkd }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-sm">No recent expenses found.</p>
                @endif
            </div>
        </div>

        <!-- Upcoming Bills -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Upcoming Bills</h3>
                    <a href="{{ route('utility-bills.index') }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] text-sm font-medium">View all</a>
                </div>
                @if(isset($upcoming_bills) && count($upcoming_bills) > 0)
                    <div class="space-y-3">
                        @foreach($upcoming_bills as $bill)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] truncate">{{ $bill->service_provider }}</p>
                                    <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($bill->utility_type) }} • Due {{ $bill->due_date->format('M j, Y') }}</p>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $bill->formatted_bill_amount_mkd }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] text-sm">No upcoming bills found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
        <h2 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('subscriptions.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-info-600)] dark:text-[color:var(--color-info-50)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Subscription
                    </h3>
                </div>
            </a>

            <a href="{{ route('expenses.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-success-600)] dark:text-[color:var(--color-success-50)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Expense
                    </h3>
                </div>
            </a>

            <a href="{{ route('contracts.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-accent-50)] text-[color:var(--color-accent-600)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-50)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Contract
                    </h3>
                </div>
            </a>

            <a href="{{ route('warranties.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-50)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Warranty
                    </h3>
                </div>
            </a>

            <a href="{{ route('investments.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-primary-600)] dark:text-[color:var(--color-primary-100)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Investment
                    </h3>
                </div>
            </a>

            <a href="{{ route('utility-bills.create') }}" class="group relative bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-[color:var(--color-accent-500)] rounded-lg shadow hover:shadow-md transition-shadow border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div>
                    <span class="rounded-lg inline-flex p-3 bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] ring-4 ring-[color:var(--color-primary-100)] dark:bg-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-50)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        Add Utility Bill
                    </h3>
                </div>
            </a>
        </div>
    </div>
@endsection

@vite(['resources/js/dashboard.js'])
