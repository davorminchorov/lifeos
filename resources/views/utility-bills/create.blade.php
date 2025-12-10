@extends('layouts.app')

@section('title', 'Add Utility Bill - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add Utility Bill</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Add a new utility bill to track your usage and payments</p>
            </div>
            <x-button href="{{ route('utility-bills.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Bills
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form action="{{ route('utility-bills.store') }}" method="POST" class="space-y-6 p-6">
                @csrf

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.select name="utility_type" label="Utility Type" :required="true" placeholder="Select utility type">
                        <option value="electricity" {{ old('utility_type') == 'electricity' ? 'selected' : '' }}>Electricity</option>
                        <option value="gas" {{ old('utility_type') == 'gas' ? 'selected' : '' }}>Gas</option>
                        <option value="water" {{ old('utility_type') == 'water' ? 'selected' : '' }}>Water</option>
                        <option value="internet" {{ old('utility_type') == 'internet' ? 'selected' : '' }}>Internet</option>
                        <option value="cable_tv" {{ old('utility_type') == 'cable_tv' ? 'selected' : '' }}>Cable TV</option>
                        <option value="phone" {{ old('utility_type') == 'phone' ? 'selected' : '' }}>Phone</option>
                        <option value="trash" {{ old('utility_type') == 'trash' ? 'selected' : '' }}>Trash Collection</option>
                        <option value="sewer" {{ old('utility_type') == 'sewer' ? 'selected' : '' }}>Sewer</option>
                        <option value="other" {{ old('utility_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </x-form.select>

                    <x-form.input
                        name="service_provider"
                        label="Service Provider"
                        :required="true"
                        placeholder="e.g., Pacific Gas & Electric"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input
                        name="account_number"
                        label="Account Number"
                        placeholder="Account number"
                    />

                    <x-form.select name="payment_status" label="Payment Status" :required="true">
                        <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ old('payment_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </x-form.select>
                </div>

                <x-form.input type="textarea" name="service_address" label="Service Address" rows="3" placeholder="Address where the service is provided" />

                <!-- Bill Details -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Bill Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.input
                            type="number"
                            name="bill_amount"
                            label="Bill Amount"
                            prefix="$"
                            step="0.01"
                            :required="true"
                            placeholder="0.00"
                        />

                        <x-form.select name="currency" label="Currency" :required="true">
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

                        <x-form.input
                            type="number"
                            name="usage_amount"
                            label="Usage Amount"
                            step="0.0001"
                            placeholder="0.0000"
                        />

                        <x-form.select name="usage_unit" label="Usage Unit" placeholder="Select unit">
                            <option value="kWh" {{ old('usage_unit') == 'kWh' ? 'selected' : '' }}>kWh (Kilowatt hours)</option>
                            <option value="therms" {{ old('usage_unit') == 'therms' ? 'selected' : '' }}>Therms</option>
                            <option value="gallons" {{ old('usage_unit') == 'gallons' ? 'selected' : '' }}>Gallons</option>
                            <option value="GB" {{ old('usage_unit') == 'GB' ? 'selected' : '' }}>GB (Gigabytes)</option>
                            <option value="minutes" {{ old('usage_unit') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                            <option value="other" {{ old('usage_unit') == 'other' ? 'selected' : '' }}>Other</option>
                        </x-form.select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <x-form.input
                            type="number"
                            name="rate_per_unit"
                            label="Rate per Unit"
                            prefix="$"
                            step="0.000001"
                            placeholder="0.000000"
                        />

                        <x-form.input
                            type="number"
                            name="budget_alert_threshold"
                            label="Budget Alert Threshold"
                            prefix="$"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <!-- Billing Period -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Billing Period</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.input type="date" name="bill_period_start" label="Period Start" :required="true" />
                        <x-form.input type="date" name="bill_period_end" label="Period End" :required="true" />
                        <x-form.input type="date" name="due_date" label="Due Date" :required="true" />
                    </div>

                    <div class="mt-4">
                        <x-form.input type="date" name="payment_date" label="Payment Date" containerClass="md:w-1/3" />
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input name="service_plan" label="Service Plan" placeholder="e.g., Residential Standard" />
                        <x-form.checkbox name="auto_pay_enabled" label="Auto-pay enabled" :checked="old('auto_pay_enabled', false)" />
                    </div>

                    <div class="mt-4">
                        <x-form.input type="textarea" name="contract_terms" label="Contract Terms" rows="3" placeholder="Contract details, terms, or special conditions" />
                    </div>

                    <div class="mt-4">
                        <x-form.input type="textarea" name="notes" label="Notes" rows="3" placeholder="Additional notes or comments" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <x-button href="{{ route('utility-bills.index') }}" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Add Utility Bill</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
