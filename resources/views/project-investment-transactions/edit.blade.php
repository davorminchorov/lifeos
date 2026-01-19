@extends('layouts.app')

@section('title', 'Edit Investment - ' . $projectInvestment->name . ' - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Edit Investment</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Update investment for {{ $projectInvestment->name }}</p>
            </div>
            <x-button href="{{ route('project-investments.show', $projectInvestment) }}" variant="secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Project
            </x-button>
        </div>

        <!-- Form -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <form method="POST" action="{{ route('project-investment-transactions.update', $transaction) }}" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Amount *</label>
                        <input type="number" step="0.01" name="amount" id="amount" required min="0.01"
                               value="{{ old('amount', $transaction->amount) }}"
                               class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                               placeholder="0.00">
                        @error('amount')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Currency *</label>
                        <select name="currency" id="currency" required
                                class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            <option value="USD" {{ old('currency', $transaction->currency) === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="EUR" {{ old('currency', $transaction->currency) === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="GBP" {{ old('currency', $transaction->currency) === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                            <option value="MKD" {{ old('currency', $transaction->currency) === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                            <option value="CAD" {{ old('currency', $transaction->currency) === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                            <option value="AUD" {{ old('currency', $transaction->currency) === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                            <option value="JPY" {{ old('currency', $transaction->currency) === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                            <option value="CHF" {{ old('currency', $transaction->currency) === 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                            <option value="RSD" {{ old('currency', $transaction->currency) === 'RSD' ? 'selected' : '' }}>RSD - Serbian Dinar</option>
                            <option value="BGN" {{ old('currency', $transaction->currency) === 'BGN' ? 'selected' : '' }}>BGN - Bulgarian Lev</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Investment Date *</label>
                    <input type="date" name="transaction_date" id="transaction_date" required
                           value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                           max="{{ date('Y-m-d') }}"
                           class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    @error('transaction_date')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Notes</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]"
                              placeholder="Add any notes about this investment...">{{ old('notes', $transaction->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-[color:var(--color-danger-600)] dark:text-[color:var(--color-danger-400)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <form method="POST" action="{{ route('project-investment-transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[color:var(--color-danger-600)] hover:text-[color:var(--color-danger-700)] dark:text-[color:var(--color-danger-400)] dark:hover:text-[color:var(--color-danger-300)] font-medium">
                            Delete Transaction
                        </button>
                    </form>

                    <div class="flex gap-3">
                        <x-button href="{{ route('project-investments.show', $projectInvestment) }}" variant="secondary">
                            Cancel
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Update Investment
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
