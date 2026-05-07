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
            'jobs.createApplication' => $this->jobsCreateApplication($tenantId, $payload),
            'cycleMenu.addItem' => $this->cycleMenuAddItem($tenantId, $payload),
            'cycleMenu.setWeek' => $this->cycleMenuSetWeek($tenantId, $payload),
            'digest.send' => $this->digestSend($tenantId, $payload),
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
        $sourceRef = $this->sourceRef($p);

        return "expenses.create|{$tenantId}|{$merchant}|{$amount}|{$currency}|{$date}|{$sourceRef}";
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
        $sourceRef = $this->sourceRef($p);

        return "subscriptions.create|{$tenantId}|{$service}|{$currency}|{$cycle}|{$sourceRef}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function contractsCreate(int $tenantId, array $p): string
    {
        $title = $this->normalize((string) ($p['title'] ?? ''));
        $counterparty = $this->normalize((string) ($p['counterparty'] ?? ''));
        $start = (string) ($p['start_date'] ?? '');
        $sourceRef = $this->sourceRef($p);

        return "contracts.create|{$tenantId}|{$title}|{$counterparty}|{$start}|{$sourceRef}";
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function warrantiesCreate(int $tenantId, array $p): string
    {
        $product = $this->normalize((string) ($p['product_name'] ?? ''));
        $serial = $this->normalize((string) ($p['serial_number'] ?? ''));
        $purchase = (string) ($p['purchase_date'] ?? '');
        $sourceRef = $this->sourceRef($p);

        return "warranties.create|{$tenantId}|{$product}|{$serial}|{$purchase}|{$sourceRef}";
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
        $sourceRef = $this->sourceRef($p);

        return "iou.create|{$tenantId}|{$direction}|{$person}|{$amount}|{$currency}|{$date}|{$sourceRef}";
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
        $sourceRef = $this->sourceRef($p);

        return "utilityBills.create|{$tenantId}|{$type}|{$provider}|{$amount}|{$currency}|{$due}|{$period}|{$sourceRef}";
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
     * Job application creation. Anchors on (tenant, normalized company,
     * normalized title, source-or-url). The url falls back into the source
     * reference so two runs that find the same posting via different channels
     * can still collide if they end up with the same canonical URL.
     *
     * @param  array<string, mixed>  $p
     */
    private function jobsCreateApplication(int $tenantId, array $p): string
    {
        $company = $this->normalize((string) ($p['company_name'] ?? ''));
        $title = $this->normalize((string) ($p['job_title'] ?? ''));

        $url = (string) ($p['job_url'] ?? '');
        $sourceRef = $this->sourceRef($p);

        // Prefer source_email_id / source_file_id when present (most stable
        // anchor); fall back to job_url; fall back to "" so otherwise-identical
        // postings collide on (company, title) alone.
        $anchor = $sourceRef !== '' ? $sourceRef : strtolower(trim($url));

        return "jobs.createApplication|{$tenantId}|{$company}|{$title}|{$anchor}";
    }

    /**
     * Cycle-menu item add. Anchors on (tenant, menu, day_index, normalized
     * title, meal_type). Re-running with the same item collapses, but adding
     * a second copy of the same dish on the same day is intentional and
     * goes through linked write paths (different position).
     *
     * @param  array<string, mixed>  $p
     */
    private function cycleMenuAddItem(int $tenantId, array $p): string
    {
        $menuId = (int) ($p['cycle_menu_id'] ?? 0);
        $dayIndex = (int) ($p['day_index'] ?? -1);
        $title = $this->normalize((string) ($p['title'] ?? ''));
        $mealType = $this->normalize((string) ($p['meal_type'] ?? ''));

        return "cycleMenu.addItem|{$tenantId}|{$menuId}|{$dayIndex}|{$title}|{$mealType}";
    }

    /**
     * Cycle-menu set-week. Anchors on (tenant, menu, sorted day-index list,
     * canonicalized item set per day). Re-running the same plan collapses;
     * tweaking any item produces a distinct key.
     *
     * @param  array<string, mixed>  $p
     */
    private function cycleMenuSetWeek(int $tenantId, array $p): string
    {
        $menuId = (int) ($p['cycle_menu_id'] ?? 0);
        $itemsByDayIndex = (array) ($p['items_by_day_index'] ?? []);

        ksort($itemsByDayIndex);

        $perDay = [];

        foreach ($itemsByDayIndex as $dayIndex => $items) {
            $rows = array_map(
                fn (array $item): string => sprintf(
                    '%s/%s',
                    $this->normalize((string) ($item['title'] ?? '')),
                    $this->normalize((string) ($item['meal_type'] ?? '')),
                ),
                (array) $items,
            );
            sort($rows);
            $perDay[] = "{$dayIndex}=".implode(',', $rows);
        }

        return "cycleMenu.setWeek|{$tenantId}|{$menuId}|".implode(';', $perDay);
    }

    /**
     * Weekly digest. Anchors on (tenant, ISO-week start date). Re-running the
     * agent on the same Sunday collapses to one pending action; the unique
     * (tenant, week_starts_on) constraint on digest_logs is the second line of
     * defence preventing a duplicate send if two pending actions somehow get
     * approved concurrently.
     *
     * @param  array<string, mixed>  $p
     */
    private function digestSend(int $tenantId, array $p): string
    {
        $weekStart = (string) ($p['week_starts_on'] ?? '');

        return "digest.send|{$tenantId}|{$weekStart}";
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

    /**
     * Compose a source-reference fragment for idempotency keys.
     *
     * Backward-compatibility contract: when only `source_email_id` is set the
     * helper returns the legacy email-only encoding so existing pending_action
     * keys continue to dedupe across agent runs. When `source_file_id` is also
     * present (Phase 7's receipt OCR agent), the helper appends a `file:<id>`
     * suffix so re-running OCR over the same Drive file collapses to the same
     * pending action even when the OCR text drifts slightly.
     *
     * @param  array<string, mixed>  $p
     */
    private function sourceRef(array $p): string
    {
        $email = (string) ($p['source_email_id'] ?? '');
        $file = (string) ($p['source_file_id'] ?? '');

        if ($file === '') {
            return $email;
        }

        return "{$email}|file:{$file}";
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
