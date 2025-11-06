@extends('layouts.app')

@section('title', 'Edit Investment - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Investment: {{ $investment->name }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Update your investment information
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('investments.show', $investment) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                View Details
            </a>
            <a href="{{ route('investments.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('investments.update', $investment) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <x-form.section title="Basic Information" description="Enter the basic details about this investment.">
                <x-form.input
                    name="name"
                    label="Investment Name"
                    type="text"
                    required
                    placeholder="e.g., Apple Inc., Bitcoin, S&P 500 ETF"
                    value="{{ old('name', $investment->name) }}"
                />

                <x-form.select
                    name="investment_type"
                    label="Investment Type"
                    required
                    placeholder="Select Investment Type"
                >
                    <option value="stock" {{ old('investment_type', $investment->investment_type) === 'stock' ? 'selected' : '' }}>Stocks</option>
                    <option value="bond" {{ old('investment_type', $investment->investment_type) === 'bond' ? 'selected' : '' }}>Bonds</option>
                    <option value="crypto" {{ old('investment_type', $investment->investment_type) === 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                    <option value="real_estate" {{ old('investment_type', $investment->investment_type) === 'real_estate' ? 'selected' : '' }}>Real Estate</option>
                    <option value="mutual_fund" {{ old('investment_type', $investment->investment_type) === 'mutual_fund' ? 'selected' : '' }}>Mutual Fund</option>
                    <option value="etf" {{ old('investment_type', $investment->investment_type) === 'etf' ? 'selected' : '' }}>ETF</option>
                    <option value="commodities" {{ old('investment_type', $investment->investment_type) === 'commodities' ? 'selected' : '' }}>Commodities</option>
                    <option value="cash" {{ old('investment_type', $investment->investment_type) === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="project" {{ old('investment_type', $investment->investment_type) === 'project' ? 'selected' : '' }}>Project</option>
                </x-form.select>

                <x-form.input
                    name="symbol_identifier"
                    label="Symbol/Ticker"
                    type="text"
                    placeholder="e.g., AAPL, BTC, SPY"
                    helpText="Stock ticker symbol or cryptocurrency identifier"
                    value="{{ old('symbol_identifier', $investment->symbol_identifier) }}"
                />

                <x-form.select
                    name="status"
                    label="Status"
                    required
                >
                    <option value="active" {{ old('status', $investment->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="monitoring" {{ old('status', $investment->status) === 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                    <option value="sold" {{ old('status', $investment->status) === 'sold' ? 'selected' : '' }}>Sold</option>
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
                    value="{{ old('quantity', $investment->quantity) }}"
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
                    value="{{ old('purchase_price', $investment->purchase_price) }}"
                />

                <x-form.select
                    name="currency"
                    label="Currency"
                >
                    <option value="MKD" {{ old('currency', $investment->currency ?? 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                    <option value="USD" {{ old('currency', $investment->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                    <option value="EUR" {{ old('currency', $investment->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                    <option value="GBP" {{ old('currency', $investment->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                    <option value="CAD" {{ old('currency', $investment->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                    <option value="AUD" {{ old('currency', $investment->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                    <option value="JPY" {{ old('currency', $investment->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                    <option value="CHF" {{ old('currency', $investment->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                    <option value="RSD" {{ old('currency', $investment->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                    <option value="BGN" {{ old('currency', $investment->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                </x-form.select>

                <x-form.input
                    name="purchase_date"
                    label="Purchase Date"
                    type="date"
                    required
                    value="{{ old('purchase_date', $investment->purchase_date->format('Y-m-d')) }}"
                />

                <x-form.input
                    name="total_fees_paid"
                    label="Total Fees Paid"
                    type="number"
                    step="0.01"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                    value="{{ old('total_fees_paid', $investment->total_fees_paid) }}"
                    helpText="Broker fees, commissions, transaction costs"
                />
            </x-form.section>

            <!-- Current Value -->
            <x-form.section title="Current Market Data" description="Update current market value and dividend information.">
                <x-form.input
                    name="current_value"
                    label="Current Price (per unit)"
                    type="number"
                    step="0.00000001"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                    helpText="Current market price per share/unit/coin"
                    value="{{ old('current_value', $investment->current_value) }}"
                />

                <x-form.input
                    name="total_dividends_received"
                    label="Total Dividends Received"
                    type="number"
                    step="0.01"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                    value="{{ old('total_dividends_received', $investment->total_dividends_received) }}"
                    helpText="Total dividends/distributions received to date"
                />

                <x-form.input
                    name="last_price_update"
                    label="Last Price Update"
                    type="date"
                    value="{{ old('last_price_update', $investment->last_price_update?->format('Y-m-d')) }}"
                    helpText="Date when current price was last updated"
                />

                <div></div> <!-- Empty div for grid alignment -->
            </x-form.section>

            <!-- Account & Goals -->
            <x-form.section title="Account & Investment Goals" description="Specify broker/account details and investment objectives.">
                <x-form.input
                    name="account_broker"
                    label="Broker/Platform"
                    type="text"
                    placeholder="e.g., Fidelity, Robinhood, Coinbase"
                    helpText="Name of the broker or trading platform"
                    value="{{ old('account_broker', $investment->account_broker) }}"
                />

                <x-form.input
                    name="account_number"
                    label="Account Number"
                    type="text"
                    placeholder="Optional account number"
                    value="{{ old('account_number', $investment->account_number) }}"
                />

                <x-form.select
                    name="risk_tolerance"
                    label="Risk Tolerance"
                    required
                >
                    <option value="conservative" {{ old('risk_tolerance', $investment->risk_tolerance) === 'conservative' ? 'selected' : '' }}>Conservative</option>
                    <option value="moderate" {{ old('risk_tolerance', $investment->risk_tolerance) === 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="aggressive" {{ old('risk_tolerance', $investment->risk_tolerance) === 'aggressive' ? 'selected' : '' }}>Aggressive</option>
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
                    value="{{ old('target_allocation_percentage', $investment->target_allocation_percentage) }}"
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
                                <input id="goal_retirement" name="investment_goals[]" type="checkbox" value="retirement" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('retirement', old('investment_goals', $investment->investment_goals ?? [])) ? 'checked' : '' }}>
                                <label for="goal_retirement" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Retirement
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_growth" name="investment_goals[]" type="checkbox" value="growth" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('growth', old('investment_goals', $investment->investment_goals ?? [])) ? 'checked' : '' }}>
                                <label for="goal_growth" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Growth
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_income" name="investment_goals[]" type="checkbox" value="income" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('income', old('investment_goals', $investment->investment_goals ?? [])) ? 'checked' : '' }}>
                                <label for="goal_income" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Income
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_speculation" name="investment_goals[]" type="checkbox" value="speculation" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array('speculation', old('investment_goals', $investment->investment_goals ?? [])) ? 'checked' : '' }}>
                                <label for="goal_speculation" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Speculation
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </x-form.section>

            <!-- Project Details -->
            <x-form.section title="Project Details" description="Describe the project specifics for project-based investments.">
                <x-form.input name="project_type" label="Project Type" placeholder="SaaS, Mobile App, Marketplace" value="{{ old('project_type', $investment->project_type) }}" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                    <div>
                        <x-form.input name="project_website" label="Project Website" type="url" placeholder="https://example.com" value="{{ old('project_website', $investment->project_website) }}" />
                    </div>
                    <div>
                        <x-form.input name="project_repository" label="Repository URL" type="url" placeholder="https://github.com/org/repo" value="{{ old('project_repository', $investment->project_repository) }}" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:col-span-2">
                    <div>
                        <x-form.select name="project_stage" label="Stage">
                            <option value="">Select a stage</option>
                            <option value="idea" {{ old('project_stage', $investment->project_stage) === 'idea' ? 'selected' : '' }}>Idea</option>
                            <option value="prototype" {{ old('project_stage', $investment->project_stage) === 'prototype' ? 'selected' : '' }}>Prototype</option>
                            <option value="mvp" {{ old('project_stage', $investment->project_stage) === 'mvp' ? 'selected' : '' }}>MVP</option>
                            <option value="growth" {{ old('project_stage', $investment->project_stage) === 'growth' ? 'selected' : '' }}>Growth</option>
                            <option value="mature" {{ old('project_stage', $investment->project_stage) === 'mature' ? 'selected' : '' }}>Mature</option>
                        </x-form.select>
                    </div>
                    <x-form.input name="equity_percentage" label="Equity %" type="number" step="0.01" min="0" max="100" value="{{ old('equity_percentage', $investment->equity_percentage) }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                    <x-form.input name="project_amount" label="Project Amount" type="number" step="0.01" min="0" prefix="$" placeholder="0.00" value="{{ old('project_amount', $investment->project_amount) }}" />
                    <div>
                        <x-form.select
                            name="project_currency"
                            label="Project Currency"
                        >
                            <option value="MKD" {{ old('project_currency', $investment->project_currency ?? 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('project_currency', $investment->project_currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('project_currency', $investment->project_currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('project_currency', $investment->project_currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('project_currency', $investment->project_currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('project_currency', $investment->project_currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('project_currency', $investment->project_currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('project_currency', $investment->project_currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('project_currency', $investment->project_currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('project_currency', $investment->project_currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:col-span-2">
                    <x-form.input name="project_start_date" label="Project Start Date" type="date" value="{{ old('project_start_date', optional($investment->project_start_date)->format('Y-m-d')) }}" />
                    <x-form.input name="project_end_date" label="Project End Date" type="date" value="{{ old('project_end_date', optional($investment->project_end_date)->format('Y-m-d')) }}" />
                </div>

                <div class="md:col-span-2">
                    <x-form.select name="project_business_model" label="Business Model">
                        <option value="">Select a business model</option>
                        <option value="subscription" {{ old('project_business_model', $investment->project_business_model) === 'subscription' ? 'selected' : '' }}>Subscription</option>
                        <option value="ads" {{ old('project_business_model', $investment->project_business_model) === 'ads' ? 'selected' : '' }}>Ads</option>
                        <option value="one-time" {{ old('project_business_model', $investment->project_business_model) === 'one-time' ? 'selected' : '' }}>One-time</option>
                        <option value="freemium" {{ old('project_business_model', $investment->project_business_model) === 'freemium' ? 'selected' : '' }}>Freemium</option>
                    </x-form.select>
                </div>

                <div class="md:col-span-2">
                    <x-form.input name="project_notes" label="Project Notes" type="textarea" rows="4" placeholder="Key milestones, KPIs, roadmap, team, etc." value="{{ old('project_notes', $investment->project_notes) }}" />
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
                        value="{{ old('notes', $investment->notes) }}"
                    />
                </div>
            </x-form.section>

            <!-- Performance Summary (Read-only) -->
            <x-form.section title="Current Performance" description="Current performance metrics (read-only).">
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Current Value</div>
                            <div class="text-gray-600 dark:text-gray-300">${{ number_format($investment->current_market_value, 2) }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Total Cost Basis</div>
                            <div class="text-gray-600 dark:text-gray-300">${{ number_format($investment->total_cost_basis, 2) }}</div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Unrealized Gain/Loss</div>
                            <div class="{{ $investment->unrealized_gain_loss >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($investment->unrealized_gain_loss, 2) }}
                                ({{ number_format($investment->unrealized_gain_loss_percentage, 2) }}%)
                            </div>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">Holding Period</div>
                            <div class="text-gray-600 dark:text-gray-300">{{ $investment->holding_period_days }} days</div>
                        </div>
                    </div>
                </div>
            </x-form.section>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('investments.show', $investment) }}" class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Update Investment
                </button>
            </div>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const investmentTypeSelect = document.querySelector('select[name="investment_type"]');

    // Get field containers - x-form components wrap inputs
    const quantityInput = document.querySelector('input[name="quantity"]');
    const purchasePriceInput = document.querySelector('input[name="purchase_price"]');
    const purchaseDateInput = document.querySelector('input[name="purchase_date"]');
    const riskToleranceSelect = document.querySelector('select[name="risk_tolerance"]');

    // Define which types require purchase details
    const typesRequiringPurchaseDetails = ['stock', 'bond', 'etf', 'mutual_fund', 'crypto'];
    // Define which types require risk tolerance
    const typesRequiringRiskTolerance = ['stock', 'bond', 'etf', 'mutual_fund'];

    function toggleFields() {
        if (!investmentTypeSelect) return;

        const selectedType = investmentTypeSelect.value;
        const requiresPurchaseDetails = typesRequiringPurchaseDetails.includes(selectedType);
        const requiresRiskTolerance = typesRequiringRiskTolerance.includes(selectedType);

        // Handle purchase details fields
        if (requiresPurchaseDetails) {
            if (quantityInput) quantityInput.setAttribute('required', 'required');
            if (purchasePriceInput) purchasePriceInput.setAttribute('required', 'required');
            if (purchaseDateInput) purchaseDateInput.setAttribute('required', 'required');
        } else {
            if (quantityInput) quantityInput.removeAttribute('required');
            if (purchasePriceInput) purchasePriceInput.removeAttribute('required');
            if (purchaseDateInput) purchaseDateInput.removeAttribute('required');
        }

        // Handle risk tolerance field
        if (requiresRiskTolerance) {
            if (riskToleranceSelect) riskToleranceSelect.setAttribute('required', 'required');
        } else {
            if (riskToleranceSelect) riskToleranceSelect.removeAttribute('required');
        }
    }

    // Run on page load
    toggleFields();

    // Run when investment type changes
    if (investmentTypeSelect) {
        investmentTypeSelect.addEventListener('change', toggleFields);
    }
});
</script>

@endsection
