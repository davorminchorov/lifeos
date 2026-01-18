<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_freelance_rate_calculator_page_loads_successfully(): void
    {
        $response = $this->get(route('currency.freelance-rate-calculator'));

        $response->assertStatus(200);
        $response->assertViewIs('currency.freelance-rate-calculator');
    }

    public function test_index_page_loads_successfully(): void
    {
        $response = $this->get(route('currency.index'));

        $response->assertStatus(200);
        $response->assertViewIs('currency.index');
        $response->assertViewHas('currencyRates');
        $response->assertViewHas('defaultCurrency');
    }

    public function test_index_page_displays_currency_rates(): void
    {
        $response = $this->get(route('currency.index'));

        $response->assertStatus(200);
        $currencyRates = $response->viewData('currencyRates');
        $this->assertIsArray($currencyRates);

        if (count($currencyRates) > 0) {
            $this->assertArrayHasKey('from_currency', $currencyRates[0]);
            $this->assertArrayHasKey('to_currency', $currencyRates[0]);
            $this->assertArrayHasKey('rate_info', $currencyRates[0]);
            $this->assertArrayHasKey('is_fresh', $currencyRates[0]);
            $this->assertArrayHasKey('is_stale', $currencyRates[0]);
        }
    }

    public function test_refresh_rate_succeeds_with_valid_currencies(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'conversion_rates' => ['EUR' => 0.92],
            ], 200),
        ]);

        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'rate',
                'freshness',
                'last_updated',
                'age_seconds',
            ],
        ]);
    }

    public function test_refresh_rate_validates_required_fields(): void
    {
        $response = $this->post(route('currency.refresh-rate'), []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid request data.',
        ]);
        $response->assertJsonValidationErrors(['from_currency', 'to_currency']);
    }

    public function test_refresh_rate_validates_currency_code_length(): void
    {
        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'US',
            'to_currency' => 'EURO',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['from_currency', 'to_currency']);
    }

    public function test_refresh_rate_rejects_unsupported_from_currency(): void
    {
        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'XXX',
            'to_currency' => 'USD',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Currency XXX is not supported.',
        ]);
    }

    public function test_refresh_rate_rejects_unsupported_to_currency(): void
    {
        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'USD',
            'to_currency' => 'XXX',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Currency XXX is not supported.',
        ]);
    }

    public function test_refresh_rate_handles_lowercase_currencies(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'conversion_rates' => ['EUR' => 0.92],
            ], 200),
        ]);

        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'usd',
            'to_currency' => 'eur',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_refresh_rate_returns_error_when_service_fails(): void
    {
        // Mock the CurrencyService to return null (failure)
        $mockService = Mockery::mock(CurrencyService::class);
        $mockService->shouldReceive('isSupported')->andReturn(true);
        $mockService->shouldReceive('refreshExchangeRate')->andReturn(null);

        $this->app->instance(CurrencyService::class, $mockService);

        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to fetch fresh exchange rate. Please try again later.',
        ]);
    }

    public function test_get_freshness_info_succeeds_with_valid_currencies(): void
    {
        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'rate',
                'freshness',
                'freshness_label',
                'last_updated',
                'age_seconds',
                'formatted_age',
                'is_fresh',
                'is_stale',
            ],
        ]);
    }

    public function test_get_freshness_info_validates_required_fields(): void
    {
        $response = $this->get(route('currency.freshness-info'));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid request data.',
        ]);
    }

    public function test_get_freshness_info_validates_currency_code_length(): void
    {
        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'US',
            'to_currency' => 'EURO',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['from_currency', 'to_currency']);
    }

    public function test_get_freshness_info_rejects_unsupported_currencies(): void
    {
        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'XXX',
            'to_currency' => 'USD',
        ]));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'One or both currencies are not supported.',
        ]);
    }

    public function test_get_freshness_info_handles_lowercase_currencies(): void
    {
        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'usd',
            'to_currency' => 'eur',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_get_freshness_info_returns_freshness_status(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'conversion_rates' => ['EUR' => 0.92],
            ], 200),
        ]);

        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]));

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertIsFloat($data['rate']);
        $this->assertIsInt($data['age_seconds']);
        $this->assertIsBool($data['is_fresh']);
        $this->assertIsBool($data['is_stale']);
        $this->assertIsString($data['freshness_label']);
        $this->assertIsString($data['formatted_age']);
    }

    public function test_unauthenticated_users_cannot_access_currency_pages(): void
    {
        $this->app['auth']->logout();

        $response = $this->get(route('currency.index'));
        $response->assertRedirect('/login');

        $response = $this->get(route('currency.freelance-rate-calculator'));
        $response->assertRedirect('/login');

        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);
        $response->assertRedirect('/login');

        $response = $this->get(route('currency.freshness-info', [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]));
        $response->assertRedirect('/login');
    }

    public function test_refresh_rate_updates_cached_exchange_rate(): void
    {
        Http::fake([
            'v6.exchangerate-api.com/*' => Http::response([
                'conversion_rates' => ['EUR' => 0.92],
            ], 200),
        ]);

        // Clear any existing cache
        Cache::flush();

        $response = $this->post(route('currency.refresh-rate'), [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        $response->assertStatus(200);

        // Verify the rate was cached
        $cacheKey = 'exchange_rate_USD_EUR';
        $this->assertTrue(Cache::has($cacheKey));
    }
}
