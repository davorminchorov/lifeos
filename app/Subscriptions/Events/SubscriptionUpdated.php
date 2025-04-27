<?php

namespace App\Subscriptions\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class SubscriptionUpdated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $name,
        private readonly string $description,
        private readonly float $amount,
        private readonly string $currency,
        private readonly string $billingCycle,
        private readonly ?string $website = null,
        private readonly ?string $category = null,
        int $aggregateVersion = 1
    ) {
        parent::__construct($aggregateId, $aggregateVersion);
    }

    public function eventName(): string
    {
        return 'subscription_updated';
    }

    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'billing_cycle' => $this->billingCycle,
            'website' => $this->website,
            'category' => $this->category,
        ];
    }

    public static function fromPayload(
        string $aggregateId,
        array $payload,
        string $eventId,
        DateTimeImmutable $occurredAt,
        int $aggregateVersion
    ): self {
        return new self(
            $aggregateId,
            $payload['name'],
            $payload['description'],
            $payload['amount'],
            $payload['currency'],
            $payload['billing_cycle'],
            $payload['website'] ?? null,
            $payload['category'] ?? null,
            $aggregateVersion
        );
    }
}
