@extends('layouts.app')

@section('title', 'Add New Investment - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add New Investment</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Track a new investment in your portfolio</p>
            </div>
            <a href="{{ route('investments.index') }}"
               class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to List
            </a>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('investments.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Name *</label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name') }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                               placeholder="e.g., Apple Inc., Bitcoin, S&P 500 ETF">
                        @error('name')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="investment_type" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Type *</label>
                        <select name="investment_type" id="investment_type" required
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            <option value="">Select Investment Type</option>
                            <option value="stocks" {{ old('investment_type') === 'stocks' ? 'selected' : '' }}>Stocks</option>
                            <option value="bonds" {{ old('investment_type') === 'bonds' ? 'selected' : '' }}>Bonds</option>
                            <option value="crypto" {{ old('investment_type') === 'crypto' ? 'selected' : '' }}>Cryptocurrency</option>
                            <option value="real_estate" {{ old('investment_type') === 'real_estate' ? 'selected' : '' }}>Real Estate</option>
                            <option value="mutual_fund" {{ old('investment_type') === 'mutual_fund' ? 'selected' : '' }}>Mutual Fund</option>
                            <option value="etf" {{ old('investment_type') === 'etf' ? 'selected' : '' }}>ETF</option>
                            <option value="commodities" {{ old('investment_type') === 'commodities' ? 'selected' : '' }}>Commodities</option>
                            <option value="cash" {{ old('investment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="project" {{ old('investment_type') === 'project' ? 'selected' : '' }}>Project</option>
                        </select>
                        @error('investment_type')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="symbol_identifier" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Symbol/Ticker</label>
                        <input type="text" name="symbol_identifier" id="symbol_identifier"
                               value="{{ old('symbol_identifier') }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                               placeholder="e.g., AAPL, BTC, SPY">
                        <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Stock ticker symbol or cryptocurrency identifier</p>
                        @error('symbol_identifier')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Status *</label>
                        <select name="status" id="status" required
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="monitoring" {{ old('status') === 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                            <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Purchase Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Purchase Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Quantity *</label>
                            <input type="number" name="quantity" id="quantity" required step="0.00000001" min="0"
                                   value="{{ old('quantity') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="0.00000000">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Number of shares/units/coins purchased</p>
                            @error('quantity')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Purchase Price (per unit) *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.00000001" name="purchase_price" id="purchase_price" required min="0"
                                       value="{{ old('purchase_price') }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Price paid per share/unit/coin</p>
                            @error('purchase_price')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Purchase Date *</label>
                            <input type="date" name="purchase_date" id="purchase_date" required
                                   value="{{ old('purchase_date', date('Y-m-d')) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('purchase_date')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_fees_paid" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Total Fees Paid</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" name="total_fees_paid" id="total_fees_paid" min="0"
                                       value="{{ old('total_fees_paid', '0.00') }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Broker fees, commissions, transaction costs</p>
                            @error('total_fees_paid')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account & Investment Goals -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Account & Investment Goals</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_broker" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Broker/Platform</label>
                            <input type="text" name="account_broker" id="account_broker"
                                   value="{{ old('account_broker') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="e.g., Fidelity, Robinhood, Coinbase">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Name of the broker or trading platform</p>
                            @error('account_broker')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="account_number" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Account Number</label>
                            <input type="text" name="account_number" id="account_number"
                                   value="{{ old('account_number') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="Optional account number">
                            @error('account_number')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="risk_tolerance" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Risk Tolerance *</label>
                            <select name="risk_tolerance" id="risk_tolerance" required
                                    class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                <option value="conservative" {{ old('risk_tolerance') === 'conservative' ? 'selected' : '' }}>Conservative</option>
                                <option value="moderate" {{ old('risk_tolerance', 'moderate') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                <option value="aggressive" {{ old('risk_tolerance') === 'aggressive' ? 'selected' : '' }}>Aggressive</option>
                            </select>
                            @error('risk_tolerance')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="target_allocation_percentage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Target Allocation %</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" step="0.01" name="target_allocation_percentage" id="target_allocation_percentage" min="0" max="100"
                                       value="{{ old('target_allocation_percentage') }}"
                                       class="block w-full pr-8 py-2 px-3 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">%</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Target percentage of total portfolio</p>
                            @error('target_allocation_percentage')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Investment Goals -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Investment Goals</h3>

                    <div>
                        <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Goals</label>
                        <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Select all that apply to this investment.</p>
                        <div class="mt-4 space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                            <div class="flex items-center">
                                <input id="goal_retirement" name="investment_goals[]" type="checkbox" value="retirement"
                                       {{ in_array('retirement', old('investment_goals', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                <label for="goal_retirement" class="ml-3 block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Retirement
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_growth" name="investment_goals[]" type="checkbox" value="growth"
                                       {{ in_array('growth', old('investment_goals', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                <label for="goal_growth" class="ml-3 block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Growth
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_income" name="investment_goals[]" type="checkbox" value="income"
                                       {{ in_array('income', old('investment_goals', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                <label for="goal_income" class="ml-3 block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Income
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="goal_speculation" name="investment_goals[]" type="checkbox" value="speculation"
                                       {{ in_array('speculation', old('investment_goals', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 text-[color:var(--color-accent-600)] focus:ring-[color:var(--color-accent-500)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                                <label for="goal_speculation" class="ml-3 block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Speculation
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                  placeholder="Investment thesis, research notes, or any additional comments...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Project Details (visible when type is Project) -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Project Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="project_type" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Type</label>
                            <input type="text" name="project_type" id="project_type"
                                   value="{{ old('project_type') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="e.g., SaaS, Mobile App, Marketplace">
                            @error('project_type')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="equity_percentage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Equity %</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" step="0.01" name="equity_percentage" id="equity_percentage" min="0" max="100"
                                       value="{{ old('equity_percentage') }}"
                                       class="block w-full pr-8 py-2 px-3 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">%</span>
                                </div>
                            </div>
                            @error('equity_percentage')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_website" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Website</label>
                            <input type="url" name="project_website" id="project_website"
                                   value="{{ old('project_website') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="https://example.com">
                            @error('project_website')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_repository" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Repository URL</label>
                            <input type="url" name="project_repository" id="project_repository"
                                   value="{{ old('project_repository') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="https://github.com/org/repo">
                            @error('project_repository')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_stage" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Stage</label>
                            <input type="text" name="project_stage" id="project_stage"
                                   value="{{ old('project_stage') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="idea, prototype, mvp, growth, mature">
                            @error('project_stage')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_business_model" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Business Model</label>
                            <input type="text" name="project_business_model" id="project_business_model"
                                   value="{{ old('project_business_model') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="subscription, ads, one-time, etc.">
                            @error('project_business_model')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_amount" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Amount</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" name="project_amount" id="project_amount" min="0"
                                       value="{{ old('project_amount') }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                       placeholder="0.00">
                            </div>
                            @error('project_amount')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_currency" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Currency</label>
                            <input type="text" name="project_currency" id="project_currency" maxlength="3"
                                   value="{{ old('project_currency') }}"
                                   class="mt-1 uppercase tracking-wider block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                   placeholder="USD">
                            @error('project_currency')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_start_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Start Date</label>
                            <input type="date" name="project_start_date" id="project_start_date"
                                   value="{{ old('project_start_date') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('project_start_date')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="project_end_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project End Date</label>
                            <input type="date" name="project_end_date" id="project_end_date"
                                   value="{{ old('project_end_date') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            @error('project_end_date')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="md:col-span-2">
                            <label for="project_notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Project Notes</label>
                            <textarea name="project_notes" id="project_notes" rows="4"
                                      class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                                      placeholder="Key milestones, KPIs, roadmap, team, etc.">{{ old('project_notes') }}</textarea>
                            @error('project_notes')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Tip: For projects, you can set quantity to 1 and purchase price to the total invested amount.</p>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('investments.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)]">
                            Create Investment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
