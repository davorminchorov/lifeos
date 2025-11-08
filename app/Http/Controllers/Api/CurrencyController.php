<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CurrencyController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    /**
     * Get exchange rate for a currency pair.
     */
    public function getExchangeRate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_currency' => 'required|string|size:3',
                'to_currency' => 'required|string|size:3',
            ]);

            $fromCurrency = strtoupper($validated['from_currency']);
            $toCurrency = strtoupper($validated['to_currency']);

            // Check if currencies are supported
            if (! $this->currencyService->isSupported($fromCurrency)) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency {$fromCurrency} is not supported.",
                ], 400);
            }

            if (! $this->currencyService->isSupported($toCurrency)) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency {$toCurrency} is not supported.",
                ], 400);
            }

            $rate = $this->currencyService->getExchangeRate($fromCurrency, $toCurrency);

            if ($rate === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch exchange rate. Please try again later.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'rate' => $rate,
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Get exchange rate failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the exchange rate.',
            ], 500);
        }
    }
}
