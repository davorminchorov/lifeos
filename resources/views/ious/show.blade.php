@extends('layouts.app')

@section('title', 'IOU Details - LifeOS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">IOU Details</h1>
                <p class="text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-2">
                    @if($iou->type === 'owe')
                        <span class="text-red-600">You owe {{ $iou->person_name }}</span>
                    @else
                        <span class="text-green-600">{{ $iou->person_name }} owes you</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('ious.edit', $iou) }}"
                   class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('ious.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to IOUs
                </a>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="mb-6">
            @if($iou->status === 'paid')
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                This IOU has been fully paid
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($iou->status === 'cancelled')
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                This IOU has been cancelled
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($iou->is_overdue && $iou->status === 'pending')
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                This IOU is overdue by {{ $iou->days_overdue }} day(s)
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Details Card -->
        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-6">
            <div class="px-6 py-5">
                <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Overview</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Type -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Type</dt>
                        <dd class="mt-1 text-sm">
                            @if($iou->type === 'owe')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                    I Owe
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Owed to Me
                                </span>
                            @endif
                        </dd>
                    </div>

                    <!-- Person -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Person</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-900)] dark:text-white font-semibold">{{ $iou->person_name }}</dd>
                    </div>

                    <!-- Amount -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Total Amount</dt>
                        <dd class="mt-1 text-2xl font-bold text-[color:var(--color-primary-900)] dark:text-white">
                            {{ $iou->formatted_amount }}
                        </dd>
                        @if($iou->currency !== 'MKD')
                            <dd class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                                ≈ {{ $iou->formatted_amount_mkd }}
                            </dd>
                        @endif
                    </div>

                    <!-- Amount Paid -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Amount Paid</dt>
                        <dd class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $iou->formatted_amount_paid }}
                        </dd>
                    </div>

                    <!-- Remaining Amount -->
                    @if($iou->remaining_amount > 0)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Remaining Amount</dt>
                            <dd class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ $iou->formatted_remaining_amount }}
                            </dd>
                        </div>
                    @endif

                    <!-- Status -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                        <dd class="mt-1">
                            @if($iou->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                    Pending
                                </span>
                            @elseif($iou->status === 'partially_paid')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    Partially Paid ({{ $iou->payment_percentage }}%)
                                </span>
                            @elseif($iou->status === 'paid')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Paid
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300">
                                    Cancelled
                                </span>
                            @endif
                        </dd>
                    </div>

                    <!-- Transaction Date -->
                    <div>
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Transaction Date</dt>
                        <dd class="mt-1 text-sm text-[color:var(--color-primary-900)] dark:text-white">{{ $iou->transaction_date->format('M d, Y') }}</dd>
                    </div>

                    <!-- Due Date -->
                    @if($iou->due_date)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Due Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-900)] dark:text-white">
                                {{ $iou->due_date->format('M d, Y') }}
                                @if($iou->is_overdue && $iou->status !== 'paid')
                                    <span class="ml-2 text-red-600 dark:text-red-400 font-medium">(Overdue)</span>
                                @endif
                            </dd>
                        </div>
                    @endif

                    <!-- Category -->
                    @if($iou->category)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Category</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-900)] dark:text-white">{{ $iou->category }}</dd>
                        </div>
                    @endif

                    <!-- Payment Method -->
                    @if($iou->payment_method)
                        <div>
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Payment Method</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-900)] dark:text-white">{{ $iou->payment_method }}</dd>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="mt-6 pt-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-2">Description</dt>
                    <dd class="text-sm text-[color:var(--color-primary-900)] dark:text-white whitespace-pre-wrap">{{ $iou->description }}</dd>
                </div>

                <!-- Notes -->
                @if($iou->notes)
                    <div class="mt-6 pt-6 border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                        <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mb-2">Notes</dt>
                        <dd class="text-sm text-[color:var(--color-primary-900)] dark:text-white whitespace-pre-wrap">{{ $iou->notes }}</dd>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        @if($iou->payments?->count() > 0)
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] mb-6">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Payment History</h2>

                    <div class="space-y-4">
                        @foreach($iou->payments as $payment)
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-[color:var(--color-dark-100)] rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                                <div>
                                    <p class="text-sm font-medium text-[color:var(--color-primary-900)] dark:text-white">
                                        Payment of {{ number_format($payment->amount, 2) }} {{ $iou->currency }}
                                    </p>
                                    <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1">
                                        {{ $payment->payment_date->format('M d, Y') }}
                                        @if($payment->payment_method)
                                            • {{ $payment->payment_method }}
                                        @endif
                                    </p>
                                    @if($payment->notes)
                                        <p class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)] mt-1 italic">{{ $payment->notes }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Paid
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        @if($iou->status !== 'paid' && $iou->status !== 'cancelled')
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow-md rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-semibold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Actions</h2>

                    <div class="flex flex-wrap gap-3">
                        <!-- Record Payment Button -->
                        <button type="button" onclick="document.getElementById('paymentModal').classList.remove('hidden')"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Record Payment
                        </button>

                        <!-- Mark as Paid Button -->
                        @if($iou->remaining_amount > 0)
                            <form method="POST" action="{{ route('ious.mark-paid', $iou) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Are you sure you want to mark this IOU as fully paid?')"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Mark as Paid
                                </button>
                            </form>
                        @endif

                        <!-- Cancel Button -->
                        <form method="POST" action="{{ route('ious.cancel', $iou) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Are you sure you want to cancel this IOU?')"
                                    class="inline-flex items-center px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-[color:var(--color-dark-100)] hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel IOU
                            </button>
                        </form>

                        <!-- Delete Button -->
                        <form method="POST" action="{{ route('ious.destroy', $iou) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this IOU? This action cannot be undone.')"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-800 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-[color:var(--color-dark-100)] hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-[color:var(--color-primary-900)] dark:text-white mb-4">Record Payment</h3>

            <form method="POST" action="{{ route('ious.record-payment', $iou) }}">
                @csrf

                <div class="mb-4">
                    <label for="payment_amount" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Amount</label>
                    <input type="number" step="0.01" name="payment_amount" id="payment_amount" required min="0.01" max="{{ $iou->remaining_amount }}"
                           class="block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                           placeholder="0.00">
                    <p class="mt-1 text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Remaining: {{ $iou->formatted_remaining_amount }}</p>
                </div>

                <div class="mb-4">
                    <label for="payment_date" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" required value="{{ date('Y-m-d') }}"
                           class="block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                </div>

                <div class="mb-4">
                    <label for="payment_method_modal" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method_modal"
                            class="block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white">
                        <option value="">Select Payment Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Venmo">Venmo</option>
                        <option value="Check">Check</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="payment_notes" class="block text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-2">Notes (Optional)</label>
                    <textarea name="notes" id="payment_notes" rows="2"
                              class="block w-full px-3 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md focus:outline-none focus:ring-[color:var(--color-accent-500)] focus:border-[color:var(--color-accent-500)] bg-white dark:bg-[color:var(--color-dark-100)] text-[color:var(--color-primary-900)] dark:text-white"
                              placeholder="Payment details..."></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')"
                            class="px-4 py-2 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)] rounded-md text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] hover:bg-[color:var(--color-primary-200)] dark:hover:bg-[color:var(--color-dark-300)]">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
