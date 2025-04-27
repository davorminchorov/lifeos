<?php

namespace App\Subscriptions\Projections;

use App\Core\EventSourcing\Projectors\Projector;
use App\Subscriptions\Events\PaymentRecorded;
use App\Subscriptions\Events\SubscriptionAdded;
use App\Subscriptions\Events\SubscriptionCancelled;
use App\Subscriptions\Events\SubscriptionUpdated;
use Illuminate\Support\Facades\DB;
use DateTimeImmutable;

class SubscriptionProjector implements Projector
{
    public function handleSubscriptionAdded(SubscriptionAdded $event): void
    {
        $payload = $event->toPayload();
        $startDate = new DateTimeImmutable($payload['start_date']);

        DB::table('subscriptions')->insert([
            'id' => $event->aggregateId,
            'name' => $payload['name'],
            'description' => $payload['description'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'billing_cycle' => $payload['billing_cycle'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => null,
            'status' => 'active',
            'website' => $payload['website'] ?? null,
            'category' => $payload['category'] ?? null,
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // Calculate the next payment date based on billing cycle
        $nextPaymentDate = $this->calculateNextPaymentDate(
            $startDate,
            $payload['billing_cycle']
        );

        // Create an entry in the upcoming payments view
        DB::table('upcoming_payments')->insert([
            'subscription_id' => $event->aggregateId,
            'expected_date' => $nextPaymentDate->format('Y-m-d'),
            'amount' => $payload['amount'],
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        $payload = $event->toPayload();

        DB::table('subscriptions')
            ->where('id', $event->aggregateId)
            ->update([
                'name' => $payload['name'],
                'description' => $payload['description'],
                'amount' => $payload['amount'],
                'currency' => $payload['currency'],
                'billing_cycle' => $payload['billing_cycle'],
                'website' => $payload['website'] ?? DB::raw('website'),
                'category' => $payload['category'] ?? DB::raw('category'),
                'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            ]);

        // Update upcoming payments
        $subscription = DB::table('subscriptions')
            ->where('id', $event->aggregateId)
            ->first();

        if ($subscription) {
            // Either get the last payment date or use start date
            $lastPaymentDate = DB::table('payments')
                ->where('subscription_id', $event->aggregateId)
                ->orderBy('payment_date', 'desc')
                ->value('payment_date');

            $lastDate = $lastPaymentDate
                ? new DateTimeImmutable($lastPaymentDate)
                : new DateTimeImmutable($subscription->start_date);

            // Calculate next payment date
            $nextPaymentDate = $this->calculateNextPaymentDate(
                $lastDate,
                $payload['billing_cycle']
            );

            // Update or create upcoming payment
            DB::table('upcoming_payments')
                ->updateOrInsert(
                    ['subscription_id' => $event->aggregateId],
                    [
                        'expected_date' => $nextPaymentDate->format('Y-m-d'),
                        'amount' => $payload['amount'],
                        'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                    ]
                );
        }
    }

    public function handleSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $payload = $event->toPayload();
        $endDate = new DateTimeImmutable($payload['end_date']);

        DB::table('subscriptions')
            ->where('id', $event->aggregateId)
            ->update([
                'end_date' => $endDate->format('Y-m-d'),
                'status' => 'cancelled',
                'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            ]);

        // Remove from upcoming payments
        DB::table('upcoming_payments')
            ->where('subscription_id', $event->aggregateId)
            ->delete();
    }

    public function handlePaymentRecorded(PaymentRecorded $event): void
    {
        $payload = $event->toPayload();
        $paymentDate = new DateTimeImmutable($payload['payment_date']);

        // Record the payment
        DB::table('payments')->insert([
            'id' => $payload['payment_id'],
            'subscription_id' => $event->aggregateId,
            'amount' => $payload['amount'],
            'payment_date' => $paymentDate->format('Y-m-d'),
            'notes' => $payload['notes'] ?? null,
            'created_at' => $event->occurredAt->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
        ]);

        // Get the subscription details
        $subscription = DB::table('subscriptions')
            ->where('id', $event->aggregateId)
            ->first();

        if ($subscription && $subscription->status === 'active') {
            // Calculate the next payment date
            $nextPaymentDate = $this->calculateNextPaymentDate(
                $paymentDate,
                $subscription->billing_cycle
            );

            // Update the upcoming payments
            DB::table('upcoming_payments')
                ->updateOrInsert(
                    ['subscription_id' => $event->aggregateId],
                    [
                        'expected_date' => $nextPaymentDate->format('Y-m-d'),
                        'amount' => $subscription->amount,
                        'updated_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                    ]
                );
        }
    }

    private function calculateNextPaymentDate(
        DateTimeImmutable $fromDate,
        string $billingCycle
    ): DateTimeImmutable {
        return match($billingCycle) {
            'daily' => $fromDate->modify('+1 day'),
            'weekly' => $fromDate->modify('+1 week'),
            'biweekly' => $fromDate->modify('+2 weeks'),
            'monthly' => $fromDate->modify('+1 month'),
            'bimonthly' => $fromDate->modify('+2 months'),
            'quarterly' => $fromDate->modify('+3 months'),
            'semiannually' => $fromDate->modify('+6 months'),
            'annually' => $fromDate->modify('+1 year'),
            default => $fromDate->modify('+1 month'), // Default to monthly
        };
    }
}
