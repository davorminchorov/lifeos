<?php

declare(strict_types=1);

namespace App\Exports;

class ExportContext
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $tenantId,
        public int $userId,
        public array $filters = [],
    ) {}

    public function dateFrom(): ?string
    {
        $value = $this->filters['date_from'] ?? null;

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    public function dateTo(): ?string
    {
        $value = $this->filters['date_to'] ?? null;

        return $value !== null && $value !== '' ? (string) $value : null;
    }
}
