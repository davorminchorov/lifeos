<?php

namespace App\Events;

use App\Models\Expense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Expense $expense, public bool $wasRecentlyCreated) {}
}
