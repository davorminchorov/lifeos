@extends('layouts.app')

@section('title', $investment->name . ' - Investments - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ $investment->name }}
                @if($investment->symbol_identifier)
                    <span class="text-xl text-gray-500 dark:text-gray-400">({{ $investment->symbol_identifier }})</span>
                @endif
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ ucfirst(str_replace('_', ' ', $investment->investment_type)) }} investment
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('investments.edit', $investment) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit
            </a>
            <a href="{{ route('investments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
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
                            <div class="text-lg font-semibold">${{ number_format($investment->current_market_value, 2) }}</div>
                            @if($investment->current_value)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    ${{ number_format($investment->current_value, 8) }} per unit
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
                            <div class="text-lg font-semibold">${{ number_format($investment->total_cost_basis, 2) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                ${{ number_format($investment->purchase_price, 8) }} × {{ number_format($investment->quantity, 8) }} + ${{ number_format($investment->total_fees_paid, 2) }} fees
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Unrealized Gain/Loss</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold {{ $investment->unrealized_gain_loss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format($investment->unrealized_gain_loss, 2) }}
                                ({{ number_format($investment->unrealized_gain_loss_percentage, 2) }}%)
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Return</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                            <div class="text-lg font-semibold {{ $investment->total_return >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format($investment->total_return, 2) }}
                                ({{ number_format($investment->total_return_percentage, 2) }}%)
                            </div>
                            @if($investment->total_dividends_received > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Includes ${{ number_format($investment->total_dividends_received, 2) }} in dividends
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
                                {{ $investment->investment_type === 'stock' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $investment->investment_type === 'crypto' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                {{ $investment->investment_type === 'bond' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $investment->investment_type === 'real_estate' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ !in_array($investment->investment_type, ['stock', 'crypto', 'bond', 'real_estate']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
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
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($investment->transaction_history as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($transaction['date'])->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $transaction['type'] === 'buy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ ucfirst($transaction['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ number_format($transaction['quantity'], 8) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($transaction['price'], 8) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
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

        <button onclick="alert('Record dividend feature coming soon')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Record Dividend
        </button>

        <button onclick="alert('Record transaction feature coming soon')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Record Transaction
        </button>
    </div>
@endsection
