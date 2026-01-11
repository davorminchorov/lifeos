@extends('layouts.app')

@section('title', 'Investments - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Investments
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your investment portfolio and performance
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 flex-shrink-0">
            <x-button href="{{ route('investments.analytics') }}" variant="secondary" class="w-full sm:w-auto">Analytics</x-button>
            <x-button href="{{ route('investments.import.form') }}" variant="secondary" class="w-full sm:w-auto">Import CSV</x-button>
            <x-button href="{{ route('investments.create') }}" variant="primary" class="w-full sm:w-auto">Add Investment</x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('investments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search investments..."
                    />
                </div>

                <!-- Type Filter -->
                <div>
                    <x-form.select
                        name="investment_type"
                        label="Type"
                        placeholder="All Types"
                    >
                        <option value="stock" {{ request('investment_type') === 'stock' ? 'selected' : '' }}>Stocks</option>
                        <option value="bond" {{ request('investment_type') === 'bond' ? 'selected' : '' }}>Bond</option>
                        <option value="crypto" {{ request('investment_type') === 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                        <option value="etf" {{ request('investment_type') === 'etf' ? 'selected' : '' }}>ETF</option>
                        <option value="mutual_fund" {{ request('investment_type') === 'mutual_fund' ? 'selected' : '' }}>Mutual Fund</option>
                        <option value="real_estate" {{ request('investment_type') === 'real_estate' ? 'selected' : '' }}>Real Estate</option>
                        <option value="commodities" {{ request('investment_type') === 'commodities' ? 'selected' : '' }}>Commodities</option>
                        <option value="cash" {{ request('investment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    </x-form.select>
                </div>

                <!-- Account Filter -->
                <div>
                    <x-form.input
                        name="account"
                        label="Account"
                        type="text"
                        placeholder="Filter by account..."
                    />
                </div>

                <!-- Performance Filter -->
                <div>
                    <x-form.select
                        name="performance"
                        label="Performance"
                        placeholder="All"
                    >
                        <option value="gains" {{ request('performance') === 'gains' ? 'selected' : '' }}>Gains Only</option>
                        <option value="losses" {{ request('performance') === 'losses' ? 'selected' : '' }}>Losses Only</option>
                    </x-form.select>
                </div>

                <div class="col-span-full">
                    <x-button type="submit" variant="primary">Apply Filters</x-button>
                    <x-button href="{{ route('investments.index') }}" variant="secondary" class="ml-2">Clear</x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Investments Table -->
    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($investments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-200)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Investment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Quantity / Equity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Price / Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Current Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Performance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($investments as $investment)
                                @php
                                    $totalValue = $investment->current_market_value;
                                    $totalCost = $investment->total_cost_basis;
                                    $gainLoss = $investment->unrealized_gain_loss;
                                    $gainLossPercent = $investment->unrealized_gain_loss_percentage;
                                @endphp
                                <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $investment->symbol_identifier ?? $investment->name }}
                                                </div>
                                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                    {{ $investment->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-info-600)] dark:text-[color:var(--color-info-50)]">
                                            {{ ucfirst(str_replace('_', ' ', $investment->investment_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $investment->quantity !== null ? number_format($investment->quantity, 4) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $investment->formatted_purchase_price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $investment->formatted_current_market_value }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <span class="font-medium {{ $gainLoss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                                {{ $gainLoss >= 0 ? '+' : '' }}{{ $investment->formatted_unrealized_gain_loss }}
                                            </span>
                                            <span class="ml-2 text-xs {{ $gainLoss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]' }}">
                                                ({{ $gainLoss >= 0 ? '+' : '' }}{{ number_format($gainLossPercent, 1) }}%)
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('investments.show', $investment) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)] dark:hover:text-[color:var(--color-accent-600)] transition-colors duration-200">View</a>
                                            <a href="{{ route('investments.edit', $investment) }}" class="text-[color:var(--color-accent-500)] hover:text-[color:var(--color-accent-600)] dark:text-[color:var(--color-accent-500)] dark:hover:text-[color:var(--color-accent-600)] transition-colors duration-200">Edit</a>
                                            <button type="button"
                                                    class="text-[color:var(--color-danger-500)] hover:text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-500)] dark:hover:text-[color:var(--color-danger-600)] transition-colors duration-200"
                                                    x-on:click="$dispatch('open-modal', { id: 'deleteInvestmentModal-{{ $investment->id }}' })">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $investments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">No investments found</h3>
                    <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-4">Get started by adding your first investment.</p>
                    <x-button href="{{ route('investments.create') }}" variant="primary">Add Investment</x-button>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modals for each investment -->
    @foreach($investments as $investment)
        <x-confirmation-modal
            id="deleteInvestmentModal-{{ $investment->id }}"
            title="Delete Investment"
            message="Are you sure you want to delete the investment '{{ $investment->symbol ?? $investment->name }}'? This action cannot be undone."
            confirm-text="Delete"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('investments.destroy', $investment)"
            method="DELETE"
        />
    @endforeach
@endsection
