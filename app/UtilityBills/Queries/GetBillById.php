<?php

namespace App\UtilityBills\Queries;

use App\Core\Queries\Query;

class GetBillById implements Query
{
    public function __construct(
        public string $id
    ) {
    }
}
