@extends('layouts.app')

@section('title', $creditNote->number . ' - Credit Note')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="creditNoteManager()">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                    {{ $creditNote->number }}
                </h1>
                <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                    Customer: {{ $creditNote->customer->name }}
                </p>
            </div>
            <div class="flex gap-3">
                <x-button href="{{ route('invoicing.credit-notes.index') }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </x-button>

                @if($creditNote->status === \App\Enums\CreditNoteStatus::AVAILABLE && $creditNote->remaining_amount > 0 && $availableInvoices->count() > 0)
                    <x-button @click="showApplyCreditForm = true" variant="primary">
                        Apply to Invoice
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <span class="inline-flex px-3 py-1 text-sm rounded-full bg-[color:var(--color-{{ $creditNote->status->color() }}-50)] text-[color:var(--color-{{ $creditNote->status->color() }}-600)]">
                {{ $creditNote->status->label() }}
            </span>
        </div>

        <!-- Credit Note Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Credit Note Information -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Credit Note Details
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Currency</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $creditNote->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Reason</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ ucwords(str_replace('_', ' ', $creditNote->reason)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Created Date</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">{{ $creditNote->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($creditNote->invoice)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Related Invoice</span>
                            <a href="{{ route('invoicing.invoices.show', $creditNote->invoice) }}" class="text-sm font-medium text-[color:var(--color-accent-500)] hover:underline">
                                {{ $creditNote->invoice->number }}
                            </a>
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
                            {{ $creditNote->customer->name }}
                        </p>
                        @if($creditNote->customer->email)
                            <p class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                {{ $creditNote->customer->email }}
                            </p>
                        @endif
                    </div>
                    @if($creditNote->customer->billing_address)
                        <div class="text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] space-y-1">
                            @if(!empty($creditNote->customer->billing_address['street']))
                                <div>{{ $creditNote->customer->billing_address['street'] }}</div>
                            @endif
                            <div>
                                @if(!empty($creditNote->customer->billing_address['city']))
                                    {{ $creditNote->customer->billing_address['city'] }}@if(!empty($creditNote->customer->billing_address['postal_code'])), {{ $creditNote->customer->billing_address['postal_code'] }}@endif
                                @endif
                            </div>
                            @if(!empty($creditNote->customer->billing_address['country']))
                                <div>{{ $creditNote->customer->billing_address['country'] }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amounts -->
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Amounts
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Total Credit</span>
                        <span class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                            {{ app(\App\Services\CurrencyService::class)->format($creditNote->total / 100, $creditNote->currency) }}
                        </span>
                    </div>
                    @if($creditNote->total !== $creditNote->remaining_amount)
                        <div class="flex justify-between">
                            <span class="text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">Applied Amount</span>
                            <span class="text-sm font-medium text-red-600">
                                {{ app(\App\Services\CurrencyService::class)->format(($creditNote->total - $creditNote->remaining_amount) / 100, $creditNote->currency) }}
                            </span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-3 border-t border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                        <span class="text-base font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">Remaining Credit</span>
                        <span class="text-base font-semibold text-green-600">
                            {{ app(\App\Services\CurrencyService::class)->format($creditNote->remaining_amount / 100, $creditNote->currency) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($creditNote->description)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Description
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $creditNote->description }}</p>
                </div>
            </div>
        @endif

        <!-- Apply Credit Form Modal -->
        <div x-show="showApplyCreditForm"
             x-cloak
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showApplyCreditForm = false">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)] flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Apply Credit to Invoice
                    </h3>
                    <button @click="showApplyCreditForm = false" class="text-[color:var(--color-primary-500)] hover:text-[color:var(--color-primary-700)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('invoicing.credit-notes.apply', $creditNote) }}" class="px-6 py-4">
                    @csrf

                    <div class="space-y-4">
                        <!-- Remaining Credit Display -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-700 dark:text-green-300">Remaining Credit:</span>
                                <span class="text-lg font-bold text-green-700 dark:text-green-300">
                                    {{ app(\App\Services\CurrencyService::class)->format($creditNote->remaining_amount / 100, $creditNote->currency) }}
                                </span>
                            </div>
                        </div>

                        <!-- Invoice Selection -->
                        <x-form.select name="invoice_id" label="Invoice" :required="true">
                            <option value="">Select an invoice</option>
                            @foreach($availableInvoices as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->number }} - {{ app(\App\Services\CurrencyService::class)->format($invoice->amount_due / 100, $invoice->currency) }} due
                                </option>
                            @endforeach
                        </x-form.select>

                        <!-- Amount -->
                        <x-form.input
                            type="number"
                            name="amount"
                            label="Credit Amount to Apply (in cents)"
                            :required="true"
                            min="1"
                            :max="$creditNote->remaining_amount"
                            placeholder="e.g., 10000 for $100.00"
                            helpText="Enter amount in cents"
                        />

                        <div class="flex gap-3 pt-4">
                            <x-button type="submit" variant="primary" class="flex-1">
                                Apply Credit
                            </x-button>
                            <x-button type="button" variant="secondary" @click="showApplyCreditForm = false">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications History -->
        @if($creditNote->applications->count() > 0)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Application History
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Invoice
                                    </th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Amount Applied
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                                @foreach($creditNote->applications as $application)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            <a href="{{ route('invoicing.invoices.show', $application->invoice) }}" class="text-[color:var(--color-accent-500)] hover:underline">
                                                {{ $application->invoice->number }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-right font-medium text-red-600">
                                            {{ app(\App\Services\CurrencyService::class)->format($application->amount / 100, $creditNote->currency) }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                                            {{ $application->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Internal Notes -->
        @if($creditNote->notes)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-8">
                <div class="px-6 py-4 border-b border-[color:var(--color-primary-200)] dark:border-[color:var(--color-dark-300)]">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                        Internal Notes
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] whitespace-pre-wrap">{{ $creditNote->notes }}</p>
                </div>
            </div>
        @endif

        <!-- Delete Credit Note -->
        @if($creditNote->applications()->count() === 0)
            <div class="mt-8">
                <form method="POST" action="{{ route('invoicing.credit-notes.destroy', $creditNote) }}"
                      onsubmit="return confirm('Are you sure you want to delete this credit note? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="secondary" class="text-red-600 hover:text-red-700">
                        Delete Credit Note
                    </x-button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
function creditNoteManager() {
    return {
        showApplyCreditForm: false
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
