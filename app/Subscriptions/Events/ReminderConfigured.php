<?php

namespace App\Subscriptions\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class ReminderConfigured extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly int $daysBefore,
        private readonly bool $enabled,
        private readonly string $method,
        int $aggregateVersion = 1
    ) {
        parent::__construct($aggregateId, $aggregateVersion);
    }

    public function eventName(): string
    {
        return 'reminder_configured';
    }

    public function toPayload(): array
    {
        return [
            'days_before' => $this->daysBefore,
            'enabled' => $this->enabled,
            'method' => $this->method,
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
            $payload['days_before'],
            $payload['enabled'],
            $payload['method'],
            $aggregateVersion
        );
    }
}
