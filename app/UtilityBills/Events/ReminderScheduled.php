<?php

namespace App\UtilityBills\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class ReminderScheduled implements DomainEvent
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $aggregateId,
        public string $reminderDate,
        public string $reminderMessage,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function toPayload(): array
    {
        return [
            'reminder_date' => $this->reminderDate,
            'reminder_message' => $this->reminderMessage,
        ];
    }

    public static function fromPayload(string $aggregateId, array $payload, DateTimeImmutable $occurredAt): static
    {
        return new static(
            $aggregateId,
            $payload['reminder_date'],
            $payload['reminder_message'],
            $occurredAt
        );
    }
}
