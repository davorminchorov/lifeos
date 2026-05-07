<?php

declare(strict_types=1);

namespace App\Services\Agents;

use InvalidArgumentException;

/**
 * Per-tool idempotency-key generators. Keys are deterministic SHA-256 of natural
 * fields, scoped to the tenant. Resubmitting the same logical write yields the
 * same key, so the unique (tenant_id, tool, idempotency_key) index on
 * pending_actions blocks duplicates.
 */
class IdempotencyKey
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function for(string $tool, int $tenantId, array $payload): string
    {
        $material = match ($tool) {
            'expenses.create' => $this->expensesCreate($tenantId, $payload),
            'expenses.bulkImport' => $this->expensesBulkImport($tenantId, $payload),
            'expenses.categorize' => $this->expensesCategorize($tenantId, $payload),
            default => throw new InvalidArgumentException("No idempotency-key generator registered for tool [{$tool}]."),
        };

        return hash('sha256', $material);
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function expensesCreate(int $tenantId, array $p): string
    {
        $merchant = $this->normalize((string) ($p['merchant'] ?? ''));
        $amount = $this->amountCents($p['amount'] ?? 0);
        $currency = strtoupper((string) ($p['currency'] ?? ''));
        $date = (string) ($p['expense_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "expenses.create|{$tenantId}|{$merchant}|{$amount}|{$currency}|{$date}|{$sourceEmail}";
    }

    /**
     * Bulk import — key over the canonical hash of the items array. Reorderings
     * that keep the same items collide; duplicate items collide.
     *
     * @param  array<string, mixed>  $p
     */
    private function expensesBulkImport(int $tenantId, array $p): string
    {
        $items = (array) ($p['items'] ?? []);

        $itemKeys = array_map(
            fn (array $item): string => $this->expensesCreate($tenantId, $item),
            $items,
        );

        sort($itemKeys);

        return "expenses.bulkImport|{$tenantId}|".implode("\n", $itemKeys);
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function expensesCategorize(int $tenantId, array $p): string
    {
        $expenseId = (int) ($p['expense_id'] ?? 0);
        $category = $this->normalize((string) ($p['category'] ?? ''));
        $subcategory = $this->normalize((string) ($p['subcategory'] ?? ''));

        return "expenses.categorize|{$tenantId}|{$expenseId}|{$category}|{$subcategory}";
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
    }

    private function amountCents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
