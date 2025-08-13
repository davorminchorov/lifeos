@extends('layouts.app')

@section('title', 'Edit Utility Bill - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Utility Bill</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Update utility bill information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('utility-bills.show', $utilityBill) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View
                </a>
                <a href="{{ route('utility-bills.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Bills
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg">
            <form action="{{ route('utility-bills.update', $utilityBill) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="utility_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Utility Type *</label>
                        <select name="utility_type" id="utility_type" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select utility type</option>
                            <option value="electricity" {{ old('utility_type', $utilityBill->utility_type) == 'electricity' ? 'selected' : '' }}>Electricity</option>
                            <option value="gas" {{ old('utility_type', $utilityBill->utility_type) == 'gas' ? 'selected' : '' }}>Gas</option>
                            <option value="water" {{ old('utility_type', $utilityBill->utility_type) == 'water' ? 'selected' : '' }}>Water</option>
                            <option value="internet" {{ old('utility_type', $utilityBill->utility_type) == 'internet' ? 'selected' : '' }}>Internet</option>
                            <option value="cable_tv" {{ old('utility_type', $utilityBill->utility_type) == 'cable_tv' ? 'selected' : '' }}>Cable TV</option>
                            <option value="phone" {{ old('utility_type', $utilityBill->utility_type) == 'phone' ? 'selected' : '' }}>Phone</option>
                            <option value="trash" {{ old('utility_type', $utilityBill->utility_type) == 'trash' ? 'selected' : '' }}>Trash Collection</option>
                            <option value="sewer" {{ old('utility_type', $utilityBill->utility_type) == 'sewer' ? 'selected' : '' }}>Sewer</option>
                            <option value="other" {{ old('utility_type', $utilityBill->utility_type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('utility_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="service_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Provider *</label>
                        <input type="text" name="service_provider" id="service_provider" required
                               value="{{ old('service_provider', $utilityBill->service_provider) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="e.g., Pacific Gas & Electric">
                        @error('service_provider')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Number</label>
                        <input type="text" name="account_number" id="account_number"
                               value="{{ old('account_number', $utilityBill->account_number) }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Account number">
                        @error('account_number')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status *</label>
                        <select name="payment_status" id="payment_status" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="pending" {{ old('payment_status', $utilityBill->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('payment_status', $utilityBill->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="overdue" {{ old('payment_status', $utilityBill->payment_status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                        @error('payment_status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="service_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Address</label>
                    <textarea name="service_address" id="service_address" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Address where the service is provided">{{ old('service_address', $utilityBill->service_address) }}</textarea>
                    @error('service_address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bill Details -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bill Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="bill_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bill Amount *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" name="bill_amount" id="bill_amount" required
                                       value="{{ old('bill_amount', $utilityBill->bill_amount) }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="0.00">
                            </div>
                            @error('bill_amount')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="usage_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Amount</label>
                            <input type="number" step="0.0001" name="usage_amount" id="usage_amount"
                                   value="{{ old('usage_amount', $utilityBill->usage_amount) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="0.0000">
                            @error('usage_amount')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="usage_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage Unit</label>
                            <select name="usage_unit" id="usage_unit"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select unit</option>
                                <option value="kWh" {{ old('usage_unit', $utilityBill->usage_unit) == 'kWh' ? 'selected' : '' }}>kWh (Kilowatt hours)</option>
                                <option value="therms" {{ old('usage_unit', $utilityBill->usage_unit) == 'therms' ? 'selected' : '' }}>Therms</option>
                                <option value="gallons" {{ old('usage_unit', $utilityBill->usage_unit) == 'gallons' ? 'selected' : '' }}>Gallons</option>
                                <option value="GB" {{ old('usage_unit', $utilityBill->usage_unit) == 'GB' ? 'selected' : '' }}>GB (Gigabytes)</option>
                                <option value="minutes" {{ old('usage_unit', $utilityBill->usage_unit) == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                <option value="other" {{ old('usage_unit', $utilityBill->usage_unit) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('usage_unit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="rate_per_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rate per Unit</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.000001" name="rate_per_unit" id="rate_per_unit"
                                       value="{{ old('rate_per_unit', $utilityBill->rate_per_unit) }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="0.000000">
                            </div>
                            @error('rate_per_unit')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="budget_alert_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget Alert Threshold</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" name="budget_alert_threshold" id="budget_alert_threshold"
                                       value="{{ old('budget_alert_threshold', $utilityBill->budget_alert_threshold) }}"
                                       class="block w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="0.00">
                            </div>
                            @error('budget_alert_threshold')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Billing Period -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Billing Period</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="bill_period_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period Start *</label>
                            <input type="date" name="bill_period_start" id="bill_period_start" required
                                   value="{{ old('bill_period_start', $utilityBill->bill_period_start?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bill_period_start')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bill_period_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period End *</label>
                            <input type="date" name="bill_period_end" id="bill_period_end" required
                                   value="{{ old('bill_period_end', $utilityBill->bill_period_end?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('bill_period_end')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date *</label>
                            <input type="date" name="due_date" id="due_date" required
                                   value="{{ old('due_date', $utilityBill->due_date?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date"
                               value="{{ old('payment_date', $utilityBill->payment_date?->format('Y-m-d')) }}"
                               class="mt-1 block w-full md:w-1/3 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('payment_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="service_plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Service Plan</label>
                            <input type="text" name="service_plan" id="service_plan"
                                   value="{{ old('service_plan', $utilityBill->service_plan) }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="e.g., Residential Standard">
                            @error('service_plan')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="auto_pay_enabled" id="auto_pay_enabled" value="1"
                                   {{ old('auto_pay_enabled', $utilityBill->auto_pay_enabled) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                            <label for="auto_pay_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Auto-pay enabled
                            </label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="contract_terms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Terms</label>
                        <textarea name="contract_terms" id="contract_terms" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                  placeholder="Contract details, terms, or special conditions">{{ old('contract_terms', $utilityBill->contract_terms) }}</textarea>
                        @error('contract_terms')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                  placeholder="Additional notes or comments">{{ old('notes', $utilityBill->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('utility-bills.show', $utilityBill) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Utility Bill
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
