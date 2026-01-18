@extends('layouts.app')

@section('title', ($invoice->number ?? 'Draft Invoice') . ' - Invoice')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="invoiceManager()">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    {{ $invoice->number ?? 'Draft Invoice' }}
                </h1>
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                    Customer: {{ $invoice->customer->name }}
                </p>
            </div>
            <div class="flex gap-3">
                <x-button href="{{ route('invoicing.invoices.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </x-button>

                <!-- PDF Actions -->
                <x-button href="{{ route('invoicing.invoices.pdf.view', $invoice) }}" variant="secondary" target="_blank">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View PDF
                </x-button>

                <x-button href="{{ route('invoicing.invoices.pdf.download', $invoice) }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </x-button>

                <!-- Email Actions -->
                @if($invoice->customer->email && $invoice->items->count() > 0 && $invoice->total > 0)
                    <x-button @click="showSendEmailForm = true" variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Email
                    </x-button>
                @endif

                @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                    <x-button href="{{ route('invoicing.invoices.edit', $invoice) }}" variant="secondary">
                        Edit Details
                    </x-button>
                    @if($invoice->items->count() > 0 && $invoice->total > 0)
                        <form method="POST" action="{{ route('invoicing.invoices.issue', $invoice) }}"
                              onsubmit="return confirm('Are you sure you want to issue this invoice? This action cannot be undone.');">
                            @csrf
                            <x-button type="submit" variant="primary">
                                Issue Invoice
                            </x-button>
                        </form>
                    @endif
                @endif

                @if(in_array($invoice->status, [\App\Enums\InvoiceStatus::ISSUED, \App\Enums\InvoiceStatus::PARTIALLY_PAID, \App\Enums\InvoiceStatus::PAST_DUE]))
                    @if($invoice->amount_due > 0)
                        <x-button @click="showRecordPaymentForm = true" variant="primary">
                            Record Payment
                        </x-button>
                    @endif
                    <form method="POST" action="{{ route('invoicing.invoices.void', $invoice) }}"
                          onsubmit="return prompt('Please provide a reason for voiding this invoice:');">
                        @csrf
                        <input type="hidden" name="reason" x-model="voidReason">
                        <x-button type="submit" variant="secondary" class="text-red-600 hover:text-red-700">
                            Void Invoice
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @php
                $statusColor = $invoice->status->color();
                $bgClass = "bg-{$statusColor}-50";
                $textClass = "text-{$statusColor}-600";
                if ($statusColor === 'slate') {
                    $bgClass = 'bg-slate-100';
                    $textClass = 'text-slate-600';
                }
            @endphp
            <span class="inline-flex px-3 py-1 text-sm rounded-full {{ $bgClass }} {{ $textClass }}">
                {{ $invoice->status->label() }}
            </span>
        </div>

        <!-- Invoice Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Invoice Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Invoice Details
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Currency</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Tax Behavior</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ ucfirst($invoice->tax_behavior) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Payment Terms</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Net {{ $invoice->net_terms_days }} days</span>
                    </div>
                    @if($invoice->issued_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Issued Date</span>
                            <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->issued_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($invoice->due_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Due Date</span>
                            <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->due_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Customer
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <p class="font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ $invoice->customer->name }}
                        </p>
                        @if($invoice->customer->email)
                            <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                {{ $invoice->customer->email }}
                            </p>
                        @endif
                    </div>
                    @if($invoice->customer->billing_address)
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-1">
                            @if(!empty($invoice->customer->billing_address['street']))
                                <div>{{ $invoice->customer->billing_address['street'] }}</div>
                            @endif
                            <div>
                                @if(!empty($invoice->customer->billing_address['city']))
                                    {{ $invoice->customer->billing_address['city'] }}@if(!empty($invoice->customer->billing_address['postal_code'])), {{ $invoice->customer->billing_address['postal_code'] }}@endif
                                @endif
                            </div>
                            @if(!empty($invoice->customer->billing_address['country']))
                                <div>{{ $invoice->customer->billing_address['country'] }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Totals
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Subtotal</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->formatted_subtotal }}</span>
                    </div>
                    @if($invoice->discount_total > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Discount</span>
                            <span class="text-sm font-medium text-green-600">-{{ app(\App\Services\CurrencyService::class)->format($invoice->discount_total / 100, $invoice->currency) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Tax</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->formatted_tax_total }}</span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <span class="text-base font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Total</span>
                        <span class="text-base font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $invoice->formatted_total }}</span>
                    </div>
                    @if($invoice->amount_paid > 0)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Amount Paid</span>
                            <span class="text-sm font-medium text-green-600">{{ app(\App\Services\CurrencyService::class)->format($invoice->amount_paid / 100, $invoice->currency) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                            <span class="text-base font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Amount Due</span>
                            <span class="text-base font-semibold text-red-600">{{ app(\App\Services\CurrencyService::class)->format($invoice->amount_due / 100, $invoice->currency) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
            <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    Line Items
                </h2>
                @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                    <x-button @click="showAddItemForm = true" variant="primary" size="sm">
                        Add Item
                    </x-button>
                @endif
            </div>
            <div class="px-6 py-4">
                @if($invoice->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Description
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Qty
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Unit Price
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Tax
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Discount
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Total
                                    </th>
                                    @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                                        <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Actions
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $item->description }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ number_format($item->quantity, 2) }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ app(\App\Services\CurrencyService::class)->format($item->unit_amount / 100, $invoice->currency) }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            @if($item->tax_amount > 0)
                                                {{ app(\App\Services\CurrencyService::class)->format($item->tax_amount / 100, $invoice->currency) }}
                                                @if($item->taxRate)
                                                    <span class="text-xs text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">({{ $item->taxRate->name }})</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right text-green-600">
                                            @if($item->discount_amount > 0)
                                                -{{ app(\App\Services\CurrencyService::class)->format($item->discount_amount / 100, $invoice->currency) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ app(\App\Services\CurrencyService::class)->format($item->total_amount / 100, $invoice->currency) }}
                                        </td>
                                        @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
                                            <td class="px-3 py-3 text-sm text-right">
                                                <form method="POST" action="{{ route('invoicing.invoices.items.destroy', [$invoice, $item]) }}"
                                                      onsubmit="return confirm('Are you sure you want to remove this item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">
                                                        Remove
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                        No line items yet. @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)Add your first item to get started.@endif
                    </p>
                @endif
            </div>
        </div>

        <!-- Add Item Form Modal -->
        <div x-show="showAddItemForm"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showAddItemForm = false">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Add Line Item
                    </h3>
                    <button @click="showAddItemForm = false" class="text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('invoicing.invoices.items.store', $invoice) }}" class="px-6 py-4">
                    @csrf

                    <div class="space-y-4">
                        <x-form.input
                            name="description"
                            label="Description"
                            :required="true"
                            placeholder="Product or service description"
                        />

                        <div class="grid grid-cols-2 gap-4">
                            <x-form.input
                                type="number"
                                name="quantity"
                                label="Quantity"
                                :required="true"
                                step="0.01"
                                min="0.01"
                                value="1"
                            />

                            <x-form.input
                                type="number"
                                name="unit_amount"
                                label="Unit Price (in cents)"
                                :required="true"
                                min="0"
                                placeholder="e.g., 10000 for $100.00"
                            />
                        </div>

                        <div class="flex gap-3 pt-4">
                            <x-button type="submit" variant="primary" class="flex-1">
                                Add Item
                            </x-button>
                            <x-button type="button" variant="secondary" @click="showAddItemForm = false">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Record Payment Form Modal -->
        <div x-show="showRecordPaymentForm"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showRecordPaymentForm = false">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Record Payment
                    </h3>
                    <button @click="showRecordPaymentForm = false" class="text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('invoicing.invoices.payments.store', $invoice) }}" class="px-6 py-4">
                    @csrf

                    <div class="space-y-4">
                        <!-- Amount Due Display -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Amount Due:</span>
                                <span class="text-lg font-bold text-blue-700 dark:text-blue-300">
                                    {{ app(\App\Services\CurrencyService::class)->format($invoice->amount_due / 100, $invoice->currency) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <x-form.input
                                type="number"
                                name="amount"
                                label="Payment Amount (in cents)"
                                :required="true"
                                min="1"
                                :max="$invoice->amount_due"
                                placeholder="e.g., 10000 for $100.00"
                                helpText="Enter amount in cents"
                            />

                            <x-form.input
                                type="date"
                                name="payment_date"
                                label="Payment Date"
                                :required="true"
                                :value="date('Y-m-d')"
                            />
                        </div>

                        <x-form.select name="payment_method" label="Payment Method" :required="true">
                            <option value="">Select payment method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="other">Other</option>
                        </x-form.select>

                        <x-form.input
                            name="reference"
                            label="Reference Number"
                            placeholder="Transaction ID, check number, etc."
                        />

                        <x-form.input
                            type="textarea"
                            name="notes"
                            label="Notes"
                            placeholder="Additional payment notes..."
                            rows="3"
                        />

                        <div class="flex gap-3 pt-4">
                            <x-button type="submit" variant="primary" class="flex-1">
                                Record Payment
                            </x-button>
                            <x-button type="button" variant="secondary" @click="showRecordPaymentForm = false">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Send Email Form Modal -->
        <div x-show="showSendEmailForm"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showSendEmailForm = false">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Send Invoice via Email
                    </h3>
                    <button @click="showSendEmailForm = false" class="text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('invoicing.invoices.send-email', $invoice) }}" class="px-6 py-4">
                    @csrf

                    <div class="space-y-4">
                        <!-- Recipient Info -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                        Email will be sent to: <strong>{{ $invoice->customer->email }}</strong>
                                    </p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                        The invoice PDF will be attached to the email.
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($invoice->last_sent_at)
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    <strong>Last sent:</strong> {{ $invoice->last_sent_at->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        @endif

                        <x-form.input
                            type="textarea"
                            name="message"
                            label="Custom Message (Optional)"
                            placeholder="Add a personal message to include in the email..."
                            rows="4"
                            helpText="Leave blank to use the default message template"
                        />

                        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-300)] border border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-400)] rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">
                                Email Preview
                            </h4>
                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-2">
                                Subject: <strong>Invoice {{ $invoice->number ?? 'Draft' }} from LifeOS</strong>
                            </p>
                            <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                The email will include invoice details, payment information, and the PDF attachment.
                            </p>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <x-button type="submit" variant="primary" class="flex-1">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Send Invoice
                            </x-button>
                            <x-button type="button" variant="secondary" @click="showSendEmailForm = false">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->status !== \App\Enums\InvoiceStatus::DRAFT)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Payment History
                    </h2>
                    @if(in_array($invoice->status, [\App\Enums\InvoiceStatus::ISSUED, \App\Enums\InvoiceStatus::PARTIALLY_PAID, \App\Enums\InvoiceStatus::PAST_DUE]) && $invoice->amount_due > 0)
                        <x-button @click="showRecordPaymentForm = true" variant="primary" size="sm">
                            Record Payment
                        </x-button>
                    @endif
                </div>
                <div class="px-6 py-4">
                    @if($invoice->payments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Date
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Method
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Reference
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Amount
                                        </th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                                {{ $payment->reference ?? '—' }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-right font-medium text-green-600">
                                                {{ app(\App\Services\CurrencyService::class)->format($payment->amount / 100, $invoice->currency) }}
                                            </td>
                                            <td class="px-3 py-3 text-sm text-right">
                                                <form method="POST" action="{{ route('invoicing.invoices.payments.destroy', [$invoice, $payment]) }}"
                                                      onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-8 text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                            No payments recorded yet
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Notes (visible to customer)
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $invoice->notes }}</p>
                </div>
            </div>
        @endif

        @if($invoice->internal_notes)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Internal Notes
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $invoice->internal_notes }}</p>
                </div>
            </div>
        @endif

        <!-- Delete Draft -->
        @if($invoice->status === \App\Enums\InvoiceStatus::DRAFT)
            <div class="mt-8">
                <form method="POST" action="{{ route('invoicing.invoices.destroy', $invoice) }}"
                      onsubmit="return confirm('Are you sure you want to delete this draft invoice? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="secondary" class="text-red-600 hover:text-red-700">
                        Delete Draft
                    </x-button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
function invoiceManager() {
    return {
        showAddItemForm: false,
        showRecordPaymentForm: false,
        showSendEmailForm: false,
        voidReason: ''
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
