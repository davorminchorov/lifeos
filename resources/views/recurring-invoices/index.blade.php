@extends('layouts.app')

@section('title', 'Recurring Invoices')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Recurring Invoices
                </h1>
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                    Manage subscription billing and automated invoices
                </p>
            </div>
            <x-button href="{{ route('invoicing.recurring-invoices.create') }}" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Recurring Invoice
            </x-button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total</p>
                        <p class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mt-2">
                            {{ $summary['total'] }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Active</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
                            {{ $summary['active'] }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-full">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Paused</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-2">
                            {{ $summary['paused'] }}
                        </p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-6 mb-6">
            <form method="GET" action="{{ route('invoicing.recurring-invoices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name..."
                           class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Status</label>
                    <select name="status" class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Customer</label>
                    <select name="customer_id" class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-300)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-button type="submit" variant="secondary" class="flex-1">
                        Filter
                    </x-button>
                    <x-button href="{{ route('invoicing.recurring-invoices.index') }}" variant="secondary">
                        Clear
                    </x-button>
                </div>
            </form>
        </div>

        <!-- Recurring Invoices Table -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                    <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Interval</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Next Billing</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        @forelse($recurringInvoices as $recurringInvoice)
                            <tr class="hover:bg-[color:var(--color-primary-50)] dark:hover:bg-[color:var(--color-dark-300)]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('invoicing.recurring-invoices.show', $recurringInvoice) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ $recurringInvoice->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $recurringInvoice->customer->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    Every {{ $recurringInvoice->interval_count > 1 ? $recurringInvoice->interval_count : '' }} {{ $recurringInvoice->billing_interval->label() }}{{ $recurringInvoice->interval_count > 1 ? 's' : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $recurringInvoice->next_billing_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full bg-[color:var(--color-{{ $recurringInvoice->status->color() }}-50)] text-[color:var(--color-{{ $recurringInvoice->status->color() }}-600)]">
                                        {{ $recurringInvoice->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $recurringInvoice->occurrences_count }}{{ $recurringInvoice->occurrences_limit ? '/' . $recurringInvoice->occurrences_limit : '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <x-button href="{{ route('invoicing.recurring-invoices.show', $recurringInvoice) }}" variant="secondary" size="sm">
                                        View
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No recurring invoices found</p>
                                    <p class="text-sm mt-1">Create your first recurring invoice to start automated billing</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recurringInvoices->hasPages())
                <div class="px-6 py-4 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    {{ $recurringInvoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
