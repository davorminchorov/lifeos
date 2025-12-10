@extends('layouts.app')

@section('title', 'Edit Subscription - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Subscription
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update {{ $subscription->service_name }} subscription details
            </p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('subscriptions.show', $subscription) }}" variant="secondary">View Details</x-button>
            <x-button href="{{ route('subscriptions.index') }}" variant="secondary">Back to List</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Basic Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Update the basic details about this subscription.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Service Name -->
                        <div>
                            <x-form.input name="service_name" label="Service Name" :required="true" :value="old('service_name', $subscription->service_name)" inputClass="@error('service_name') border-danger-400 @enderror" />
                        </div>

                        <!-- Category -->
                        <div>
                            <x-form.select name="category" label="Category" :required="true" placeholder="Select Category" selectClass="@error('category') border-danger-400 @enderror">
                                <option value="Entertainment" {{ old('category', $subscription->category) === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                                <option value="Software" {{ old('category', $subscription->category) === 'Software' ? 'selected' : '' }}>Software</option>
                                <option value="Fitness" {{ old('category', $subscription->category) === 'Fitness' ? 'selected' : '' }}>Fitness</option>
                                <option value="Storage" {{ old('category', $subscription->category) === 'Storage' ? 'selected' : '' }}>Storage</option>
                                <option value="Productivity" {{ old('category', $subscription->category) === 'Productivity' ? 'selected' : '' }}>Productivity</option>
                                <option value="Development" {{ old('category', $subscription->category) === 'Development' ? 'selected' : '' }}>Development</option>
                                <option value="Health" {{ old('category', $subscription->category) === 'Health' ? 'selected' : '' }}>Health</option>
                                <option value="Communication" {{ old('category', $subscription->category) === 'Communication' ? 'selected' : '' }}>Communication</option>
                            </x-form.select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <x-form.input type="textarea" name="description" label="Description" rows="3" :value="old('description', $subscription->description)" inputClass="@error('description') border-danger-400 @enderror" />
                        </div>

                        <!-- Status -->
                        <div>
                            <x-form.select name="status" label="Status" :required="true" selectClass="@error('status') border-danger-400 @enderror">
                                <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status', $subscription->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </x-form.select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Billing Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Update the cost and billing schedule.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cost -->
                        <x-form.input
                            type="number"
                            name="cost"
                            label="Cost"
                            prefix="$"
                            step="0.01"
                            min="0"
                            :required="true"
                            :value="old('cost', $subscription->cost)"
                            inputClass="@error('cost') border-danger-400 @enderror"
                        />

                        <!-- Currency -->
                        <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                            <option value="MKD" {{ old('currency', $subscription->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency', $subscription->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency', $subscription->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency', $subscription->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency', $subscription->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $subscription->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="CHF" {{ old('currency', $subscription->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $subscription->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $subscription->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>

                        <!-- Billing Cycle -->
                        <x-form.select name="billing_cycle" label="Billing Cycle" :required="true" onchange="toggleCustomDays(this)" selectClass="@error('billing_cycle') border-danger-400 @enderror">
                            <option value="">Select Billing Cycle</option>
                            <option value="weekly" {{ old('billing_cycle', $subscription->billing_cycle) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('billing_cycle', $subscription->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ old('billing_cycle', $subscription->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="custom" {{ old('billing_cycle', $subscription->billing_cycle) === 'custom' ? 'selected' : '' }}>Custom</option>
                        </x-form.select>

                        <!-- Custom Billing Days -->
                        <div id="custom_days_field" style="display: {{ old('billing_cycle', $subscription->billing_cycle) === 'custom' ? 'block' : 'none' }};">
                            <x-form.input
                                type="number"
                                name="billing_cycle_days"
                                id="billing_cycle_days"
                                label="Custom Days"
                                min="1"
                                max="365"
                                :value="old('billing_cycle_days', $subscription->billing_cycle_days)"
                                helpText="Number of days between billing"
                                inputClass="@error('billing_cycle_days') border-danger-400 @enderror"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Important Dates
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Update the start and billing dates.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Start Date -->
                        <x-form.input type="date" name="start_date" label="Start Date" :required="true" :value="old('start_date', $subscription->start_date?->format('Y-m-d'))" inputClass="@error('start_date') border-danger-400 @enderror" />

                        <!-- Next Billing Date -->
                        <x-form.input type="date" name="next_billing_date" label="Next Billing Date" :required="true" :value="old('next_billing_date', $subscription->next_billing_date?->format('Y-m-d'))" inputClass="@error('next_billing_date') border-danger-400 @enderror" />

                        <!-- Cancellation Date -->
                        <x-form.input type="date" name="cancellation_date" label="Cancellation Date" :value="old('cancellation_date', $subscription->cancellation_date?->format('Y-m-d'))" helpText="Only if cancelled" inputClass="@error('cancellation_date') border-danger-400 @enderror" />
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Payment Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Update payment and merchant details.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payment Method -->
                        <x-form.select name="payment_method" label="Payment Method" selectClass="@error('payment_method') border-danger-400 @enderror">
                            <option value="">Select Payment Method</option>
                            <option value="Credit Card" {{ old('payment_method', $subscription->payment_method) === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="Debit Card" {{ old('payment_method', $subscription->payment_method) === 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="PayPal" {{ old('payment_method', $subscription->payment_method) === 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <option value="Bank Transfer" {{ old('payment_method', $subscription->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Apple Pay" {{ old('payment_method', $subscription->payment_method) === 'Apple Pay' ? 'selected' : '' }}>Apple Pay</option>
                            <option value="Google Pay" {{ old('payment_method', $subscription->payment_method) === 'Google Pay' ? 'selected' : '' }}>Google Pay</option>
                        </x-form.select>

                        <!-- Merchant Info -->
                        <x-form.input name="merchant_info" label="Merchant/Company" :value="old('merchant_info', $subscription->merchant_info)" inputClass="@error('merchant_info') border-danger-400 @enderror" />

                        <!-- Auto Renewal -->
                        <x-form.checkbox name="auto_renewal" label="Auto-renewal enabled" :checked="old('auto_renewal', $subscription->auto_renewal)" />

                        <!-- Cancellation Difficulty -->
                        <x-form.select name="cancellation_difficulty" label="Cancellation Difficulty" selectClass="@error('cancellation_difficulty') border-danger-400 @enderror">
                            <option value="">Not Rated</option>
                            <option value="1" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                            <option value="2" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '2' ? 'selected' : '' }}>2 - Easy</option>
                            <option value="3" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '3' ? 'selected' : '' }}>3 - Moderate</option>
                            <option value="4" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '4' ? 'selected' : '' }}>4 - Hard</option>
                            <option value="5" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                        </x-form.select>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Additional Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)]">
                        Update notes and tags for organization.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="space-y-6">
                        <!-- Tags -->
                        @php
                            $currentTags = old('tags', is_array($subscription->tags) ? implode(', ', $subscription->tags) : '');
                        @endphp
                        <x-form.input name="tags" id="tags" label="Tags" :value="$currentTags" placeholder="essential, work, family (separated by commas)" helpText="Enter tags separated by commas" inputClass="@error('tags') border-danger-400 @enderror" />

                        <!-- Notes -->
                        <x-form.input type="textarea" name="notes" label="Notes" rows="4" :value="old('notes', $subscription->notes)" placeholder="Additional notes about this subscription..." inputClass="@error('notes') border-danger-400 @enderror" />
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <x-button href="{{ route('subscriptions.show', $subscription) }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Subscription</x-button>
            </div>
        </form>
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

        // Note: Tags are submitted as a comma-separated string. The server can split them as needed.
    </script>
@endsection
