@extends('layouts.app')

@section('title', 'Edit Expense - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Edit Expense
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Update expense details
            </p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('expenses.show', $expense) }}" variant="secondary">View Expense</x-button>
            <x-button href="{{ route('expenses.index') }}" variant="secondary">Back to Expenses</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Amount -->
                    <div>
                        <x-form.input
                            type="number"
                            name="amount"
                            label="Amount"
                            prefix="$"
                            :required="true"
                            min="0"
                            step="0.01"
                            :value="old('amount', $expense->amount)"
                            inputClass="@error('amount') border-danger-400 @enderror"
                        />
                    </div>

                    <!-- Currency -->
                    <div>
                        <x-form.select name="currency" label="Currency" :required="true" selectClass="@error('currency') border-danger-400 @enderror">
                            <option value="MKD" {{ old('currency', $expense->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="USD" {{ old('currency', $expense->currency) === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                            <option value="EUR" {{ old('currency', $expense->currency) === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            <option value="GBP" {{ old('currency', $expense->currency) === 'GBP' ? 'selected' : '' }}>GBP (£) - British Pound</option>
                            <option value="CAD" {{ old('currency', $expense->currency) === 'CAD' ? 'selected' : '' }}>CAD (C$) - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $expense->currency) === 'AUD' ? 'selected' : '' }}>AUD (A$) - Australian Dollar</option>
                            <option value="JPY" {{ old('currency', $expense->currency) === 'JPY' ? 'selected' : '' }}>JPY (¥) - Japanese Yen</option>
                            <option value="CHF" {{ old('currency', $expense->currency) === 'CHF' ? 'selected' : '' }}>CHF (CHF) - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $expense->currency) === 'RSD' ? 'selected' : '' }}>RSD (RSD) - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $expense->currency) === 'BGN' ? 'selected' : '' }}>BGN (лв) - Bulgarian Lev</option>
                        </x-form.select>
                    </div>

                    <!-- Expense Date -->
                    <div>
                        <x-form.input
                            type="date"
                            name="expense_date"
                            label="Date"
                            :required="true"
                            :value="old('expense_date', $expense->expense_date->format('Y-m-d'))"
                            inputClass="@error('expense_date') border-danger-400 @enderror"
                        />
                    </div>

                    <!-- Category -->
                    <div>
                        <x-form.select name="category" label="Category" :required="true" placeholder="Select Category" selectClass="@error('category') border-danger-400 @enderror">
                            <option value="Food & Dining" {{ old('category', $expense->category) === 'Food & Dining' ? 'selected' : '' }}>Food & Dining</option>
                            <option value="Transportation" {{ old('category', $expense->category) === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                            <option value="Shopping" {{ old('category', $expense->category) === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                            <option value="Entertainment" {{ old('category', $expense->category) === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                            <option value="Bills & Utilities" {{ old('category', $expense->category) === 'Bills & Utilities' ? 'selected' : '' }}>Bills & Utilities</option>
                            <option value="Healthcare" {{ old('category', $expense->category) === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                            <option value="Travel" {{ old('category', $expense->category) === 'Travel' ? 'selected' : '' }}>Travel</option>
                            <option value="Other" {{ old('category', $expense->category) === 'Other' ? 'selected' : '' }}>Other</option>
                        </x-form.select>
                    </div>

                    <!-- Subcategory -->
                    <div>
                        <x-form.input name="subcategory" label="Subcategory" :value="old('subcategory', $expense->subcategory)" inputClass="@error('subcategory') border-danger-400 @enderror" />
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <x-form.select name="payment_method" label="Payment Method" placeholder="Select Payment Method" selectClass="@error('payment_method') border-danger-400 @enderror">
                            <option value="cash" {{ old('payment_method', $expense->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ old('payment_method', $expense->payment_method) === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="debit_card" {{ old('payment_method', $expense->payment_method) === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="digital_wallet" {{ old('payment_method', $expense->payment_method) === 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
                        </x-form.select>
                    </div>

                    <!-- Expense Type -->
                    <div>
                        <x-form.select name="expense_type" label="Type" selectClass="@error('expense_type') border-danger-400 @enderror">
                            <option value="personal" {{ old('expense_type', $expense->expense_type) === 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="business" {{ old('expense_type', $expense->expense_type) === 'business' ? 'selected' : '' }}>Business</option>
                        </x-form.select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <x-form.input name="description" label="Description" :required="true" :value="old('description', $expense->description)" inputClass="@error('description') border-danger-400 @enderror" />
                </div>

                <!-- Merchant -->
                <div>
                    <x-form.input name="merchant" label="Merchant" :value="old('merchant', $expense->merchant)" inputClass="@error('merchant') border-danger-400 @enderror" />
                </div>

                <!-- Location -->
                <div>
                    <x-form.input name="location" label="Location" :value="old('location', $expense->location)" inputClass="@error('location') border-danger-400 @enderror" />
                </div>

                <!-- Tags -->
                <div>
                    <x-form.input name="tags" label="Tags" :value="old('tags', is_array($expense->tags) ? implode(', ', $expense->tags) : $expense->tags)" placeholder="Comma-separated tags" helpText="Separate multiple tags with commas" inputClass="@error('tags') border-danger-400 @enderror" />
                </div>

                <!-- Notes -->
                <div>
                    <x-form.input type="textarea" name="notes" label="Notes" rows="3" :value="old('notes', $expense->notes)" inputClass="@error('notes') border-danger-400 @enderror" />
                </div>

                <!-- Budget Allocated -->
                <div>
                    <x-form.input type="number" name="budget_allocated" label="Budget Allocated" step="0.01" min="0" :value="old('budget_allocated', $expense->budget_allocated)" helpText="Optional budget amount for this expense" inputClass="@error('budget_allocated') border-danger-400 @enderror" />
                </div>

                <!-- Status -->
                <div>
                    <x-form.select name="status" label="Status" selectClass="@error('status') border-danger-400 @enderror">
                        <option value="pending" {{ old('status', $expense->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reimbursed" {{ old('status', $expense->status) === 'reimbursed' ? 'selected' : '' }}>Reimbursed</option>
                        <option value="approved" {{ old('status', $expense->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('status', $expense->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </x-form.select>
                </div>

                <!-- Checkboxes -->
                <div class="space-y-4">
                    <x-form.checkbox name="is_tax_deductible" label="Tax Deductible" :checked="old('is_tax_deductible', $expense->is_tax_deductible)" />
                    <x-form.checkbox name="is_recurring" label="Recurring Expense" :checked="old('is_recurring', $expense->is_recurring)" />
                </div>

                <!-- Recurring Schedule (conditionally shown) -->
                <div id="recurring-schedule" class="{{ $expense->is_recurring ? '' : 'hidden' }}">
                    <x-form.select name="recurring_schedule" label="Recurring Schedule">
                        <option value="weekly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('recurring_schedule', $expense->recurring_schedule) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </x-form.select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <x-button href="{{ route('expenses.show', $expense) }}" variant="secondary">Cancel</x-button>
                    <x-button type="submit" variant="primary">Update Expense</x-button>
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
