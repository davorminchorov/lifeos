<?php

namespace App\Events;

use App\Models\Warranty;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarrantyExpirationDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Warranty $warranty,
        public int $days
    ) {}
}
