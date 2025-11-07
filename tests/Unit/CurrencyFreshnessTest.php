<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyFreshnessTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyService = app(CurrencyService::class);
    }

    public function test_calculates_fresh_rate_correctly(): void
    {
        $timestamp = now()->timestamp - 3600; // 1 hour ago
        Config::set('currency.freshness.fresh_threshold', 3600 * 4); // 4 hours

        $freshness = $this->currencyService->calculateFreshness($timestamp);

        $this->assertEquals('fresh', $freshness);
    }

    public function test_calculates_stale_rate_correctly(): void
    {
        $timestamp = now()->timestamp - (3600 * 8); // 8 hours ago
        Config::set('currency.freshness.fresh_threshold', 3600 * 4); // 4 hours
        Config::set('currency.freshness.stale_threshold', 3600 * 12); // 12 hours

        $freshness = $this->currencyService->calculateFreshness($timestamp);

        $this->assertEquals('stale', $freshness);
    }

    public function test_calculates_warning_rate_correctly(): void
    {
        $timestamp = now()->timestamp - (3600 * 18); // 18 hours ago
        Config::set('currency.freshness.fresh_threshold', 3600 * 4); // 4 hours
        Config::set('currency.freshness.stale_threshold', 3600 * 12); // 12 hours
        Config::set('currency.freshness.warning_threshold', 3600 * 24); // 24 hours

        $freshness = $this->currencyService->calculateFreshness($timestamp);

        $this->assertEquals('warning', $freshness);
    }

    public function test_calculates_unknown_for_very_old_rate(): void
    {
        $timestamp = now()->timestamp - (3600 * 30); // 30 hours ago
        Config::set('currency.freshness.warning_threshold', 3600 * 24); // 24 hours

        $freshness = $this->currencyService->calculateFreshness($timestamp);

        $this->assertEquals('unknown', $freshness);
    }

    public function test_calculates_unknown_for_null_timestamp(): void
    {
        $freshness = $this->currencyService->calculateFreshness(null);

        $this->assertEquals('unknown', $freshness);
    }

    public function test_stores_timestamp_when_fetching_rate(): void
    {
        Http::fake([
            'api.exchangerate-api.io/*' => Http::response([
                'rates' => ['EUR' => 1.2],
            ], 200),
        ]);

        Cache::flush();

        $this->currencyService->getExchangeRate('USD', 'EUR');

        // Check that timestamp was stored
        $timestampKey = 'exchange_rate_timestamp_USD_EUR';
        $timestamp = Cache::get($timestampKey);

        $this->assertNotNull($timestamp);
        $this->assertIsInt($timestamp);
        $this->assertLessThanOrEqual(now()->timestamp, $timestamp);
        $this->assertGreaterThan(now()->timestamp - 10, $timestamp); // Within last 10 seconds
    }

    public function test_get_exchange_rate_with_freshness(): void
    {
        Http::fake([
            'api.exchangerate-api.io/*' => Http::response([
                'rates' => ['EUR' => 1.2],
            ], 200),
        ]);

        Cache::flush();

        $result = $this->currencyService->getExchangeRateWithFreshness('USD', 'EUR');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('rate', $result);
        $this->assertArrayHasKey('freshness', $result);
        $this->assertArrayHasKey('last_updated', $result);
        $this->assertArrayHasKey('age_seconds', $result);

        $this->assertEquals(1.2, $result['rate']);
        $this->assertEquals('fresh', $result['freshness']);
        $this->assertNotNull($result['last_updated']);
        $this->assertIsInt($result['age_seconds']);
    }

    public function test_same_currency_returns_fresh_rate(): void
    {
        $result = $this->currencyService->getExchangeRateWithFreshness('USD', 'USD');

        $this->assertEquals(1.0, $result['rate']);
        $this->assertEquals('fresh', $result['freshness']);
        $this->assertEquals(0, $result['age_seconds']);
        $this->assertNotNull($result['last_updated']);
    }

    public function test_is_rate_fresh(): void
    {
        Http::fake([
            'api.exchangerate-api.io/*' => Http::response([
                'rates' => ['EUR' => 1.2],
            ], 200),
        ]);

        Cache::flush();

        // Fresh rate
        $this->assertTrue($this->currencyService->isRateFresh('USD', 'EUR'));

        // Same currency is always fresh
        $this->assertTrue($this->currencyService->isRateFresh('USD', 'USD'));
    }

    public function test_is_rate_stale(): void
    {
        // Mock old timestamp
        $timestampKey = 'exchange_rate_timestamp_USD_EUR';
        $cacheKey = 'exchange_rate_USD_EUR';
        $oldTimestamp = now()->timestamp - (3600 * 30); // 30 hours ago

        Cache::put($timestampKey, $oldTimestamp, 3600);
        Cache::put($cacheKey, 1.2, 3600);

        $this->assertTrue($this->currencyService->isRateStale('USD', 'EUR'));

        // Same currency is never stale
        $this->assertFalse($this->currencyService->isRateStale('USD', 'USD'));
    }

    public function test_refresh_exchange_rate_clears_cache(): void
    {
        Http::fake([
            'api.exchangerate-api.io/*' => Http::response([
                'rates' => ['EUR' => 1.5],
            ], 200),
        ]);

        // Set up cached data
        $timestampKey = 'exchange_rate_timestamp_USD_EUR';
        $cacheKey = 'exchange_rate_USD_EUR';
        Cache::put($timestampKey, now()->timestamp - 3600, 3600);
        Cache::put($cacheKey, 1.2, 3600);

        $this->assertEquals(1.2, Cache::get($cacheKey));

        // Refresh should clear cache and get new rate
        $newRate = $this->currencyService->refreshExchangeRate('USD', 'EUR');

        $this->assertEquals(1.5, $newRate);
    }

    public function test_get_formatted_age(): void
    {
        $this->assertEquals('Unknown', $this->currencyService->getFormattedAge(null));
        $this->assertEquals('Less than 1 minute ago', $this->currencyService->getFormattedAge(30));
        $this->assertEquals('5 minutes ago', $this->currencyService->getFormattedAge(300));
        $this->assertEquals('1 hour ago', $this->currencyService->getFormattedAge(3600));
        $this->assertEquals('2 hours ago', $this->currencyService->getFormattedAge(7200));
        $this->assertEquals('1 day ago', $this->currencyService->getFormattedAge(86400));
        $this->assertEquals('3 days ago', $this->currencyService->getFormattedAge(259200));
    }

    public function test_get_freshness_label(): void
    {
        Config::set('currency.freshness.labels', [
            'fresh' => 'Fresh',
            'stale' => 'Stale',
            'warning' => 'Very Stale',
            'unknown' => 'Unknown',
        ]);

        $this->assertEquals('Fresh', $this->currencyService->getFreshnessLabel('fresh'));
        $this->assertEquals('Stale', $this->currencyService->getFreshnessLabel('stale'));
        $this->assertEquals('Very Stale', $this->currencyService->getFreshnessLabel('warning'));
        $this->assertEquals('Unknown', $this->currencyService->getFreshnessLabel('unknown'));
    }

    public function test_get_freshness_color(): void
    {
        Config::set('currency.freshness.colors', [
            'fresh' => 'green',
            'stale' => 'yellow',
            'warning' => 'red',
            'unknown' => 'gray',
        ]);

        $this->assertEquals('green', $this->currencyService->getFreshnessColor('fresh'));
        $this->assertEquals('yellow', $this->currencyService->getFreshnessColor('stale'));
        $this->assertEquals('red', $this->currencyService->getFreshnessColor('warning'));
        $this->assertEquals('gray', $this->currencyService->getFreshnessColor('unknown'));
    }
}
