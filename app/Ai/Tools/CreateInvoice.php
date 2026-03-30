<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateInvoice extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new draft invoice for a customer with line items.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_name' => $schema->string()->required()->description('Customer name to find'),
            'items' => $schema->array()->required()->description('Array of line items, each with: name (string), quantity (number), unit_amount (number in whole currency units)'),
            'currency' => $schema->string()->description('3-letter currency code, defaults to MKD'),
            'due_days' => $schema->integer()->description('Days until due, defaults to 30'),
            'notes' => $schema->string()->description('Customer-facing notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $customerName = $request['customer_name'] ?? null;

        $customers = $this->scopedQuery(Customer::class)
            ->where('name', 'LIKE', '%'.$customerName.'%')
            ->limit(5)
            ->get();

        if ($customers->isEmpty()) {
            $available = $this->scopedQuery(Customer::class)
                ->pluck('name')
                ->implode(', ');

            return "No customer found matching '{$customerName}'. Available customers: {$available}";
        }

        if ($customers->count() > 1) {
            $names = $customers->pluck('name')->implode(', ');

            return "Multiple customers match '{$customerName}'. Please be more specific: {$names}";
        }

        $customer = $customers->first();
        $items = $request['items'] ?? [];

        if (empty($items) || ! is_array($items)) {
            return 'At least one line item is required. Each item needs: name, quantity, unit_amount.';
        }

        $currency = $request['currency'] ?? 'MKD';
        $dueDays = (int) ($request['due_days'] ?? 30);
        $now = now();

        $subtotal = 0;
        foreach ($items as $item) {
            if (empty($item['name']) || ! isset($item['quantity']) || ! isset($item['unit_amount'])) {
                return 'Each item must have name, quantity, and unit_amount.';
            }
            $subtotal += (int) (((float) $item['quantity']) * ((float) $item['unit_amount']) * 100);
        }

        $invoice = Invoice::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::DRAFT,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => $subtotal,
            'amount_due' => $subtotal,
            'amount_paid' => 0,
            'issued_at' => $now,
            'due_at' => $now->addDays($dueDays),
            'notes' => $request['notes'] ?? null,
        ]);

        foreach ($items as $index => $item) {
            $unitAmountCents = (int) ((float) $item['unit_amount'] * 100);
            $quantity = (float) $item['quantity'];
            $amountCents = (int) ($quantity * $unitAmountCents);

            InvoiceItem::create([
                'tenant_id' => $this->tenantId,
                'invoice_id' => $invoice->id,
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'quantity' => $quantity,
                'unit_amount' => $unitAmountCents,
                'currency' => $currency,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'amount' => $amountCents,
                'total_amount' => $amountCents,
                'sort_order' => $index,
            ]);
        }

        return sprintf(
            'Created draft invoice for %s: %d items, %s %s total, due %s.',
            $customer->name,
            count($items),
            number_format($subtotal / 100, 2),
            $currency,
            $now->addDays($dueDays)->format('Y-m-d'),
        );
    }
}
