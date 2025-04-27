<?php

namespace App\UtilityBills\Queries;

use App\Core\Queries\Query;

class GetUpcomingReminders implements Query
{
    public function __construct(
        public ?string $afterDate = null,
        public ?string $beforeDate = null
    ) {
    }
}
