<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Warranty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class FileWarrantyClaim extends TenantScopedTool
{
    public function description(): string
    {
        return 'File a warranty claim for an existing product warranty.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_name' => $schema->string()->required()->description('Product name to find the warranty'),
            'reason' => $schema->string()->required()->description('Reason for the warranty claim'),
            'description' => $schema->string()->description('Detailed description of the issue'),
            'claim_date' => $schema->string()->description('YYYY-MM-DD claim date, defaults to today'),
        ];
    }

    public function handle(Request $request): string
    {
        $productName = $request['product_name'] ?? null;

        $matches = $this->scopedQuery(Warranty::class)
            ->where('product_name', 'LIKE', '%'.$productName.'%')
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(Warranty::class)
                ->pluck('product_name')
                ->implode(', ');

            return "No warranty found matching '{$productName}'. Available warranties: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->pluck('product_name')->implode(', ');

            return "Multiple warranties match '{$productName}'. Please be more specific: {$names}";
        }

        $warranty = $matches->first();

        $data = [
            'reason' => $request['reason'] ?? null,
            'description' => $request['description'] ?? null,
            'claim_date' => $request['claim_date'] ?? date('Y-m-d'),
        ];

        $validated = $this->validate($data, [
            'reason' => 'required|string|max:500',
            'description' => 'nullable|string|max:10000',
            'claim_date' => 'required|date',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        $claimHistory = $warranty->claim_history ?? [];
        $claimNumber = count($claimHistory) + 1;

        $claimHistory[] = [
            'date' => $validated['claim_date'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'filed',
        ];

        $warranty->update([
            'claim_history' => $claimHistory,
            'current_status' => 'claimed',
        ]);

        $expiredNote = $warranty->is_expired ? ' (note: warranty is expired)' : '';

        return sprintf(
            "Filed warranty claim #%d for '%s': %s%s.",
            $claimNumber,
            $warranty->product_name,
            $validated['reason'],
            $expiredNote,
        );
    }
}
