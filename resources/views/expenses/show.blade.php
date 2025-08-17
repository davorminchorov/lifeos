@extends('layouts.app')

@section('title', 'Expense Details - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Expense Details
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ $expense->description }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('expenses.edit', $expense) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Edit
            </a>
            <a href="{{ route('expenses.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Back to Expenses
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{}">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Expense Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Details about this expense.</p>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700">
                    <dl>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->description }}</dd>
                        </div>
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <span class="text-2xl font-bold">${{ number_format($expense->amount, 2) }}</span>
                                @if($expense->currency !== 'USD')
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $expense->currency }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->expense_date->format('F j, Y') }}</dd>
                        </div>
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $expense->category }}
                                </span>
                            </dd>
                        </div>
                        @if($expense->subcategory)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Subcategory</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->subcategory }}</dd>
                        </div>
                        @endif
                        @if($expense->merchant)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Merchant</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->merchant }}</dd>
                        </div>
                        @endif
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Payment Method</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</dd>
                        </div>
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ ucfirst($expense->expense_type) }}</dd>
                        </div>
                        @if($expense->location)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Location</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->location }}</dd>
                        </div>
                        @endif
                        @if($expense->tags)
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Tags</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($expense->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                        @endif
                        @if($expense->notes)
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white sm:mt-0 sm:col-span-2">{{ $expense->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status</h3>

                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Reimbursement Status</span>
                        <div class="mt-1">
                            @if($expense->is_reimbursed)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Reimbursed
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Pending
                                </span>
                                <div class="mt-2">
                                    <form method="POST" action="{{ route('expenses.mark-reimbursed', $expense) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Mark as Reimbursed
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($expense->is_tax_deductible)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Tax Status</span>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Tax Deductible
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($expense->is_recurring)
                    <div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Recurring</span>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Recurring Expense
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('expenses.edit', $expense) }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                        Edit Expense
                    </a>

                    <button type="button"
                            class="w-full bg-[color:var(--color-danger-600)] hover:bg-[color:var(--color-danger-700)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                            x-on:click="$dispatch('open-modal', { id: 'deleteExpenseModal' })">
                        Delete Expense
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Stats</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Age:</span>
                        <span class="text-gray-900 dark:text-white">{{ $expense->age_days }} days</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Created:</span>
                        <span class="text-gray-900 dark:text-white">{{ $expense->created_at->format('M j, Y') }}</span>
                    </div>

                    @if($expense->updated_at->ne($expense->created_at))
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Updated:</span>
                        <span class="text-gray-900 dark:text-white">{{ $expense->updated_at->format('M j, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <x-confirmation-modal
        id="deleteExpenseModal"
        title="Delete Expense"
        message="Are you sure you want to delete the expense '{{ $expense->description }}'? This action cannot be undone."
        confirm-text="Delete"
        confirm-button-class="bg-[color:var(--color-danger-500)] hover:bg-[color:var(--color-danger-600)] text-white"
        :action="route('expenses.destroy', $expense)"
        method="DELETE"
    />
@endsection
