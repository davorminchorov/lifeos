@extends('layouts.app')

@section('title', 'Create Contract - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('contracts.index') }}" class="text-[color:var(--color-primary-400)] hover:text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] dark:hover:text-[color:var(--color-dark-300)] transition-colors duration-200">
                                <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L3.586 10l4.707-4.707a1 1 0 011.414 1.414L6.414 10l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Back</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <a href="{{ route('contracts.index') }}" class="text-sm font-medium text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-400)] transition-colors duration-200">Contracts</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-300)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Create Contract</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Create New Contract
            </h1>
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Add a new contract to track agreements and obligations.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('contracts.store') }}" class="space-y-8">
        @csrf

        <!-- Basic Information -->
        <x-form.section title="Basic Information" description="Enter the basic details about this contract.">
            <div class="sm:col-span-2">
                <x-form.input
                    name="title"
                    label="Contract Title"
                    type="text"
                    required
                    placeholder="e.g., Apartment Lease Agreement"
                />
            </div>

            <x-form.select
                name="contract_type"
                label="Contract Type"
                required
                placeholder="Select contract type"
            >
                <option value="lease" {{ old('contract_type') === 'lease' ? 'selected' : '' }}>Lease</option>
                <option value="employment" {{ old('contract_type') === 'employment' ? 'selected' : '' }}>Employment</option>
                <option value="service" {{ old('contract_type') === 'service' ? 'selected' : '' }}>Service</option>
                <option value="insurance" {{ old('contract_type') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                <option value="phone" {{ old('contract_type') === 'phone' ? 'selected' : '' }}>Phone</option>
                <option value="internet" {{ old('contract_type') === 'internet' ? 'selected' : '' }}>Internet</option>
                <option value="maintenance" {{ old('contract_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="other" {{ old('contract_type') === 'other' ? 'selected' : '' }}>Other</option>
            </x-form.select>

            <x-form.input
                name="counterparty"
                label="Counterparty"
                type="text"
                required
                placeholder="Company or individual name"
            />

            <x-form.input
                name="start_date"
                label="Start Date"
                type="date"
                required
            />

            <x-form.input
                name="end_date"
                label="End Date"
                type="date"
                helpText="Leave blank for open-ended contracts"
            />
        </x-form.section>

        <!-- Terms and Conditions -->
        <x-form.section title="Terms & Conditions" description="Contract terms, obligations, and conditions.">
            <x-form.input
                name="notice_period_days"
                label="Notice Period (days)"
                type="number"
                min="1"
                max="365"
                placeholder="e.g., 30"
            />

            <x-form.checkbox
                name="auto_renewal"
                label="Auto-renewal enabled"
                :checked="old('auto_renewal', false)"
            />

            <x-form.input
                name="contract_value"
                label="Contract Value"
                type="number"
                min="0"
                step="0.01"
                prefix="$"
                placeholder="0.00"
            />

            <x-form.select
                name="currency"
                label="Currency"
                required
            >
                <option value="MKD" {{ old('currency', 'MKD') === 'MKD' ? 'selected' : '' }}>MKD (ден) - Macedonian Denar</option>
                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                <option value="CHF" {{ old('currency') === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                <option value="RSD" {{ old('currency') === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                <option value="BGN" {{ old('currency') === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
            </x-form.select>

            <x-form.select
                name="payment_terms"
                label="Payment Terms"
                placeholder="Select payment terms"
            >
                <option value="Monthly" {{ old('payment_terms') === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="Quarterly" {{ old('payment_terms') === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="Semi-annually" {{ old('payment_terms') === 'Semi-annually' ? 'selected' : '' }}>Semi-annually</option>
                <option value="Annually" {{ old('payment_terms') === 'Annually' ? 'selected' : '' }}>Annually</option>
                <option value="One-time" {{ old('payment_terms') === 'One-time' ? 'selected' : '' }}>One-time</option>
                <option value="Net 30" {{ old('payment_terms') === 'Net 30' ? 'selected' : '' }}>Net 30</option>
                <option value="Due on receipt" {{ old('payment_terms') === 'Due on receipt' ? 'selected' : '' }}>Due on receipt</option>
            </x-form.select>

            <div class="md:col-span-2">
                <x-form.input
                    name="key_obligations"
                    label="Key Obligations"
                    type="textarea"
                    rows="4"
                    placeholder="List the main obligations and responsibilities for each party..."
                />
            </div>

            <x-form.input
                name="penalties"
                label="Penalties"
                type="textarea"
                rows="3"
                placeholder="Describe any penalties for breach of contract..."
            />

            <x-form.input
                name="termination_clauses"
                label="Termination Clauses"
                type="textarea"
                rows="3"
                placeholder="Specify conditions and procedures for contract termination..."
            />
        </x-form.section>

        <!-- Additional Information -->
        <x-form.section title="Additional Information" description="Optional notes and contract status information." :grid="false">
            <x-form.select
                name="performance_rating"
                label="Performance Rating"
                placeholder="Not rated"
                helpText="Rate the performance of the counterparty (optional)"
            >
                <option value="1" {{ old('performance_rating') == '1' ? 'selected' : '' }}>1 - Very Poor</option>
                <option value="2" {{ old('performance_rating') == '2' ? 'selected' : '' }}>2 - Poor</option>
                <option value="3" {{ old('performance_rating') == '3' ? 'selected' : '' }}>3 - Average</option>
                <option value="4" {{ old('performance_rating') == '4' ? 'selected' : '' }}>4 - Good</option>
                <option value="5" {{ old('performance_rating') == '5' ? 'selected' : '' }}>5 - Excellent</option>
            </x-form.select>

            <x-form.input
                name="notes"
                label="Notes"
                type="textarea"
                rows="4"
                placeholder="Add any additional notes or comments about this contract..."
            />

            <x-form.select
                name="status"
                label="Status"
                :value="old('status', 'active')"
            >
                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="expired" {{ old('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="terminated" {{ old('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
            </x-form.select>
        </x-form.section>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('contracts.index') }}"
               class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create Contract
            </button>
        </div>
    </form>
@endsection
