@extends('layouts.app')

@section('title', 'Expenses - LifeOS')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Expenses
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Track your spending and manage your budget
            </p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('expenses.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-6 py-3 sm:px-4 sm:py-2 rounded-lg sm:rounded-md text-base sm:text-sm font-medium transition-colors duration-200 shadow-sm touch-manipulation">
                Add Expense
            </a>
        </div>
    </div>
@endsection

@section('content')
    <!-- Filters and Search -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg mb-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="px-4 py-5 sm:px-6">
            <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <x-form.input
                        name="search"
                        label="Search"
                        type="text"
                        placeholder="Search expenses..."
                    />
                </div>

                <!-- Category Filter -->
                <div>
                    <x-form.select
                        name="category"
                        label="Category"
                        placeholder="All Categories"
                    >
                        <option value="Food & Dining" {{ request('category') === 'Food & Dining' ? 'selected' : '' }}>Food & Dining</option>
                        <option value="Transportation" {{ request('category') === 'Transportation' ? 'selected' : '' }}>Transportation</option>
                        <option value="Shopping" {{ request('category') === 'Shopping' ? 'selected' : '' }}>Shopping</option>
                        <option value="Entertainment" {{ request('category') === 'Entertainment' ? 'selected' : '' }}>Entertainment</option>
                        <option value="Bills & Utilities" {{ request('category') === 'Bills & Utilities' ? 'selected' : '' }}>Bills & Utilities</option>
                        <option value="Healthcare" {{ request('category') === 'Healthcare' ? 'selected' : '' }}>Healthcare</option>
                        <option value="Travel" {{ request('category') === 'Travel' ? 'selected' : '' }}>Travel</option>
                        <option value="Other" {{ request('category') === 'Other' ? 'selected' : '' }}>Other</option>
                    </x-form.select>
                </div>

                <!-- Payment Method Filter -->
                <div>
                    <x-form.select
                        name="payment_method"
                        label="Payment Method"
                        placeholder="All Methods"
                    >
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="debit_card" {{ request('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="digital_wallet" {{ request('payment_method') === 'digital_wallet' ? 'selected' : '' }}>Digital Wallet</option>
                    </x-form.select>
                </div>

                <!-- Date Range -->
                <div>
                    <x-form.input
                        name="date_from"
                        label="Date From"
                        type="date"
                    />
                </div>

                <div>
                    <x-form.input
                        name="date_to"
                        label="Date To"
                        type="date"
                    />
                </div>

                <div class="col-span-full">
                    <button type="submit" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 shadow-sm">
                        Apply Filters
                    </button>
                    <a href="{{ route('expenses.index') }}" class="ml-2 bg-[color:var(--color-primary-200)] hover:bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:hover:bg-[color:var(--color-dark-400)] dark:text-[color:var(--color-dark-600)] px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]" x-data="{}">
        <div class="px-4 py-5 sm:p-6">
            @if($expenses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-300)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($expenses as $expense)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $expense->expense_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $expense->description }}
                                        </div>
                                        @if($expense->merchant)
                                            <div class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                                {{ $expense->merchant }}
                                            </div>
                                        @endif
                                        @if($expense->tags)
                                            <div class="mt-1">
                                                @foreach($expense->tags as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)] mr-1">
                                                        {{ trim($tag) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-50)] text-[color:var(--color-info-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-info-500)]">
                                            {{ $expense->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $expense->formatted_amount_mkd }}
                                        @if($expense->currency && $expense->currency !== config('currency.default', 'MKD'))
                                            <span class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] ml-1">
                                                ({{ $expense->formatted_amount }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($expense->is_reimbursed)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-success-500)]">
                                                Reimbursed
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                                Pending
                                            </span>
                                        @endif
                                        @if($expense->is_tax_deductible)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-warning-500)] ml-1">
                                                Tax Deductible
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('expenses.show', $expense) }}" class="text-[color:var(--color-accent-600)] hover:text-[color:var(--color-accent-700)]">View</a>
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-[color:var(--color-warning-600)] hover:text-[color:var(--color-warning-700)]">Edit</a>
                                            @if(!$expense->is_reimbursed)
                                                <form method="POST" action="{{ route('expenses.mark-reimbursed', $expense) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-[color:var(--color-success-600)] hover:text-[color:var(--color-success-700)]">
                                                        Mark Reimbursed
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button"
                                                    class="text-[color:var(--color-danger-600)] hover:text-[color:var(--color-danger-700)]"
                                                    x-on:click="$dispatch('open-modal', { id: 'deleteExpenseModal-{{ $expense->id }}' })">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $expenses->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)] mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">No expenses found</h3>
                    <p class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] mb-4">Get started by adding your first expense.</p>
                    <a href="{{ route('expenses.create') }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        Add Expense
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modals for each expense -->
    @foreach($expenses as $expense)
        <x-confirmation-modal
            id="deleteExpenseModal-{{ $expense->id }}"
            title="Delete Expense"
            message="Are you sure you want to delete the expense '{{ $expense->description }}'? This action cannot be undone."
            confirm-text="Delete"
            confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
            :action="route('expenses.destroy', $expense)"
            method="DELETE"
        />
    @endforeach
@endsection
