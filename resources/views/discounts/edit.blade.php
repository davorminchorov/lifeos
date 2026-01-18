@extends('layouts.app')

@section('title', 'Edit Discount - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Discount</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Modify discount code details</p>
            </div>
            <x-button href="{{ route('invoicing.discounts.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Discounts
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('invoicing.discounts.update', $discount) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Discount Details -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Discount Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Code -->
                        <x-form.input
                            name="code"
                            label="Discount Code"
                            :required="true"
                            :value="old('code', $discount->code)"
                            placeholder="e.g., SAVE20, SUMMER2026"
                            helpText="Unique code customers can use"
                        />

                        <!-- Type -->
                        <x-form.select name="type" label="Discount Type" :required="true">
                            <option value="">Select type</option>
                            <option value="percent" {{ old('type', $discount->type->value) === 'percent' ? 'selected' : '' }}>Percentage Discount</option>
                            <option value="fixed" {{ old('type', $discount->type->value) === 'fixed' ? 'selected' : '' }}>Fixed Amount Discount</option>
                        </x-form.select>

                        <!-- Value -->
                        <x-form.input
                            type="number"
                            name="value"
                            label="Discount Value"
                            :required="true"
                            min="0"
                            :value="old('value', $discount->value)"
                            placeholder="For percentage: 2000 = 20%, For fixed: amount in cents"
                            helpText="Percentage in basis points (100 = 1%) or fixed amount in cents"
                        />

                        <!-- Active -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="active" value="1" {{ old('active', $discount->active) ? 'checked' : '' }}
                                       class="rounded border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] text-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                <span class="ml-2 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Active</span>
                            </label>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Only active discounts can be applied to invoices
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
                            name="starts_at"
                            label="Valid From"
                            :value="old('starts_at', $discount->starts_at?->format('Y-m-d'))"
                        />

                        <!-- Valid Until -->
                        <x-form.input
                            type="date"
                            name="ends_at"
                            label="Valid Until"
                            :value="old('ends_at', $discount->ends_at?->format('Y-m-d'))"
                        />
                    </div>
                </div>

                <!-- Redemption Limits -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Redemption Limits (Optional)
                    </h2>

                    <x-form.input
                        type="number"
                        name="max_redemptions"
                        label="Maximum Redemptions"
                        min="1"
                        :value="old('max_redemptions', $discount->max_redemptions)"
                        placeholder="Leave empty for unlimited redemptions"
                    />

                    <p class="mt-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Current redemptions: {{ $discount->current_redemptions }}
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
                        :value="old('description', $discount->description)"
                        placeholder="Additional details about this discount..."
                        rows="3"
                    />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <x-button href="{{ route('invoicing.discounts.index') }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Update Discount
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
