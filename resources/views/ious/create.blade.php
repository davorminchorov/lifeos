@extends('layouts.app')

@section('title', 'Add IOU - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add IOU</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Record a new IOU transaction</p>
            </div>
            <a href="{{ route('ious.index') }}"
               class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to IOUs
            </a>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('ious.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-3">Type *</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="radio" name="type" id="type_owe" value="owe" {{ old('type') === 'owe' ? 'checked' : '' }} required
                                   class="peer sr-only">
                            <label for="type_owe"
                                   class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                                <div class="text-center">
                                    <span class="block text-lg font-semibold text-red-600">I Owe</span>
                                    <span class="block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Money I owe to someone</span>
                                </div>
                            </label>
                        </div>
                        <div class="relative">
                            <input type="radio" name="type" id="type_owed" value="owed" {{ old('type') === 'owed' ? 'checked' : '' }} required
                                   class="peer sr-only">
                            <label for="type_owed"
                                   class="flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors">
                                <div class="text-center">
                                    <span class="block text-lg font-semibold text-green-600">Owed to Me</span>
                                    <span class="block text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">Money someone owes me</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    @error('type')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Person Name -->
                <div>
                    <label for="person_name" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Person Name *</label>
                    <input type="text" name="person_name" id="person_name" required
                           value="{{ old('person_name') }}"
                           class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                           placeholder="Enter person's name">
                    @error('person_name')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount and Currency -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Amount *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" step="0.01" name="amount" id="amount" required min="0.01"
                                   value="{{ old('amount') }}"
                                   class="block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                                   placeholder="0.00">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Currency</label>
                        <select name="currency" id="currency"
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
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
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="transaction_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Transaction Date *</label>
                        <input type="date" name="transaction_date" id="transaction_date" required
                               value="{{ old('transaction_date', date('Y-m-d')) }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                        @error('transaction_date')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Due Date</label>
                        <input type="date" name="due_date" id="due_date"
                               value="{{ old('due_date') }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                        @error('due_date')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Category and Payment Method -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Category</label>
                        <input type="text" name="category" id="category"
                               value="{{ old('category') }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                               placeholder="e.g., Loan, Borrowed Item, Service">
                        @error('category')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Payment Method</label>
                        <select name="payment_method" id="payment_method"
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                            <option value="">Select Payment Method</option>
                            <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Credit Card" {{ old('payment_method') === 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="PayPal" {{ old('payment_method') === 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <option value="Venmo" {{ old('payment_method') === 'Venmo' ? 'selected' : '' }}>Venmo</option>
                            <option value="Check" {{ old('payment_method') === 'Check' ? 'selected' : '' }}>Check</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Description *</label>
                    <textarea name="description" id="description" rows="3" required
                              class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                              placeholder="What is this IOU for? (e.g., Borrowed money for car repair, Loan for business, Rent payment)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                              placeholder="Any additional information or reminders (optional)">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status and Amount Paid (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Status</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partially_paid" {{ old('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount_paid" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Amount Paid</label>
                        <input type="number" step="0.01" name="amount_paid" id="amount_paid" min="0"
                               value="{{ old('amount_paid', 0) }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                               placeholder="0.00">
                        @error('amount_paid')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <a href="{{ route('ious.index') }}"
                       class="px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-primary-500)]">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[color:var(--color-accent-600)] hover:bg-[color:var(--color-accent-700)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--color-accent-500)]">
                        Create IOU
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
