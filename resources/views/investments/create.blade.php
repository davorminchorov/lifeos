@extends('layouts.app')

@section('title', 'Add New Investment - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Add New Investment
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Track a new investment in your portfolio
            </p>
        </div>
        <a href="{{ route('investments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Back to List
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('investments.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <x-form.section title="Basic Information" description="Enter the basic details about this investment.">
                <x-form.input
                    name="name"
                    label="Investment Name"
                    type="text"
                    required
                    placeholder="e.g., Apple Inc., Bitcoin, S&P 500 ETF"
                />

                <x-form.select
                    name="investment_type"
                    label="Investment Type"
                    required
                    placeholder="Select Investment Type"
                >
                    <option value="stock" {{ old('investment_type') === 'stock' ? 'selected' : '' }}>Stock</option>
                    <option value="bond" {{ old('investment_type') === 'bond' ? 'selected' : '' }}>Bond</option>
                    <option value="crypto" {{ old('investment_type') === 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                    <option value="real_estate" {{ old('investment_type') === 'real_estate' ? 'selected' : '' }}>Real Estate</option>
                    <option value="mutual_fund" {{ old('investment_type') === 'mutual_fund' ? 'selected' : '' }}>Mutual Fund</option>
                    <option value="etf" {{ old('investment_type') === 'etf' ? 'selected' : '' }}>ETF</option>
                    <option value="commodity" {{ old('investment_type') === 'commodity' ? 'selected' : '' }}>Commodity</option>
                    <option value="other" {{ old('investment_type') === 'other' ? 'selected' : '' }}>Other</option>
                </x-form.select>

                <x-form.input
                    name="symbol_identifier"
                    label="Symbol/Ticker"
                    type="text"
                    placeholder="e.g., AAPL, BTC, SPY"
                    helpText="Stock ticker symbol or cryptocurrency identifier"
                />

                <x-form.select
                    name="status"
                    label="Status"
                    required
                >
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="monitoring" {{ old('status') === 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                    <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                </x-form.select>
            </x-form.section>

            <!-- Purchase Details -->
            <x-form.section title="Purchase Details" description="Enter the purchase information for this investment.">
                <x-form.input
                    name="quantity"
                    label="Quantity"
                    type="number"
                    required
                    step="0.00000001"
                    min="0"
                    placeholder="0.00000000"
                    helpText="Number of shares/units/coins purchased"
                />

                <x-form.input
                    name="purchase_price"
                    label="Purchase Price (per unit)"
                    type="number"
                    required
                    step="0.00000001"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                    helpText="Price paid per share/unit/coin"
                />

                <x-form.input
                    name="purchase_date"
                    label="Purchase Date"
                    type="date"
                    required
                    value="{{ old('purchase_date', date('Y-m-d')) }}"
                />

                <x-form.input
                    name="total_fees_paid"
                    label="Total Fees Paid"
                    type="number"
                    step="0.01"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                    value="{{ old('total_fees_paid', '0.00') }}"
                    helpText="Broker fees, commissions, transaction costs"
                />
            </x-form.section>

            <!-- Account & Goals -->
            <x-form.section title="Account & Investment Goals" description="Specify broker/account details and investment objectives.">
                <x-form.input
                    name="account_broker"
                    label="Broker/Platform"
                    type="text"
                    placeholder="e.g., Fidelity, Robinhood, Coinbase"
                    helpText="Name of the broker or trading platform"
                />

                <x-form.input
                    name="account_number"
                    label="Account Number"
                    type="text"
                    placeholder="Optional account number"
                />

                <x-form.select
                    name="risk_tolerance"
                    label="Risk Tolerance"
                    required
                >
                    <option value="conservative" {{ old('risk_tolerance') === 'conservative' ? 'selected' : '' }}>Conservative</option>
                    <option value="moderate" {{ old('risk_tolerance', 'moderate') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="aggressive" {{ old('risk_tolerance') === 'aggressive' ? 'selected' : '' }}>Aggressive</option>
                </x-form.select>

                <x-form.input
                    name="target_allocation_percentage"
                    label="Target Allocation %"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    placeholder="0.00"
                    suffix="%"
                    helpText="Target percentage of total portfolio"
                />
            </x-form.section>

            <!-- Investment Goals -->
            <x-form.section title="Investment Goals" description="Select your investment objectives for this position.">
                <div class="md:col-span-2">
                    <label class="text-base font-medium text-gray-900 dark:text-white">Investment Goals</label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Select all that apply to this investment.</p>
                    <fieldset class="mt-4">
                        <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                            <div class="flex items-center">
                                <input id="goal_retirement" name="investment_goals[]" type="checkbox" value="retirement" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('retirement', old('investment_goals', [])) ? 'checked' : '' }}>
                                <label for="goal_retirement" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Retirement
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_growth" name="investment_goals[]" type="checkbox" value="growth" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('growth', old('investment_goals', [])) ? 'checked' : '' }}>
                                <label for="goal_growth" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Growth
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_income" name="investment_goals[]" type="checkbox" value="income" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('income', old('investment_goals', [])) ? 'checked' : '' }}>
                                <label for="goal_income" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Income
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_speculation" name="investment_goals[]" type="checkbox" value="speculation" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('speculation', old('investment_goals', [])) ? 'checked' : '' }}>
                                <label for="goal_speculation" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Speculation
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </x-form.section>

            <!-- Additional Information -->
            <x-form.section title="Additional Information" description="Optional notes and additional details.">
                <div class="md:col-span-2">
                    <x-form.input
                        name="notes"
                        label="Notes"
                        type="textarea"
                        rows="4"
                        placeholder="Investment thesis, research notes, or any additional comments..."
                    />
                </div>
            </x-form.section>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('investments.index') }}" class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Create Investment
                </button>
            </div>
        </form>
    </div>
@endsection
