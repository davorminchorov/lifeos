<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateWarranty extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new warranty record for a product.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_name' => $schema->string()->required()->description('Name of the product'),
            'brand' => $schema->string()->description('Brand name'),
            'model' => $schema->string()->description('Model number or name'),
            'serial_number' => $schema->string()->description('Serial number'),
            'purchase_date' => $schema->string()->required()->description('YYYY-MM-DD purchase date'),
            'purchase_price' => $schema->number()->description('Purchase price'),
            'retailer' => $schema->string()->description('Where it was purchased'),
            'warranty_expiration_date' => $schema->string()->required()->description('YYYY-MM-DD warranty expiration'),
            'warranty_type' => $schema->string()->description('Type: manufacturer, extended, or both. Defaults to manufacturer'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $data = [
            'product_name' => $request['product_name'] ?? null,
            'brand' => $request['brand'] ?? null,
            'model' => $request['model'] ?? null,
            'serial_number' => $request['serial_number'] ?? null,
            'purchase_date' => $request['purchase_date'] ?? null,
            'purchase_price' => $request['purchase_price'] ?? null,
            'retailer' => $request['retailer'] ?? '',
            'warranty_expiration_date' => $request['warranty_expiration_date'] ?? null,
            'warranty_type' => $request['warranty_type'] ?? 'manufacturer',
            'notes' => $request['notes'] ?? null,
        ];

        $validated = $this->validate($data, [
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'retailer' => 'string|max:255',
            'warranty_expiration_date' => 'required|date|after:purchase_date',
            'warranty_type' => 'string|in:manufacturer,extended,both',
            'notes' => 'nullable|string|max:10000',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        $purchaseDate = CarbonImmutable::parse($validated['purchase_date']);
        $expirationDate = CarbonImmutable::parse($validated['warranty_expiration_date']);
        $durationMonths = (int) $purchaseDate->diffInMonths($expirationDate);

        Warranty::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'current_status' => 'active',
            'warranty_duration_months' => $durationMonths,
            ...$validated,
        ]);

        $brandModel = array_filter([$validated['brand'], $validated['model']]);
        $brandDisplay = $brandModel ? ' ('.implode(' ', $brandModel).')' : '';

        return sprintf(
            "Created warranty for '%s'%s: purchased %s, expires %s.",
            $validated['product_name'],
            $brandDisplay,
            $validated['purchase_date'],
            $validated['warranty_expiration_date'],
        );
    }
}
