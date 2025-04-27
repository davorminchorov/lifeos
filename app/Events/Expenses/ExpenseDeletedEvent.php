<?php

namespace App\Events\Expenses;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The expense ID.
     *
     * @var string
     */
    public $expenseId;

    /**
     * Optional command ID for correlation with frontend actions.
     *
     * @var string|null
     */
    public $commandId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $expenseId, ?string $commandId = null)
    {
        $this->expenseId = $expenseId;
        $this->commandId = $commandId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('expenses'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'expense.deleted';
    }
}
