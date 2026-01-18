@extends('layouts.app')

@section('title', 'Edit Invoice - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Invoice</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Modify invoice draft details</p>
            </div>
            <x-button href="{{ route('invoicing.invoices.show', $invoice) }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Invoice
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('invoicing.invoices.update', $invoice) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Customer Selection -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Invoice Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Customer -->
                        <div>
                            <x-form.select name="customer_id" label="Customer" :required="true">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (old('customer_id', $invoice->customer_id) == $customer->id) ? 'selected' : '' }}>
                                        {{ $customer->name }}{{ $customer->company_name ? ' (' . $customer->company_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <!-- Currency -->
                        <div>
                            <x-form.select name="currency" label="Currency" :required="true">
                                <option value="MKD" {{ old('currency', $invoice->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                                <option value="USD" {{ old('currency', $invoice->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ old('currency', $invoice->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ old('currency', $invoice->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="CAD" {{ old('currency', $invoice->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency', $invoice->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                                <option value="JPY" {{ old('currency', $invoice->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                                <option value="CHF" {{ old('currency', $invoice->currency) === 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                                <option value="RSD" {{ old('currency', $invoice->currency) === 'RSD' ? 'selected' : '' }}>RSD - Serbian Dinar</option>
                                <option value="BGN" {{ old('currency', $invoice->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                            </x-form.select>
                        </div>

                        <!-- Tax Behavior -->
                        <div>
                            <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">
                                Tax Behavior *
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <input type="radio" name="tax_behavior" id="tax_exclusive" value="exclusive" {{ old('tax_behavior', $invoice->tax_behavior) === 'exclusive' ? 'checked' : '' }} required class="peer sr-only">
                                    <label for="tax_exclusive" class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                                        <div class="text-center">
                                            <span class="block text-lg font-semibold text-blue-600">Tax Exclusive</span>
                                            <span class="block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Tax added on top</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="relative">
                                    <input type="radio" name="tax_behavior" id="tax_inclusive" value="inclusive" {{ old('tax_behavior', $invoice->tax_behavior) === 'inclusive' ? 'checked' : '' }} required class="peer sr-only">
                                    <label for="tax_inclusive" class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                                        <div class="text-center">
                                            <span class="block text-lg font-semibold text-blue-600">Tax Inclusive</span>
                                            <span class="block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Tax included in price</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('tax_behavior')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Terms -->
                        <div>
                            <x-form.input type="number" name="net_terms_days" label="Payment Terms (Days)"
                                :value="old('net_terms_days', $invoice->net_terms_days)"
                                min="0" max="365"
                                helpText="Number of days until payment is due (e.g., Net 14)" />
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Notes
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <x-form.input type="textarea" name="notes" label="Customer Notes"
                                :value="old('notes', $invoice->notes)"
                                placeholder="Notes visible to the customer on the invoice..." rows="3" />
                        </div>

                        <div>
                            <x-form.input type="textarea" name="internal_notes" label="Internal Notes"
                                :value="old('internal_notes', $invoice->internal_notes)"
                                placeholder="Internal notes (not visible to customer)..." rows="3" />
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <x-button href="{{ route('invoicing.invoices.show', $invoice) }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Update Invoice
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
                        You can only edit invoice details while it's in draft status. To manage line items, go back to the invoice view.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
