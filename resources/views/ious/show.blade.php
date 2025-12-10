@extends('layouts.app')

@section('title', 'IOU Details - LifeOS')

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                IOU Details
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                {{ $iou->type_label }} with {{ $iou->person_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            @if($iou->status !== 'paid' && $iou->status !== 'cancelled')
                <x-button type="button" variant="primary" onclick="document.getElementById('paymentModal').classList.remove('hidden')">
                    Record Payment
                </x-button>
                <form method="POST" action="{{ route('ious.mark-paid', $iou) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <x-button type="submit" variant="secondary" onclick="return confirm('Mark this IOU as fully paid?')">Mark Paid</x-button>
                </form>
                <form method="POST" action="{{ route('ious.cancel', $iou) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <x-button type="submit" variant="secondary" onclick="return confirm('Cancel this IOU?')">Cancel</x-button>
                </form>
            @endif
            <x-button href="{{ route('ious.edit', $iou) }}" variant="secondary">Edit</x-button>
            <x-button href="{{ route('ious.index') }}" variant="secondary">Back to IOUs</x-button>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow overflow-hidden sm:rounded-lg border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">IOU Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Details about this IOU.</p>
                </div>
                <div class="border-t border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                    <dl>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Person</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->person_name }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Type</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->type_label }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Amount</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <span class="text-2xl font-bold text-[color:var(--color-primary-900)] dark:text-white">{{ $iou->formatted_amount }}</span>
                                <span class="ml-2 text-sm text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">(~ {{ $iou->formatted_amount_mkd }})</span>
                            </dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Amount Paid</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ $iou->formatted_amount_paid }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Remaining</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                <div class="flex items-center gap-2">
                                    <span>{{ $iou->formatted_remaining_amount }}</span>
                                    <span class="text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">(~ {{ $iou->formatted_remaining_balance_mkd }})</span>
                                </div>
                                <div class="mt-2 h-2 bg-[color:var(--color-primary-300)] dark:bg-[color:var(--color-dark-300)] rounded">
                                    <div class="h-2 bg-[color:var(--color-accent-500)] rounded" style="width: {{ $iou->payment_progress }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">{{ $iou->payment_percentage }}% paid</p>
                            </dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Transaction Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->transaction_date?->format('F j, Y') }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Due Date</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                @if($iou->due_date)
                                    {{ $iou->due_date->format('F j, Y') }}
                                    @if($iou->days_until_due !== null)
                                        @if($iou->days_until_due < 0)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-danger-50)] text-[color:var(--color-danger-600)] dark:bg-[color:var(--color-danger-900)] dark:text-[color:var(--color-danger-200)]">
                                                Overdue by {{ abs($iou->days_until_due) }} days
                                            </span>
                                        @elseif($iou->days_until_due <= 7)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[color:var(--color-warning-50)] text-[color:var(--color-warning-600)] dark:bg-[color:var(--color-warning-900)] dark:text-[color:var(--color-warning-200)]">
                                                Due in {{ $iou->days_until_due }} days
                                            </span>
                                        @endif
                                    @endif
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Currency</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->currency }}</dd>
                        </div>
                        @if($iou->payment_method)
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Payment Method</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->payment_method }}</dd>
                        </div>
                        @endif
                        @if($iou->category)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Category</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->category }}</dd>
                        </div>
                        @endif
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Status</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2 capitalize">{{ str_replace('_', ' ', $iou->status) }}</dd>
                        </div>
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Description</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->description }}</dd>
                        </div>
                        @if($iou->notes)
                        <div class="bg-[color:var(--color-primary-200)] dark:bg-[color:var(--color-dark-300)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Notes</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">{{ $iou->notes }}</dd>
                        </div>
                        @endif
                        @php($attachments = is_array($iou->attachments) ? $iou->attachments : [])
                        @if(count($attachments) > 0)
                        <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">Attachments</dt>
                            <dd class="mt-1 text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] sm:mt-0 sm:col-span-2">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($attachments as $file)
                                        <li class="truncate">{{ $file }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)] shadow rounded-lg p-6 border border-[color:var(--color-primary-300)] dark:border-[color:var(--color-dark-300)]">
                <h3 class="text-lg font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)] mb-4">Actions</h3>
                <div class="flex flex-col gap-3">
                    @if($iou->status !== 'paid' && $iou->status !== 'cancelled')
                        <x-button type="button" variant="primary" onclick="document.getElementById('paymentModal').classList.remove('hidden')">Record Payment</x-button>
                        <form method="POST" action="{{ route('ious.mark-paid', $iou) }}">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="secondary" class="w-full" onclick="return confirm('Mark this IOU as fully paid?')">Mark Paid</x-button>
                        </form>
                        <form method="POST" action="{{ route('ious.cancel', $iou) }}">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="secondary" class="w-full" onclick="return confirm('Cancel this IOU?')">Cancel</x-button>
                        </form>
                    @endif
                    <x-button href="{{ route('ious.edit', $iou) }}" variant="secondary" class="w-full">Edit</x-button>
                    <form method="POST" action="{{ route('ious.destroy', $iou) }}">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" class="w-full" onclick="return confirm('Are you sure you want to delete this IOU? This action cannot be undone.')">
                            Delete
                        </x-button>
                    </form>
                </div>
            </div>
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
                        <x-button type="button" variant="secondary" onclick="document.getElementById('paymentModal').classList.add('hidden')">Cancel</x-button>
                        <x-button type="submit" variant="primary">Record Payment</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
