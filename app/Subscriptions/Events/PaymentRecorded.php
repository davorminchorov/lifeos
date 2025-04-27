<?php

namespace App\Subscriptions\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class PaymentRecorded extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        private readonly string $paymentId,
        private readonly float $amount,
        private readonly string $paymentDate,
        private readonly ?string $notes = null,
        int $aggregateVersion = 1
    ) {
        parent::__construct($aggregateId, $aggregateVersion);
    }

    public function eventName(): string
    {
        return 'payment_recorded';
    }

    public function toPayload(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'amount' => $this->amount,
            'payment_date' => $this->paymentDate,
            'notes' => $this->notes,
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
            $payload['payment_id'],
            $payload['amount'],
            $payload['payment_date'],
            $payload['notes'] ?? null,
            $aggregateVersion
        );
    }
}
