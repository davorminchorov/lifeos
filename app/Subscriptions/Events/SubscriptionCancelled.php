<?php

namespace App\Subscriptions\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class SubscriptionCancelled extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $endDate,
        int $aggregateVersion = 1
    ) {
        parent::__construct($aggregateId, $aggregateVersion);
    }

    public function eventName(): string
    {
        return 'subscription_cancelled';
    }

    public function toPayload(): array
    {
        return [
            'end_date' => $this->endDate,
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
            $payload['end_date'],
            $aggregateVersion
        );
    }
}
