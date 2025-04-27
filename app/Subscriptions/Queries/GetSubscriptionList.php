<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\Query;

class GetSubscriptionList implements Query
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $category = null,
        public readonly ?string $search = null,
        public readonly string $sortBy = 'name',
        public readonly string $sortDirection = 'asc',
        public readonly int $perPage = 10,
        public readonly int $page = 1
    ) {}
}
