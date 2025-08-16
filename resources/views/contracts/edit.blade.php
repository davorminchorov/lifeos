@extends('layouts.app')

@section('title', 'Edit ' . $contract->title . ' - Contracts - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('contracts.index') }}" class="text-[color:var(--color-primary-400)] hover:text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-400)] dark:hover:text-[color:var(--color-dark-300)] transition-colors duration-200">
                                <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L3.586 10l4.707-4.707a1 1 0 011.414 1.414L6.414 10l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Back</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <a href="{{ route('contracts.index') }}" class="text-sm font-medium text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-400)] transition-colors duration-200">Contracts</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-300)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <a href="{{ route('contracts.show', $contract) }}" class="ml-4 text-sm font-medium text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-500)] dark:hover:text-[color:var(--color-dark-400)] transition-colors duration-200">{{ $contract->title }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-[color:var(--color-primary-300)] dark:text-[color:var(--color-dark-300)]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Edit</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Contract
            </h1>
            <p class="mt-1 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update contract information and terms.
            </p>
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('contracts.update', $contract) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Title -->
                    <div class="sm:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Title *</label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $contract->title) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-500 @enderror"
                               placeholder="e.g., Apartment Lease Agreement">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contract Type -->
                    <div>
                        <label for="contract_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Type *</label>
                        <select name="contract_type" id="contract_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('contract_type') border-red-500 @enderror">
                            <option value="">Select contract type</option>
                            <option value="lease" {{ old('contract_type', $contract->contract_type) === 'lease' ? 'selected' : '' }}>Lease</option>
                            <option value="employment" {{ old('contract_type', $contract->contract_type) === 'employment' ? 'selected' : '' }}>Employment</option>
                            <option value="service" {{ old('contract_type', $contract->contract_type) === 'service' ? 'selected' : '' }}>Service</option>
                            <option value="insurance" {{ old('contract_type', $contract->contract_type) === 'insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="phone" {{ old('contract_type', $contract->contract_type) === 'phone' ? 'selected' : '' }}>Phone</option>
                            <option value="internet" {{ old('contract_type', $contract->contract_type) === 'internet' ? 'selected' : '' }}>Internet</option>
                            <option value="maintenance" {{ old('contract_type', $contract->contract_type) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="other" {{ old('contract_type', $contract->contract_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('contract_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Counterparty -->
                    <div>
                        <label for="counterparty" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Counterparty *</label>
                        <input type="text" name="counterparty" id="counterparty" required value="{{ old('counterparty', $contract->counterparty) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('counterparty') border-red-500 @enderror"
                               placeholder="Company or individual name">
                        @error('counterparty')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date *</label>
                        <input type="date" name="start_date" id="start_date" required value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave blank for open-ended contracts</p>
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Terms & Conditions</h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Notice Period -->
                    <div>
                        <label for="notice_period_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notice Period (days)</label>
                        <input type="number" name="notice_period_days" id="notice_period_days" min="1" max="365" value="{{ old('notice_period_days', $contract->notice_period_days) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notice_period_days') border-red-500 @enderror"
                               placeholder="e.g., 30">
                        @error('notice_period_days')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Auto Renewal -->
                    <div class="flex items-center h-full">
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_renewal" id="auto_renewal" value="1" {{ old('auto_renewal', $contract->auto_renewal) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-700 rounded @error('auto_renewal') border-red-500 @enderror">
                            <label for="auto_renewal" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Auto-renewal enabled
                            </label>
                        </div>
                    </div>

                    <!-- Contract Value -->
                    <div>
                        <label for="contract_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Value ($)</label>
                        <input type="number" name="contract_value" id="contract_value" min="0" step="0.01" value="{{ old('contract_value', $contract->contract_value) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('contract_value') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('contract_value')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Terms -->
                    <div>
                        <label for="payment_terms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Terms</label>
                        <select name="payment_terms" id="payment_terms"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('payment_terms') border-red-500 @enderror">
                            <option value="">Select payment terms</option>
                            <option value="Monthly" {{ old('payment_terms', $contract->payment_terms) === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Quarterly" {{ old('payment_terms', $contract->payment_terms) === 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="Semi-annually" {{ old('payment_terms', $contract->payment_terms) === 'Semi-annually' ? 'selected' : '' }}>Semi-annually</option>
                            <option value="Annually" {{ old('payment_terms', $contract->payment_terms) === 'Annually' ? 'selected' : '' }}>Annually</option>
                            <option value="One-time" {{ old('payment_terms', $contract->payment_terms) === 'One-time' ? 'selected' : '' }}>One-time</option>
                            <option value="Net 30" {{ old('payment_terms', $contract->payment_terms) === 'Net 30' ? 'selected' : '' }}>Net 30</option>
                            <option value="Due on receipt" {{ old('payment_terms', $contract->payment_terms) === 'Due on receipt' ? 'selected' : '' }}>Due on receipt</option>
                        </select>
                        @error('payment_terms')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 space-y-6">
                    <!-- Key Obligations -->
                    <div>
                        <label for="key_obligations" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key Obligations</label>
                        <textarea name="key_obligations" id="key_obligations" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('key_obligations') border-red-500 @enderror"
                                  placeholder="List the main obligations and responsibilities for each party...">{{ old('key_obligations', $contract->key_obligations) }}</textarea>
                        @error('key_obligations')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Penalties -->
                    <div>
                        <label for="penalties" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penalties</label>
                        <textarea name="penalties" id="penalties" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('penalties') border-red-500 @enderror"
                                  placeholder="Describe any penalties for breach of contract...">{{ old('penalties', $contract->penalties) }}</textarea>
                        @error('penalties')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Termination Clauses -->
                    <div>
                        <label for="termination_clauses" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Termination Clauses</label>
                        <textarea name="termination_clauses" id="termination_clauses" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('termination_clauses') border-red-500 @enderror"
                                  placeholder="Specify conditions and procedures for contract termination...">{{ old('termination_clauses', $contract->termination_clauses) }}</textarea>
                        @error('termination_clauses')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Additional Information</h3>

                <div class="space-y-6">
                    <!-- Performance Rating -->
                    <div>
                        <label for="performance_rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Performance Rating</label>
                        <select name="performance_rating" id="performance_rating"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs @error('performance_rating') border-red-500 @enderror">
                            <option value="">Not rated</option>
                            <option value="1" {{ old('performance_rating', $contract->performance_rating) == '1' ? 'selected' : '' }}>1 - Very Poor</option>
                            <option value="2" {{ old('performance_rating', $contract->performance_rating) == '2' ? 'selected' : '' }}>2 - Poor</option>
                            <option value="3" {{ old('performance_rating', $contract->performance_rating) == '3' ? 'selected' : '' }}>3 - Average</option>
                            <option value="4" {{ old('performance_rating', $contract->performance_rating) == '4' ? 'selected' : '' }}>4 - Good</option>
                            <option value="5" {{ old('performance_rating', $contract->performance_rating) == '5' ? 'selected' : '' }}>5 - Excellent</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rate the performance of the counterparty (optional)</p>
                        @error('performance_rating')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-500 @enderror"
                                  placeholder="Add any additional notes or comments about this contract...">{{ old('notes', $contract->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status', $contract->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status', $contract->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ old('status', $contract->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="terminated" {{ old('status', $contract->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('contracts.show', $contract) }}"
               class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Contract
            </button>
        </div>
    </form>

    <!-- Delete Contract -->
    <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Delete Contract</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                        <p>Once you delete a contract, there is no going back. Please be certain.</p>
                    </div>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('Are you sure you want to delete this contract? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Delete Contract
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
