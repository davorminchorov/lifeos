@extends('layouts.app')

@section('title', 'Edit Tax Rate - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Tax Rate</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Modify tax rate details</p>
            </div>
            <x-button href="{{ route('invoicing.tax-rates.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Tax Rates
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('invoicing.tax-rates.update', $taxRate) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Tax Rate Details -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Tax Rate Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Name -->
                        <x-form.input
                            name="name"
                            label="Tax Rate Name"
                            :required="true"
                            :value="old('name', $taxRate->name)"
                            placeholder="e.g., VAT, Sales Tax, GST"
                        />

                        <!-- Percentage -->
                        <x-form.input
                            type="number"
                            name="percentage_basis_points"
                            label="Tax Rate (in basis points)"
                            :required="true"
                            min="0"
                            max="1000000"
                            :value="old('percentage_basis_points', $taxRate->percentage_basis_points)"
                            placeholder="e.g., 2000 for 20%"
                            helpText="Enter percentage in basis points (100 basis points = 1%)"
                        />

                        <!-- Country -->
                        <x-form.input
                            name="country"
                            label="Country Code (Optional)"
                            maxlength="2"
                            :value="old('country', $taxRate->country)"
                            placeholder="e.g., US, GB, MK"
                            helpText="2-letter ISO country code"
                        />

                        <!-- Active -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $taxRate->active) ? 'checked' : '' }}
                                       class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Active</span>
                            </label>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Only active tax rates can be applied to invoices
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Valid Period -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Valid Period (Optional)
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Valid From -->
                        <x-form.input
                            type="date"
                            name="valid_from"
                            label="Valid From"
                            :value="old('valid_from', $taxRate->valid_from?->format('Y-m-d'))"
                        />

                        <!-- Valid Until -->
                        <x-form.input
                            type="date"
                            name="valid_until"
                            label="Valid Until"
                            :value="old('valid_until', $taxRate->valid_until?->format('Y-m-d'))"
                        />
                    </div>
                    <p class="mt-2 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Leave empty for no date restrictions
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Description (Optional)
                    </h2>

                    <x-form.input
                        type="textarea"
                        name="description"
                        label="Description"
                        :value="old('description', $taxRate->description)"
                        placeholder="Additional details about this tax rate..."
                        rows="3"
                    />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <x-button href="{{ route('invoicing.tax-rates.index') }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Update Tax Rate
                    </x-button>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Changing this tax rate will not affect existing invoices, only new line items using this tax rate.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
