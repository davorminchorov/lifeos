@extends('layouts.app')

@section('title', 'Create Contract - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Create New Contract</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Add a new contract to track agreements and obligations</p>
            </div>
            <x-button href="{{ route('contracts.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Contracts
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('contracts.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-form.input
                            name="title"
                            label="Contract Title"
                            :required="true"
                            placeholder="e.g., Apartment Lease Agreement"
                            inputClass="@error('title') border-danger-400 @enderror"
                        />
                    </div>

                    <div>
                        <x-form.select name="contract_type" label="Contract Type" :required="true" placeholder="Select contract type" selectClass="@error('contract_type') border-danger-400 @enderror">
                            <option value="lease" {{ old('contract_type') === 'lease' ? 'selected' : '' }}>Lease</option>
                            <option value="employment" {{ old('contract_type') === 'employment' ? 'selected' : '' }}>Employment</option>
                            <option value="service" {{ old('contract_type') === 'service' ? 'selected' : '' }}>Service</option>
                            <option value="insurance" {{ old('contract_type') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="phone" {{ old('contract_type') === 'phone' ? 'selected' : '' }}>Phone</option>
                            <option value="internet" {{ old('contract_type') === 'internet' ? 'selected' : '' }}>Internet</option>
                            <option value="maintenance" {{ old('contract_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="other" {{ old('contract_type') === 'other' ? 'selected' : '' }}>Other</option>
                        </x-form.select>
                    </div>

                    <div>
                        <x-form.input
                            name="counterparty"
                            label="Counterparty"
                            :required="true"
                            placeholder="Company or individual name"
                            inputClass="@error('counterparty') border-danger-400 @enderror"
                        />
                    </div>

                    <div>
                        <x-form.input
                            type="date"
                            name="start_date"
                            label="Start Date"
                            :required="true"
                            inputClass="@error('start_date') border-danger-400 @enderror"
                        />
                    </div>

                    <div>
                        <x-form.input
                            type="date"
                            name="end_date"
                            label="End Date"
                            helpText="Leave blank for open-ended contracts"
                            inputClass="@error('end_date') border-danger-400 @enderror"
                        />
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Terms & Conditions</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-form.input
                                type="number"
                                name="notice_period_days"
                                label="Notice Period (days)"
                                min="1"
                                max="365"
                                placeholder="e.g., 30"
                                inputClass="@error('notice_period_days') border-danger-400 @enderror"
                            />
                        </div>

                        <div>
                            <x-form.input
                                type="number"
                                name="contract_value"
                                label="Contract Value"
                                step="0.01"
                                min="0"
                                prefix="$"
                                placeholder="0.00"
                                inputClass="@error('contract_value') border-danger-400 @enderror"
                            />
                        </div>

                        <div>
                            <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                                <option value="MKD" {{ old('currency', 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
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
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <x-form.select name="payment_terms" label="Payment Terms" placeholder="Select payment terms" selectClass="@error('payment_terms') border-danger-400 @enderror">
                                <option value="Monthly" {{ old('payment_terms') === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Quarterly" {{ old('payment_terms') === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="Semi-annually" {{ old('payment_terms') === 'Semi-annually' ? 'selected' : '' }}>Semi-annually</option>
                                <option value="Annually" {{ old('payment_terms') === 'Annually' ? 'selected' : '' }}>Annually</option>
                                <option value="One-time" {{ old('payment_terms') === 'One-time' ? 'selected' : '' }}>One-time</option>
                                <option value="Net 30" {{ old('payment_terms') === 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Due on receipt" {{ old('payment_terms') === 'Due on receipt' ? 'selected' : '' }}>Due on receipt</option>
                            </x-form.select>
                        </div>

                        <div class="flex items-center">
                            <x-form.checkbox name="auto_renewal" label="Auto-renewal enabled" :checked="old('auto_renewal', false)" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-form.input
                            type="textarea"
                            name="key_obligations"
                            label="Key Obligations"
                            rows="4"
                            placeholder="List the main obligations and responsibilities for each party..."
                            inputClass="@error('key_obligations') border-danger-400 @enderror"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <x-form.input
                                type="textarea"
                                name="penalties"
                                label="Penalties"
                                rows="3"
                                placeholder="Describe any penalties for breach of contract..."
                                inputClass="@error('penalties') border-danger-400 @enderror"
                            />
                        </div>

                        <div>
                            <x-form.input
                                type="textarea"
                                name="termination_clauses"
                                label="Termination Clauses"
                                rows="3"
                                placeholder="Specify conditions and procedures for contract termination..."
                                inputClass="@error('termination_clauses') border-danger-400 @enderror"
                            />
                        </div>
                </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.select name="performance_rating" label="Performance Rating" placeholder="Not rated" selectClass="@error('performance_rating') border-danger-400 @enderror">
                                <option value="1" {{ old('performance_rating') == '1' ? 'selected' : '' }}>1 - Very Poor</option>
                                <option value="2" {{ old('performance_rating') == '2' ? 'selected' : '' }}>2 - Poor</option>
                                <option value="3" {{ old('performance_rating') == '3' ? 'selected' : '' }}>3 - Average</option>
                                <option value="4" {{ old('performance_rating') == '4' ? 'selected' : '' }}>4 - Good</option>
                                <option value="5" {{ old('performance_rating') == '5' ? 'selected' : '' }}>5 - Excellent</option>
                            </x-form.select>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">Rate the performance of the counterparty (optional)</p>
                        </div>

                        <div>
                            <x-form.select name="status" label="Status" selectClass="@error('status') border-danger-400 @enderror">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="expired" {{ old('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="terminated" {{ old('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </x-form.select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-form.input
                            type="textarea"
                            name="notes"
                            label="Notes"
                            rows="4"
                            placeholder="Add any additional notes or comments about this contract..."
                            inputClass="@error('notes') border-danger-400 @enderror"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <x-button href="{{ route('contracts.index') }}" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Create Contract</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
