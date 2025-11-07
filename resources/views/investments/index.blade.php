@extends('layouts.app')

@section('title', 'Investments - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4" x-data="{ importOpen: false }">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Investments
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your investment portfolio and performance
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-2 flex-shrink-0">
            <a href="{{ route('investments.analytics') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation">
                Analytics
            </a>
            <button type="button"
                    class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 touch-manipulation"
                    x-on:click="importOpen = !importOpen">
                Import CSV
            </button>
            <a href="{{ route('investments.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                Add Investment
            </a>
        </div>
    </div>

    <!-- Import CSV Inline Panel -->
    <div x-show="importOpen" x-transition class="mt-4 rounded-md border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
        <div class="p-4">
            <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Import Investments from CSV</h2>
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Upload a CSV export from your broker. The file will be validated and processed in the background queue named <span class="font-mono">imports</span>.</p>

            @if ($errors->has('file'))
                <p class="mt-3 text-sm text-[color:var(--color-danger-600)]">{{ $errors->first('file') }}</p>
            @endif

            <form class="mt-4 flex items-center gap-3" method="POST" action="{{ route('investments.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-[color:var(--color-primary-200)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[color:var(--color-primary-700)] hover:file:bg-[color:var(--color-primary-300)] dark:file:bg-[color:var(--color-dark-300)] dark:hover:file:bg-[color:var(--color-dark-400)]" required>
                <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">Start Import</button>
                <button type="button" class="text-sm text-[color:var(--color-primary-600)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-600)]" x-on:click="importOpen = false">Cancel</button>
            </form>

            <p class="mt-2 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Max file size 10MB. Supported types: CSV/TXT.</p>
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
                        <option value="project" {{ request('investment_type') === 'project' ? 'selected' : '' }}>Project</option>
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
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Apply Filters
                    </button>
                    <a href="{{ route('investments.index') }}" class="ml-2 bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Clear
                    </a>
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
                                    $currencyService = app(\App\Services\CurrencyService::class);
                                @endphp
                                <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                    {{ $investment->symbol_identifier ?? $investment->name }}
                                                </div>
                                                <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                    @if($investment->investment_type === 'project')
                                                        @if($investment->project_type)
                                                            {{ $investment->project_type }}
                                                            @if($investment->project_stage)
                                                                · {{ ucfirst($investment->project_stage) }}
                                                            @endif
                                                        @else
                                                            {{ $investment->name }}
                                                        @endif
                                                    @else
                                                        {{ $investment->name }}
                                                    @endif
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
                                        @if($investment->investment_type === 'project')
                                            @if($investment->equity_percentage)
                                                {{ number_format($investment->equity_percentage, 2) }}% equity
                                            @else
                                                N/A
                                            @endif
                                        @else
                                            {{ $investment->quantity !== null ? number_format($investment->quantity, 4) : 'N/A' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        @if($investment->investment_type === 'project' && $investment->project_amount)
                                            {{ app(\App\Services\CurrencyService::class)->format($investment->project_amount, $investment->project_currency ?? 'MKD') }}
                                        @else
                                            {{ $investment->formatted_purchase_price }}
                                        @endif
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
                    <a href="{{ route('investments.create') }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Add Investment
                    </a>
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
