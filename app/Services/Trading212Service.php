<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Trading212Service
{
    public function __construct(
    ) {}

    /**
     * Fetch executed BUY/SELL orders since the given timestamp.
     * Returns a normalized array of orders with keys:
     * - id (string)
     * - symbol (string)
     * - name (string|null)
     * - side ('buy'|'sell')
     * - quantity (string|float)
     * - price (string|float)
     * - fee (string|float|null)
     * - executed_at (Carbon|string)
     * - currency (string|null)
     */
    public function fetchFilledOrdersSince(CarbonInterface $since): array
    {
        // If the official SDK is installed, use it. Otherwise, log and skip.
        if (! class_exists('MarekSkopal\\Trading212\\Trading212')) {
            Log::warning('Trading212 SDK not installed; please run composer install to fetch marekskopal/trading212.');

            return [];
        }

        // Defer actual integration details to the SDK; keep normalization here.
        $client = $this->makeSdkClient();

        // Try to fetch filled orders since the timestamp using the SDK's documented endpoint.
        $orders = [];
        try {
            $sdkOrders = $client->orders()->getFilledOrders([
                'from' => $since->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Trading212 API error: '.$e->getMessage(), ['exception' => $e]);

            return [];
        }

        foreach ($sdkOrders as $order) {
            $side = strtolower((string) Arr::get($order, 'side', ''));
            if (! in_array($side, ['buy', 'sell'])) {
                continue;
            }

            $executedAt = Arr::get($order, 'executedAt') ?: Arr::get($order, 'filledAt') ?: Arr::get($order, 'submittedAt');

            $orders[] = [
                'id' => (string) Arr::get($order, 'id'),
                'symbol' => (string) Arr::get($order, 'symbol'),
                'name' => Arr::get($order, 'name'),
                'side' => $side,
                'quantity' => (string) (Arr::get($order, 'quantity') ?? Arr::get($order, 'filledQuantity', 0)),
                'price' => (string) (Arr::get($order, 'price') ?? Arr::get($order, 'averagePrice', 0)),
                'fee' => Arr::get($order, 'fee', 0),
                'executed_at' => $executedAt,
                'currency' => Arr::get($order, 'currency'),
            ];
        }

        return $orders;
    }

    protected function makeSdkClient()
    {
        $apiKey = config('trading212.api_key');
        $env = config('trading212.environment', 'practice');
        $baseUrl = config('trading212.base_url');

        if (! $apiKey) {
            throw new \RuntimeException('TRADING212_API_KEY is not set.');
        }

        // Construct the SDK using the documented Config + Trading212 classes.
        $configClass = 'MarekSkopal\\Trading212\\Config\\Config';
        $clientClass = 'MarekSkopal\\Trading212\\Trading212';

        if (!class_exists($configClass)) {
            throw new \RuntimeException('Trading212 SDK Config class not found. Please run composer install.');
        }

        $cfg = new $configClass($apiKey, $env === 'live' ? 'live' : 'practice', $baseUrl);
        return new $clientClass($cfg);
    }

    public function lastSyncKey(): string
    {
        return 'trading212.last_sync';
    }

    public function getLastSyncOrDefault(CarbonInterface $now): CarbonInterface
    {
        $ts = Cache::get($this->lastSyncKey());
        if ($ts) {
            return \Illuminate\Support\Carbon::parse($ts);
        }

        $days = (int) config('trading212.initial_lookback_days', 30);

        return $now->copy()->subDays(max(1, $days));
    }

    public function setLastSync(CarbonInterface $when): void
    {
        Cache::forever($this->lastSyncKey(), $when->toIso8601String());
    }
}
