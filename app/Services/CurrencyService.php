<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Get the default currency code.
     */
    public function getDefaultCurrency(): string
    {
        return config('currency.default', 'MKD');
    }

    /**
     * Get all supported currencies.
     */
    public function getSupportedCurrencies(): array
    {
        return config('currency.supported', []);
    }

    /**
     * Get currency information by code.
     */
    public function getCurrencyInfo(string $currencyCode): ?array
    {
        return config("currency.supported.{$currencyCode}");
    }

    /**
     * Get currency symbol by code.
     */
    public function getCurrencySymbol(string $currencyCode): string
    {
        $currencyInfo = $this->getCurrencyInfo($currencyCode);

        return $currencyInfo['symbol'] ?? $currencyCode;
    }

    /**
     * Get currency name by code.
     */
    public function getCurrencyName(string $currencyCode): string
    {
        $currencyInfo = $this->getCurrencyInfo($currencyCode);

        return $currencyInfo['name'] ?? $currencyCode;
    }

    /**
     * Format amount with currency.
     */
    public function format(float $amount, ?string $currencyCode = null, bool $showSymbol = true): string
    {
        $currencyCode = $currencyCode ?? $this->getDefaultCurrency();
        $currencyInfo = $this->getCurrencyInfo($currencyCode);

        if (! $currencyInfo) {
            return number_format($amount, 2).' '.$currencyCode;
        }

        $decimalPlaces = $currencyInfo['decimal_places'] ?? 2;
        $thousandSep = config('currency.display.thousand_separator', ',');
        $decimalSep = config('currency.display.decimal_separator', '.');

        $formattedAmount = number_format($amount, $decimalPlaces, $decimalSep, $thousandSep);

        if (! $showSymbol) {
            return $formattedAmount;
        }

        $symbol = $currencyInfo['symbol'];
        $symbolPosition = config('currency.display.symbol_position', 'before');
        $showCurrencyCode = config('currency.display.show_currency_code', true);

        if ($symbolPosition === 'before') {
            $result = $symbol.' '.$formattedAmount;
        } else {
            $result = $formattedAmount.' '.$symbol;
        }

        if ($showCurrencyCode && $currencyCode !== $this->getDefaultCurrency()) {
            $result .= ' ('.$currencyCode.')';
        }

        return $result;
    }

    /**
     * Convert amount from one currency to another.
     */
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        if (! config('currency.conversion.enabled', true)) {
            Log::warning('Currency conversion is disabled', [
                'amount' => $amount,
                'from' => $fromCurrency,
                'to' => $toCurrency,
            ]);

            return $amount;
        }

        $exchangeRate = $this->getExchangeRate($fromCurrency, $toCurrency);

        if ($exchangeRate === null) {
            Log::error('Failed to get exchange rate', [
                'from' => $fromCurrency,
                'to' => $toCurrency,
            ]);

            return $amount;
        }

        return round($amount * $exchangeRate, 2);
    }

    /**
     * Get exchange rate between two currencies.
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        $cacheDuration = config('currency.conversion.cache_duration', 3600);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($fromCurrency, $toCurrency, $cacheDuration) {
            $rate = $this->fetchExchangeRate($fromCurrency, $toCurrency);

            if ($rate !== null) {
                // Store timestamp when rate was fetched
                $timestampKey = "exchange_rate_timestamp_{$fromCurrency}_{$toCurrency}";
                Cache::put($timestampKey, now()->timestamp, $cacheDuration);
            }

            return $rate;
        });
    }

    /**
     * Get exchange rate with freshness information.
     */
    public function getExchangeRateWithFreshness(string $fromCurrency, string $toCurrency): array
    {
        if ($fromCurrency === $toCurrency) {
            return [
                'rate' => 1.0,
                'freshness' => 'fresh',
                'last_updated' => now()->timestamp,
                'age_seconds' => 0,
            ];
        }

        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        $timestampKey = "exchange_rate_timestamp_{$fromCurrency}_{$toCurrency}";
        $lastUpdated = Cache::get($timestampKey);

        $freshness = $this->calculateFreshness($lastUpdated);
        $ageSeconds = $lastUpdated ? (now()->timestamp - $lastUpdated) : null;

        return [
            'rate' => $rate,
            'freshness' => $freshness,
            'last_updated' => $lastUpdated,
            'age_seconds' => $ageSeconds,
        ];
    }

    /**
     * Calculate freshness level based on timestamp.
     */
    public function calculateFreshness(?int $timestamp): string
    {
        if (!$timestamp) {
            return 'unknown';
        }

        $ageSeconds = now()->timestamp - $timestamp;
        $freshThreshold = config('currency.freshness.fresh_threshold', 3600 * 4);
        $staleThreshold = config('currency.freshness.stale_threshold', 3600 * 12);
        $warningThreshold = config('currency.freshness.warning_threshold', 3600 * 24);

        if ($ageSeconds <= $freshThreshold) {
            return 'fresh';
        } elseif ($ageSeconds <= $staleThreshold) {
            return 'stale';
        } elseif ($ageSeconds <= $warningThreshold) {
            return 'warning';
        }

        return 'unknown';
    }

    /**
     * Get freshness label for display.
     */
    public function getFreshnessLabel(string $freshness): string
    {
        return config("currency.freshness.labels.{$freshness}", ucfirst($freshness));
    }

    /**
     * Get freshness color for styling.
     */
    public function getFreshnessColor(string $freshness): string
    {
        return config("currency.freshness.colors.{$freshness}", 'gray');
    }

    /**
     * Check if exchange rate is considered fresh.
     */
    public function isRateFresh(string $fromCurrency, string $toCurrency): bool
    {
        $rateInfo = $this->getExchangeRateWithFreshness($fromCurrency, $toCurrency);
        return $rateInfo['freshness'] === 'fresh';
    }

    /**
     * Check if exchange rate needs warning indicator.
     */
    public function isRateStale(string $fromCurrency, string $toCurrency): bool
    {
        $rateInfo = $this->getExchangeRateWithFreshness($fromCurrency, $toCurrency);
        return in_array($rateInfo['freshness'], ['warning', 'unknown']);
    }

    /**
     * Get formatted age string for display.
     */
    public function getFormattedAge(?int $ageSeconds): string
    {
        if (!$ageSeconds) {
            return 'Unknown';
        }

        if ($ageSeconds < 60) {
            return 'Less than 1 minute ago';
        } elseif ($ageSeconds < 3600) {
            $minutes = floor($ageSeconds / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($ageSeconds < 86400) {
            $hours = floor($ageSeconds / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($ageSeconds / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Force refresh of exchange rate (bypass cache).
     */
    public function refreshExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // Clear cached rate and timestamp
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        $timestampKey = "exchange_rate_timestamp_{$fromCurrency}_{$toCurrency}";

        Cache::forget($cacheKey);
        Cache::forget($timestampKey);

        // Fetch fresh rate
        return $this->getExchangeRate($fromCurrency, $toCurrency);
    }

    /**
     * Fetch exchange rate from external API.
     */
    private function fetchExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        $provider = config('currency.conversion.api_provider', 'exchangerate');

        try {
            switch ($provider) {
                case 'exchangerate':
                    return $this->fetchFromExchangeRateApi($fromCurrency, $toCurrency);
                case 'fixer':
                    return $this->fetchFromFixerApi($fromCurrency, $toCurrency);
                default:
                    Log::error('Unknown currency API provider: '.$provider);

                    return null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange rate', [
                'from' => $fromCurrency,
                'to' => $toCurrency,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch exchange rate from exchangerate-api.io
     */
    private function fetchFromExchangeRateApi(string $fromCurrency, string $toCurrency): ?float
    {
        $apiKey = config('currency.conversion.api_key');

        if (! $apiKey) {
            // Use free tier without API key (limited requests)
            $url = "https://api.exchangerate-api.io/v4/latest/{$fromCurrency}";
        } else {
            $url = "https://v6.exchangerate-api.io/v6/{$apiKey}/latest/{$fromCurrency}";
        }

        $response = Http::timeout(10)->get($url);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! isset($data['rates'][$toCurrency])) {
            return null;
        }

        return (float) $data['rates'][$toCurrency];
    }

    /**
     * Fetch exchange rate from fixer.io
     */
    private function fetchFromFixerApi(string $fromCurrency, string $toCurrency): ?float
    {
        $apiKey = config('currency.conversion.api_key');

        if (! $apiKey) {
            Log::error('Fixer.io API key is required');

            return null;
        }

        $url = 'http://data.fixer.io/api/latest';

        $response = Http::timeout(10)->get($url, [
            'access_key' => $apiKey,
            'base' => $fromCurrency,
            'symbols' => $toCurrency,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (! $data['success'] || ! isset($data['rates'][$toCurrency])) {
            return null;
        }

        return (float) $data['rates'][$toCurrency];
    }

    /**
     * Get currencies as options for select dropdown.
     */
    public function getCurrencyOptions(): array
    {
        $currencies = $this->getSupportedCurrencies();
        $options = [];

        foreach ($currencies as $code => $info) {
            $options[$code] = $code.' ('.$info['symbol'].') - '.$info['name'];
        }

        return $options;
    }

    /**
     * Convert amount to default currency if needed.
     */
    public function convertToDefault(float $amount, string $fromCurrency): float
    {
        $defaultCurrency = $this->getDefaultCurrency();

        return $this->convert($amount, $fromCurrency, $defaultCurrency);
    }

    /**
     * Convert amount from default currency to target currency.
     */
    public function convertFromDefault(float $amount, string $toCurrency): float
    {
        $defaultCurrency = $this->getDefaultCurrency();

        return $this->convert($amount, $defaultCurrency, $toCurrency);
    }

    /**
     * Check if currency is supported.
     */
    public function isSupported(string $currencyCode): bool
    {
        return array_key_exists($currencyCode, $this->getSupportedCurrencies());
    }

    /**
     * Get formatted currency list for display.
     */
    public function getFormattedCurrencyList(): array
    {
        $currencies = $this->getSupportedCurrencies();
        $formatted = [];

        foreach ($currencies as $code => $info) {
            $formatted[] = [
                'code' => $code,
                'name' => $info['name'],
                'symbol' => $info['symbol'],
                'is_default' => $code === $this->getDefaultCurrency(),
            ];
        }

        return $formatted;
    }
}
