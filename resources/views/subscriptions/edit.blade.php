@extends('layouts.app')

@section('title', 'Edit Subscription - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Subscription
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Update {{ $subscription->service_name }} subscription details
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('subscriptions.show', $subscription) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                View Details
            </a>
            <a href="{{ route('subscriptions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Basic Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update the basic details about this subscription.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Service Name -->
                        <div>
                            <label for="service_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Name *</label>
                            <input type="text" name="service_name" id="service_name" value="{{ old('service_name', $subscription->service_name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('service_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                            <select name="category" id="category" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Category</option>
                                <option value="Entertainment" {{ old('category', $subscription->category) === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                                <option value="Software" {{ old('category', $subscription->category) === 'Software' ? 'selected' : '' }}>Software</option>
                                <option value="Fitness" {{ old('category', $subscription->category) === 'Fitness' ? 'selected' : '' }}>Fitness</option>
                                <option value="Storage" {{ old('category', $subscription->category) === 'Storage' ? 'selected' : '' }}>Storage</option>
                                <option value="Productivity" {{ old('category', $subscription->category) === 'Productivity' ? 'selected' : '' }}>Productivity</option>
                                <option value="Development" {{ old('category', $subscription->category) === 'Development' ? 'selected' : '' }}>Development</option>
                                <option value="Health" {{ old('category', $subscription->category) === 'Health' ? 'selected' : '' }}>Health</option>
                                <option value="Communication" {{ old('category', $subscription->category) === 'Communication' ? 'selected' : '' }}>Communication</option>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $subscription->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status *</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status', $subscription->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Billing Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update the cost and billing schedule.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cost -->
                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">{{ $subscription->currency }}</span>
                                </div>
                                <input type="number" name="cost" id="cost" step="0.01" min="0" value="{{ old('cost', $subscription->cost) }}" required
                                       class="pl-12 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            @error('cost')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency *</label>
                            <select name="currency" id="currency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="MKD" {{ old('currency', $subscription->currency) === 'MKD' ? 'selected' : '' }}>MKD (ден) - Macedonian Denar</option>
                                <option value="USD" {{ old('currency', $subscription->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ old('currency', $subscription->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ old('currency', $subscription->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="CAD" {{ old('currency', $subscription->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency', $subscription->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                                <option value="CHF" {{ old('currency', $subscription->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                                <option value="RSD" {{ old('currency', $subscription->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                                <option value="BGN" {{ old('currency', $subscription->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Cycle *</label>
                            <select name="billing_cycle" id="billing_cycle" required onchange="toggleCustomDays(this)"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Billing Cycle</option>
                                <option value="weekly" {{ old('billing_cycle', $subscription->billing_cycle) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('billing_cycle', $subscription->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('billing_cycle', $subscription->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('billing_cycle', $subscription->billing_cycle) === 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('billing_cycle')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Billing Days -->
                        <div id="custom_days_field" style="display: {{ old('billing_cycle', $subscription->billing_cycle) === 'custom' ? 'block' : 'none' }};">
                            <label for="billing_cycle_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Days</label>
                            <input type="number" name="billing_cycle_days" id="billing_cycle_days" min="1" max="365" value="{{ old('billing_cycle_days', $subscription->billing_cycle_days) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Number of days between billing</p>
                            @error('billing_cycle_days')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Important Dates
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update the start and billing dates.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $subscription->start_date?->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Next Billing Date -->
                        <div>
                            <label for="next_billing_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Next Billing Date *</label>
                            <input type="date" name="next_billing_date" id="next_billing_date" value="{{ old('next_billing_date', $subscription->next_billing_date?->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('next_billing_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cancellation Date -->
                        <div>
                            <label for="cancellation_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cancellation Date</label>
                            <input type="date" name="cancellation_date" id="cancellation_date" value="{{ old('cancellation_date', $subscription->cancellation_date?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only if cancelled</p>
                            @error('cancellation_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Payment Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update payment and merchant details.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <select name="payment_method" id="payment_method"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Payment Method</option>
                                <option value="Credit Card" {{ old('payment_method', $subscription->payment_method) === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="Debit Card" {{ old('payment_method', $subscription->payment_method) === 'Debit Card' ? 'selected' : '' }}>Debit Card</option>
                                <option value="PayPal" {{ old('payment_method', $subscription->payment_method) === 'PayPal' ? 'selected' : '' }}>PayPal</option>
                                <option value="Bank Transfer" {{ old('payment_method', $subscription->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Apple Pay" {{ old('payment_method', $subscription->payment_method) === 'Apple Pay' ? 'selected' : '' }}>Apple Pay</option>
                                <option value="Google Pay" {{ old('payment_method', $subscription->payment_method) === 'Google Pay' ? 'selected' : '' }}>Google Pay</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Merchant Info -->
                        <div>
                            <label for="merchant_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Merchant/Company</label>
                            <input type="text" name="merchant_info" id="merchant_info" value="{{ old('merchant_info', $subscription->merchant_info) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('merchant_info')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Auto Renewal -->
                        <div class="flex items-center">
                            <input type="hidden" name="auto_renewal" value="0">
                            <input type="checkbox" name="auto_renewal" id="auto_renewal" value="1" {{ old('auto_renewal', $subscription->auto_renewal) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <label for="auto_renewal" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Auto-renewal enabled
                            </label>
                        </div>

                        <!-- Cancellation Difficulty -->
                        <div>
                            <label for="cancellation_difficulty" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cancellation Difficulty</label>
                            <select name="cancellation_difficulty" id="cancellation_difficulty"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Not Rated</option>
                                <option value="1" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                                <option value="2" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '2' ? 'selected' : '' }}>2 - Easy</option>
                                <option value="3" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '3' ? 'selected' : '' }}>3 - Moderate</option>
                                <option value="4" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '4' ? 'selected' : '' }}>4 - Hard</option>
                                <option value="5" {{ old('cancellation_difficulty', $subscription->cancellation_difficulty) == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                            </select>
                            @error('cancellation_difficulty')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Additional Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update notes and tags for organization.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="space-y-6">
                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                            @php
                                $currentTags = old('tags', is_array($subscription->tags) ? implode(', ', $subscription->tags) : '');
                            @endphp
                            <input type="text" name="tags" id="tags" value="{{ $currentTags }}" placeholder="essential, work, family (separated by commas)"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter tags separated by commas</p>
                            @error('tags')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" id="notes" rows="4" placeholder="Additional notes about this subscription..."
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $subscription->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('subscriptions.show', $subscription) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Update Subscription
                </button>
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

        // Parse tags from comma-separated string on form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const tagsInput = document.getElementById('tags');

            form.addEventListener('submit', function(e) {
                if (tagsInput.value) {
                    // Convert comma-separated tags to array format that Laravel expects
                    const tags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);

                    // Remove the original tags input
                    tagsInput.remove();

                    // Add hidden inputs for each tag
                    tags.forEach((tag, index) => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `tags[${index}]`;
                        hiddenInput.value = tag;
                        form.appendChild(hiddenInput);
                    });
                }
            });
        });
    </script>
@endsection
