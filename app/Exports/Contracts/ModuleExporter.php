<?php

declare(strict_types=1);

namespace App\Exports\Contracts;

use App\Exports\ExportContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ModuleExporter
{
    /**
     * The stable machine key used in routes, requests, and ZIP entry names.
     */
    public function key(): string;

    /**
     * The human-readable label shown in the UI.
     */
    public function label(): string;

    /**
     * The CSV header row.
     *
     * @return array<int, string>
     */
    public function headers(): array;

    /**
     * The tenant- and user-scoped query that yields the rows to export.
     */
    public function query(ExportContext $context): Builder;

    /**
     * Map a single model into a CSV row aligned with headers().
     *
     * @return array<int, string|int|float|null>
     */
    public function mapRow(Model $model): array;
}
