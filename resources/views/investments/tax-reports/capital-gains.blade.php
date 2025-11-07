@extends('layouts.app')

@section('title', 'Capital Gains Report - LifeOS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Capital Gains Report - {{ $report['tax_year'] }}</h2>
                        <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Detailed capital gains and losses for tax reporting</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('investments.tax-reports.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Short-Term Gains</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($report['total_short_term_gains'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Short-Term Losses</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($report['total_short_term_losses'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Long-Term Gains</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($report['total_long_term_gains'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">Long-Term Losses</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">${{ number_format($report['total_long_term_losses'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Summary -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Net Capital Gains/Losses Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @php
                            $netShortTerm = $report['total_short_term_gains'] - $report['total_short_term_losses'];
                            $netLongTerm = $report['total_long_term_gains'] - $report['total_long_term_losses'];
                            $netTotal = $netShortTerm + $netLongTerm;
                        @endphp

                        <div class="text-center">
                            <div class="text-2xl font-bold {{ $netShortTerm >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                ${{ number_format($netShortTerm, 2) }}
                            </div>
                            <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Net Short-Term</div>
                        </div>

                        <div class="text-center">
                            <div class="text-2xl font-bold {{ $netLongTerm >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                ${{ number_format($netLongTerm, 2) }}
                            </div>
                            <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Net Long-Term</div>
                        </div>

                        <div class="text-center">
                            <div class="text-3xl font-bold {{ $netTotal >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                ${{ number_format($netTotal, 2) }}
                            </div>
                            <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Net Total</div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                @if($report['transactions']->count() > 0)
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Transaction Details</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                    <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Investment</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Purchase Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Sale Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Cost Basis</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Proceeds</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Gain/Loss</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Term</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                        @foreach($report['transactions'] as $transaction)
                                            <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $transaction['investment_name'] }}</div>
                                                    <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $transaction['symbol'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $transaction['purchase_date'] ? date('M j, Y', strtotime($transaction['purchase_date'])) : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $transaction['sale_date'] ? date('M j, Y', strtotime($transaction['sale_date'])) : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ number_format($transaction['quantity'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    ${{ number_format($transaction['cost_basis'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    ${{ number_format($transaction['proceeds'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction['gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]' }}">
                                                    ${{ number_format($transaction['gain_loss'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction['holding_period'] === 'Long-term' ? 'bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]' : 'bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]' }}">
                                                        {{ $transaction['holding_period'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No capital gains transactions</h3>
                        <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No sold investments found for {{ $report['tax_year'] }}.</p>
                    </div>
                @endif

                <!-- Tax Information -->
                <div class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-warning-400)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Capital Gains Tax Information
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Short-term gains</strong> (held ≤ 1 year) are taxed as ordinary income</li>
                                    <li><strong>Long-term gains</strong> (held > 1 year) qualify for preferential tax rates (0%, 15%, or 20%)</li>
                                    <li>Capital losses can offset capital gains, with up to $3,000 of net losses deductible against ordinary income</li>
                                    <li>Unused capital losses can be carried forward to future tax years</li>
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
