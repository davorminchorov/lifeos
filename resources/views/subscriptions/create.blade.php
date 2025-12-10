@extends('layouts.app')

@section('title', 'Add New Subscription - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add New Subscription</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Track a new recurring subscription service</p>
            </div>
            <x-button href="{{ route('subscriptions.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to List
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('subscriptions.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input
                        name="service_name"
                        label="Service Name"
                        :required="true"
                        placeholder="e.g., Netflix, Spotify"
                        inputClass="@error('service_name') border-danger-400 @enderror"
                    />

                    <x-form.select name="category" label="Category" :required="true" placeholder="Select Category" selectClass="@error('category') border-danger-400 @enderror">
                        <option value="Entertainment" {{ old('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                        <option value="Software" {{ old('category') === 'Software' ? 'selected' : '' }}>Software</option>
                        <option value="Fitness" {{ old('category') === 'Fitness' ? 'selected' : '' }}>Fitness</option>
                        <option value="Storage" {{ old('category') === 'Storage' ? 'selected' : '' }}>Storage</option>
                        <option value="Productivity" {{ old('category') === 'Productivity' ? 'selected' : '' }}>Productivity</option>
                        <option value="Development" {{ old('category') === 'Development' ? 'selected' : '' }}>Development</option>
                        <option value="Health" {{ old('category') === 'Health' ? 'selected' : '' }}>Health</option>
                        <option value="Communication" {{ old('category') === 'Communication' ? 'selected' : '' }}>Communication</option>
                    </x-form.select>
                </div>

                <x-form.input
                    type="textarea"
                    name="description"
                    label="Description"
                    rows="3"
                    placeholder="Optional description of the service"
                    inputClass="@error('description') border-danger-400 @enderror"
                />

                <!-- Billing Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Billing Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-form.input
                            type="number"
                            name="cost"
                            label="Cost"
                            :required="true"
                            step="0.01"
                            min="0"
                            prefix="$"
                            placeholder="0.00"
                            inputClass="@error('cost') border-danger-400 @enderror"
                        />

                        <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                            <option value="MKD" {{ old('currency', 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="CHF" {{ old('currency') === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency') === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency') === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>

                        <x-form.select name="billing_cycle" label="Billing Cycle" :required="true" placeholder="Select Billing Cycle" onchange="toggleCustomDays(this)" selectClass="@error('billing_cycle') border-danger-400 @enderror">
                            <option value="weekly" {{ old('billing_cycle') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('billing_cycle') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </x-form.select>
                    </div>

                    <!-- Custom Billing Days -->
                    <div id="custom_days_field" class="mt-4" style="display: {{ old('billing_cycle') === 'custom' ? 'block' : 'none' }};">
                        <x-form.input
                            type="number"
                            name="billing_cycle_days"
                            id="billing_cycle_days"
                            label="Custom Days"
                            min="1"
                            max="365"
                            placeholder="30"
                            helpText="Number of days between billing"
                            inputClass="md:w-1/3 @error('billing_cycle_days') border-danger-400 @enderror"
                        />
                    </div>
                </div>

                <!-- Important Dates -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Important Dates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input type="date" name="start_date" label="Start Date" :required="true" inputClass="@error('start_date') border-danger-400 @enderror" />
                        <x-form.input type="date" name="next_billing_date" label="Next Billing Date" :required="true" inputClass="@error('next_billing_date') border-danger-400 @enderror" />
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Payment Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.select name="payment_method" label="Payment Method" placeholder="Select Payment Method" selectClass="@error('payment_method') border-danger-400 @enderror">
                            <option value="Credit Card" {{ old('payment_method') === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="Debit Card" {{ old('payment_method') === 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="PayPal" {{ old('payment_method') === 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Apple Pay" {{ old('payment_method') === 'Apple Pay' ? 'selected' : '' }}>Apple Pay</option>
                            <option value="Google Pay" {{ old('payment_method') === 'Google Pay' ? 'selected' : '' }}>Google Pay</option>
                        </x-form.select>

                        <x-form.input
                            name="merchant_info"
                            label="Merchant/Company"
                            placeholder="e.g., Apple, Google, Netflix"
                            inputClass="@error('merchant_info') border-danger-400 @enderror"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <x-form.checkbox name="auto_renewal" label="Auto-renewal enabled" :checked="old('auto_renewal', false)" />

                        <x-form.select name="cancellation_difficulty" label="Cancellation Difficulty" selectClass="@error('cancellation_difficulty') border-danger-400 @enderror">
                            <option value="">Not Rated</option>
                            <option value="1" {{ old('cancellation_difficulty') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                            <option value="2" {{ old('cancellation_difficulty') == '2' ? 'selected' : '' }}>2 - Easy</option>
                            <option value="3" {{ old('cancellation_difficulty') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                            <option value="4" {{ old('cancellation_difficulty') == '4' ? 'selected' : '' }}>4 - Hard</option>
                            <option value="5" {{ old('cancellation_difficulty') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                        </x-form.select>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input
                            name="tags"
                            label="Tags"
                            placeholder="essential, work, family (separated by commas)"
                            helpText="Enter tags separated by commas"
                            inputClass="@error('tags') border-danger-400 @enderror"
                        />

                        <x-form.input
                            type="textarea"
                            name="notes"
                            label="Notes"
                            rows="4"
                            placeholder="Additional notes about this subscription..."
                            inputClass="@error('notes') border-danger-400 @enderror"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <x-button href="{{ route('subscriptions.index') }}" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Create Subscription</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Custom Days Field -->
<script>
    function toggleCustomDays(select) {
        const customField = document.getElementById('custom_days_field');
        const customInput = document.getElementById('billing_cycle_days');

        if (select.value === 'custom') {
            customField.style.display = 'block';
            customInput.required = true;
        } else {
            customField.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }
</script>
@endsection
