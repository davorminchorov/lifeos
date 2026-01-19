@extends('layouts.app')

@section('title', 'Add Customer - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add Customer</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Create a new customer for invoicing</p>
            </div>
            <x-button href="{{ route('invoicing.customers.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Customers
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('invoicing.customers.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Basic Information -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Basic Information
                    </h2>

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <x-form.input name="name" label="Customer Name" :required="true" placeholder="Enter customer name" />
                        </div>

                        <!-- Email and Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-form.input type="email" name="email" label="Email" placeholder="customer@example.com" />
                            </div>
                            <div>
                                <x-form.input type="tel" name="phone" label="Phone" placeholder="+1 234 567 8900" />
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <x-form.input name="company_name" label="Company Name" placeholder="Optional company name" />
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Billing Address
                    </h2>

                    <div class="space-y-4">
                        <!-- Street -->
                        <div>
                            <x-form.input name="billing_address[street]" label="Street Address" placeholder="123 Main St" />
                        </div>

                        <!-- City and State -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-form.input name="billing_address[city]" label="City" placeholder="New York" />
                            </div>
                            <div>
                                <x-form.input name="billing_address[state]" label="State/Province" placeholder="NY" />
                            </div>
                        </div>

                        <!-- Postal Code and Country -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-form.input name="billing_address[postal_code]" label="Postal Code" placeholder="10001" />
                            </div>
                            <div>
                                <x-form.input name="billing_address[country]" label="Country" placeholder="United States" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax Information -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Tax Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-form.input name="tax_id" label="Tax ID / VAT Number" placeholder="Optional tax identification number" />
                        </div>
                        <div>
                            <x-form.input name="tax_country" label="Tax Country Code" placeholder="US" maxlength="2"
                                helpText="2-letter country code (e.g., US, GB, DE)" />
                        </div>
                    </div>
                </div>

                <!-- Billing Defaults -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Billing Defaults
                    </h2>

                    <div>
                        <x-form.select name="currency" label="Default Currency" :required="true">
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
                </div>

                <!-- Notes -->
                <div>
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">
                        Notes
                    </h2>

                    <div>
                        <x-form.input type="textarea" name="notes" label="Internal Notes"
                            placeholder="Add any internal notes about this customer..." rows="4" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <x-button href="{{ route('invoicing.customers.index') }}" variant="secondary">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Create Customer
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
