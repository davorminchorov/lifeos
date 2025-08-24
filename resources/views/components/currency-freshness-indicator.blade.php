<div class="inline-flex items-center gap-2">
    {{-- Freshness Badge --}}
    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium border rounded-full {{ $getBadgeColorClass() }}">
        <span class="text-xs">{{ $getFreshnessIcon() }}</span>
        {{ $getFreshnessLabel() }}
    </span>

    {{-- Age Display --}}
    @if($showAge && $rateInfo['age_seconds'])
        <span class="text-xs text-gray-500">
            {{ $getFormattedAge() }}
        </span>
    @endif

    {{-- Warning Message for Stale Rates --}}
    @if($needsAttention())
        <span class="text-xs text-red-600 font-medium">
            Exchange rate may be outdated
        </span>
    @endif

    {{-- Refresh Button --}}
    @if($showRefreshButton)
        <button
            type="button"
            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
            onclick="refreshExchangeRate('{{ $fromCurrency }}', '{{ $toCurrency }}')"
            title="Refresh exchange rate"
        >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    @endif
</div>

{{-- JavaScript for refresh functionality --}}
@if($showRefreshButton)
    @push('scripts')
    <script>
        function refreshExchangeRate(fromCurrency, toCurrency) {
            // Show loading state
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `
                <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refreshing...
            `;

            // Make AJAX request to refresh rate
            fetch('/currency/refresh-rate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    from_currency: fromCurrency,
                    to_currency: toCurrency
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page or update the component
                    window.location.reload();
                } else {
                    alert('Failed to refresh exchange rate: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error refreshing exchange rate:', error);
                alert('Failed to refresh exchange rate. Please try again.');
            })
            .finally(() => {
                // Restore button state
                button.disabled = false;
                button.innerHTML = originalContent;
            });
        }
    </script>
    @endpush
@endif
