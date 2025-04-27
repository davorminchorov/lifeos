<?php

namespace App\UtilityBills\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetAllBills
{
    public function __invoke(): Collection
    {
        return DB::table('utility_bills_read_model')
            ->get();
    }
}
