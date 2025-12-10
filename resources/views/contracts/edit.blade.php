@extends('layouts.app')

@section('title', 'Edit ' . $contract->title . ' - Contracts - LifeOS')

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
                            <a href="{{ route('contracts.show', $contract) }}" class="ml-4 text-sm font-medium text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-400)] transition-colors duration-200">{{ $contract->title }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-300)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Edit</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Contract
            </h1>
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update contract information and terms.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('contracts.update', $contract) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Basic Information</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Title -->
                    <div class="sm:col-span-2">
                        <x-form.input name="title" label="Contract Title" :required="true" :value="old('title', $contract->title)" placeholder="e.g., Apartment Lease Agreement" inputClass="@error('title') border-danger-400 @enderror" />
                    </div>

                    <!-- Contract Type -->
                    <div>
                        <x-form.select name="contract_type" label="Contract Type" :required="true" placeholder="Select contract type" selectClass="@error('contract_type') border-danger-400 @enderror">
                            <option value="lease" {{ old('contract_type', $contract->contract_type) === 'lease' ? 'selected' : '' }}>Lease</option>
                            <option value="employment" {{ old('contract_type', $contract->contract_type) === 'employment' ? 'selected' : '' }}>Employment</option>
                            <option value="service" {{ old('contract_type', $contract->contract_type) === 'service' ? 'selected' : '' }}>Service</option>
                            <option value="insurance" {{ old('contract_type', $contract->contract_type) === 'insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="phone" {{ old('contract_type', $contract->contract_type) === 'phone' ? 'selected' : '' }}>Phone</option>
                            <option value="internet" {{ old('contract_type', $contract->contract_type) === 'internet' ? 'selected' : '' }}>Internet</option>
                            <option value="maintenance" {{ old('contract_type', $contract->contract_type) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="other" {{ old('contract_type', $contract->contract_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </x-form.select>
                    </div>

                    <!-- Counterparty -->
                    <div>
                        <x-form.input name="counterparty" label="Counterparty" :required="true" :value="old('counterparty', $contract->counterparty)" placeholder="Company or individual name" inputClass="@error('counterparty') border-danger-400 @enderror" />
                    </div>

                    <!-- Start Date -->
                    <div>
                        <x-form.input type="date" name="start_date" label="Start Date" :required="true" :value="old('start_date', $contract->start_date?->format('Y-m-d'))" inputClass="@error('start_date') border-danger-400 @enderror" />
                    </div>

                    <!-- End Date -->
                    <div>
                        <x-form.input type="date" name="end_date" label="End Date" :value="old('end_date', $contract->end_date?->format('Y-m-d'))" helpText="Leave blank for open-ended contracts" inputClass="@error('end_date') border-danger-400 @enderror" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Terms & Conditions</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Notice Period -->
                    <div>
                        <x-form.input type="number" name="notice_period_days" label="Notice Period (days)" min="1" max="365" :value="old('notice_period_days', $contract->notice_period_days)" placeholder="e.g., 30" inputClass="@error('notice_period_days') border-danger-400 @enderror" />
                    </div>

                    <!-- Auto Renewal -->
                    <div class="flex items-center h-full">
                        <x-form.checkbox name="auto_renewal" label="Auto-renewal enabled" :checked="old('auto_renewal', $contract->auto_renewal)" />
                    </div>

                    <!-- Contract Value -->
                    <div>
                        <x-form.input type="number" name="contract_value" label="Contract Value" step="0.01" min="0" :value="old('contract_value', $contract->contract_value)" prefix="$" placeholder="0.00" inputClass="@error('contract_value') border-danger-400 @enderror" />
                    </div>

                    <!-- Currency -->
                    <div>
                        <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                            <option value="MKD" {{ old('currency', $contract->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency', $contract->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency', $contract->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency', $contract->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency', $contract->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $contract->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('currency', $contract->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('currency', $contract->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $contract->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $contract->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>
                    </div>

                    <!-- Payment Terms -->
                    <div>
                        <x-form.select name="payment_terms" label="Payment Terms" placeholder="Select payment terms" selectClass="@error('payment_terms') border-danger-400 @enderror">
                            <option value="Monthly" {{ old('payment_terms', $contract->payment_terms) === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Quarterly" {{ old('payment_terms', $contract->payment_terms) === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="Semi-annually" {{ old('payment_terms', $contract->payment_terms) === 'Semi-annually' ? 'selected' : '' }}>Semi-annually</option>
                            <option value="Annually" {{ old('payment_terms', $contract->payment_terms) === 'Annually' ? 'selected' : '' }}>Annually</option>
                            <option value="One-time" {{ old('payment_terms', $contract->payment_terms) === 'One-time' ? 'selected' : '' }}>One-time</option>
                            <option value="Net 30" {{ old('payment_terms', $contract->payment_terms) === 'Net 30' ? 'selected' : '' }}>Net 30</option>
                            <option value="Due on receipt" {{ old('payment_terms', $contract->payment_terms) === 'Due on receipt' ? 'selected' : '' }}>Due on receipt</option>
                        </x-form.select>
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <!-- Key Obligations -->
                    <div>
                        <x-form.input type="textarea" name="key_obligations" label="Key Obligations" rows="4" :value="old('key_obligations', $contract->key_obligations)" placeholder="List the main obligations and responsibilities for each party..." inputClass="@error('key_obligations') border-danger-400 @enderror" />
                    </div>

                    <!-- Penalties -->
                    <div>
                        <x-form.input type="textarea" name="penalties" label="Penalties" rows="3" :value="old('penalties', $contract->penalties)" placeholder="Describe any penalties for breach of contract..." inputClass="@error('penalties') border-danger-400 @enderror" />
                    </div>

                    <!-- Termination Clauses -->
                    <div>
                        <x-form.input type="textarea" name="termination_clauses" label="Termination Clauses" rows="3" :value="old('termination_clauses', $contract->termination_clauses)" placeholder="Specify conditions and procedures for contract termination..." inputClass="@error('termination_clauses') border-danger-400 @enderror" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                <div class="space-y-6">
                    <!-- Performance Rating -->
                    <div>
                        <x-form.select name="performance_rating" label="Performance Rating" placeholder="Not rated" selectClass="sm:max-w-xs @error('performance_rating') border-danger-400 @enderror">
                            <option value="1" {{ old('performance_rating', $contract->performance_rating) == '1' ? 'selected' : '' }}>1 - Very Poor</option>
                            <option value="2" {{ old('performance_rating', $contract->performance_rating) == '2' ? 'selected' : '' }}>2 - Poor</option>
                            <option value="3" {{ old('performance_rating', $contract->performance_rating) == '3' ? 'selected' : '' }}>3 - Average</option>
                            <option value="4" {{ old('performance_rating', $contract->performance_rating) == '4' ? 'selected' : '' }}>4 - Good</option>
                            <option value="5" {{ old('performance_rating', $contract->performance_rating) == '5' ? 'selected' : '' }}>5 - Excellent</option>
                        </x-form.select>
                        <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Rate the performance of the counterparty (optional)</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <x-form.input type="textarea" name="notes" label="Notes" rows="4" :value="old('notes', $contract->notes)" placeholder="Add any additional notes or comments about this contract..." inputClass="@error('notes') border-danger-400 @enderror" />
                    </div>

                    <!-- Status -->
                    <div>
                        <x-form.select name="status" label="Status" selectClass="sm:max-w-xs @error('status') border-danger-400 @enderror">
                            <option value="active" {{ old('status', $contract->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status', $contract->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('status', $contract->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('status', $contract->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </x-form.select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <x-button href="{{ route('contracts.show', $contract) }}" variant="secondary">Cancel</x-button>
            <x-button type="submit" variant="primary">Update Contract</x-button>
        </div>
    </form>

    <!-- Delete Contract -->
    <div class="mt-10 pt-8 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-[color:var(--color-danger-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-300)]">Delete Contract</h3>
                    <div class="mt-2 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">
                        <p>Once you delete a contract, there is no going back. Please be certain.</p>
                    </div>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('Are you sure you want to delete this contract? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger">Delete Contract</x-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
