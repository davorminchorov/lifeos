<?php

namespace App\Expenses\Queries;

use App\Core\EventSourcing\Query;

class GetExpenses implements Query
{
    public function __construct(
        public readonly ?string $period = null,
        public readonly ?string $categoryId = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $search = null,
        public readonly ?string $sortBy = 'date',
        public readonly ?string $sortOrder = 'desc',
        public readonly ?int $page = 1,
        public readonly ?int $perPage = 10
    ) {}
}
