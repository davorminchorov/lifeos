@extends('layouts.app')

@section('title', 'Create Recurring Invoice')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Create Recurring Invoice
                </h1>
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                    Set up automated billing for subscription services
                </p>
            </div>
            <x-button href="{{ route('invoicing.recurring-invoices.index') }}" variant="secondary">
                Cancel
            </x-button>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('invoicing.recurring-invoices.store') }}">
            @csrf

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6 mb-6">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-form.select name="customer_id" label="Customer" :required="true">
                            <option value="">Select customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $selectedCustomerId) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div class="md:col-span-2">
                        <x-form.input
                            name="name"
                            label="Name"
                            :required="true"
                            placeholder="e.g., Monthly Web Hosting"
                            :value="old('name')"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <x-form.input
                            type="textarea"
                            name="description"
                            label="Description"
                            placeholder="Describe what this recurring invoice is for..."
                            rows="3"
                            :value="old('description')"
                        />
                    </div>
                </div>
            </div>

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6 mb-6">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                    Billing Schedule
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.select name="billing_interval" label="Billing Interval" :required="true">
                        <option value="">Select interval</option>
                        <option value="daily" {{ old('billing_interval') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('billing_interval') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('billing_interval') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('billing_interval') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('billing_interval') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </x-form.select>

                    <x-form.input
                        type="number"
                        name="interval_count"
                        label="Interval Count"
                        :required="true"
                        min="1"
                        max="12"
                        :value="old('interval_count', 1)"
                        helpText="e.g., 2 for every 2 months"
                    />

                    <x-form.input
                        type="date"
                        name="start_date"
                        label="Start Date"
                        :required="true"
                        :value="old('start_date', date('Y-m-d'))"
                    />

                    <x-form.input
                        type="date"
                        name="end_date"
                        label="End Date (Optional)"
                        :value="old('end_date')"
                        helpText="Leave blank for ongoing"
                    />

                    <x-form.input
                        type="number"
                        name="billing_day_of_month"
                        label="Day of Month (Optional)"
                        min="1"
                        max="31"
                        :value="old('billing_day_of_month')"
                        helpText="For monthly/yearly billing"
                    />

                    <x-form.input
                        type="number"
                        name="occurrences_limit"
                        label="Occurrence Limit (Optional)"
                        min="1"
                        :value="old('occurrences_limit')"
                        helpText="Leave blank for unlimited"
                    />
                </div>
            </div>

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6 mb-6">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                    Invoice Settings
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form.select name="currency" label="Currency" :required="true">
                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                        <option value="MKD" {{ old('currency') == 'MKD' ? 'selected' : '' }}>MKD</option>
                    </x-form.select>

                    <x-form.select name="tax_behavior" label="Tax Behavior" :required="true">
                        <option value="exclusive" {{ old('tax_behavior', 'exclusive') == 'exclusive' ? 'selected' : '' }}>Tax Exclusive</option>
                        <option value="inclusive" {{ old('tax_behavior') == 'inclusive' ? 'selected' : '' }}>Tax Inclusive</option>
                    </x-form.select>

                    <x-form.input
                        type="number"
                        name="net_terms_days"
                        label="Payment Terms (Days)"
                        :required="true"
                        min="0"
                        max="365"
                        :value="old('net_terms_days', 14)"
                    />
                </div>

                <div class="mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_send_email" value="1" {{ old('auto_send_email', true) ? 'checked' : '' }}
                               class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            Automatically send invoice email to customer when generated
                        </span>
                    </label>
                </div>

                <div class="mt-6">
                    <x-form.input
                        type="textarea"
                        name="notes"
                        label="Notes"
                        placeholder="Additional notes to include on generated invoices..."
                        rows="3"
                        :value="old('notes')"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <x-button href="{{ route('invoicing.recurring-invoices.index') }}" variant="secondary">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary">
                    Create Recurring Invoice
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
