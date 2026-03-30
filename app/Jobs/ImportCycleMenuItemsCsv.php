<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MealType;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use App\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportCycleMenuItemsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    private const HEADER_ALIASES = [
        'day_index' => ['day', 'day_number', 'day number'],
        'meal_type' => ['meal', 'type', 'meal type'],
        'title' => ['name', 'item', 'item_name', 'item name', 'description'],
        'time_of_day' => ['time', 'scheduled_time', 'scheduled time'],
        'quantity' => ['qty', 'amount', 'serving'],
        'notes' => ['note', 'comment', 'comments'],
        'position' => ['order', 'sort_order', 'sort order'],
    ];

    public function __construct(
        public int $cycleMenuId,
        public int $userId,
        public int $tenantId,
        public string $storedPath,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->storedPath)) {
            Log::error('ImportCycleMenuItemsCsv: CSV file does not exist', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $content = Storage::get($this->storedPath);
        $lines = array_values(array_filter(explode("\n", $content), fn ($line) => trim($line) !== ''));

        if (empty($lines)) {
            Log::warning('ImportCycleMenuItemsCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $header = str_getcsv(array_shift($lines));
        if (! $header) {
            Log::warning('ImportCycleMenuItemsCsv: Empty CSV header', [
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
        $cacheKey = 'cycle_menu_items_import_progress:'.$this->userId;

        $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

        // Validate that the cycle menu exists
        $cycleMenu = CycleMenu::withoutGlobalScope(TenantScope::class)
            ->where('id', $this->cycleMenuId)
            ->where('tenant_id', $this->tenantId)
            ->first();

        if (! $cycleMenu) {
            Log::error('ImportCycleMenuItemsCsv: Cycle menu not found', [
                'cycle_menu_id' => $this->cycleMenuId,
                'tenant_id' => $this->tenantId,
            ]);
            $this->updateProgress($cacheKey, 'failed', $totalRows, $created, $skipped, $failed);

            return;
        }

        // Collect valid meal type values
        $validMealTypes = array_map(fn (MealType $case) => $case->value, MealType::cases());

        foreach ($lines as $line) {
            $row = str_getcsv($line);

            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $dayIndexRaw = (string) $this->getValue($row, $index, 'day_index');
                $mealTypeRaw = (string) $this->getValue($row, $index, 'meal_type');
                $title = (string) $this->getValue($row, $index, 'title');

                if ($dayIndexRaw === '' || $mealTypeRaw === '' || $title === '') {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                    Log::warning('ImportCycleMenuItemsCsv: skipped row due to missing required fields', [
                        'day_index' => $dayIndexRaw,
                        'meal_type' => $mealTypeRaw,
                        'title' => $title,
                    ]);

                    continue;
                }

                // Parse day_index (CSV uses 1-based, model uses 0-based)
                $dayIndex = (int) $dayIndexRaw - 1;
                if ($dayIndex < 0) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

                    continue;
                }

                // Normalize meal type to lowercase
                $mealType = strtolower(trim($mealTypeRaw));
                if (! in_array($mealType, $validMealTypes, true)) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                    Log::warning('ImportCycleMenuItemsCsv: invalid meal_type', [
                        'meal_type' => $mealTypeRaw,
                    ]);

                    continue;
                }

                // Find or create the CycleMenuDay
                $day = CycleMenuDay::withoutGlobalScope(TenantScope::class)
                    ->firstOrCreate(
                        [
                            'cycle_menu_id' => $this->cycleMenuId,
                            'day_index' => $dayIndex,
                        ],
                        [
                            'tenant_id' => $this->tenantId,
                        ]
                    );

                // Duplicate detection: skip if same day + meal_type + title already exists
                $exists = CycleMenuItem::withoutGlobalScope(TenantScope::class)
                    ->where('cycle_menu_day_id', $day->id)
                    ->where('meal_type', $mealType)
                    ->where('title', $title)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);

                    continue;
                }

                $timeOfDay = (string) $this->getValue($row, $index, 'time_of_day', '');
                $quantity = (string) $this->getValue($row, $index, 'quantity', '');
                $notes = (string) $this->getValue($row, $index, 'notes', '');
                $positionRaw = (string) $this->getValue($row, $index, 'position', '');

                // Auto-assign position if not provided
                if ($positionRaw !== '') {
                    $position = (int) $positionRaw;
                } else {
                    $maxPosition = CycleMenuItem::withoutGlobalScope(TenantScope::class)
                        ->where('cycle_menu_day_id', $day->id)
                        ->max('position');
                    $position = is_null($maxPosition) ? 0 : $maxPosition + 1;
                }

                $item = new CycleMenuItem([
                    'tenant_id' => $this->tenantId,
                    'cycle_menu_day_id' => $day->id,
                    'title' => $title,
                    'meal_type' => $mealType,
                    'time_of_day' => $timeOfDay ?: null,
                    'quantity' => $quantity ?: null,
                    'position' => $position,
                ]);
                $item->save();

                $created++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
            } catch (\Throwable $e) {
                $failed++;
                $this->updateProgress($cacheKey, 'processing', $totalRows, $created, $skipped, $failed);
                Log::error('ImportCycleMenuItemsCsv: row import failed', [
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

        Log::info('ImportCycleMenuItemsCsv: import finished', [
            'user_id' => $this->userId,
            'cycle_menu_id' => $this->cycleMenuId,
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
        Cache::put('cycle_menu_items_import_progress:'.$this->userId, [
            'status' => 'failed',
            'total' => 0,
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'error' => $exception->getMessage(),
        ], 300);

        Log::error('ImportCycleMenuItemsCsv failed', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
