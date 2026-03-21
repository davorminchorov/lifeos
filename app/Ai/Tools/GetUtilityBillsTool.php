<?php

namespace App\Ai\Tools;

use App\Models\UtilityBill;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetUtilityBillsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get utility bills — overdue, pending, and upcoming due dates.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $currency = resolve(CurrencyService::class);
        $userId = Auth::id();
        $toDefault = fn ($b) => $currency->convertToDefault((float) $b->bill_amount, $b->currency ?? config('currency.default', 'MKD'));

        $overdue = UtilityBill::where('user_id', $userId)->overdue()->get();
        $pending = UtilityBill::where('user_id', $userId)->pending()->orderBy('due_date')->get();

        return json_encode([
            'overdue_count' => $overdue->count(),
            'overdue_total' => round($overdue->sum($toDefault), 2),
            'pending_count' => $pending->count(),
            'pending_total' => round($pending->sum($toDefault), 2),
            'currency' => config('currency.default', 'MKD'),
            'overdue' => $overdue->map(fn ($b) => [
                'provider' => $b->service_provider,
                'amount' => $b->bill_amount,
                'currency' => $b->currency,
                'due_date' => $b->due_date?->format('Y-m-d'),
                'days_overdue' => $b->due_date ? now()->diffInDays($b->due_date) : null,
            ])->values(),
            'upcoming' => $pending->take(5)->map(fn ($b) => [
                'provider' => $b->service_provider,
                'amount' => $b->bill_amount,
                'currency' => $b->currency,
                'due_date' => $b->due_date?->format('Y-m-d'),
                'days_until_due' => $b->due_date ? now()->diffInDays($b->due_date, false) : null,
            ])->values(),
        ]);
    }
}
