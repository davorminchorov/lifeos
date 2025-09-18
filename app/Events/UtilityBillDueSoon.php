<?php

namespace App\Events;

use App\Models\UtilityBill;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UtilityBillDueSoon
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public UtilityBill $utilityBill,
        public int $days
    ) {}
}
