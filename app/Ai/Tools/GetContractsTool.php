<?php

namespace App\Ai\Tools;

use App\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetContractsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get active contracts, their values, and any expiring within 60 days.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $userId = Auth::id();

        $contracts = Contract::where('user_id', $userId)->active()->get();
        $expiring = Contract::where('user_id', $userId)->expiringSoon(60)->get();

        return json_encode([
            'active_count' => $contracts->count(),
            'expiring_in_60_days' => $expiring->count(),
            'contracts' => $contracts->map(fn ($c) => [
                'title' => $c->title,
                'counterparty' => $c->counterparty,
                'value' => $c->contract_value,
                'currency' => $c->currency,
                'end_date' => $c->end_date?->format('Y-m-d'),
                'days_until_expiry' => $c->end_date ? now()->diffInDays($c->end_date, false) : null,
                'category' => $c->category,
            ])->sortBy('days_until_expiry')->values(),
        ]);
    }
}
