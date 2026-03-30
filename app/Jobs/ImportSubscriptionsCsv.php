<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Subscription;
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

class ImportSubscriptionsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    private const HEADER_ALIASES = [
        'service_name' => ['name', 'service', 'subscription', 'subscription_name'],
        'cost' => ['price', 'amount', 'total'],
        'billing_cycle' => ['cycle', 'frequency', 'billing_frequency', 'billing cycle'],
        'next_billing_date' => ['next_date', 'renewal_date', 'next billing date', 'next_renewal'],
        'currency' => ['currency_code'],
        'category' => ['type', 'subscription_category'],
        'status' => ['state', 'subscription_status'],
        'auto_renewal' => ['auto_renew', 'auto renewal', 'renews_automatically'],
        'payment_method' => ['payment', 'method', 'payment method', 'payment_type'],
        'start_date' => ['started', 'start date', 'subscription_start'],
        'description' => ['desc', 'memo'],
        'notes' => ['comment', 'comments', 'additional_notes'],
        'tags' => ['labels'],
        'url' => ['link', 'website'],
    ];

    public function __construct(
        public int $userId,
        public int $tenantId,
        public string $storedPath,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->storedPath)) {
            Log::error('ImportSubscriptionsCsv: CSV file does not exist', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $content = Storage::get($this->storedPath);
        $lines = array_values(array_filter(explode("\n", $content), fn ($line) => trim($line) !== ''));

        if (empty($lines)) {
            Log::warning('ImportSubscriptionsCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $header = str_getcsv(array_shift($lines));
        if (! $header) {
            Log::warning('ImportSubscriptionsCsv: Empty CSV header', [
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
        $cacheKey = 'subscription_import_progress:'.$this->userId;

        $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $serviceName = (string) $this->getValue($row, $index, 'service_name');
                $cost = (string) $this->getValue($row, $index, 'cost');
                $billingCycle = (string) $this->getValue($row, $index, 'billing_cycle');
                $nextBillingDate = (string) $this->getValue($row, $index, 'next_billing_date');

                if (! $serviceName || ! $cost || ! $billingCycle || ! $nextBillingDate) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                    Log::warning('ImportSubscriptionsCsv: skipped row due to missing required fields', [
                        'service_name' => $serviceName,
                        'cost' => $cost,
                        'billing_cycle' => $billingCycle,
                        'next_billing_date' => $nextBillingDate,
                    ]);

                    continue;
                }

                $parsedCost = $this->parseDecimal($cost);
                $parsedNextBillingDate = $this->parseDate($nextBillingDate);
                $uniqueKey = 'csv-import:'.md5($serviceName.$cost.$billingCycle.$nextBillingDate);

                $exists = Subscription::withoutGlobalScope(TenantScope::class)
                    ->where('user_id', $this->userId)
                    ->where(function ($query) use ($uniqueKey, $serviceName, $parsedCost, $billingCycle, $parsedNextBillingDate) {
                        $query->where('unique_key', $uniqueKey)
                            ->orWhere(function ($query) use ($serviceName, $parsedCost, $billingCycle, $parsedNextBillingDate) {
                                $query->where('service_name', $serviceName)
                                    ->where('cost', round($parsedCost, 2))
                                    ->where('billing_cycle', strtolower($billingCycle))
                                    ->whereDate('next_billing_date', $parsedNextBillingDate);
                            });
                    })
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

                    continue;
                }

                $currency = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency', 'MKD'));
                $category = (string) $this->getValue($row, $index, 'category', 'Other');
                $status = strtolower((string) $this->getValue($row, $index, 'status', 'active'));
                $autoRenewal = $this->parseBoolean((string) $this->getValue($row, $index, 'auto_renewal', 'true'));
                $paymentMethod = (string) $this->getValue($row, $index, 'payment_method', '');
                $startDateRaw = (string) $this->getValue($row, $index, 'start_date', '');
                $description = (string) $this->getValue($row, $index, 'description', '');
                $notes = (string) $this->getValue($row, $index, 'notes', '');
                $tagsRaw = (string) $this->getValue($row, $index, 'tags', '');
                $url = (string) $this->getValue($row, $index, 'url', '');

                if (! in_array($status, ['active', 'cancelled', 'paused'], true)) {
                    $status = 'active';
                }

                $normalizedBillingCycle = strtolower($billingCycle);
                if (! in_array($normalizedBillingCycle, ['monthly', 'yearly', 'weekly', 'custom'], true)) {
                    $normalizedBillingCycle = 'monthly';
                }

                $tags = $tagsRaw !== ''
                    ? array_map('trim', explode(',', $tagsRaw))
                    : null;

                $startDate = $startDateRaw !== '' ? $this->parseDate($startDateRaw) : $parsedNextBillingDate;

                $subscription = new Subscription([
                    'tenant_id' => $this->tenantId,
                    'user_id' => $this->userId,
                    'service_name' => $serviceName,
                    'description' => $description ?: null,
                    'category' => $category,
                    'cost' => $parsedCost,
                    'billing_cycle' => $normalizedBillingCycle,
                    'currency' => $currency,
                    'start_date' => $startDate,
                    'next_billing_date' => $parsedNextBillingDate,
                    'payment_method' => $paymentMethod ?: null,
                    'merchant_info' => $url ?: null,
                    'auto_renewal' => $autoRenewal,
                    'notes' => $notes ?: null,
                    'tags' => $tags,
                    'status' => $status,
                    'unique_key' => $uniqueKey,
                ]);
                $subscription->save();

                $created++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
            } catch (\Throwable $e) {
                $failed++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                Log::error('ImportSubscriptionsCsv: row import failed', [
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

        Log::info('ImportSubscriptionsCsv: import finished', [
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
        Cache::put('subscription_import_progress:'.$this->userId, [
            'status' => 'failed',
            'total' => 0,
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'error' => $exception->getMessage(),
        ], 300);

        Log::error('ImportSubscriptionsCsv failed', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
