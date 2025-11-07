@extends('layouts.app')
@section('title', 'Investment Analytics - LifeOS')
@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Investment Analytics
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Comprehensive insights into your investment portfolio
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('investments.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Investments
            </a>
        </div>
    </div>
@endsection
@section('content')
    <!-- Portfolio Overview -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Portfolio Overview</h2>
        </div>
        <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Value -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Value</p>
                    <p class="mt-2 text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ number_format($analytics['overview']['total_value'], 2) }} {{ $analytics['overview']['currency'] }}
                    </p>
                </div>
                <!-- Total Cost -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Cost</p>
                    <p class="mt-2 text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ number_format($analytics['overview']['total_cost'], 2) }} {{ $analytics['overview']['currency'] }}
                    </p>
                </div>
                <!-- Unrealized Gain/Loss -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Unrealized Gain/Loss</p>
                    <p class="mt-2 text-2xl font-bold {{ $analytics['overview']['unrealized_gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                        {{ number_format($analytics['overview']['unrealized_gain_loss'], 2) }} {{ $analytics['overview']['currency'] }}
                        <span class="text-sm">({{ number_format($analytics['overview']['unrealized_gain_loss_percentage'], 2) }}%)</span>
                    </p>
                </div>
                <!-- Total Return -->
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Return</p>
                    <p class="mt-2 text-2xl font-bold {{ $analytics['overview']['total_return'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                        {{ number_format($analytics['overview']['total_return'], 2) }} {{ $analytics['overview']['currency'] }}
                        <span class="text-sm">({{ number_format($analytics['overview']['total_return_percentage'], 2) }}%)</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocation Breakdown -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Asset Allocation</h2>
        </div>
        <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
            @if(count($analytics['allocation']) > 0)
                <div class="space-y-4">
                    @foreach($analytics['allocation'] as $type => $data)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($data['percentage'], 2) }}%</span>
                            </div>
                            <div class="w-full bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full h-2.5">
                                <div class="bg-[color:var(--color-accent-500)] h-2.5 rounded-full" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                {{ number_format($data['value'], 2) }} {{ $analytics['overview']['currency'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No allocation data available.</p>
            @endif
        </div>
    </div>

    <!-- Performance Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Performers -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Top Performers</h2>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                @if($analytics['top_performers']->count() > 0)
                    <div class="space-y-3">
                        @foreach($analytics['top_performers'] as $investment)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $investment->name }}</p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $investment->symbol_identifier }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-[color:var(--color-success-600)]">+{{ number_format($investment->unrealized_gain_loss_percentage, 2) }}%</p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ number_format($investment->unrealized_gain_loss, 2) }} {{ $investment->currency }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No top performers data available.</p>
                @endif
            </div>
        </div>

        <!-- Worst Performers -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Underperformers</h2>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                @if($analytics['worst_performers']->count() > 0)
                    <div class="space-y-3">
                        @foreach($analytics['worst_performers'] as $investment)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $investment->name }}</p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $investment->symbol_identifier }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-[color:var(--color-danger-600)]">{{ number_format($investment->unrealized_gain_loss_percentage, 2) }}%</p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ number_format($investment->unrealized_gain_loss, 2) }} {{ $investment->currency }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No underperformer data available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Dividend Analytics -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Dividend Analytics</h2>
        </div>
        <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Dividends</p>
                    <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ number_format($analytics['dividends']['total_dividends'], 2) }} {{ $analytics['overview']['currency'] }}
                    </p>
                </div>
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Avg Monthly Dividend</p>
                    <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ number_format($analytics['dividends']['average_monthly_dividend'], 2) }} {{ $analytics['overview']['currency'] }}
                    </p>
                </div>
                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Projected Annual</p>
                    <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        {{ number_format($analytics['dividends']['projected_annual_dividend'], 2) }} {{ $analytics['overview']['currency'] }}
                    </p>
                </div>
            </div>
            @if(count($analytics['dividends']['dividends_by_year']) > 0)
                <div class="mt-4">
                    <h3 class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">Dividends by Year</h3>
                    <div class="space-y-2">
                        @foreach($analytics['dividends']['dividends_by_year'] as $year => $amount)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $year }}</span>
                                <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($amount, 2) }} {{ $analytics['overview']['currency'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Risk Analysis -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Risk Analysis</h2>
        </div>
        <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
            @if(count($analytics['risk_analysis']) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($analytics['risk_analysis'] as $risk => $data)
                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                            <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($risk) }}</p>
                            <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ number_format($data['percentage'], 2) }}%
                            </p>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                {{ number_format($data['value'], 2) }} {{ $analytics['overview']['currency'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">No risk analysis data available.</p>
            @endif
        </div>
    </div>

    <!-- Investment Type Analytics -->
    @if(count($analytics['by_type']) > 0)
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Analytics by Investment Type</h2>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Count</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Total Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Total Cost</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Gain/Loss</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Return %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($analytics['by_type'] as $type => $data)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $data['count'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ number_format($data['total_value'], 2) }} {{ $analytics['overview']['currency'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ number_format($data['total_cost'], 2) }} {{ $analytics['overview']['currency'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $data['gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                                        {{ number_format($data['gain_loss'], 2) }} {{ $analytics['overview']['currency'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $data['gain_loss_percentage'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                                        {{ number_format($data['gain_loss_percentage'], 2) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Project Investment Analytics (if applicable) -->
    @if($analytics['project_investments']['total_projects'] > 0)
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Investment Analytics</h2>
            </div>
            <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Projects</p>
                        <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $analytics['project_investments']['total_projects'] }}
                        </p>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Invested</p>
                        <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ number_format($analytics['project_investments']['total_invested'], 2) }} {{ $analytics['overview']['currency'] }}
                        </p>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Active Projects</p>
                        <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $analytics['project_investments']['active_projects'] }}
                        </p>
                    </div>
                    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-md">
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Completed Projects</p>
                        <p class="mt-2 text-xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $analytics['project_investments']['completed_projects'] }}
                        </p>
                    </div>
                </div>

                <!-- Individual Projects -->
                @if(count($analytics['project_investments']['projects']) > 0)
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Individual Projects</h3>
                        <div class="space-y-4">
                            @foreach($analytics['project_investments']['projects'] as $project)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-4 rounded-lg border border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-base font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ $project['name'] }}
                                            </h4>
                                            <div class="flex gap-2 mt-1 flex-wrap">
                                                @if($project['project_type'])
                                                    <span class="text-xs px-2 py-1 bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] rounded">
                                                        {{ ucfirst($project['project_type']) }}
                                                    </span>
                                                @endif
                                                @if($project['project_stage'])
                                                    <span class="text-xs px-2 py-1 bg-[color:var(--color-accent-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-accent-700)] dark:text-[color:var(--color-dark-600)] rounded">
                                                        {{ ucfirst($project['project_stage']) }}
                                                    </span>
                                                @endif
                                                @if($project['project_business_model'])
                                                    <span class="text-xs px-2 py-1 bg-[color:var(--color-secondary-200)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-secondary-700)] dark:text-[color:var(--color-dark-600)] rounded">
                                                        {{ ucfirst($project['project_business_model']) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right ml-4">
                                            <a href="{{ route('investments.show', $project['id']) }}" class="text-sm text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-500)]">
                                                View Details →
                                            </a>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Invested Amount</p>
                                            <p class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ number_format($project['invested_amount'], 2) }} {{ $project['currency'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Current Value</p>
                                            <p class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ number_format($project['current_value'], 2) }} {{ $project['currency'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Gain/Loss</p>
                                            <p class="text-sm font-semibold {{ $project['gain_loss'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                                                {{ $project['gain_loss'] >= 0 ? '+' : '' }}{{ number_format($project['gain_loss'], 2) }} {{ $project['currency'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Return</p>
                                            <p class="text-sm font-semibold {{ $project['gain_loss_percentage'] >= 0 ? 'text-[color:var(--color-success-600)]' : 'text-[color:var(--color-danger-600)]' }}">
                                                {{ $project['gain_loss_percentage'] >= 0 ? '+' : '' }}{{ number_format($project['gain_loss_percentage'], 2) }}%
                                            </p>
                                        </div>
                                    </div>
                                    @if($project['equity_percentage'])
                                        <div class="mt-3 pt-3 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                Equity: <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ number_format($project['equity_percentage'], 2) }}%</span>
                                            </p>
                                        </div>
                                    @endif
                                    @if($project['project_website'] || $project['project_repository'])
                                        <div class="mt-3 flex gap-3">
                                            @if($project['project_website'])
                                                <a href="{{ $project['project_website'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-500)]">
                                                    🌐 Website
                                                </a>
                                            @endif
                                            @if($project['project_repository'])
                                                <a href="{{ $project['project_repository'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-500)]">
                                                    📦 Repository
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($analytics['project_investments']['by_stage']) > 0)
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">By Stage</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($analytics['project_investments']['by_stage'] as $stage => $data)
                                <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] p-3 rounded-md">
                                    <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ ucfirst($stage) }}</p>
                                    <p class="text-lg font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $data['count'] }} projects</p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ number_format($data['total_invested'], 2) }} {{ $analytics['overview']['currency'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
