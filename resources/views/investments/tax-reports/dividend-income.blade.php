@extends('layouts.app')

@section('title', 'Dividend Income Report - LifeOS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Dividend Income Report - {{ $report['tax_year'] }}</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Detailed dividend income for tax reporting</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('investments.tax-reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            ← Back to Tax Reports
                        </a>
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Report
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Dividend Income</dt>
                                    <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($report['total_dividend_income'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Qualified Dividends</dt>
                                    <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($report['total_qualified_dividends'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Non-Qualified Dividends</dt>
                                    <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($report['total_non_qualified_dividends'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dividend Breakdown Chart -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Dividend Classification</h3>
                    <div class="flex items-center justify-center">
                        @php
                            $totalDividends = $report['total_dividend_income'];
                            $qualifiedPercentage = $totalDividends > 0 ? ($report['total_qualified_dividends'] / $totalDividends) * 100 : 0;
                            $nonQualifiedPercentage = 100 - $qualifiedPercentage;
                        @endphp

                        <div class="w-64 h-64">
                            <div class="relative w-full h-full">
                                <!-- Simplified pie chart representation -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ number_format($qualifiedPercentage, 1) }}%
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Qualified</div>
                                    </div>
                                </div>
                                <div class="w-full h-full rounded-full border-8 border-green-500"
                                     style="background: conic-gradient(#10b981 0deg {{ $qualifiedPercentage * 3.6 }}deg, #f59e0b {{ $qualifiedPercentage * 3.6 }}deg 360deg);">
                                </div>
                            </div>
                        </div>

                        <div class="ml-8 space-y-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Qualified Dividends: {{ number_format($qualifiedPercentage, 1) }}%
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Eligible for capital gains tax rates
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Non-Qualified Dividends: {{ number_format($nonQualifiedPercentage, 1) }}%
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        Taxed as ordinary income
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dividend Details Table -->
                @if($report['dividend_details']->count() > 0)
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Dividend Details by Investment</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                    <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Investment</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Total Dividends</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Qualified</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Non-Qualified</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Payments</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                        @foreach($report['dividend_details'] as $dividend)
                                            <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $dividend['investment_name'] }}</div>
                                                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $dividend['symbol'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    ${{ number_format($dividend['total_dividends'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]">
                                                    ${{ number_format($dividend['qualified_dividends'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-500)]">
                                                    ${{ number_format($dividend['non_qualified_dividends'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                    {{ count($dividend['dividend_history']) }} payments
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Payment History -->
                    <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Payment History</h3>
                            <div class="space-y-4">
                                @foreach($report['dividend_details'] as $dividend)
                                    @if(count($dividend['dividend_history']) > 0)
                                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                                {{ $dividend['investment_name'] }} ({{ $dividend['symbol'] }})
                                            </h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach($dividend['dividend_history'] as $payment)
                                                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-3">
                                                        <div class="flex justify-between items-center">
                                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                                {{ date('M j, Y', strtotime($payment['date'])) }}
                                                            </div>
                                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                ${{ number_format($payment['amount'], 2) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No dividend income</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No dividend payments found for {{ $report['tax_year'] }}.</p>
                    </div>
                @endif

                <!-- Tax Information -->
                <div class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Dividend Tax Information
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Qualified dividends</strong> are eligible for capital gains tax rates (0%, 15%, or 20%)</li>
                                    <li><strong>Non-qualified dividends</strong> are taxed as ordinary income at your marginal tax rate</li>
                                    <li>Most dividends from U.S. corporations and qualified foreign corporations are considered qualified</li>
                                    <li>Dividends must meet holding period requirements to be qualified (generally 60+ days)</li>
                                    <li>REITs, MLPs, and some foreign dividends may be non-qualified</li>
                                    <li>Verify dividend classifications with your broker statements - these are estimates</li>
                                    <li>This report is for informational purposes only - consult a tax professional for specific advice</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
