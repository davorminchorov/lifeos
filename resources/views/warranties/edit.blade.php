@extends('layouts.app')

@section('title', 'Edit Warranty - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Warranty
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update warranty information for {{ $warranty->product_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('warranties.show', $warranty) }}" variant="secondary">View Warranty</x-button>
            <x-button href="{{ route('warranties.index') }}" variant="secondary">Back to List</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('warranties.update', $warranty) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Product Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Product Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Update the basic details about the product.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input name="product_name" label="Product Name" :required="true" :value="old('product_name', $warranty->product_name)" />
                        <x-form.input name="brand" label="Brand" :required="true" :value="old('brand', $warranty->brand)" />
                        <x-form.input name="model" label="Model" :value="old('model', $warranty->model)" />
                        <x-form.input name="serial_number" label="Serial Number" :value="old('serial_number', $warranty->serial_number)" />
                    </div>
                </div>
            </div>

            <!-- Purchase Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Purchase Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Update details about when and where you purchased this product.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input type="date" name="purchase_date" label="Purchase Date" :required="true" :value="old('purchase_date', $warranty->purchase_date->format('Y-m-d'))" />

                        <x-form.input type="number" name="purchase_price" label="Purchase Price" prefix="$" step="0.01" min="0" :required="true" :value="old('purchase_price', $warranty->purchase_price)" />

                        <x-form.select name="currency" label="Currency" :required="true">
                            <option value="MKD" {{ old('currency', $warranty->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency', $warranty->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency', $warranty->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency', $warranty->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency', $warranty->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $warranty->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('currency', $warranty->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('currency', $warranty->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $warranty->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $warranty->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>

                        <div class="md:col-span-2">
                            <x-form.input name="retailer" label="Retailer" :value="old('retailer', $warranty->retailer)" placeholder="e.g., Amazon, Best Buy, Apple Store" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warranty Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Warranty Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Update the warranty coverage details.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form.input type="number" name="warranty_duration_months" label="Warranty Duration (Months)" min="1" :required="true" :value="old('warranty_duration_months', $warranty->warranty_duration_months)" />

                        <x-form.select name="warranty_type" label="Warranty Type" :required="true" placeholder="Select Type">
                            <option value="manufacturer" {{ old('warranty_type', $warranty->warranty_type) === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                            <option value="extended" {{ old('warranty_type', $warranty->warranty_type) === 'extended' ? 'selected' : '' }}>Extended</option>
                            <option value="store" {{ old('warranty_type', $warranty->warranty_type) === 'store' ? 'selected' : '' }}>Store</option>
                        </x-form.select>

                        <x-form.input type="date" name="warranty_expiration_date" label="Warranty Expiration Date" :value="old('warranty_expiration_date', $warranty->warranty_expiration_date->format('Y-m-d'))" helpText="Will be auto-calculated from purchase date and duration if changed" />

                        <x-form.select name="current_status" label="Current Status">
                            <option value="active" {{ old('current_status', $warranty->current_status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('current_status', $warranty->current_status) === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="claimed" {{ old('current_status', $warranty->current_status) === 'claimed' ? 'selected' : '' }}>Claimed</option>
                            <option value="transferred" {{ old('current_status', $warranty->current_status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                        </x-form.select>

                        <div class="md:col-span-2">
                            <x-form.input type="textarea" name="warranty_terms" label="Warranty Terms" rows="3" placeholder="Key terms and conditions of the warranty..." :value="old('warranty_terms', $warranty->warranty_terms)" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Attachments -->
            @if($warranty->receipt_attachments || $warranty->proof_of_purchase_attachments)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Current Attachments
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Currently uploaded files for this warranty.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($warranty->receipt_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Receipt/Invoice Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->receipt_attachments as $file)
                                    <div class="flex items-center justify-between p-2 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded">
                                        <span class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] dark:text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-400)]">{{ basename($file) }}</span>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-800)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-300)] text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($warranty->proof_of_purchase_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Proof of Purchase Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->proof_of_purchase_attachments as $file)
                                    <div class="flex items-center justify-between p-2 bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] rounded">
                                        <span class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] dark:text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-400)]">{{ basename($file) }}</span>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-800)] dark:text-[color:var(--color-accent-400)] dark:hover:text-[color:var(--color-accent-300)] text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- New Attachments -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Add New Attachments
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Upload additional receipts and proof of purchase documents.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Receipt Attachments -->
                        <div>
                            <label for="receipt_attachments" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Receipt/Invoice</label>
                            <input type="file" name="receipt_attachments[]" id="receipt_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[color:var(--color-accent-50)] file:text-[color:var(--color-accent-700)] hover:file:bg-[color:var(--color-accent-100)] dark:file:bg-[color:var(--color-primary-700)] dark:file:text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-400)]">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Upload images or PDF files (will be added to existing files)</p>
                            @error('receipt_attachments')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proof of Purchase -->
                        <div>
                            <label for="proof_of_purchase_attachments" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Proof of Purchase</label>
                            <input type="file" name="proof_of_purchase_attachments[]" id="proof_of_purchase_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[color:var(--color-accent-50)] file:text-[color:var(--color-accent-700)] hover:file:bg-[color:var(--color-accent-100)] dark:file:bg-[color:var(--color-primary-700)] dark:file:text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-400)]">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Additional purchase documentation (will be added to existing files)</p>
                            @error('proof_of_purchase_attachments')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Additional Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Optional notes and reminders.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <!-- Notes -->
                    <div>
                        <x-form.input type="textarea" name="notes" label="Notes" rows="4" placeholder="Any additional notes about this warranty..." :value="old('notes', $warranty->notes)" />
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <x-button href="{{ route('warranties.show', $warranty) }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">Update Warranty</x-button>
            </div>
        </form>
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
</script>
@endpush
