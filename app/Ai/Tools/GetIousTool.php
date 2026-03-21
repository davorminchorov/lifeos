<?php

namespace App\Ai\Tools;

use App\Models\Iou;
use App\Services\CurrencyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetIousTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get outstanding IOUs — money owed to the user and money the user owes others.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $currency = resolve(CurrencyService::class);
        $userId = Auth::id();
        $toDefault = fn ($i) => $currency->convertToDefault((float) $i->amount, $i->currency ?? config('currency.default', 'MKD'));

        $ious = Iou::where('user_id', $userId)->whereNotIn('status', ['settled'])->get();

        if ($ious->isEmpty()) {
            return 'No outstanding IOUs.';
        }

        $owedToMe = $ious->where('type', 'owed_to_me');
        $owedByMe = $ious->where('type', 'owed_by_me');

        return json_encode([
            'net_position' => round($owedToMe->sum($toDefault) - $owedByMe->sum($toDefault), 2),
            'owed_to_me_total' => round($owedToMe->sum($toDefault), 2),
            'owed_by_me_total' => round($owedByMe->sum($toDefault), 2),
            'currency' => config('currency.default', 'MKD'),
            'owed_to_me' => $owedToMe->map(fn ($i) => [
                'person' => $i->person_name,
                'amount' => $i->amount,
                'currency' => $i->currency,
                'due_date' => $i->due_date?->format('Y-m-d'),
                'description' => $i->description,
                'days_overdue' => $i->due_date?->isPast() ? now()->diffInDays($i->due_date) : null,
            ])->values(),
            'owed_by_me' => $owedByMe->map(fn ($i) => [
                'person' => $i->person_name,
                'amount' => $i->amount,
                'currency' => $i->currency,
                'due_date' => $i->due_date?->format('Y-m-d'),
                'description' => $i->description,
            ])->values(),
        ]);
    }
}
