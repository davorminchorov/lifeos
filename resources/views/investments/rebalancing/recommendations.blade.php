@extends('layouts.app')

@section('title', 'Rebalancing Recommendations - LifeOS')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)]">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Portfolio Rebalancing Recommendations</h2>
                        <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Actionable steps to achieve your target allocation</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('investments.rebalancing.alerts') }}" class="inline-flex items-center px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] shadow-sm text-sm leading-4 font-medium rounded-md text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                            ← Back to Alerts
                        </a>
                        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Recommendations
                        </button>
                    </div>
                </div>

                <!-- Recommendations Summary -->
                @if(count($recommendations) > 0)
                    <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)]/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-[color:var(--color-info-600)] dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-[color:var(--color-info-600)] dark:text-[color:var(--color-info-200)]">
                                    Rebalancing Required
                                </h3>
                                <div class="mt-2 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">
                                    <p>Your portfolio allocation has drifted from your target. Here are {{ count($recommendations) }} recommended adjustments to rebalance your holdings.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendations Table -->
                    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg mb-8">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)] mb-4">Recommended Actions</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                    <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Investment Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Current</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Target</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Action</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                        @foreach($recommendations as $index => $rec)
                                            <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                        {{ ucfirst($rec['investment_type']) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                        {{ number_format($rec['current_percentage'], 1) }}%
                                                    </div>
                                                    <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                        ${{ number_format($rec['current_value'], 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                        {{ number_format($rec['target_percentage'], 1) }}%
                                                    </div>
                                                    <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                        ${{ number_format($rec['target_value'], 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rec['action'] === 'buy' ? 'bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]' : 'bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]' }}">
                                                        {{ $rec['action'] === 'buy' ? '▲ Buy More' : '▼ Sell Some' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium {{ $rec['action'] === 'buy' ? 'text-[color:var(--color-success-600)] dark:text-[color:var(--color-success-500)]' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)]' }}">
                                                        ${{ number_format($rec['amount'], 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @php
                                                        $priority = abs($rec['current_percentage'] - $rec['target_percentage']) > 10 ? 'high' : (abs($rec['current_percentage'] - $rec['target_percentage']) > 5 ? 'medium' : 'low');
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priority === 'high' ? 'bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]' : ($priority === 'medium' ? 'bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)]' : 'bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]') }}">
                                                        {{ ucfirst($priority) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Implementation Strategy -->
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)] mb-4">Implementation Strategy</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)] mb-3">Step-by-Step Approach</h4>
                                <ol class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-2">
                                    @php $step = 1; @endphp
                                    @foreach($recommendations->sortByDesc(function($rec) { return abs($rec['current_percentage'] - $rec['target_percentage']); }) as $rec)
                                        <li class="flex items-start">
                                            <span class="inline-flex items-center justify-center w-5 h-5 bg-[color:var(--color-accent-600)] text-white text-xs font-medium rounded-full mr-3 mt-0.5 flex-shrink-0">
                                                {{ $step }}
                                            </span>
                                            <span>
                                                <strong>{{ ucfirst($rec['action']) }}</strong> ${{ number_format($rec['amount'], 2) }} of {{ ucfirst($rec['investment_type']) }}
                                                ({{ number_format(abs($rec['current_percentage'] - $rec['target_percentage']), 1) }}% adjustment needed)
                                            </span>
                                        </li>
                                        @php $step++; @endphp
                                    @endforeach
                                </ol>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)] mb-3">Execution Tips</h4>
                                <ul class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-2">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Start with tax-advantaged accounts if possible
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Use new contributions before selling existing holdings
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Consider tax implications of selling appreciated assets
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Execute trades during market hours for better pricing
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Cost Analysis -->
                    @php
                        $totalBuyAmount = collect($recommendations)->where('action', 'buy')->sum('amount');
                        $totalSellAmount = collect($recommendations)->where('action', 'sell')->sum('amount');
                        $estimatedFees = (count($recommendations) * 10); // Estimate $10 per trade
                    @endphp

                    <div class="bg-[color:var(--color-warning-50)] dark:bg-[color:var(--color-warning-900)]/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6 mb-8">
                        <h3 class="text-lg font-medium text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-200)] mb-4">Cost Analysis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-200)]">
                                    ${{ number_format($totalBuyAmount, 2) }}
                                </div>
                                <div class="text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-300)]">Total Purchases</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-200)]">
                                    ${{ number_format($totalSellAmount, 2) }}
                                </div>
                                <div class="text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-300)]">Total Sales</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-200)]">
                                    ${{ number_format($estimatedFees, 2) }}
                                </div>
                                <div class="text-sm text-[color:var(--color-warning-600)] dark:text-[color:var(--color-warning-300)]">Est. Trading Fees</div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- No Recommendations State -->
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] dark:text-[color:var(--color-primary-100)] dark:text-[color:var(--color-dark-200)]">Portfolio is Well Balanced</h3>
                        <p class="mt-2 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Your current allocation is within acceptable ranges of your target. No rebalancing needed at this time.</p>
                        <div class="mt-6">
                            <a href="{{ route('investments.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)]">
                                Return to Investments
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Important Disclaimers -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[color:var(--color-danger-400)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-200)]">
                                Important Disclaimers
                            </h3>
                            <div class="mt-2 text-sm text-[color:var(--color-danger-700)] dark:text-[color:var(--color-danger-300)]">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>These recommendations are for informational purposes only and do not constitute financial advice</li>
                                    <li>Consider your tax situation, risk tolerance, and investment timeline before making changes</li>
                                    <li>Market timing and transaction costs may affect the effectiveness of rebalancing</li>
                                    <li>Consult with a qualified financial advisor before implementing any investment strategy</li>
                                    <li>Past performance does not guarantee future results</li>
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
