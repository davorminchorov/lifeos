<?php

namespace App\Console\Commands;

use App\Events\Expenses\ExpenseCreatedEvent;
use App\Events\Expenses\ExpenseUpdatedEvent;
use App\Events\Expenses\ExpenseDeletedEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BroadcastTestEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:test-event
                            {type=created : The type of event to broadcast (created, updated, deleted)}
                            {--id= : The ID to use for the expense (random UUID if not provided)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast a test expense event for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventType = $this->argument('type');
        $expenseId = $this->option('id') ?? (string) Str::uuid();
        $commandId = (string) Str::uuid();

        switch ($eventType) {
            case 'created':
                $this->broadcastCreated($expenseId, $commandId);
                break;
            case 'updated':
                $this->broadcastUpdated($expenseId, $commandId);
                break;
            case 'deleted':
                $this->broadcastDeleted($expenseId, $commandId);
                break;
            default:
                $this->error("Unknown event type: {$eventType}");
                return 1;
        }

        return 0;
    }

    /**
     * Broadcast a test created event.
     */
    private function broadcastCreated(string $expenseId, string $commandId): void
    {
        $expense = [
            'id' => $expenseId,
            'description' => 'Test expense',
            'amount' => 123.45,
            'category_id' => null,
            'date' => now()->toDateString(),
            'notes' => 'Test notes',
            'created_at' => now()->toIso8601String(),
        ];

        broadcast(new ExpenseCreatedEvent($expense, $commandId));

        $this->info("Broadcasted ExpenseCreatedEvent with ID: {$expenseId}");
        $this->info("Command ID: {$commandId}");
    }

    /**
     * Broadcast a test updated event.
     */
    private function broadcastUpdated(string $expenseId, string $commandId): void
    {
        $updates = [
            'description' => 'Updated expense',
            'amount' => 678.90,
            'category_id' => 'test-category',
            'date' => now()->toDateString(),
            'notes' => 'Updated notes',
            'updated_at' => now()->toIso8601String(),
        ];

        broadcast(new ExpenseUpdatedEvent($expenseId, $updates, $commandId));

        $this->info("Broadcasted ExpenseUpdatedEvent with ID: {$expenseId}");
        $this->info("Command ID: {$commandId}");
    }

    /**
     * Broadcast a test deleted event.
     */
    private function broadcastDeleted(string $expenseId, string $commandId): void
    {
        broadcast(new ExpenseDeletedEvent($expenseId, $commandId));

        $this->info("Broadcasted ExpenseDeletedEvent with ID: {$expenseId}");
        $this->info("Command ID: {$commandId}");
    }
}
