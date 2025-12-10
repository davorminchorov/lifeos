                        </form>

                        <!-- Delete Button -->
                        <form method="POST" action="{{ route('ious.destroy', $iou) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" onclick="return confirm('Are you sure you want to delete this IOU? This action cannot be undone.')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </x-button>
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
                    <x-button type="button" variant="secondary" onclick="document.getElementById('paymentModal').classList.add('hidden')">Cancel</x-button>
                    <x-button type="submit" variant="primary">Record Payment</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
