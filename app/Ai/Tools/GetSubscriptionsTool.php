<?php

namespace App\Ai\Tools;

use App\Models\Subscription;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSubscriptionsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get all subscriptions with their costs, status, and upcoming renewal dates.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $currency = resolve(CurrencyService::class);
        $userId = Auth::id();

        $subs = Subscription::where('user_id', $userId)->get();

        if ($subs->isEmpty()) {
            return 'No subscriptions found.';
        }

        $active = $subs->where('status', 'active');
        $toDefault = fn ($s) => $currency->convertToDefault((float) $s->cost, $s->currency ?? config('currency.default', 'MKD'));
        $monthlyTotal = $active->sum($toDefault);

        return json_encode([
            'active_count' => $active->count(),
            'total_count' => $subs->count(),
            'monthly_total' => round($monthlyTotal, 2),
            'currency' => config('currency.default', 'MKD'),
            'subscriptions' => $active->map(fn ($s) => [
                'name' => $s->service_name,
                'cost' => $s->cost,
                'currency' => $s->currency,
                'billing_cycle' => $s->billing_cycle,
                'next_billing_date' => $s->next_billing_date?->format('Y-m-d'),
                'category' => $s->category,
                'days_until_renewal' => $s->next_billing_date ? now()->diffInDays($s->next_billing_date, false) : null,
            ])->sortBy('days_until_renewal')->values(),
        ]);
    }
}
