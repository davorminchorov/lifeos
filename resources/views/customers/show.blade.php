@extends('layouts.app')

@section('title', $customer->name . ' - Customer')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    {{ $customer->name }}
                </h1>
                @if($customer->company_name)
                    <p class="text-lg text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                        {{ $customer->company_name }}
                    </p>
                @endif
            </div>
            <div class="flex gap-3">
                <x-button href="{{ route('invoicing.customers.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </x-button>
                <x-button href="{{ route('invoicing.customers.edit', $customer) }}" variant="primary">
                    Edit Customer
                </x-button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Outstanding Balance -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Outstanding Balance
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-red-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($customer->outstanding_balance / 100, $customer->currency) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit Balance -->
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
                                    Credit Balance
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-green-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($customer->credit_balance / 100, $customer->currency) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Customer Details -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Customer Details
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <dl class="space-y-3">
                        <div class="flex justify-between py-2">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Email
                            </dt>
                            <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $customer->email ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between py-2">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Phone
                            </dt>
                            <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $customer->phone ?? '—' }}
                            </dd>
                        </div>

                        <div class="flex justify-between py-2">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Default Currency
                            </dt>
                            <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $customer->currency }}
                            </dd>
                        </div>

                        @if($customer->tax_id)
                            <div class="flex justify-between py-2">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Tax ID
                                </dt>
                                <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $customer->tax_id }}
                                </dd>
                            </div>
                        @endif

                        @if($customer->tax_country)
                            <div class="flex justify-between py-2">
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    Tax Country
                                </dt>
                                <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                    {{ $customer->tax_country }}
                                </dd>
                            </div>
                        @endif

                        <div class="flex justify-between py-2">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                Created
                            </dt>
                            <dd class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                {{ $customer->created_at->format('M d, Y') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Billing Address
                    </h2>
                </div>
                <div class="px-6 py-4">
                    @if($customer->billing_address)
                        <address class="not-italic text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] space-y-1">
                            @if(!empty($customer->billing_address['street']))
                                <div>{{ $customer->billing_address['street'] }}</div>
                            @endif
                            <div>
                                @if(!empty($customer->billing_address['city']))
                                    {{ $customer->billing_address['city'] }}@if(!empty($customer->billing_address['state'])),@endif
                                @endif
                                @if(!empty($customer->billing_address['state']))
                                    {{ $customer->billing_address['state'] }}
                                @endif
                                @if(!empty($customer->billing_address['postal_code']))
                                    {{ $customer->billing_address['postal_code'] }}
                                @endif
                            </div>
                            @if(!empty($customer->billing_address['country']))
                                <div>{{ $customer->billing_address['country'] }}</div>
                            @endif
                        </address>
                    @else
                        <p class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            No billing address provided
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($customer->notes)
            <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Internal Notes
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $customer->notes }}</p>
                </div>
            </div>
        @endif

        <!-- Recent Invoices -->
        <div class="mt-8 bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
            <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Recent Invoices
                </h2>
                <x-button href="{{ route('invoicing.invoices.create', ['customer_id' => $customer->id]) }}" variant="primary" size="sm">
                    Create Invoice
                </x-button>
            </div>
            <div class="px-6 py-4">
                @if($customer->invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Number
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Status
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Total
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Date
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                                @foreach($customer->invoices as $invoice)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $invoice->number ?? 'Draft' }}
                                        </td>
                                        <td class="px-3 py-3 text-sm">
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-[color:var(--color-{{ $invoice->status->color() }}-50)] text-[color:var(--color-{{ $invoice->status->color() }}-600)]">
                                                {{ $invoice->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $invoice->formatted_total }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                            {{ $invoice->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right">
                                            <x-button href="{{ route('invoicing.invoices.show', $invoice) }}" variant="secondary" size="sm">
                                                View
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        No invoices yet
                    </p>
                @endif
            </div>
        </div>

        <!-- Delete Customer -->
        <div class="mt-8">
            <form method="POST" action="{{ route('invoicing.customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="secondary" class="text-red-600 hover:text-red-700">
                    Delete Customer
                </x-button>
            </form>
        </div>
    </div>
</div>
@endsection
