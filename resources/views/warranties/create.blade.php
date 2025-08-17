@extends('layouts.app')

@section('title', 'Add New Warranty - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Add New Warranty
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Register a new product warranty for tracking
            </p>
        </div>
        <a href="{{ route('warranties.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
            Back to List
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('warranties.store') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <!-- Product Information -->
            <x-form.section title="Product Information" description="Enter the basic details about the product.">
                <x-form.input
                    name="product_name"
                    label="Product Name"
                    type="text"
                    required
                />

                <x-form.input
                    name="brand"
                    label="Brand"
                    type="text"
                    required
                />

                <x-form.input
                    name="model"
                    label="Model"
                    type="text"
                />

                <x-form.input
                    name="serial_number"
                    label="Serial Number"
                    type="text"
                />
            </x-form.section>

            <!-- Purchase Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Purchase Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Details about when and where you purchased this product.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Purchase Date -->
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Purchase Date *</label>
                            <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date') }}" required
                                   class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            @error('purchase_date')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Purchase Price -->
                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Purchase Price *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] sm:text-sm">$</span>
                                </div>
                                <input type="number" name="purchase_price" id="purchase_price" step="0.01" min="0" value="{{ old('purchase_price') }}" required
                                       class="pl-7 mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            </div>
                            @error('purchase_price')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Currency *</label>
                            <select name="currency" id="currency" required
                                    class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
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
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Retailer -->
                        <div class="md:col-span-2">
                            <label for="retailer" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Retailer</label>
                            <input type="text" name="retailer" id="retailer" value="{{ old('retailer') }}"
                                   placeholder="e.g., Amazon, Best Buy, Apple Store"
                                   class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            @error('retailer')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warranty Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Warranty Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Details about the warranty coverage.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Warranty Duration -->
                        <div>
                            <label for="warranty_duration_months" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Warranty Duration (Months) *</label>
                            <input type="number" name="warranty_duration_months" id="warranty_duration_months" min="1" value="{{ old('warranty_duration_months') }}" required
                                   class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            @error('warranty_duration_months')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Type -->
                        <div>
                            <label for="warranty_type" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Warranty Type *</label>
                            <select name="warranty_type" id="warranty_type" required
                                    class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                <option value="">Select Type</option>
                                <option value="manufacturer" {{ old('warranty_type') === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                <option value="extended" {{ old('warranty_type') === 'extended' ? 'selected' : '' }}>Extended</option>
                                <option value="store" {{ old('warranty_type') === 'store' ? 'selected' : '' }}>Store</option>
                            </select>
                            @error('warranty_type')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Expiration Date -->
                        <div>
                            <label for="warranty_expiration_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Warranty Expiration Date</label>
                            <input type="date" name="warranty_expiration_date" id="warranty_expiration_date" value="{{ old('warranty_expiration_date') }}"
                                   class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            <p class="mt-1 text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Will be auto-calculated from purchase date and duration if left empty</p>
                            @error('warranty_expiration_date')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Status -->
                        <div>
                            <label for="current_status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Current Status</label>
                            <select name="current_status" id="current_status"
                                    class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                                <option value="active" {{ old('current_status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ old('current_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="claimed" {{ old('current_status') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                                <option value="transferred" {{ old('current_status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            </select>
                            @error('current_status')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Terms -->
                        <div class="md:col-span-2">
                            <label for="warranty_terms" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Warranty Terms</label>
                            <textarea name="warranty_terms" id="warranty_terms" rows="3"
                                      placeholder="Key terms and conditions of the warranty..."
                                      class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">{{ old('warranty_terms') }}</textarea>
                            @error('warranty_terms')
                                <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Attachments
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Upload receipts and proof of purchase documents.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
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
            </div>

            <!-- Additional Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Additional Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                        Optional notes and reminders.
                    </p>
                </div>
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] px-4 py-5 sm:px-6">
                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  placeholder="Any additional notes about this warranty..."
                                  class="mt-1 block w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] shadow-sm focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('warranties.index') }}" class="bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-6 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" class="bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] text-white px-6 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                    Create Warranty
                </button>
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

// Calculate initial expiration date if values are already set
document.addEventListener('DOMContentLoaded', calculateExpirationDate);
</script>
@endpush
