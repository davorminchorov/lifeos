<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Expense;
use App\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportExpensesCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    private const HEADER_ALIASES = [
        'expense_date' => ['date', 'transaction_date', 'expense date'],
        'amount' => ['total', 'cost', 'price'],
        'currency' => ['currency_code'],
        'category' => ['type', 'expense_category'],
        'subcategory' => ['sub_category', 'sub category'],
        'description' => ['desc', 'memo', 'note'],
        'merchant' => ['vendor', 'store', 'name', 'payee'],
        'payment_method' => ['payment', 'method', 'payment method', 'payment_type'],
        'expense_type' => ['expense type', 'business_personal'],
        'is_tax_deductible' => ['tax_deductible', 'tax deductible', 'deductible'],
        'is_recurring' => ['recurring'],
        'tags' => ['labels'],
        'notes' => ['comment', 'comments', 'additional_notes'],
    ];

    public function __construct(
        public int $userId,
        public int $tenantId,
        public string $storedPath,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->storedPath)) {
            Log::error('ImportExpensesCsv: CSV file does not exist', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $content = Storage::get($this->storedPath);
        $lines = array_values(array_filter(explode("\n", $content), fn ($line) => trim($line) !== ''));

        if (empty($lines)) {
            Log::warning('ImportExpensesCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $header = str_getcsv(array_shift($lines));
        if (! $header) {
            Log::warning('ImportExpensesCsv: Empty CSV header', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $index = $this->buildHeaderIndex($header);

        $totalRows = count($lines);
        $created = 0;
        $skipped = 0;
        $failed = 0;
        $cacheKey = 'expense_import_progress:'.$this->userId;

        $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $expenseDate = (string) $this->getValue($row, $index, 'expense_date');
                $amount = (string) $this->getValue($row, $index, 'amount');
                $category = (string) $this->getValue($row, $index, 'category');
                $description = (string) $this->getValue($row, $index, 'description');

                if (! $expenseDate || ! $amount || ! $category || ! $description) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                    Log::warning('ImportExpensesCsv: skipped row due to missing required fields', [
                        'expense_date' => $expenseDate,
                        'amount' => $amount,
                        'category' => $category,
                        'description' => $description,
                    ]);

                    continue;
                }

                $merchant = (string) $this->getValue($row, $index, 'merchant', '');
                $uniqueKey = 'csv-import:'.md5($expenseDate.$amount.$merchant.$description);

                $exists = Expense::withoutGlobalScope(TenantScope::class)
                    ->where('user_id', $this->userId)
                    ->where('unique_key', $uniqueKey)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

                    continue;
                }

                $parsedAmount = $this->parseDecimal($amount);
                $currency = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency', 'MKD'));
                $subcategory = (string) $this->getValue($row, $index, 'subcategory', '');
                $paymentMethod = (string) $this->getValue($row, $index, 'payment_method', '');
                $expenseType = strtolower((string) $this->getValue($row, $index, 'expense_type', 'personal'));
                $isTaxDeductible = $this->parseBoolean((string) $this->getValue($row, $index, 'is_tax_deductible', 'false'));
                $isRecurring = $this->parseBoolean((string) $this->getValue($row, $index, 'is_recurring', 'false'));
                $tagsRaw = (string) $this->getValue($row, $index, 'tags', '');
                $notes = (string) $this->getValue($row, $index, 'notes', '');

                if (! in_array($expenseType, ['business', 'personal'], true)) {
                    $expenseType = 'personal';
                }

                $tags = $tagsRaw !== ''
                    ? array_map('trim', explode(',', $tagsRaw))
                    : null;

                $expense = new Expense([
                    'tenant_id' => $this->tenantId,
                    'user_id' => $this->userId,
                    'amount' => $parsedAmount,
                    'currency' => $currency,
                    'category' => $category,
                    'subcategory' => $subcategory ?: null,
                    'expense_date' => $this->parseDate($expenseDate),
                    'description' => $description,
                    'merchant' => $merchant ?: null,
                    'payment_method' => $paymentMethod ?: null,
                    'expense_type' => $expenseType,
                    'is_tax_deductible' => $isTaxDeductible,
                    'is_recurring' => $isRecurring,
                    'tags' => $tags,
                    'notes' => $notes ?: null,
                    'status' => 'confirmed',
                    'unique_key' => $uniqueKey,
                ]);
                $expense->save();

                $created++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
            } catch (\Throwable $e) {
                $failed++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                Log::error('ImportExpensesCsv: row import failed', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        try {
            Storage::delete($this->storedPath);
        } catch (\Throwable $e) {
            // Ignore cleanup errors
        }

        $this->updateProgress($cacheKey, 'completed', $totalRows, $created, $skipped, $failed);

        Log::info('ImportExpensesCsv: import finished', [
            'user_id' => $this->userId,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);
    }

    /**
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    private function buildHeaderIndex(array $header): array
    {
        $index = [];
        foreach ($header as $i => $col) {
            $normalized = strtolower(trim((string) $col));
            $index[$normalized] = $i;
        }

        return $index;
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $index
     */
    private function getValue(array $row, array $index, string $name, mixed $default = null): mixed
    {
        foreach ([$name, trim($name), strtolower($name)] as $key) {
            if (array_key_exists($key, $index)) {
                $pos = $index[$key];

                return isset($row[$pos]) ? trim((string) $row[$pos]) : $default;
            }
        }

        foreach ((self::HEADER_ALIASES[$name] ?? []) as $alias) {
            $alias = strtolower($alias);
            if (array_key_exists($alias, $index)) {
                $pos = $index[$alias];

                return isset($row[$pos]) ? trim((string) $row[$pos]) : $default;
            }
        }

        return $default;
    }

    private function parseDecimal(?string $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $v = trim($value);
        if ($v === '') {
            return 0.0;
        }

        $negative = false;
        if (str_starts_with($v, '(') && str_ends_with($v, ')')) {
            $negative = true;
            $v = substr($v, 1, -1);
        }
        $v = str_replace([',', ' '], ['', ''], $v);

        $num = (float) $v;

        return $negative ? -$num : $num;
    }

    private function normalizeCurrency(?string $code): string
    {
        $code = strtoupper(substr((string) ($code ?: 'MKD'), 0, 3));

        return $code ?: 'MKD';
    }

    private function parseDate(?string $value): string
    {
        if ($value) {
            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable $e) {
                // fall back below
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        return now()->toDateString();
    }

    private function parseBoolean(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['true', '1', 'yes'], true);
    }

    private function updateProgress(string $cacheKey, string $status, int $total, int $created, int $skipped, int $failed): void
    {
        Cache::put($cacheKey, [
            'status' => $status,
            'total' => $total,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ], 300);
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put('expense_import_progress:'.$this->userId, [
            'status' => 'failed',
            'total' => 0,
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'error' => $exception->getMessage(),
        ], 300);

        Log::error('ImportExpensesCsv failed', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
