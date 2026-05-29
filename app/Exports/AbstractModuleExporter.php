<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Contracts\ModuleExporter;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModuleExporter implements ModuleExporter
{
    /**
     * The Eloquent model this exporter reads from.
     *
     * @var class-string<Model>
     */
    protected string $model;

    /**
     * Whether the model carries a user_id column that should be scoped to the
     * current user. Tenant-shared models (e.g. holidays) set this to false.
     */
    protected bool $scopedToUser = true;

    /**
     * The column the optional date-range filter applies to, if any.
     */
    protected ?string $dateColumn = null;

    /**
     * Relations to eager load while streaming.
     *
     * @var array<int, string>
     */
    protected array $with = [];

    /**
     * Build the base query, scoped explicitly to the export context.
     *
     * The TenantScope global scope is fail-closed and depends on the
     * authenticated user, which is absent on the queue worker — so we drop it
     * and apply tenant/user constraints by hand to guarantee correct,
     * deterministic results off-request.
     */
    public function query(ExportContext $context): Builder
    {
        $table = (new $this->model)->getTable();

        $query = $this->model::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where($table.'.tenant_id', $context->tenantId);

        if ($this->scopedToUser) {
            $query->where($table.'.user_id', $context->userId);
        }

        if (! empty($this->with)) {
            $query->with($this->with);
        }

        $this->applyDateRange($query, $context, $table);

        return $query->orderBy($table.'.id');
    }

    protected function applyDateRange(Builder $query, ExportContext $context, string $table): void
    {
        if ($this->dateColumn === null) {
            return;
        }

        $column = $table.'.'.$this->dateColumn;

        if ($from = $context->dateFrom()) {
            $query->where($column, '>=', $from);
        }

        if ($to = $context->dateTo()) {
            $query->where($column, '<=', $to);
        }
    }

    protected function date(mixed $value): ?string
    {
        return $value?->format('Y-m-d');
    }

    protected function datetime(mixed $value): ?string
    {
        return $value?->format('Y-m-d H:i:s');
    }

    protected function bool(mixed $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    /**
     * Flatten an array attribute (tags, metadata, etc.) into a single cell.
     */
    protected function list(mixed $value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        if (! is_array($value)) {
            return (string) $value;
        }

        return array_is_list($value)
            ? implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value))
            : json_encode($value);
    }

    /**
     * Resolve a backed enum or string status to its human label.
     */
    protected function enumLabel(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : (string) $value->value;
        }

        return (string) $value;
    }

    /**
     * Convert an integer minor-unit (cents) amount to a decimal string.
     */
    protected function money(int|float|string|null $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return number_format(((int) $cents) / 100, 2, '.', '');
    }
}
