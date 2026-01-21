<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencyService = new CurrencyService;
    }

    public function test_can_convert_mkd_to_default_currency(): void
    {
        $amount = 1000.0;
        $result = $this->currencyService->convertToDefault($amount, 'MKD');

        $this->assertEquals($amount, $result);
    }

    public function test_can_convert_usd_to_default_currency(): void
    {
        $amount = 100.0;
        $result = $this->currencyService->convertToDefault($amount, 'USD');

        // USD to MKD conversion
        $this->assertIsFloat($result);
    }

    public function test_can_convert_eur_to_default_currency(): void
    {
        $amount = 100.0;
        $result = $this->currencyService->convertToDefault($amount, 'EUR');

        // EUR to MKD conversion
        $this->assertIsFloat($result);
    }

    public function test_can_convert_default_to_usd(): void
    {
        $amount = 5800.0; // 5800 MKD
        $result = $this->currencyService->convertFromDefault($amount, 'USD');

        $this->assertIsFloat($result);
    }

    public function test_can_convert_default_to_eur(): void
    {
        $amount = 6100.0; // 6100 MKD
        $result = $this->currencyService->convertFromDefault($amount, 'EUR');

        $this->assertIsFloat($result);
    }

    public function test_can_convert_default_to_mkd(): void
    {
        $amount = 1000.0;
        $result = $this->currencyService->convertFromDefault($amount, 'MKD');

        $this->assertEquals($amount, $result);
    }

    public function test_handles_unknown_currency_gracefully(): void
    {
        $amount = 100.0;
        $result = $this->currencyService->convertToDefault($amount, 'UNKNOWN');

        // Should return original amount for unknown currencies
        $this->assertEquals($amount, $result);
    }

    public function test_handles_empty_currency(): void
    {
        $amount = 100.0;
        $result = $this->currencyService->convertToDefault($amount, '');

        // Should return original amount for empty currency
        $this->assertEquals($amount, $result);
    }

    public function test_handles_zero_amount(): void
    {
        $amount = 0.0;
        $result = $this->currencyService->convertToDefault($amount, 'USD');

        $this->assertEquals(0.0, $result);
    }

    public function test_handles_negative_amount(): void
    {
        $amount = -100.0;
        $result = $this->currencyService->convertToDefault($amount, 'USD');

        $this->assertLessThan(0, $result);
        $this->assertIsFloat($result);
    }

    public function test_conversion_rates_are_consistent(): void
    {
        $amount = 100.0;

        // Convert USD to MKD and back
        $toDefault = $this->currencyService->convertToDefault($amount, 'USD');
        $backToOriginal = $this->currencyService->convertFromDefault($toDefault, 'USD');

        // Should be approximately the same (allowing for small rounding differences)
        $this->assertEqualsWithDelta($amount, $backToOriginal, 0.02);
    }

    public function test_get_supported_currencies(): void
    {
        $currencies = $this->currencyService->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertNotEmpty($currencies);
    }

    public function test_format_currency_with_mkd(): void
    {
        $amount = 1234.56;
        $formatted = $this->currencyService->format($amount, 'MKD');

        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertIsString($formatted);
    }

    public function test_format_currency_with_usd(): void
    {
        $amount = 1234.56;
        $formatted = $this->currencyService->format($amount, 'USD');

        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertIsString($formatted);
    }

    public function test_format_currency_with_eur(): void
    {
        $amount = 1234.56;
        $formatted = $this->currencyService->format($amount, 'EUR');

        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertIsString($formatted);
    }

    public function test_get_exchange_rate(): void
    {
        $rate = $this->currencyService->getExchangeRate('USD', 'MKD');

        $this->assertTrue(is_float($rate) || is_null($rate));
        if ($rate !== null) {
            $this->assertGreaterThan(0, $rate);
        }
    }

    public function test_get_exchange_rate_for_mkd(): void
    {
        $rate = $this->currencyService->getExchangeRate('MKD', 'MKD');

        $this->assertEquals(1.0, $rate);
    }

    public function test_get_exchange_rate_for_unknown_currency(): void
    {
        $rate = $this->currencyService->getExchangeRate('UNKNOWN', 'MKD');

        $this->assertNull($rate); // Should return null for unknown currencies
    }

    public function test_get_tenant_default_currency_returns_tenant_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'USD',
            'owner_id' => $user->id,
        ]);

        $currency = $this->currencyService->getTenantDefaultCurrency($tenant);

        $this->assertEquals('USD', $currency);
    }

    public function test_get_tenant_default_currency_returns_global_default_when_no_tenant_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => null,
            'owner_id' => $user->id,
        ]);

        $currency = $this->currencyService->getTenantDefaultCurrency($tenant);

        $this->assertEquals('MKD', $currency);
    }

    public function test_get_tenant_default_currency_returns_global_default_when_null_tenant(): void
    {
        $currency = $this->currencyService->getTenantDefaultCurrency(null);

        $this->assertEquals('MKD', $currency);
    }

    public function test_convert_to_tenant_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'USD',
            'owner_id' => $user->id,
        ]);

        $amount = 6100.0; // EUR amount
        $result = $this->currencyService->convertToTenantCurrency($amount, 'EUR', $tenant);

        $this->assertIsFloat($result);
    }

    public function test_convert_to_tenant_currency_same_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'USD',
            'owner_id' => $user->id,
        ]);

        $amount = 100.0;
        $result = $this->currencyService->convertToTenantCurrency($amount, 'USD', $tenant);

        $this->assertEquals($amount, $result);
    }

    public function test_convert_from_tenant_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'USD',
            'owner_id' => $user->id,
        ]);

        $amount = 100.0;
        $result = $this->currencyService->convertFromTenantCurrency($amount, 'EUR', $tenant);

        $this->assertIsFloat($result);
    }

    public function test_format_for_tenant(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'USD',
            'owner_id' => $user->id,
        ]);

        $amount = 1234.56;
        $formatted = $this->currencyService->formatForTenant($amount, $tenant);

        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }

    public function test_format_for_tenant_with_different_currency(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'default_currency' => 'EUR',
            'owner_id' => $user->id,
        ]);

        $amount = 1234.56;
        $formatted = $this->currencyService->formatForTenant($amount, $tenant);

        $this->assertStringContainsString('1,234.56', $formatted);
        $this->assertStringContainsString('€', $formatted);
    }
}
