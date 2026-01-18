@extends('layouts.app')

@section('title', 'Create Credit Note - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Create Credit Note</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Issue a credit note for a customer</p>
            </div>
            <x-button href="{{ route('invoicing.credit-notes.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Credit Notes
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('invoicing.credit-notes.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Credit Note Details -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Credit Note Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Customer -->
                        <div>
                            <x-form.select name="customer_id" label="Customer" :required="true">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (old('customer_id', $selectedCustomerId) == $customer->id) ? 'selected' : '' }}>
                                        {{ $customer->name }}{{ $customer->company_name ? ' (' . $customer->company_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </x-form.select>
                            @if($customers->count() === 0)
                                <p class="mt-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    No customers found.
                                    <a href="{{ route('invoicing.customers.create') }}" class="text-[color:var(--color-accent-500)] hover:underline">
                                        Create a customer first
                                    </a>
                                </p>
                            @endif
                        </div>

                        <!-- Invoice (Optional) -->
                        <div>
                            <x-form.select name="invoice_id" label="Related Invoice (Optional)">
                                <option value="">No specific invoice</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" {{ (old('invoice_id', $selectedInvoiceId) == $invoice->id) ? 'selected' : '' }}>
                                        {{ $invoice->number }} - {{ $invoice->customer->name }} ({{ app(\App\Services\CurrencyService::class)->format($invoice->total / 100, $invoice->currency) }})
                                    </option>
                                @endforeach
                            </x-form.select>
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Select an invoice if this credit note is related to a specific invoice
                            </p>
                        </div>

                        <!-- Currency -->
                        <div>
                            <x-form.select name="currency" label="Currency" :required="true">
                                <option value="MKD" {{ old('currency', 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                                <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                                <option value="CHF" {{ old('currency') === 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                                <option value="RSD" {{ old('currency') === 'RSD' ? 'selected' : '' }}>RSD - Serbian Dinar</option>
                                <option value="BGN" {{ old('currency') === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                            </x-form.select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <x-form.input
                                type="number"
                                name="amount"
                                label="Credit Amount (in cents)"
                                :required="true"
                                min="1"
                                placeholder="e.g., 10000 for $100.00"
                                helpText="Enter amount in cents"
                            />
                        </div>

                        <!-- Reason -->
                        <div>
                            <x-form.select name="reason" label="Reason" :required="true">
                                <option value="">Select a reason</option>
                                <option value="product_return" {{ old('reason') === 'product_return' ? 'selected' : '' }}>Product Return</option>
                                <option value="service_cancellation" {{ old('reason') === 'service_cancellation' ? 'selected' : '' }}>Service Cancellation</option>
                                <option value="billing_error" {{ old('reason') === 'billing_error' ? 'selected' : '' }}>Billing Error</option>
                                <option value="goodwill" {{ old('reason') === 'goodwill' ? 'selected' : '' }}>Goodwill</option>
                                <option value="duplicate_payment" {{ old('reason') === 'duplicate_payment' ? 'selected' : '' }}>Duplicate Payment</option>
                                <option value="other" {{ old('reason') === 'other' ? 'selected' : '' }}>Other</option>
                            </x-form.select>
                        </div>

                        <!-- Description -->
                        <div>
                            <x-form.input
                                type="textarea"
                                name="description"
                                label="Description"
                                placeholder="Detailed description of the credit note reason..."
                                rows="3"
                            />
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
                            <x-form.input
                                type="textarea"
                                name="notes"
                                label="Internal Notes"
                                placeholder="Internal notes (not visible to customer)..."
                                rows="3"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <x-button href="{{ route('invoicing.credit-notes.index') }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Create Credit Note
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
                        After creating the credit note, you can apply it to outstanding invoices or issue a refund to the customer.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
