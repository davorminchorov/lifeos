@extends('layouts.app')

@section('title', $investment->name . ' - Investments - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                {{ $investment->name }}
                @if($investment->symbol_identifier)
                    <span class="text-xl text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">({{ $investment->symbol_identifier }})</span>
                @endif
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ ucfirst(str_replace('_', ' ', $investment->investment_type)) }} investment
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('investments.edit', $investment) }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit
            </a>
            <a href="{{ route('investments.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Investment Performance -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Performance Overview
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Current performance metrics and returns.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Current Value</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ app(\App\Services\CurrencyService::class)->format($investment->current_market_value, $investment->currency ?? 'MKD') }}</div>
                            @if($investment->current_value)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ app(\App\Services\CurrencyService::class)->format($investment->current_value, $investment->currency ?? 'MKD') }} per unit
                                    @if($investment->last_price_update)
                                        (Updated: {{ $investment->last_price_update->format('M j, Y') }})
                                    @endif
                                </div>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Cost Basis</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold">{{ app(\App\Services\CurrencyService::class)->format($investment->total_cost_basis, $investment->currency ?? 'MKD') }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ app(\App\Services\CurrencyService::class)->format($investment->purchase_price, $investment->currency ?? 'MKD') }} × {{ number_format($investment->quantity, 8) }} + {{ app(\App\Services\CurrencyService::class)->format($investment->total_fees_paid, $investment->currency ?? 'MKD') }} fees
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Unrealized Gain/Loss</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold {{ $investment->unrealized_gain_loss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ app(\App\Services\CurrencyService::class)->format($investment->unrealized_gain_loss, $investment->currency ?? 'MKD') }}
                                ({{ number_format($investment->unrealized_gain_loss_percentage, 2) }}%)
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Return</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold {{ $investment->total_return >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ app(\App\Services\CurrencyService::class)->format($investment->total_return, $investment->currency ?? 'MKD') }}
                                ({{ number_format($investment->total_return_percentage, 2) }}%)
                            </div>
                            @if($investment->total_dividends_received > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Includes {{ app(\App\Services\CurrencyService::class)->format($investment->total_dividends_received, $investment->currency ?? 'MKD') }} in dividends
                                </div>
                            @endif
                        </dd>
                    </div>
                    @if($investment->holding_period_days >= 365)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Annualized Return</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <div class="text-lg font-semibold {{ $investment->annualized_return >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($investment->annualized_return, 2) }}%
                                </div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Investment Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Investment Details
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Basic information about this investment.
                </p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Investment Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $investment->investment_type === 'stocks' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $investment->investment_type === 'crypto' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                {{ $investment->investment_type === 'bond' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $investment->investment_type === 'real_estate' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ !in_array($investment->investment_type, ['stocks', 'crypto', 'bond', 'real_estate']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                            ">
                                {{ ucfirst(str_replace('_', ' ', $investment->investment_type)) }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            @if($investment->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @elseif($investment->status === 'sold')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Sold
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Monitoring
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Quantity</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ number_format($investment->quantity, 8) }}</dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Purchase Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            {{ $investment->purchase_date->format('M j, Y') }}
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                ({{ $investment->holding_period_days }} days ago)
                            </span>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Risk Tolerance</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $investment->risk_tolerance === 'low' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $investment->risk_tolerance === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $investment->risk_tolerance === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                            ">
                                {{ ucfirst($investment->risk_tolerance) }} Risk
                            </span>
                        </dd>
                    </div>
                    @if($investment->account_broker)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Broker/Platform</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $investment->account_broker }}</dd>
                        </div>
                    @endif
                    @if($investment->target_allocation_percentage)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Target Allocation</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $investment->target_allocation_percentage }}% of portfolio</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Investment Goals -->
        @if($investment->investment_goals && count($investment->investment_goals) > 0)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Investment Goals
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Your objectives for this investment.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="flex flex-wrap gap-2">
                        @foreach($investment->investment_goals as $goal)
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ ucfirst($goal) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Transaction History -->
        @if($investment->transaction_history && count($investment->transaction_history) > 0)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Transaction History
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Buy/sell transactions for this investment.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                                @foreach($investment->transaction_history as $transaction)
                                    <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ \Carbon\Carbon::parse($transaction['date'])->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $transaction['type'] === 'buy' ? 'bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]' : 'bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-danger-500)]' }}">
                                                {{ ucfirst($transaction['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ number_format($transaction['quantity'], 8) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            ${{ number_format($transaction['price'], 8) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            ${{ number_format($transaction['quantity'] * $transaction['price'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($investment->notes)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Notes
                    </h3>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $investment->notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 flex flex-wrap gap-4">
        <form method="POST" action="{{ route('investments.update-price', $investment) }}" class="inline">
            @csrf
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Update Price
            </button>
        </form>

        <form method="POST" action="{{ route('investments.record-dividend', $investment) }}" class="inline">
            @csrf
            <input type="hidden" name="investment_id" value="{{ $investment->id }}">
            <button type="button" onclick="openDividendModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Record Dividend
            </button>
        </form>

        <form method="POST" action="{{ route('investments.record-transaction', $investment) }}" class="inline">
            @csrf
            <input type="hidden" name="investment_id" value="{{ $investment->id }}">
            <button type="button" onclick="openTransactionModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Record Transaction
            </button>
        </form>
    </div>

    <!-- Record Dividend Modal -->
    <div id="dividendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Record Dividend</h3>
                <button type="button" onclick="closeDividendModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('investments.record-dividend', $investment) }}">
                @csrf
                <input type="hidden" name="investment_id" value="{{ $investment->id }}">

                <div class="mb-4">
                    <label for="dividend_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dividend Amount ($)</label>
                    <input type="number" step="0.01" name="amount" id="dividend_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="dividend_per_share" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dividend per Share ($)</label>
                    <input type="number" step="0.00000001" name="dividend_per_share" id="dividend_per_share" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="shares_held" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Shares Held</label>
                    <input type="number" step="0.00000001" name="shares_held" id="shares_held" value="{{ $investment->quantity }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="record_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Record Date</label>
                    <input type="date" name="record_date" id="record_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="dividend_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dividend Type</label>
                    <select name="dividend_type" id="dividend_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="ordinary">Ordinary</option>
                        <option value="qualified">Qualified</option>
                        <option value="special">Special</option>
                        <option value="return_of_capital">Return of Capital</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequency</label>
                    <select name="frequency" id="frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="quarterly">Quarterly</option>
                        <option value="monthly">Monthly</option>
                        <option value="semi_annual">Semi-Annual</option>
                        <option value="annual">Annual</option>
                        <option value="special">Special</option>
                    </select>
                </div>

                <input type="hidden" name="currency" value="MKD">

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeDividendModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                        Record Dividend
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Record Transaction Modal -->
    <div id="transactionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Record Transaction</h3>
                <button type="button" onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('investments.record-transaction', $investment) }}">
                @csrf
                <input type="hidden" name="investment_id" value="{{ $investment->id }}">

                <div class="mb-4">
                    <label for="transaction_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                        <option value="buy">Buy</option>
                        <option value="sell">Sell</option>
                        <option value="dividend_reinvestment">Dividend Reinvestment</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="transaction_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                    <input type="number" step="0.00000001" name="quantity" id="transaction_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="price_per_share" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price per Share ($)</label>
                    <input type="number" step="0.00000001" name="price_per_share" id="price_per_share" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <div class="mb-4">
                    <label for="total_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount ($)</label>
                    <input type="number" step="0.00000001" name="total_amount" id="total_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required readonly>
                </div>

                <div class="mb-4">
                    <label for="transaction_fees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fees ($)</label>
                    <input type="number" step="0.01" name="fees" id="transaction_fees" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="mb-4">
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Date</label>
                    <input type="date" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                </div>

                <input type="hidden" name="currency" value="MKD">
                <input type="hidden" name="taxes" value="0">

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700">
                        Record Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDividendModal() {
            document.getElementById('dividendModal').classList.remove('hidden');
            document.getElementById('dividendModal').classList.add('flex');
        }

        function closeDividendModal() {
            document.getElementById('dividendModal').classList.add('hidden');
            document.getElementById('dividendModal').classList.remove('flex');
        }

        function openTransactionModal() {
            document.getElementById('transactionModal').classList.remove('hidden');
            document.getElementById('transactionModal').classList.add('flex');
        }

        function closeTransactionModal() {
            document.getElementById('transactionModal').classList.add('hidden');
            document.getElementById('transactionModal').classList.remove('flex');
        }

        // Auto-calculate dividend amount
        document.getElementById('dividend_per_share').addEventListener('input', calculateDividendAmount);
        document.getElementById('shares_held').addEventListener('input', calculateDividendAmount);

        function calculateDividendAmount() {
            const perShare = parseFloat(document.getElementById('dividend_per_share').value) || 0;
            const shares = parseFloat(document.getElementById('shares_held').value) || 0;
            document.getElementById('dividend_amount').value = (perShare * shares).toFixed(2);
        }

        // Auto-calculate transaction total amount
        document.getElementById('transaction_quantity').addEventListener('input', calculateTransactionAmount);
        document.getElementById('price_per_share').addEventListener('input', calculateTransactionAmount);

        function calculateTransactionAmount() {
            const quantity = parseFloat(document.getElementById('transaction_quantity').value) || 0;
            const price = parseFloat(document.getElementById('price_per_share').value) || 0;
            document.getElementById('total_amount').value = (quantity * price).toFixed(8);
        }
    </script>
@endsection
