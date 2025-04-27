<?php

namespace App\UtilityBills\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class BillPaid implements DomainEvent
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $aggregateId,
        public string $paymentDate,
        public float $paymentAmount,
        public string $paymentMethod,
        public ?string $notes,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function toPayload(): array
    {
        return [
            'payment_date' => $this->paymentDate,
            'payment_amount' => $this->paymentAmount,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
        ];
    }

    public static function fromPayload(string $aggregateId, array $payload, DateTimeImmutable $occurredAt): static
    {
        return new static(
            $aggregateId,
            $payload['payment_date'],
            $payload['payment_amount'],
            $payload['payment_method'],
            $payload['notes'] ?? null,
            $occurredAt
        );
    }
}
