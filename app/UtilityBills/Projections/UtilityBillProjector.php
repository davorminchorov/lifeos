<?php

namespace App\UtilityBills\Projections;

use App\Core\EventSourcing\Projectors\Projector;
use App\UtilityBills\Events\BillAdded;
use App\UtilityBills\Events\BillPaid;
use App\UtilityBills\Events\BillUpdated;
use App\UtilityBills\Events\ReminderScheduled;
use App\UtilityBills\Events\ReminderSent;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class UtilityBillProjector implements Projector
{
    public function handleBillAdded(BillAdded $event): void
    {
        $payload = $event->toPayload();
        $dueDate = new DateTimeImmutable($payload['due_date']);

        DB::table('utility_bills')->insert([
            'id' => $event->aggregateId,
            'name' => $payload['name'],
            'provider' => $payload['provider'],
            'amount' => $payload['amount'],
            'due_date' => $dueDate->format('Y-m-d'),
            'category' => $payload['category'],
            'is_recurring' => $payload['is_recurring'],
            'recurrence_period' => $payload['recurrence_period'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'status' => 'pending',
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // Add to pending bills view
        DB::table('pending_bills')->insert([
            'bill_id' => $event->aggregateId,
            'name' => $payload['name'],
            'provider' => $payload['provider'],
            'amount' => $payload['amount'],
            'due_date' => $dueDate->format('Y-m-d'),
            'category' => $payload['category'],
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function handleBillUpdated(BillUpdated $event): void
    {
        $payload = $event->toPayload();
        $updates = [];

        if ($payload['name'] !== null) {
            $updates['name'] = $payload['name'];
        }

        if ($payload['provider'] !== null) {
            $updates['provider'] = $payload['provider'];
        }

        if ($payload['amount'] !== null) {
            $updates['amount'] = $payload['amount'];
        }

        if ($payload['due_date'] !== null) {
            $updates['due_date'] = (new DateTimeImmutable($payload['due_date']))->format('Y-m-d');
        }

        if ($payload['category'] !== null) {
            $updates['category'] = $payload['category'];
        }

        if ($payload['is_recurring'] !== null) {
            $updates['is_recurring'] = $payload['is_recurring'];
        }

        if ($payload['recurrence_period'] !== null) {
            $updates['recurrence_period'] = $payload['recurrence_period'];
        }

        if ($payload['notes'] !== null) {
            $updates['notes'] = $payload['notes'];
        }

        $updates['updated_at'] = $event->occurredAt->format('Y-m-d H:i:s');

        if (!empty($updates)) {
            DB::table('utility_bills')
                ->where('id', $event->aggregateId)
                ->update($updates);

            // Update pending bills view if bill is still pending
            $bill = DB::table('utility_bills')
                ->where('id', $event->aggregateId)
                ->first();

            if ($bill && $bill->status === 'pending') {
                // First, check if the bill exists in pending_bills
                $pendingBill = DB::table('pending_bills')
                    ->where('bill_id', $event->aggregateId)
                    ->first();

                if ($pendingBill) {
                    DB::table('pending_bills')
                        ->where('bill_id', $event->aggregateId)
                        ->update(array_merge(
                            array_filter($updates, function ($key) {
                                return in_array($key, ['name', 'provider', 'amount', 'due_date', 'category', 'updated_at']);
                            }, ARRAY_FILTER_USE_KEY),
                            ['bill_id' => $event->aggregateId]
                        ));
                } else {
                    // Re-insert into pending bills if it was removed
                    DB::table('pending_bills')->insert([
                        'bill_id' => $event->aggregateId,
                        'name' => $bill->name,
                        'provider' => $bill->provider,
                        'amount' => $bill->amount,
                        'due_date' => $bill->due_date,
                        'category' => $bill->category,
                        'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                        'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }

    public function handleBillPaid(BillPaid $event): void
    {
        $payload = $event->toPayload();
        $paymentDate = new DateTimeImmutable($payload['payment_date']);

        // Record payment
        DB::table('bill_payments')->insert([
            'id' => DB::raw('UUID()'),
            'bill_id' => $event->aggregateId,
            'payment_date' => $paymentDate->format('Y-m-d'),
            'payment_amount' => $payload['payment_amount'],
            'payment_method' => $payload['payment_method'],
            'notes' => $payload['notes'] ?? null,
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // Update the bill status
        DB::table('utility_bills')
            ->where('id', $event->aggregateId)
            ->update([
                'status' => 'paid',
                'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            ]);

        // Get the bill to check if it's recurring
        $bill = DB::table('utility_bills')
            ->where('id', $event->aggregateId)
            ->first();

        // Remove from pending bills view
        DB::table('pending_bills')
            ->where('bill_id', $event->aggregateId)
            ->delete();

        // Add to payment history view
        DB::table('payment_history')->insert([
            'bill_id' => $event->aggregateId,
            'bill_name' => $bill->name,
            'provider' => $bill->provider,
            'payment_date' => $paymentDate->format('Y-m-d'),
            'payment_amount' => $payload['payment_amount'],
            'payment_method' => $payload['payment_method'],
            'category' => $bill->category,
            'notes' => $payload['notes'] ?? null,
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // If bill is recurring, update the due date and set status back to pending
        if ($bill->is_recurring && $bill->recurrence_period) {
            $nextDueDate = match($bill->recurrence_period) {
                'monthly' => $paymentDate->modify('+1 month')->format('Y-m-d'),
                'bimonthly' => $paymentDate->modify('+2 months')->format('Y-m-d'),
                'quarterly' => $paymentDate->modify('+3 months')->format('Y-m-d'),
                'annually' => $paymentDate->modify('+1 year')->format('Y-m-d'),
                default => $paymentDate->modify('+1 month')->format('Y-m-d'),
            };

            DB::table('utility_bills')
                ->where('id', $event->aggregateId)
                ->update([
                    'due_date' => $nextDueDate,
                    'status' => 'pending',
                    'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                ]);

            // Add back to pending bills view with new due date
            DB::table('pending_bills')->insert([
                'bill_id' => $event->aggregateId,
                'name' => $bill->name,
                'provider' => $bill->provider,
                'amount' => $bill->amount,
                'due_date' => $nextDueDate,
                'category' => $bill->category,
                'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function handleReminderScheduled(ReminderScheduled $event): void
    {
        $payload = $event->toPayload();
        $reminderDate = new DateTimeImmutable($payload['reminder_date']);

        // Get the bill
        $bill = DB::table('utility_bills')
            ->where('id', $event->aggregateId)
            ->first();

        // Add to reminders table
        DB::table('bill_reminders')->insert([
            'id' => DB::raw('UUID()'),
            'bill_id' => $event->aggregateId,
            'reminder_date' => $reminderDate->format('Y-m-d'),
            'reminder_message' => $payload['reminder_message'],
            'status' => 'scheduled',
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // Add to upcoming reminders view
        DB::table('upcoming_reminders')->insert([
            'bill_id' => $event->aggregateId,
            'bill_name' => $bill->name,
            'provider' => $bill->provider,
            'due_date' => $bill->due_date,
            'amount' => $bill->amount,
            'reminder_date' => $reminderDate->format('Y-m-d'),
            'reminder_message' => $payload['reminder_message'],
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function handleReminderSent(ReminderSent $event): void
    {
        $payload = $event->toPayload();
        $sentAt = new DateTimeImmutable($payload['sent_at']);

        // Update reminder status
        DB::table('bill_reminders')
            ->where('bill_id', $event->aggregateId)
            ->where('status', 'scheduled')
            ->update([
                'status' => 'sent',
                'sent_at' => $sentAt->format('Y-m-d H:i:s'),
                'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            ]);

        // Remove from upcoming reminders view
        DB::table('upcoming_reminders')
            ->where('bill_id', $event->aggregateId)
            ->delete();
    }
}
