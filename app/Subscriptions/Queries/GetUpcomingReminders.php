<?php

namespace App\Subscriptions\Queries;

use App\Core\EventSourcing\Query;

class GetUpcomingReminders implements Query
{
    public function __construct(
        public readonly int $daysAhead = 14
    ) {}
}
