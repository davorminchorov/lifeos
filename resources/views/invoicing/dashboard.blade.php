@extends('layouts.app')

@section('title', 'Invoicing Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Invoicing Dashboard</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">Overview of your invoicing activity</p>
            </div>
            <div class="flex gap-3">
                <x-button href="{{ route('invoicing.export.invoices') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export Invoices
                </x-button>
                <x-button href="{{ route('invoicing.export.payments') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export Payments
                </x-button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Total Revenue
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-green-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($summary['total_revenue'] / 100, 'MKD') }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outstanding Amount -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Outstanding
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-orange-600">
                                        {{ app(\App\Services\CurrencyService::class)->format($summary['outstanding_amount'] / 100, 'MKD') }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Invoices -->
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
                                    Total Invoices
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-blue-600">
                                        {{ number_format($summary['total_invoices']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] overflow-hidden shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] truncate">
                                    Customers
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-purple-600">
                                        {{ number_format($summary['total_customers']) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-5">
                <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Draft Invoices</div>
                <div class="text-2xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mt-1">{{ $summary['draft_invoices'] }}</div>
            </div>
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-5">
                <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Overdue Invoices</div>
                <div class="text-2xl font-bold text-red-600 mt-1">{{ $summary['overdue_invoices'] }}</div>
            </div>
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] p-5">
                <div class="text-sm font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Available Credit</div>
                <div class="text-2xl font-bold text-green-600 mt-1">{{ app(\App\Services\CurrencyService::class)->format($summary['available_credit'] / 100, 'MKD') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Trend -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Revenue Trend (Last 6 Months)
                    </h2>
                </div>
                <div class="p-6">
                    @if(count($revenueByMonth) > 0)
                        <div class="space-y-3">
                            @foreach($revenueByMonth as $month => $total)
                                @php
                                    $maxRevenue = max($revenueByMonth);
                                    $percentage = $maxRevenue > 0 ? ($total / $maxRevenue) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ \Carbon\Carbon::parse($month . '-01')->format('M Y') }}</span>
                                        <span class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ app(\App\Services\CurrencyService::class)->format($total / 100, 'MKD') }}</span>
                                    </div>
                                    <div class="w-full bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No revenue data available</p>
                    @endif
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Top Customers by Revenue
                    </h2>
                </div>
                <div class="p-6">
                    @if($topCustomers->count() > 0)
                        <div class="space-y-4">
                            @foreach($topCustomers as $customer)
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $customer->name }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $customer->email ?? 'No email' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">{{ app(\App\Services\CurrencyService::class)->format(($customer->total_revenue ?? 0) / 100, $customer->currency) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No customer data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Invoices -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Recent Invoices
                    </h2>
                    <x-button href="{{ route('invoicing.invoices.index') }}" variant="secondary" size="sm">View All</x-button>
                </div>
                <div class="p-6">
                    @if($recentInvoices->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentInvoices as $invoice)
                                <div class="flex justify-between items-center py-2 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] last:border-0">
                                    <div>
                                        <div class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->number ?? 'Draft' }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ $invoice->customer->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ app(\App\Services\CurrencyService::class)->format($invoice->total / 100, $invoice->currency) }}</div>
                                        <span class="text-xs px-2 py-1 rounded-full bg-[color:var(--color-{{ $invoice->status->color() }}-50)] text-[color:var(--color-{{ $invoice->status->color() }}-600)]">
                                            {{ $invoice->status->label() }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No recent invoices</p>
                    @endif
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Recent Payments
                    </h2>
                </div>
                <div class="p-6">
                    @if($recentPayments->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentPayments as $payment)
                                <div class="flex justify-between items-center py-2 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] last:border-0">
                                    <div>
                                        <div class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $payment->invoice->number ?? 'Draft' }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                            {{ $payment->invoice->customer->name }} • {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">{{ app(\App\Services\CurrencyService::class)->format($payment->amount / 100, $payment->invoice->currency) }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">No recent payments</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
