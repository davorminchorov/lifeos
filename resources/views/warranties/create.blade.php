@extends('layouts.app')

@section('title', 'Add New Warranty - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add New Warranty</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Register a new product warranty for tracking</p>
            </div>
            <x-button href="{{ route('warranties.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to List
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('warranties.store') }}" class="space-y-6 p-6" enctype="multipart/form-data">
                @csrf

                <!-- Product Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input name="product_name" label="Product Name" :required="true" placeholder="" inputClass="@error('product_name') border-danger-400 @enderror" />
                    <x-form.input name="brand" label="Brand" :required="true" inputClass="@error('brand') border-danger-400 @enderror" />
                    <x-form.input name="model" label="Model" inputClass="@error('model') border-danger-400 @enderror" />
                    <x-form.input name="serial_number" label="Serial Number" inputClass="@error('serial_number') border-danger-400 @enderror" />
                </div>

                <!-- Purchase Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Purchase Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Purchase Date -->
                        <x-form.input type="date" name="purchase_date" label="Purchase Date" :required="true" inputClass="@error('purchase_date') border-danger-400 @enderror" />

                        <!-- Purchase Price -->
                        <x-form.input type="number" name="purchase_price" label="Purchase Price" step="0.01" min="0" prefix="$" inputClass="@error('purchase_price') border-danger-400 @enderror" />

                        <!-- Currency -->
                        <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                            <option value="MKD" {{ old('currency', 'MKD') === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('currency') === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency') === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency') === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>

                        <!-- Retailer -->
                        <div class="md:col-span-2">
                            <x-form.input name="retailer" label="Retailer" placeholder="e.g., Amazon, Best Buy, Apple Store" inputClass="@error('retailer') border-danger-400 @enderror" />
                        </div>
                    </div>
                </div>

                <!-- Warranty Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Warranty Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Warranty Duration -->
                        <x-form.input type="number" name="warranty_duration_months" label="Warranty Duration (Months)" min="1" :required="true" inputClass="@error('warranty_duration_months') border-danger-400 @enderror" />

                        <!-- Warranty Type -->
                        <x-form.select name="warranty_type" label="Warranty Type" :required="true" placeholder="Select Type" selectClass="@error('warranty_type') border-danger-400 @enderror">
                            <option value="manufacturer" {{ old('warranty_type') === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                            <option value="extended" {{ old('warranty_type') === 'extended' ? 'selected' : '' }}>Extended</option>
                            <option value="store" {{ old('warranty_type') === 'store' ? 'selected' : '' }}>Store</option>
                        </x-form.select>

                        <!-- Warranty Expiration Date -->
                        <x-form.input type="date" name="warranty_expiration_date" label="Warranty Expiration Date" helpText="Will be auto-calculated from purchase date and duration if left empty" inputClass="@error('warranty_expiration_date') border-danger-400 @enderror" />

                        <!-- Current Status -->
                        <x-form.select name="current_status" label="Current Status" selectClass="@error('current_status') border-danger-400 @enderror">
                            <option value="active" {{ old('current_status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('current_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="claimed" {{ old('current_status') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                            <option value="transferred" {{ old('current_status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                        </x-form.select>

                        <!-- Warranty Terms -->
                        <div class="md:col-span-2">
                            <x-form.input type="textarea" name="warranty_terms" label="Warranty Terms" rows="3" placeholder="Key terms and conditions of the warranty..." inputClass="@error('warranty_terms') border-danger-400 @enderror" />
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Attachments</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Receipt Attachments -->
                        <div>
                            <label for="receipt_attachments" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Receipt/Invoice</label>
                            <input type="file" name="receipt_attachments[]" id="receipt_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[color:var(--color-accent-50)] file:text-[color:var(--color-accent-600)] hover:file:bg-[color:var(--color-accent-100)] dark:file:bg-[color:var(--color-dark-300)] dark:file:text-[color:var(--color-dark-600)]">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Upload images or PDF files</p>
                            @error('receipt_attachments')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proof of Purchase -->
                        <div>
                            <label for="proof_of_purchase_attachments" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Proof of Purchase</label>
                            <input type="file" name="proof_of_purchase_attachments[]" id="proof_of_purchase_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[color:var(--color-accent-50)] file:text-[color:var(--color-accent-600)] hover:file:bg-[color:var(--color-accent-100)] dark:file:bg-[color:var(--color-dark-300)] dark:file:text-[color:var(--color-dark-600)]">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Additional purchase documentation</p>
                            @error('proof_of_purchase_attachments')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <x-form.input type="textarea" name="notes" label="Notes" rows="4" placeholder="Any additional notes about this warranty..." inputClass="@error('notes') border-danger-400 @enderror" />
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <x-button href="{{ route('warranties.index') }}" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Create Warranty</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-calculate warranty expiration date when purchase date or duration changes
function calculateExpirationDate() {
    const purchaseDate = document.getElementById('purchase_date').value;
    const durationMonths = document.getElementById('warranty_duration_months').value;
    const expirationField = document.getElementById('warranty_expiration_date');

    if (purchaseDate && durationMonths) {
        const purchase = new Date(purchaseDate);
        const expiration = new Date(purchase);
        expiration.setMonth(expiration.getMonth() + parseInt(durationMonths));

        // Format date as YYYY-MM-DD for the input field
        const year = expiration.getFullYear();
        const month = String(expiration.getMonth() + 1).padStart(2, '0');
        const day = String(expiration.getDate()).padStart(2, '0');

        expirationField.value = `${year}-${month}-${day}`;
    }
}

document.getElementById('purchase_date').addEventListener('change', calculateExpirationDate);
document.getElementById('warranty_duration_months').addEventListener('input', calculateExpirationDate);

// Calculate initial expiration date if values are already set
document.addEventListener('DOMContentLoaded', calculateExpirationDate);
</script>
@endpush
