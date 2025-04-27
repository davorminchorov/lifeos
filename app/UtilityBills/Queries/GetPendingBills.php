<?php

namespace App\UtilityBills\Queries;

use App\Core\Queries\Query;

class GetPendingBills implements Query
{
    public function __construct(
        public ?string $category = null,
        public ?string $dueDate = null
    ) {
    }
}
