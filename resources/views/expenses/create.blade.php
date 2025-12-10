@extends('layouts.app')

@section('title', 'Add Expense - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Add Expense</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Record a new expense</p>
            </div>
            <x-button href="{{ route('expenses.index') }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Expenses
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-6 p-6">
                @csrf

                <!-- Basic Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-form.input
                            type="number"
                            name="amount"
                            label="Amount"
                            prefix="$"
                            :required="true"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            inputClass="@error('amount') border-danger-400 @enderror"
                        />
                    </div>

                    <div>
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
                    </div>

                    <div>
                        <x-form.input
                            type="date"
                            name="expense_date"
                            label="Date"
                            :required="true"
                            :value="old('expense_date', date('Y-m-d'))"
                            inputClass="@error('expense_date') border-danger-400 @enderror"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-form.select name="category" label="Category" :required="true" placeholder="Select Category" selectClass="@error('category') border-danger-400 @enderror">
                            <option value="Food & Dining" {{ old('category') === 'Food & Dining' ? 'selected' : '' }}>Food & Dining</option>
                            <option value="Transportation" {{ old('category') === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                            <option value="Shopping" {{ old('category') === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                            <option value="Entertainment" {{ old('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                            <option value="Bills & Utilities" {{ old('category') === 'Bills & Utilities' ? 'selected' : '' }}>Bills & Utilities</option>
                            <option value="Healthcare" {{ old('category') === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                            <option value="Travel" {{ old('category') === 'Travel' ? 'selected' : '' }}>Travel</option>
                            <option value="Other" {{ old('category') === 'Other' ? 'selected' : '' }}>Other</option>
                        </x-form.select>
                    </div>

                    <div>
                        <x-form.select name="payment_method" label="Payment Method" placeholder="Select Payment Method" selectClass="@error('payment_method') border-danger-400 @enderror">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="digital_wallet" {{ old('payment_method') === 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
                        </x-form.select>
                    </div>

                    <div>
                        <x-form.select name="expense_type" label="Type" placeholder="Select Type" selectClass="@error('expense_type') border-danger-400 @enderror">
                            <option value="personal" {{ old('expense_type') === 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="business" {{ old('expense_type') === 'business' ? 'selected' : '' }}>Business</option>
                        </x-form.select>
                    </div>
                </div>

                <div>
                    <x-form.input
                        name="description"
                        label="Description"
                        :required="true"
                        placeholder="Brief description of the expense"
                        inputClass="@error('description') border-danger-400 @enderror"
                    />
                </div>

                <!-- Additional Information -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.input
                                name="merchant"
                                label="Merchant"
                                placeholder="Store or company name"
                                inputClass="@error('merchant') border-danger-400 @enderror"
                            />
                        </div>

                        <div>
                            <x-form.input
                                name="location"
                                label="Location"
                                placeholder="City, address, or general location"
                                inputClass="@error('location') border-danger-400 @enderror"
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-form.input
                            name="tags"
                            label="Tags"
                            placeholder="Comma-separated tags"
                            helpText="Separate multiple tags with commas"
                            inputClass="@error('tags') border-danger-400 @enderror"
                        />
                    </div>

                    <div class="mt-4">
                        <x-form.input
                            type="textarea"
                            name="notes"
                            label="Notes"
                            rows="3"
                            placeholder="Any additional notes about this expense"
                            inputClass="@error('notes') border-danger-400 @enderror"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <x-form.checkbox name="is_tax_deductible" label="Tax Deductible" :checked="old('is_tax_deductible', false)" />
                        <x-form.checkbox name="is_recurring" label="Recurring Expense" :checked="old('is_recurring', false)" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] pt-6">
                    <div class="flex justify-end space-x-3">
                        <x-button href="{{ route('expenses.index') }}" variant="secondary">Cancel</x-button>
                        <x-button type="submit" variant="primary">Create Expense</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
