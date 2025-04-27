<?php

namespace App\UtilityBills\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class ReminderSent implements DomainEvent
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $aggregateId,
        public string $sentAt,
        public string $reminderMessage,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function toPayload(): array
    {
        return [
            'sent_at' => $this->sentAt,
            'reminder_message' => $this->reminderMessage,
        ];
    }

    public static function fromPayload(string $aggregateId, array $payload, DateTimeImmutable $occurredAt): static
    {
        return new static(
            $aggregateId,
            $payload['sent_at'],
            $payload['reminder_message'],
            $occurredAt
        );
    }
}
