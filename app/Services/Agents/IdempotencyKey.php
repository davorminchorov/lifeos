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
            'subscriptions.create' => $this->subscriptionsCreate($tenantId, $payload),
            'contracts.create' => $this->contractsCreate($tenantId, $payload),
            'warranties.create' => $this->warrantiesCreate($tenantId, $payload),
            'iou.create' => $this->iouCreate($tenantId, $payload),
            'utilityBills.create' => $this->utilityBillsCreate($tenantId, $payload),
            'jobs.updateStatus' => $this->jobsUpdateStatus($tenantId, $payload),
            'jobs.addInterview' => $this->jobsAddInterview($tenantId, $payload),
            'investments.recordTransaction' => $this->investmentsRecordTransaction($tenantId, $payload),
            'investments.recordDividend' => $this->investmentsRecordDividend($tenantId, $payload),
            'investments.repriceLot' => $this->investmentsRepriceLot($tenantId, $payload),
            'investments.bulkImportTransactions' => $this->investmentsBulkImportTransactions($tenantId, $payload),
            'bank.recordLines' => $this->bankRecordLines($tenantId, $payload),
            'bank.linkExpense' => $this->bankLinkExpense($tenantId, $payload),
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

    /**
     * @param  array<string, mixed>  $p
     */
    private function subscriptionsCreate(int $tenantId, array $p): string
    {
        $service = $this->normalize((string) ($p['service_name'] ?? ''));
        $currency = strtoupper((string) ($p['currency'] ?? ''));
        $cycle = $this->normalize((string) ($p['billing_cycle'] ?? ''));
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "subscriptions.create|{$tenantId}|{$service}|{$currency}|{$cycle}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function contractsCreate(int $tenantId, array $p): string
    {
        $title = $this->normalize((string) ($p['title'] ?? ''));
        $counterparty = $this->normalize((string) ($p['counterparty'] ?? ''));
        $start = (string) ($p['start_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "contracts.create|{$tenantId}|{$title}|{$counterparty}|{$start}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function warrantiesCreate(int $tenantId, array $p): string
    {
        $product = $this->normalize((string) ($p['product_name'] ?? ''));
        $serial = $this->normalize((string) ($p['serial_number'] ?? ''));
        $purchase = (string) ($p['purchase_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "warranties.create|{$tenantId}|{$product}|{$serial}|{$purchase}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function iouCreate(int $tenantId, array $p): string
    {
        $direction = $this->normalize((string) ($p['type'] ?? ''));
        $person = $this->normalize((string) ($p['person_name'] ?? ''));
        $amount = $this->amountCents($p['amount'] ?? 0);
        $currency = strtoupper((string) ($p['currency'] ?? ''));
        $date = (string) ($p['transaction_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "iou.create|{$tenantId}|{$direction}|{$person}|{$amount}|{$currency}|{$date}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function utilityBillsCreate(int $tenantId, array $p): string
    {
        $type = $this->normalize((string) ($p['utility_type'] ?? ''));
        $provider = $this->normalize((string) ($p['service_provider'] ?? ''));
        $amount = $this->amountCents($p['bill_amount'] ?? 0);
        $currency = strtoupper((string) ($p['currency'] ?? ''));
        $due = (string) ($p['due_date'] ?? '');
        $period = (string) ($p['bill_period_end'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "utilityBills.create|{$tenantId}|{$type}|{$provider}|{$amount}|{$currency}|{$due}|{$period}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function jobsUpdateStatus(int $tenantId, array $p): string
    {
        $jobId = (int) ($p['job_application_id'] ?? 0);
        $status = $this->normalize((string) ($p['status'] ?? ''));
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "jobs.updateStatus|{$tenantId}|{$jobId}|{$status}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function jobsAddInterview(int $tenantId, array $p): string
    {
        $jobId = (int) ($p['job_application_id'] ?? 0);
        $scheduledAt = (string) ($p['scheduled_at'] ?? '');
        $type = $this->normalize((string) ($p['interview_type'] ?? ''));
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "jobs.addInterview|{$tenantId}|{$jobId}|{$scheduledAt}|{$type}|{$sourceEmail}";
    }

    /**
     * Investment transactions are keyed on broker-provided fields when present
     * (order_id / confirmation_number), falling back to (investment, type, qty,
     * price, date). The order_id collapses any retry of the same broker action.
     *
     * @param  array<string, mixed>  $p
     */
    private function investmentsRecordTransaction(int $tenantId, array $p): string
    {
        $investmentId = (int) ($p['investment_id'] ?? 0);
        $orderId = $this->normalize((string) ($p['order_id'] ?? ''));
        $confirmation = $this->normalize((string) ($p['confirmation_number'] ?? ''));

        if ($orderId !== '') {
            return "investments.recordTransaction|{$tenantId}|{$investmentId}|order:{$orderId}";
        }

        if ($confirmation !== '') {
            return "investments.recordTransaction|{$tenantId}|{$investmentId}|conf:{$confirmation}";
        }

        $type = $this->normalize((string) ($p['transaction_type'] ?? ''));
        $qty = $this->amountCents($p['quantity'] ?? 0);
        $price = $this->amountCents($p['price_per_share'] ?? 0);
        $date = (string) ($p['transaction_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "investments.recordTransaction|{$tenantId}|{$investmentId}|{$type}|{$qty}|{$price}|{$date}|{$sourceEmail}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function investmentsRecordDividend(int $tenantId, array $p): string
    {
        $investmentId = (int) ($p['investment_id'] ?? 0);
        $amount = $this->amountCents($p['amount'] ?? 0);
        $payment = (string) ($p['payment_date'] ?? '');
        $sourceEmail = (string) ($p['source_email_id'] ?? '');

        return "investments.recordDividend|{$tenantId}|{$investmentId}|{$amount}|{$payment}|{$sourceEmail}";
    }

    /**
     * Mark-to-market: one logical price per (investment, as_of date). Re-running
     * the agent on the same day collapses to the same row.
     *
     * @param  array<string, mixed>  $p
     */
    private function investmentsRepriceLot(int $tenantId, array $p): string
    {
        $investmentId = (int) ($p['investment_id'] ?? 0);
        $asOf = (string) ($p['as_of'] ?? date('Y-m-d'));

        return "investments.repriceLot|{$tenantId}|{$investmentId}|{$asOf}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function investmentsBulkImportTransactions(int $tenantId, array $p): string
    {
        $items = (array) ($p['items'] ?? []);

        $itemKeys = array_map(
            fn (array $item): string => $this->investmentsRecordTransaction($tenantId, $item),
            $items,
        );

        sort($itemKeys);

        return "investments.bulkImportTransactions|{$tenantId}|".implode("\n", $itemKeys);
    }

    /**
     * Bank statements are keyed on the sorted set of per-line fingerprints, so
     * re-importing the same statement (even with rows in a different order)
     * collapses to the same pending action.
     *
     * @param  array<string, mixed>  $p
     */
    private function bankRecordLines(int $tenantId, array $p): string
    {
        $fingerprints = array_map(
            static fn (array $line): string => (string) ($line['fingerprint'] ?? ''),
            (array) ($p['lines'] ?? []),
        );

        sort($fingerprints);

        return "bank.recordLines|{$tenantId}|".implode(',', $fingerprints);
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function bankLinkExpense(int $tenantId, array $p): string
    {
        $bankLineId = (int) ($p['bank_line_id'] ?? 0);
        $expenseId = (int) ($p['expense_id'] ?? 0);

        return "bank.linkExpense|{$tenantId}|{$bankLineId}|{$expenseId}";
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
