<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractNotificationDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Contract $contract,
        public int $days,
        public bool $isNoticeAlert
    ) {}
}
