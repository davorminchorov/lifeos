<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
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
        $this->assertEqualsWithDelta($amount, $backToOriginal, 0.01);
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
}
