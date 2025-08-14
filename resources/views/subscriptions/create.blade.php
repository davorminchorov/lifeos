@extends('layouts.app')

@section('title', 'Add New Subscription - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Add New Subscription
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track a new recurring subscription service
            </p>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="bg-[color:var(--color-primary-600)] hover:bg-[color:var(--color-primary-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
            Back to List
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('subscriptions.store') }}" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <x-form.section title="Basic Information" description="Enter the basic details about this subscription.">
                <x-form.input
                    name="service_name"
                    label="Service Name"
                    type="text"
                    required
                    placeholder="e.g., Netflix, Spotify"
                />

                <x-form.select
                    name="category"
                    label="Category"
                    required
                    placeholder="Select Category"
                >
                    <option value="Entertainment" {{ old('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                    <option value="Software" {{ old('category') === 'Software' ? 'selected' : '' }}>Software</option>
                    <option value="Fitness" {{ old('category') === 'Fitness' ? 'selected' : '' }}>Fitness</option>
                    <option value="Storage" {{ old('category') === 'Storage' ? 'selected' : '' }}>Storage</option>
                    <option value="Productivity" {{ old('category') === 'Productivity' ? 'selected' : '' }}>Productivity</option>
                    <option value="Development" {{ old('category') === 'Development' ? 'selected' : '' }}>Development</option>
                    <option value="Health" {{ old('category') === 'Health' ? 'selected' : '' }}>Health</option>
                    <option value="Communication" {{ old('category') === 'Communication' ? 'selected' : '' }}>Communication</option>
                </x-form.select>

                <div class="md:col-span-2">
                    <x-form.input
                        name="description"
                        label="Description"
                        type="textarea"
                        rows="3"
                        placeholder="Optional description of the service"
                    />
                </div>
            </x-form.section>

            <!-- Billing Information -->
            <x-form.section title="Billing Information" description="Set up the cost and billing schedule.">
                <x-form.input
                    name="cost"
                    label="Cost"
                    type="number"
                    required
                    step="0.01"
                    min="0"
                    prefix="$"
                    placeholder="0.00"
                />

                <x-form.select
                    name="currency"
                    label="Currency"
                    required
                >
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                    <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                    <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                    <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                </x-form.select>

                <x-form.select
                    name="billing_cycle"
                    label="Billing Cycle"
                    required
                    placeholder="Select Billing Cycle"
                    onchange="toggleCustomDays(this)"
                >
                    <option value="weekly" {{ old('billing_cycle') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="custom" {{ old('billing_cycle') === 'custom' ? 'selected' : '' }}>Custom</option>
                </x-form.select>

                <!-- Custom Billing Days -->
                <div id="custom_days_field" style="display: {{ old('billing_cycle') === 'custom' ? 'block' : 'none' }};">
                    <x-form.input
                        name="billing_cycle_days"
                        label="Custom Days"
                        type="number"
                        min="1"
                        max="365"
                        helpText="Number of days between billing"
                    />
                </div>
            </x-form.section>

            <!-- Dates -->
            <x-form.section title="Important Dates" description="Set the start and billing dates.">
                <x-form.input
                    name="start_date"
                    label="Start Date"
                    type="date"
                    required
                />

                <x-form.input
                    name="next_billing_date"
                    label="Next Billing Date"
                    type="date"
                    required
                />
            </x-form.section>

            <!-- Payment Information -->
            <x-form.section title="Payment Information" description="Optional payment and merchant details.">
                <x-form.select
                    name="payment_method"
                    label="Payment Method"
                    placeholder="Select Payment Method"
                >
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
                    type="text"
                    placeholder="e.g., Apple, Google, Netflix"
                />

                <x-form.checkbox
                    name="auto_renewal"
                    label="Auto-renewal enabled"
                    :checked="old('auto_renewal', false)"
                />

                <x-form.select
                    name="cancellation_difficulty"
                    label="Cancellation Difficulty"
                    placeholder="Not Rated"
                >
                    <option value="1" {{ old('cancellation_difficulty') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                    <option value="2" {{ old('cancellation_difficulty') == '2' ? 'selected' : '' }}>2 - Easy</option>
                    <option value="3" {{ old('cancellation_difficulty') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                    <option value="4" {{ old('cancellation_difficulty') == '4' ? 'selected' : '' }}>4 - Hard</option>
                    <option value="5" {{ old('cancellation_difficulty') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                </x-form.select>
            </x-form.section>

            <!-- Additional Information -->
            <x-form.section title="Additional Information" description="Optional notes and tags for organization." :grid="false">
                <x-form.input
                    name="tags"
                    label="Tags"
                    type="text"
                    placeholder="essential, work, family (separated by commas)"
                    helpText="Enter tags separated by commas"
                />

                <x-form.input
                    name="notes"
                    label="Notes"
                    type="textarea"
                    rows="4"
                    placeholder="Additional notes about this subscription..."
                />
            </x-form.section>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('subscriptions.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Create Subscription
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
