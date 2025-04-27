<?php

namespace App\UtilityBills\Events;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;

class BillAdded implements DomainEvent
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $aggregateId,
        public string $name,
        public string $provider,
        public float $amount,
        public string $dueDate,
        public string $category,
        public bool $isRecurring,
        public ?string $recurrencePeriod,
        public ?string $notes,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'provider' => $this->provider,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'category' => $this->category,
            'is_recurring' => $this->isRecurring,
            'recurrence_period' => $this->recurrencePeriod,
            'notes' => $this->notes,
        ];
    }

    public static function fromPayload(string $aggregateId, array $payload, DateTimeImmutable $occurredAt): static
    {
        return new static(
            $aggregateId,
            $payload['name'],
            $payload['provider'],
            $payload['amount'],
            $payload['due_date'],
            $payload['category'],
            $payload['is_recurring'],
            $payload['recurrence_period'] ?? null,
            $payload['notes'] ?? null,
            $occurredAt
        );
    }
}
