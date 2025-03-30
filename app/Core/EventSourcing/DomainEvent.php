<?php

namespace App\Core\EventSourcing;

use DateTimeImmutable;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent implements JsonSerializable
{
    public readonly string $eventId;
    public readonly DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly string $aggregateId,
        public readonly int $aggregateVersion = 1
    ) {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredAt = new DateTimeImmutable();
    }

    abstract public function eventName(): string;

    public function jsonSerialize(): array
    {
        return [
            'event_id' => $this->eventId,
            'aggregate_id' => $this->aggregateId,
            'aggregate_version' => $this->aggregateVersion,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'event_name' => $this->eventName(),
            'payload' => $this->toPayload(),
        ];
    }

    abstract public function toPayload(): array;

    abstract public static function fromPayload(string $aggregateId, array $payload, string $eventId, DateTimeImmutable $occurredAt, int $aggregateVersion): self;
}
