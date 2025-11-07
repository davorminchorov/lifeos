@extends('layouts.app')

@section('title', 'Expense Details - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Expense Details
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $expense->description }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('expenses.edit', $expense) }}" class="bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Edit
            </a>
            <a href="{{ route('expenses.index') }}" class="bg-[color:var(--color-primary-500)] hover:bg-[color:var(--color-primary-600)] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Back to Expenses
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{}">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Expense Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Details about this expense.</p>
                </div>
                <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <dl>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Description</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->description }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Amount</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <span class="text-2xl font-bold">{{ $expense->formatted_amount }}</span>
                            </dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->expense_date->format('F j, Y') }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Category</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-info-200)] text-[color:var(--color-info-800)] dark:bg-[color:var(--color-info-900)] dark:text-[color:var(--color-info-200)]">
                                    {{ $expense->category }}
                                </span>
                            </dd>
                        </div>
                        @if($expense->subcategory)
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Subcategory</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->subcategory }}</dd>
                        </div>
                        @endif
                        @if($expense->merchant)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Merchant</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->merchant }}</dd>
                        </div>
                        @endif
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Payment Method</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Type</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ ucfirst($expense->expense_type) }}</dd>
                        </div>
                        @if($expense->location)
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Location</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->location }}</dd>
                        </div>
                        @endif
                        @if($expense->tags)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Tags</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($expense->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[color:var(--color-primary-300)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                            {{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                        @endif
                        @if($expense->notes)
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Notes</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $expense->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Status</h3>

                <div class="space-y-4">
                    <div>
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Reimbursement Status</span>
                        <div class="mt-1">
                            @if($expense->is_reimbursed)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-success-50)] text-[color:var(--color-success-600)] dark:bg-[color:var(--color-success-900)] dark:text-[color:var(--color-success-200)]">
                                    Reimbursed
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-primary-200)] text-[color:var(--color-primary-700)] dark:bg-[color:var(--color-dark-300)] dark:text-[color:var(--color-dark-600)]">
                                    Pending
                                </span>
                                <div class="mt-2">
                                    <form method="POST" action="{{ route('expenses.mark-reimbursed', $expense) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-sm text-[color:var(--color-success-600)] hover:text-[color:var(--color-success-700)] dark:text-[color:var(--color-success-400)] dark:hover:text-[color:var(--color-success-300)]">
                                            Mark as Reimbursed
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($expense->is_tax_deductible)
                    <div>
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Tax Status</span>
                        <div class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-warning-900)] dark:text-[color:var(--color-warning-200)]">
                                Tax Deductible
                            </span>
                        </div>
                    </div>
                    @endif

                    @if($expense->is_recurring)
                    <div>
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Recurring</span>
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
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('expenses.edit', $expense) }}" class="w-full bg-[color:var(--color-accent-500)] hover:bg-[color:var(--color-accent-600)] text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
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
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Quick Stats</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Age:</span>
                        <span class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $expense->age_days }} days</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Created:</span>
                        <span class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $expense->created_at->format('M j, Y') }}</span>
                    </div>

                    @if($expense->updated_at->ne($expense->created_at))
                    <div class="flex justify-between">
                        <span class="text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Updated:</span>
                        <span class="text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $expense->updated_at->format('M j, Y') }}</span>
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
