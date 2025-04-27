<?php

namespace App\UtilityBills\Queries;

use App\Core\Queries\Query;

class GetBills implements Query
{
    public function __construct(
        public ?array $filters = null
    ) {
    }
}
