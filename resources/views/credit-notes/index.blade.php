@extends('layouts.app')

@section('title', 'Credit Notes - Invoicing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Credit Notes</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Manage credit notes and refunds</p>
            </div>
            <x-button href="{{ route('invoicing.credit-notes.create') }}" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Credit Note
            </x-button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Total Credit Notes -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-[color:var(--color-accent-500)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Total Credit Notes
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ number_format($summary['total_credit_notes']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Total Amount
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-blue-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($summary['total_amount'] / 100, 'MKD') }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Credit -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Available Credit
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-green-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($summary['available_credit'] / 100, 'MKD') }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-6">
            <form method="GET" action="{{ route('invoicing.credit-notes.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-1">
                            Search
                        </label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Credit note number..."
                               class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                    </div>

                    <!-- Customer Filter -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-1">
                            Customer
                        </label>
                        <select name="customer_id" id="customer_id"
                                class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-1">
                            Status
                        </label>
                        <select name="status" id="status"
                                class="w-full rounded-md border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] focus:border-[color:var(--color-accent-500)] focus:ring-[color:var(--color-accent-500)]">
                            <option value="">All Statuses</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="applied" {{ request('status') === 'applied' ? 'selected' : '' }}>Applied</option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <div class="flex items-end">
                        <x-button type="submit" variant="secondary" class="w-full">
                            Filter
                        </x-button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Credit Notes Table -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            @if($creditNotes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        <thead class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Invoice
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Remaining
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            @foreach($creditNotes as $creditNote)
                                <tr class="hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)] transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $creditNote->number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $creditNote->customer->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        @if($creditNote->invoice)
                                            <a href="{{ route('invoicing.invoices.show', $creditNote->invoice) }}" class="text-[color:var(--color-accent-500)] hover:underline">
                                                {{ $creditNote->invoice->number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex px-2 py-1 text-xs rounded-full bg-[color:var(--color-{{ $creditNote->status->color() }}-50)] text-[color:var(--color-{{ $creditNote->status->color() }}-600)]">
                                            {{ $creditNote->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ app(\App\Services\CurrencyService::class)->format($creditNote->total / 100, $creditNote->currency) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($creditNote->remaining_amount / 100, $creditNote->currency) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $creditNote->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                        <x-button href="{{ route('invoicing.credit-notes.show', $creditNote) }}" variant="secondary" size="sm">
                                            View
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    {{ $creditNotes->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">No credit notes found</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        Get started by creating a new credit note.
                    </p>
                    <div class="mt-6">
                        <x-button href="{{ route('invoicing.credit-notes.create') }}" variant="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Credit Note
                        </x-button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
