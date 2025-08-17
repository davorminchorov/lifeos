@extends('layouts.app')

@section('title', 'Edit Warranty - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Warranty
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Update warranty information for {{ $warranty->product_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('warranties.show', $warranty) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                View Warranty
            </a>
            <a href="{{ route('warranties.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to List
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('warranties.update', $warranty) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Product Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Product Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update the basic details about the product.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div>
                            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name *</label>
                            <input type="text" name="product_name" id="product_name" value="{{ old('product_name', $warranty->product_name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('product_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Brand -->
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand *</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand', $warranty->brand) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('brand')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Model -->
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                            <input type="text" name="model" id="model" value="{{ old('model', $warranty->model) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('model')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Serial Number -->
                        <div>
                            <label for="serial_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
                            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $warranty->serial_number) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('serial_number')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Purchase Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update details about when and where you purchased this product.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Purchase Date -->
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Date *</label>
                            <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', $warranty->purchase_date->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('purchase_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Purchase Price -->
                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Price *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="purchase_price" id="purchase_price" step="0.01" min="0" value="{{ old('purchase_price', $warranty->purchase_price) }}" required
                                       class="pl-7 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            @error('purchase_price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency *</label>
                            <select name="currency" id="currency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="MKD" {{ old('currency', $warranty->currency) === 'MKD' ? 'selected' : '' }}>MKD (ден) - Macedonian Denar</option>
                                <option value="USD" {{ old('currency', $warranty->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ old('currency', $warranty->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                                <option value="GBP" {{ old('currency', $warranty->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                                <option value="CAD" {{ old('currency', $warranty->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                                <option value="AUD" {{ old('currency', $warranty->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                                <option value="JPY" {{ old('currency', $warranty->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                                <option value="CHF" {{ old('currency', $warranty->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                                <option value="RSD" {{ old('currency', $warranty->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                                <option value="BGN" {{ old('currency', $warranty->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Retailer -->
                        <div class="md:col-span-2">
                            <label for="retailer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Retailer</label>
                            <input type="text" name="retailer" id="retailer" value="{{ old('retailer', $warranty->retailer) }}"
                                   placeholder="e.g., Amazon, Best Buy, Apple Store"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('retailer')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warranty Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Warranty Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Update the warranty coverage details.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Warranty Duration -->
                        <div>
                            <label for="warranty_duration_months" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warranty Duration (Months) *</label>
                            <input type="number" name="warranty_duration_months" id="warranty_duration_months" min="1" value="{{ old('warranty_duration_months', $warranty->warranty_duration_months) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('warranty_duration_months')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Type -->
                        <div>
                            <label for="warranty_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warranty Type *</label>
                            <select name="warranty_type" id="warranty_type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Type</option>
                                <option value="manufacturer" {{ old('warranty_type', $warranty->warranty_type) === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                <option value="extended" {{ old('warranty_type', $warranty->warranty_type) === 'extended' ? 'selected' : '' }}>Extended</option>
                                <option value="store" {{ old('warranty_type', $warranty->warranty_type) === 'store' ? 'selected' : '' }}>Store</option>
                            </select>
                            @error('warranty_type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Expiration Date -->
                        <div>
                            <label for="warranty_expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warranty Expiration Date</label>
                            <input type="date" name="warranty_expiration_date" id="warranty_expiration_date" value="{{ old('warranty_expiration_date', $warranty->warranty_expiration_date->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Will be auto-calculated from purchase date and duration if changed</p>
                            @error('warranty_expiration_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Status -->
                        <div>
                            <label for="current_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Status</label>
                            <select name="current_status" id="current_status"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('current_status', $warranty->current_status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ old('current_status', $warranty->current_status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="claimed" {{ old('current_status', $warranty->current_status) === 'claimed' ? 'selected' : '' }}>Claimed</option>
                                <option value="transferred" {{ old('current_status', $warranty->current_status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            </select>
                            @error('current_status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warranty Terms -->
                        <div class="md:col-span-2">
                            <label for="warranty_terms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warranty Terms</label>
                            <textarea name="warranty_terms" id="warranty_terms" rows="3"
                                      placeholder="Key terms and conditions of the warranty..."
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('warranty_terms', $warranty->warranty_terms) }}</textarea>
                            @error('warranty_terms')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing Attachments -->
            @if($warranty->receipt_attachments || $warranty->proof_of_purchase_attachments)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Current Attachments
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Currently uploaded files for this warranty.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($warranty->receipt_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Receipt/Invoice Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->receipt_attachments as $file)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ basename($file) }}</span>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($warranty->proof_of_purchase_attachments)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Proof of Purchase Files</h4>
                            <div class="space-y-2">
                                @foreach($warranty->proof_of_purchase_attachments as $file)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ basename($file) }}</span>
                                        <a href="{{ Storage::url($file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>
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
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Add New Attachments
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Upload additional receipts and proof of purchase documents.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Receipt Attachments -->
                        <div>
                            <label for="receipt_attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt/Invoice</label>
                            <input type="file" name="receipt_attachments[]" id="receipt_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload images or PDF files (will be added to existing files)</p>
                            @error('receipt_attachments')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Proof of Purchase -->
                        <div>
                            <label for="proof_of_purchase_attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proof of Purchase</label>
                            <input type="file" name="proof_of_purchase_attachments[]" id="proof_of_purchase_attachments" multiple accept="image/*,application/pdf"
                                   class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Additional purchase documentation (will be added to existing files)</p>
                            @error('proof_of_purchase_attachments')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Additional Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Optional notes and reminders.
                    </p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  placeholder="Any additional notes about this warranty..."
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $warranty->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('warranties.show', $warranty) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-md text-sm font-medium">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                    Update Warranty
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
</script>
@endpush
