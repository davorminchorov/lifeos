<?php

namespace App\Events;

use App\Models\Budget;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetThresholdCrossed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $direction  'up' when crossing above threshold/exceeded, 'down' when going below
     */
    public function __construct(public Budget $budget, public string $direction) {}
}
