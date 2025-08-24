<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    /**
     * Show the currency freshness overview page.
     */
    public function index(): View
    {
        $defaultCurrency = config('currency.default', 'MKD');
        $supportedCurrencies = array_keys(config('currency.supported', [
            'MKD' => [], 'USD' => [], 'EUR' => [], 'GBP' => [], 'CAD' => [], 'AUD' => [], 'CHF' => [], 'RSD' => [], 'BGN' => []
        ]));

        $currencyRates = [];

        foreach ($supportedCurrencies as $currency) {
            if ($currency !== $defaultCurrency) {
                $rateInfo = $this->currencyService->getExchangeRateWithFreshness($currency, $defaultCurrency);
                $currencyRates[] = [
                    'from_currency' => $currency,
                    'to_currency' => $defaultCurrency,
                    'rate_info' => $rateInfo,
                    'is_fresh' => $this->currencyService->isRateFresh($currency, $defaultCurrency),
                    'is_stale' => $this->currencyService->isRateStale($currency, $defaultCurrency),
                    'formatted_age' => $this->currencyService->getFormattedAge($rateInfo['age_seconds']),
                ];
            }
        }

        return view('currency.index', compact('currencyRates', 'defaultCurrency'));
    }

    /**
     * Refresh exchange rate for a currency pair.
     */
    public function refreshRate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_currency' => 'required|string|size:3',
                'to_currency' => 'required|string|size:3',
            ]);

            $fromCurrency = strtoupper($validated['from_currency']);
            $toCurrency = strtoupper($validated['to_currency']);

            // Check if currencies are supported
            if (!$this->currencyService->isSupported($fromCurrency)) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency {$fromCurrency} is not supported.",
                ], 400);
            }

            if (!$this->currencyService->isSupported($toCurrency)) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency {$toCurrency} is not supported.",
                ], 400);
            }

            // Refresh the exchange rate
            $newRate = $this->currencyService->refreshExchangeRate($fromCurrency, $toCurrency);

            if ($newRate === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch fresh exchange rate. Please try again later.',
                ], 500);
            }

            // Get updated freshness information
            $rateInfo = $this->currencyService->getExchangeRateWithFreshness($fromCurrency, $toCurrency);

            return response()->json([
                'success' => true,
                'message' => 'Exchange rate refreshed successfully.',
                'data' => [
                    'rate' => $newRate,
                    'freshness' => $rateInfo['freshness'],
                    'last_updated' => $rateInfo['last_updated'],
                    'age_seconds' => $rateInfo['age_seconds'],
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Currency refresh failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while refreshing the exchange rate.',
            ], 500);
        }
    }

    /**
     * Get freshness information for a currency pair.
     */
    public function getFreshnessInfo(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_currency' => 'required|string|size:3',
                'to_currency' => 'required|string|size:3',
            ]);

            $fromCurrency = strtoupper($validated['from_currency']);
            $toCurrency = strtoupper($validated['to_currency']);

            // Check if currencies are supported
            if (!$this->currencyService->isSupported($fromCurrency) || !$this->currencyService->isSupported($toCurrency)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or both currencies are not supported.',
                ], 400);
            }

            $rateInfo = $this->currencyService->getExchangeRateWithFreshness($fromCurrency, $toCurrency);
            $formattedAge = $this->currencyService->getFormattedAge($rateInfo['age_seconds']);

            return response()->json([
                'success' => true,
                'data' => [
                    'rate' => $rateInfo['rate'],
                    'freshness' => $rateInfo['freshness'],
                    'freshness_label' => $this->currencyService->getFreshnessLabel($rateInfo['freshness']),
                    'last_updated' => $rateInfo['last_updated'],
                    'age_seconds' => $rateInfo['age_seconds'],
                    'formatted_age' => $formattedAge,
                    'is_fresh' => $this->currencyService->isRateFresh($fromCurrency, $toCurrency),
                    'is_stale' => $this->currencyService->isRateStale($fromCurrency, $toCurrency),
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Currency freshness info failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving freshness information.',
            ], 500);
        }
    }
}
