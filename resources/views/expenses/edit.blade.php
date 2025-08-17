@extends('layouts.app')

@section('title', 'Edit Expense - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Edit Expense
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Update expense details
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('expenses.show', $expense) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                View Expense
            </a>
            <a href="{{ route('expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Expenses
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount *</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount', $expense->amount) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency *</label>
                        <select name="currency" id="currency" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="MKD" {{ old('currency', $expense->currency) === 'MKD' ? 'selected' : '' }}>MKD (ден) - Macedonian Denar</option>
                            <option value="USD" {{ old('currency', $expense->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency', $expense->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency', $expense->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency', $expense->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $expense->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('currency', $expense->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('currency', $expense->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $expense->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $expense->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expense Date -->
                    <div>
                        <label for="expense_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date *</label>
                        <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('expense_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                        <select name="category" id="category" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Category</option>
                            <option value="Food & Dining" {{ old('category', $expense->category) === 'Food & Dining' ? 'selected' : '' }}>Food & Dining</option>
                            <option value="Transportation" {{ old('category', $expense->category) === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                            <option value="Shopping" {{ old('category', $expense->category) === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                            <option value="Entertainment" {{ old('category', $expense->category) === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                            <option value="Bills & Utilities" {{ old('category', $expense->category) === 'Bills & Utilities' ? 'selected' : '' }}>Bills & Utilities</option>
                            <option value="Healthcare" {{ old('category', $expense->category) === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                            <option value="Travel" {{ old('category', $expense->category) === 'Travel' ? 'selected' : '' }}>Travel</option>
                            <option value="Other" {{ old('category', $expense->category) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subcategory -->
                    <div>
                        <label for="subcategory" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subcategory</label>
                        <input type="text" name="subcategory" id="subcategory" value="{{ old('subcategory', $expense->subcategory) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('subcategory')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cash" {{ old('payment_method', $expense->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ old('payment_method', $expense->payment_method) === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="debit_card" {{ old('payment_method', $expense->payment_method) === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="digital_wallet" {{ old('payment_method', $expense->payment_method) === 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expense Type -->
                    <div>
                        <label for="expense_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select name="expense_type" id="expense_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="personal" {{ old('expense_type', $expense->expense_type) === 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="business" {{ old('expense_type', $expense->expense_type) === 'business' ? 'selected' : '' }}>Business</option>
                        </select>
                        @error('expense_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description *</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $expense->description) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merchant -->
                <div>
                    <label for="merchant" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Merchant</label>
                    <input type="text" name="merchant" id="merchant" value="{{ old('merchant', $expense->merchant) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('merchant')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $expense->location) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tags -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                    <input type="text" name="tags" id="tags" value="{{ old('tags', is_array($expense->tags) ? implode(', ', $expense->tags) : $expense->tags) }}" placeholder="Comma-separated tags"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Separate multiple tags with commas</p>
                    @error('tags')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $expense->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Budget Allocated -->
                <div>
                    <label for="budget_allocated" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget Allocated</label>
                    <input type="number" name="budget_allocated" id="budget_allocated" step="0.01" min="0" value="{{ old('budget_allocated', $expense->budget_allocated) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optional budget amount for this expense</p>
                    @error('budget_allocated')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="pending" {{ old('status', $expense->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reimbursed" {{ old('status', $expense->status) === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                        <option value="approved" {{ old('status', $expense->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status', $expense->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Checkboxes -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_tax_deductible" id="is_tax_deductible" value="1" {{ old('is_tax_deductible', $expense->is_tax_deductible) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_tax_deductible" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            Tax Deductible
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_recurring" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            Recurring Expense
                        </label>
                    </div>
                </div>

                <!-- Recurring Schedule (conditionally shown) -->
                <div id="recurring-schedule" class="{{ $expense->is_recurring ? '' : 'hidden' }}">
                    <label for="recurring_schedule" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recurring Schedule</label>
                    <select name="recurring_schedule" id="recurring_schedule" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="weekly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    @error('recurring_schedule')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('expenses.show', $expense) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('is_recurring').addEventListener('change', function() {
            const recurringSchedule = document.getElementById('recurring-schedule');
            if (this.checked) {
                recurringSchedule.classList.remove('hidden');
            } else {
                recurringSchedule.classList.add('hidden');
            }
        });
    </script>
@endsection
