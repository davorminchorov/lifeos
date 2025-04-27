<?php

namespace App\Subscriptions\Domain;

use App\Core\EventSourcing\AggregateRoot;
use App\Subscriptions\Events\SubscriptionAdded;
use App\Subscriptions\Events\SubscriptionUpdated;
use App\Subscriptions\Events\SubscriptionCancelled;
use App\Subscriptions\Events\PaymentRecorded;
use DateTimeImmutable;

class Subscription extends AggregateRoot
{
    private string $name;
    private string $description;
    private float $amount;
    private string $currency;
    private BillingCycle $billingCycle;
    private DateTimeImmutable $startDate;
    private ?DateTimeImmutable $endDate;
    private string $status;
    private array $payments = [];
    private ?DateTimeImmutable $nextPaymentDate;
    private ?string $website;
    private ?string $category;

    public static function create(
        string $subscriptionId,
        string $name,
        string $description,
        float $amount,
        string $currency,
        BillingCycle $billingCycle,
        DateTimeImmutable $startDate,
        ?string $website = null,
        ?string $category = null
    ): self {
        $subscription = new self($subscriptionId);

        $subscription->apply(new SubscriptionAdded(
            $subscriptionId,
            $name,
            $description,
            $amount,
            $currency,
            $billingCycle->value,
            $startDate->format(DATE_ATOM),
            $website,
            $category
        ));

        return $subscription;
    }

    public function update(
        string $name,
        string $description,
        float $amount,
        string $currency,
        BillingCycle $billingCycle,
        ?string $website = null,
        ?string $category = null
    ): void {
        $this->apply(new SubscriptionUpdated(
            $this->aggregateId,
            $name,
            $description,
            $amount,
            $currency,
            $billingCycle->value,
            $website,
            $category
        ));
    }

    public function cancel(DateTimeImmutable $endDate): void
    {
        if ($this->status === SubscriptionStatus::CANCELLED->value) {
            throw new \InvalidArgumentException('Cannot cancel an already cancelled subscription');
        }

        $this->apply(new SubscriptionCancelled(
            $this->aggregateId,
            $endDate->format(DATE_ATOM)
        ));
    }

    public function recordPayment(
        string $paymentId,
        float $amount,
        DateTimeImmutable $paymentDate,
        string $notes = null
    ): void {
        $this->apply(new PaymentRecorded(
            $this->aggregateId,
            $paymentId,
            $amount,
            $paymentDate->format(DATE_ATOM),
            $notes
        ));

        $this->calculateNextPaymentDate();
    }

    private function calculateNextPaymentDate(): void
    {
        if ($this->status === SubscriptionStatus::CANCELLED->value) {
            $this->nextPaymentDate = null;
            return;
        }

        $lastPaymentDate = null;

        if (!empty($this->payments)) {
            $lastPayment = end($this->payments);
            $lastPaymentDate = new DateTimeImmutable($lastPayment['date']);
        } else {
            $lastPaymentDate = $this->startDate;
        }

        $this->nextPaymentDate = $this->billingCycle->calculateNextDate($lastPaymentDate);
    }

    protected function applySubscriptionAdded(SubscriptionAdded $event): void
    {
        $payload = $event->toPayload();

        $this->name = $payload['name'];
        $this->description = $payload['description'];
        $this->amount = $payload['amount'];
        $this->currency = $payload['currency'];
        $this->billingCycle = BillingCycle::from($payload['billing_cycle']);
        $this->startDate = new DateTimeImmutable($payload['start_date']);
        $this->endDate = null;
        $this->status = SubscriptionStatus::ACTIVE->value;
        $this->website = $payload['website'] ?? null;
        $this->category = $payload['category'] ?? null;

        $this->calculateNextPaymentDate();
    }

    protected function applySubscriptionUpdated(SubscriptionUpdated $event): void
    {
        $payload = $event->toPayload();

        $this->name = $payload['name'];
        $this->description = $payload['description'];
        $this->amount = $payload['amount'];
        $this->currency = $payload['currency'];
        $this->billingCycle = BillingCycle::from($payload['billing_cycle']);
        $this->website = $payload['website'] ?? $this->website;
        $this->category = $payload['category'] ?? $this->category;

        $this->calculateNextPaymentDate();
    }

    protected function applySubscriptionCancelled(SubscriptionCancelled $event): void
    {
        $payload = $event->toPayload();

        $this->endDate = new DateTimeImmutable($payload['end_date']);
        $this->status = SubscriptionStatus::CANCELLED->value;
        $this->nextPaymentDate = null;
    }

    protected function applyPaymentRecorded(PaymentRecorded $event): void
    {
        $payload = $event->toPayload();

        $this->payments[] = [
            'id' => $payload['payment_id'],
            'amount' => $payload['amount'],
            'date' => $payload['payment_date'],
            'notes' => $payload['notes'] ?? null
        ];

        $this->calculateNextPaymentDate();
    }
}
