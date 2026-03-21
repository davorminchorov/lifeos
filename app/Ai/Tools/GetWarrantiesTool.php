<?php

namespace App\Ai\Tools;

use App\Models\Warranty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetWarrantiesTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get active product warranties, especially those expiring within 90 days.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $userId = Auth::id();

        $warranties = Warranty::where('user_id', $userId)->active()->get();
        $expiring = Warranty::where('user_id', $userId)->expiringSoon(90)->get();

        return json_encode([
            'active_count' => $warranties->count(),
            'expiring_in_90_days' => $expiring->count(),
            'expiring_soon' => $expiring->map(fn ($w) => [
                'product' => $w->product_name,
                'expiry_date' => $w->warranty_expiration_date?->format('Y-m-d'),
                'days_remaining' => $w->warranty_expiration_date ? now()->diffInDays($w->warranty_expiration_date, false) : null,
            ])->sortBy('days_remaining')->values(),
        ]);
    }
}
