<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UtilityBill;
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

class ImportUtilityBillsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    private const HEADER_ALIASES = [
        'utility_type' => ['type', 'utility', 'service_type', 'service type'],
        'service_provider' => ['provider', 'company', 'vendor', 'supplier'],
        'account_number' => ['account', 'account_no', 'acct'],
        'service_address' => ['address', 'location'],
        'bill_amount' => ['amount', 'total', 'cost', 'price'],
        'currency' => ['currency_code'],
        'usage_amount' => ['usage', 'consumption'],
        'usage_unit' => ['unit', 'measure'],
        'rate_per_unit' => ['rate', 'unit_rate', 'unit_price'],
        'bill_period_start' => ['period_start', 'start_date', 'billing_start'],
        'bill_period_end' => ['period_end', 'end_date', 'billing_end'],
        'due_date' => ['due', 'payment_due', 'pay_by'],
        'payment_status' => ['status', 'paid_status'],
        'payment_date' => ['paid_date', 'date_paid'],
        'service_plan' => ['plan', 'tariff'],
        'contract_terms' => ['terms', 'contract'],
        'auto_pay_enabled' => ['auto_pay', 'autopay'],
        'budget_alert_threshold' => ['budget_threshold', 'threshold', 'budget'],
        'notes' => ['note', 'comment', 'comments', 'memo'],
    ];

    public function __construct(
        public int $userId,
        public int $tenantId,
        public string $storedPath,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->storedPath)) {
            Log::error('ImportUtilityBillsCsv: CSV file does not exist', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $content = Storage::get($this->storedPath);
        $lines = array_values(array_filter(explode("\n", $content), fn ($line) => trim($line) !== ''));

        if (empty($lines)) {
            Log::warning('ImportUtilityBillsCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $header = str_getcsv(array_shift($lines));
        if (! $header) {
            Log::warning('ImportUtilityBillsCsv: Empty CSV header', [
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
        $cacheKey = 'utility_bill_import_progress:'.$this->userId;

        $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $utilityType = (string) $this->getValue($row, $index, 'utility_type');
                $serviceProvider = (string) $this->getValue($row, $index, 'service_provider');
                $billAmount = (string) $this->getValue($row, $index, 'bill_amount');
                $dueDateRaw = (string) $this->getValue($row, $index, 'due_date');

                if (! $utilityType || ! $serviceProvider || ! $billAmount || ! $dueDateRaw) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                    Log::warning('ImportUtilityBillsCsv: skipped row due to missing required fields', [
                        'utility_type' => $utilityType,
                        'service_provider' => $serviceProvider,
                        'bill_amount' => $billAmount,
                        'due_date' => $dueDateRaw,
                    ]);

                    continue;
                }

                $parsedAmount = $this->parseDecimal($billAmount);
                $parsedDueDate = $this->parseDate($dueDateRaw);
                $billPeriodStartRaw = (string) $this->getValue($row, $index, 'bill_period_start', '');
                $parsedBillDate = $billPeriodStartRaw ? $this->parseDate($billPeriodStartRaw) : $parsedDueDate;

                $uniqueKey = 'csv-import:'.md5($serviceProvider.$parsedBillDate.$billAmount.$utilityType);

                $exists = UtilityBill::withoutGlobalScope(TenantScope::class)
                    ->where('user_id', $this->userId)
                    ->where(function ($query) use ($uniqueKey, $parsedDueDate, $parsedAmount, $serviceProvider, $utilityType) {
                        $query->where('unique_key', $uniqueKey)
                            ->orWhere(function ($query) use ($parsedDueDate, $parsedAmount, $serviceProvider, $utilityType) {
                                $query->whereDate('due_date', $parsedDueDate)
                                    ->where('bill_amount', round($parsedAmount, 2))
                                    ->where('service_provider', $serviceProvider)
                                    ->where('utility_type', $utilityType);
                            });
                    })
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

                    continue;
                }

                $currency = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency', 'MKD'));
                $accountNumber = (string) $this->getValue($row, $index, 'account_number', '');
                $serviceAddress = (string) $this->getValue($row, $index, 'service_address', '');
                $usageAmount = (string) $this->getValue($row, $index, 'usage_amount', '');
                $usageUnit = (string) $this->getValue($row, $index, 'usage_unit', '');
                $ratePerUnit = (string) $this->getValue($row, $index, 'rate_per_unit', '');
                $billPeriodEndRaw = (string) $this->getValue($row, $index, 'bill_period_end', '');
                $paymentStatus = strtolower((string) $this->getValue($row, $index, 'payment_status', 'pending'));
                $paymentDateRaw = (string) $this->getValue($row, $index, 'payment_date', '');
                $servicePlan = (string) $this->getValue($row, $index, 'service_plan', '');
                $contractTerms = (string) $this->getValue($row, $index, 'contract_terms', '');
                $autoPayEnabled = $this->parseBoolean((string) $this->getValue($row, $index, 'auto_pay_enabled', 'false'));
                $budgetThreshold = (string) $this->getValue($row, $index, 'budget_alert_threshold', '');
                $notes = (string) $this->getValue($row, $index, 'notes', '');

                if (! in_array($paymentStatus, ['pending', 'paid', 'overdue', 'disputed'], true)) {
                    $paymentStatus = 'pending';
                }

                $billPeriodStart = $billPeriodStartRaw ? $this->parseDate($billPeriodStartRaw) : null;
                $billPeriodEnd = $billPeriodEndRaw ? $this->parseDate($billPeriodEndRaw) : null;
                $paymentDate = $paymentDateRaw ? $this->parseDate($paymentDateRaw) : null;

                // bill_period_start/end and account_number/service_address are NOT NULL in schema
                $effectiveBillPeriodStart = $billPeriodStart ?? $parsedDueDate;
                $effectiveBillPeriodEnd = $billPeriodEnd ?? $parsedDueDate;

                $bill = new UtilityBill([
                    'tenant_id' => $this->tenantId,
                    'user_id' => $this->userId,
                    'utility_type' => $utilityType,
                    'service_provider' => $serviceProvider,
                    'account_number' => $accountNumber ?: '',
                    'service_address' => $serviceAddress ?: '',
                    'bill_amount' => $parsedAmount,
                    'currency' => $currency,
                    'usage_amount' => $usageAmount ? $this->parseDecimal($usageAmount) : null,
                    'usage_unit' => $usageUnit ?: null,
                    'rate_per_unit' => $ratePerUnit ? $this->parseDecimal($ratePerUnit) : null,
                    'bill_period_start' => $effectiveBillPeriodStart,
                    'bill_period_end' => $effectiveBillPeriodEnd,
                    'due_date' => $parsedDueDate,
                    'payment_status' => $paymentStatus,
                    'payment_date' => $paymentDate,
                    'service_plan' => $servicePlan ?: null,
                    'contract_terms' => $contractTerms ?: null,
                    'auto_pay_enabled' => $autoPayEnabled,
                    'budget_alert_threshold' => $budgetThreshold ? $this->parseDecimal($budgetThreshold) : null,
                    'notes' => $notes ?: null,
                    'unique_key' => $uniqueKey,
                ]);
                $bill->save();

                $created++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
            } catch (\Throwable $e) {
                $failed++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                Log::error('ImportUtilityBillsCsv: row import failed', [
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

        Log::info('ImportUtilityBillsCsv: import finished', [
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
        Cache::put('utility_bill_import_progress:'.$this->userId, [
            'status' => 'failed',
            'total' => 0,
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'error' => $exception->getMessage(),
        ], 300);

        Log::error('ImportUtilityBillsCsv failed', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
