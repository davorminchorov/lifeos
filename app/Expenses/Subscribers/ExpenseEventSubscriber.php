<?php

namespace App\Expenses\Subscribers;

use App\Events\Expenses\ExpenseCreatedEvent;
use App\Events\Expenses\ExpenseDeletedEvent;
use App\Events\Expenses\ExpenseUpdatedEvent;
use App\Expenses\Events\ExpenseCategorized;
use App\Expenses\Events\ExpenseDeleted;
use App\Expenses\Events\ExpenseRecorded;
use App\Expenses\Events\ExpenseUpdated;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ExpenseRecorded::class,
            [self::class, 'handleExpenseRecorded']
        );

        $events->listen(
            ExpenseUpdated::class,
            [self::class, 'handleExpenseUpdated']
        );

        $events->listen(
            ExpenseDeleted::class,
            [self::class, 'handleExpenseDeleted']
        );

        $events->listen(
            ExpenseCategorized::class,
            [self::class, 'handleExpenseCategorized']
        );
    }

    /**
     * Handle expense recorded events.
     */
    public function handleExpenseRecorded(ExpenseRecorded $event): void
    {
        try {
            // Get expense details from the event
            $expense = [
                'id' => $event->expenseId,
                'description' => $event->description,
                'amount' => $event->amount,
                'category_id' => $event->categoryId,
                'date' => $event->date->toDateString(),
                'notes' => $event->notes,
                'created_at' => now()->toIso8601String(),
            ];

            // Extract commandId from metadata if available
            $commandId = $event->metadata['commandId'] ?? null;

            // Broadcast the event
            broadcast(new ExpenseCreatedEvent($expense, $commandId));

        } catch (\Exception $e) {
            Log::error('Failed to broadcast expense creation event', [
                'error' => $e->getMessage(),
                'expense_id' => $event->expenseId,
            ]);
        }
    }

    /**
     * Handle expense updated events.
     */
    public function handleExpenseUpdated(ExpenseUpdated $event): void
    {
        try {
            // Extract updated fields
            $updates = [
                'description' => $event->description,
                'amount' => $event->amount,
                'category_id' => $event->categoryId,
                'date' => $event->date->toDateString(),
                'notes' => $event->notes,
                'updated_at' => now()->toIso8601String(),
            ];

            // Extract commandId from metadata if available
            $commandId = $event->metadata['commandId'] ?? null;

            // Broadcast the event
            broadcast(new ExpenseUpdatedEvent($event->expenseId, $updates, $commandId));

        } catch (\Exception $e) {
            Log::error('Failed to broadcast expense update event', [
                'error' => $e->getMessage(),
                'expense_id' => $event->expenseId,
            ]);
        }
    }

    /**
     * Handle expense deleted events.
     */
    public function handleExpenseDeleted(ExpenseDeleted $event): void
    {
        try {
            // Extract commandId from metadata if available
            $commandId = $event->metadata['commandId'] ?? null;

            // Broadcast the event
            broadcast(new ExpenseDeletedEvent($event->expenseId, $commandId));

        } catch (\Exception $e) {
            Log::error('Failed to broadcast expense deletion event', [
                'error' => $e->getMessage(),
                'expense_id' => $event->expenseId,
            ]);
        }
    }

    /**
     * Handle expense categorized events.
     */
    public function handleExpenseCategorized(ExpenseCategorized $event): void
    {
        try {
            // Get expense from database
            $expense = DB::table('expenses')
                ->where('expense_id', $event->expenseId)
                ->first();

            if (!$expense) {
                return;
            }

            $updates = [
                'category_id' => $event->categoryId,
                'updated_at' => now()->toIso8601String(),
            ];

            // Extract commandId from metadata if available
            $commandId = $event->metadata['commandId'] ?? null;

            // Broadcast the event
            broadcast(new ExpenseUpdatedEvent($event->expenseId, $updates, $commandId));

        } catch (\Exception $e) {
            Log::error('Failed to broadcast expense categorization event', [
                'error' => $e->getMessage(),
                'expense_id' => $event->expenseId,
            ]);
        }
    }
}
