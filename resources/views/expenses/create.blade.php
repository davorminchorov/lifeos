@extends('layouts.app')

@section('title', 'Add Expense - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Add Expense
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Record a new expense
            </p>
        </div>
        <a href="{{ route('expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Back to Expenses
        </a>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-6">
        @csrf

        <!-- Basic Details -->
        <x-form.section title="Basic Details" description="Record the basic information about this expense.">
            <x-form.input
                name="amount"
                label="Amount"
                type="number"
                required
                step="0.01"
                min="0"
                placeholder="0.00"
            />

            <x-form.select
                name="currency"
                label="Currency"
            >
                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD</option>
            </x-form.select>

            <x-form.input
                name="expense_date"
                label="Date"
                type="date"
                required
                :value="old('expense_date', date('Y-m-d'))"
            />

            <x-form.select
                name="category"
                label="Category"
                required
                placeholder="Select Category"
            >
                <option value="Food & Dining" {{ old('category') === 'Food & Dining' ? 'selected' : '' }}>Food & Dining</option>
                <option value="Transportation" {{ old('category') === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                <option value="Shopping" {{ old('category') === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                <option value="Entertainment" {{ old('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                <option value="Bills & Utilities" {{ old('category') === 'Bills & Utilities' ? 'selected' : '' }}>Bills & Utilities</option>
                <option value="Healthcare" {{ old('category') === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                <option value="Travel" {{ old('category') === 'Travel' ? 'selected' : '' }}>Travel</option>
                <option value="Other" {{ old('category') === 'Other' ? 'selected' : '' }}>Other</option>
            </x-form.select>

            <x-form.select
                name="payment_method"
                label="Payment Method"
            >
                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="digital_wallet" {{ old('payment_method') === 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
            </x-form.select>

            <x-form.select
                name="expense_type"
                label="Type"
            >
                <option value="personal" {{ old('expense_type') === 'personal' ? 'selected' : '' }}>Personal</option>
                <option value="business" {{ old('expense_type') === 'business' ? 'selected' : '' }}>Business</option>
            </x-form.select>

            <div class="md:col-span-2">
                <x-form.input
                    name="description"
                    label="Description"
                    type="text"
                    required
                    placeholder="Brief description of the expense"
                />
            </div>
        </x-form.section>

        <!-- Additional Information -->
        <x-form.section title="Additional Information" description="Optional details about the expense." :grid="false">
            <x-form.input
                name="merchant"
                label="Merchant"
                type="text"
                placeholder="Store or company name"
            />

            <x-form.input
                name="location"
                label="Location"
                type="text"
                placeholder="City, address, or general location"
            />

            <x-form.input
                name="tags"
                label="Tags"
                type="text"
                placeholder="Comma-separated tags"
                helpText="Separate multiple tags with commas"
            />

            <x-form.input
                name="notes"
                label="Notes"
                type="textarea"
                rows="3"
                placeholder="Any additional notes about this expense"
            />

            <div class="space-y-4">
                <x-form.checkbox
                    name="is_tax_deductible"
                    label="Tax Deductible"
                    :checked="old('is_tax_deductible', false)"
                />

                <x-form.checkbox
                    name="is_recurring"
                    label="Recurring Expense"
                    :checked="old('is_recurring', false)"
                />
            </div>
        </x-form.section>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('expenses.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Create Expense
            </button>
        </div>
    </form>
@endsection
