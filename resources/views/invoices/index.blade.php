@extends('layouts.app')

@section('title', 'Invoices - Invoicing')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Invoices
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Create and manage your invoices
            </p>
        </div>
        <div class="flex-shrink-0">
            <x-button href="{{ route('invoicing.invoices.create') }}" variant="primary" class="w-full sm:w-auto">
                Create Invoice
            </x-button>
        </div>
    </div>
@endsection

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Total
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $summary['total_invoices'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Drafts
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $summary['draft_count'] }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Outstanding
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-yellow-600">
                                    {{ app(\App\Services\CurrencyService::class)->format($summary['total_outstanding'] / 100) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                Overdue
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-red-600">
                                    {{ app(\App\Services\CurrencyService::class)->format($summary['total_overdue'] / 100) }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-4">
        <form method="GET" action="{{ route('invoicing.invoices.index') }}" class="flex flex-wrap gap-2 flex-1">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by invoice number..."
                class="flex-1 min-w-[200px] rounded-lg border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]"
            >
            <select name="status" class="rounded-lg border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="past_due" {{ request('status') === 'past_due' ? 'selected' : '' }}>Past Due</option>
            </select>
            <select name="customer_id" class="rounded-lg border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-400)] bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)]">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">
                Filter
            </x-button>
            @if(request()->hasAny(['search', 'status', 'customer_id']))
                <x-button href="{{ route('invoicing.invoices.index') }}" variant="secondary">
                    Clear
                </x-button>
            @endif
        </form>
    </div>

    <!-- Invoices List -->
    <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                    <thead class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Number
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Due Date
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ $invoice->number ?? 'Draft' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $invoice->customer->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs rounded-full bg-[color:var(--color-{{ $invoice->status->color() }}-50)] text-[color:var(--color-{{ $invoice->status->color() }}-600)]">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $invoice->formatted_total }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                        {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : '—' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <x-button href="{{ route('invoicing.invoices.show', $invoice) }}" variant="secondary" size="sm">
                                        View
                                    </x-button>
                                    @if($invoice->status->value === 'draft')
                                        <x-button href="{{ route('invoicing.invoices.edit', $invoice) }}" variant="secondary" size="sm">
                                            Edit
                                        </x-button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                {{ $invoices->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    No invoices found
                </h3>
                <p class="mt-1 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                    Get started by creating a new invoice.
                </p>
                <div class="mt-6">
                    <x-button href="{{ route('invoicing.invoices.create') }}" variant="primary">
                        Create Invoice
                    </x-button>
                </div>
            </div>
        @endif
    </div>
@endsection
