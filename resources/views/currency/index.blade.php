@extends('layouts.app')

@section('title', 'Currency Exchange Rates & Freshness')

@section('header')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                Currency Exchange Rates
            </h1>
            <p class="mt-2 text-[color:var(--color-primary-600)] dark:text-[color:var(--color-dark-500)]">
                Monitor exchange rate freshness and update rates as needed. All rates are converted to {{ $defaultCurrency }}.
            </p>
        </div>
    </div>
@endsection

@section('content')
    @if(empty($currencyRates))
        <div class="bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] border border-[color:var(--color-info-200)] dark:border-[color:var(--color-info-800)] rounded-lg p-6">
            <div class="flex">
                <svg class="w-5 h-5 text-[color:var(--color-info-400)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-[color:var(--color-info-800)] dark:text-[color:var(--color-info-200)]">No Exchange Rates</h3>
                    <p class="mt-1 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">
                        No currency exchange rates are currently available. This could be because all supported currencies are the same as the default currency ({{ $defaultCurrency }}).
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                    <thead class="bg-[color:var(--color-primary-100)] dark:bg-[color:var(--color-dark-200)]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Currency Pair
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Exchange Rate
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Freshness
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Last Updated
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)] uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-[color:var(--color-primary-50)] dark:bg-[color:var(--color-dark-100)] divide-y divide-[color:var(--color-primary-200)] dark:divide-[color:var(--color-dark-300)]">
                        @foreach($currencyRates as $currencyRate)
                            <tr class="hover:bg-[color:var(--color-primary-100)] dark:hover:bg-[color:var(--color-dark-200)]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                            {{ $currencyRate['from_currency'] }} → {{ $currencyRate['to_currency'] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-[color:var(--color-primary-700)] dark:text-[color:var(--color-dark-600)]">
                                        {{ number_format($currencyRate['rate_info']['rate'], 4) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-currency-freshness-indicator
                                        :from-currency="$currencyRate['from_currency']"
                                        :to-currency="$currencyRate['to_currency']"
                                        :show-age="true"
                                        :show-refresh-button="false"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[color:var(--color-primary-500)] dark:text-[color:var(--color-dark-500)]">
                                    @if($currencyRate['rate_info']['last_updated'])
                                        <div>{{ \Carbon\Carbon::createFromTimestamp($currencyRate['rate_info']['last_updated'])->format('M j, Y') }}</div>
                                        <div class="text-xs">{{ \Carbon\Carbon::createFromTimestamp($currencyRate['rate_info']['last_updated'])->format('g:i A') }}</div>
                                        <div class="text-xs text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]">{{ $currencyRate['formatted_age'] }} ago</div>
                                    @else
                                        <span class="text-[color:var(--color-primary-400)] dark:text-[color:var(--color-dark-400)]">Never updated</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-[color:var(--color-info-500)] border border-transparent rounded-md hover:bg-[color:var(--color-info-600)] focus:outline-none focus:ring-2 focus:ring-[color:var(--color-info-500)] focus:ring-offset-2 transition-colors"
                                        onclick="refreshExchangeRate('{{ $currencyRate['from_currency'] }}', '{{ $currencyRate['to_currency'] }}')"
                                        title="Refresh exchange rate"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Refresh Rate
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 bg-[color:var(--color-info-50)] dark:bg-[color:var(--color-info-900)] border border-[color:var(--color-info-200)] dark:border-[color:var(--color-info-800)] rounded-lg p-6">
            <div class="flex">
                <svg class="w-5 h-5 text-[color:var(--color-info-400)] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-[color:var(--color-info-800)] dark:text-[color:var(--color-info-200)]">About Currency Freshness</h3>
                    <div class="mt-1 text-sm text-[color:var(--color-info-700)] dark:text-[color:var(--color-info-300)]">
                        <ul class="list-disc list-inside space-y-1">
                            <li><span class="font-medium">Fresh:</span> Exchange rate was updated within the last 24 hours</li>
                            <li><span class="font-medium">Stale:</span> Exchange rate is 1-7 days old and may be slightly outdated</li>
                            <li><span class="font-medium">Warning:</span> Exchange rate is over 7 days old and should be refreshed</li>
                        </ul>
                        <p class="mt-2">Click "Refresh Rate" to get the latest exchange rate from your configured provider.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

{{-- JavaScript for refresh functionality --}}
@push('scripts')
<script>
    function refreshExchangeRate(fromCurrency, toCurrency) {
        // Show loading state
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                // Reload the page to show updated rates
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
@endsection
