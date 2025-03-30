<?php

namespace App\Core\EventSourcing;

use Ramsey\Uuid\Uuid;

abstract class AggregateRoot
{
    /** @var DomainEvent[] */
    private array $recordedEvents = [];

    public readonly string $aggregateId;
    protected int $aggregateVersion = 0;

    public function __construct(string $aggregateId = null)
    {
        $this->aggregateId = $aggregateId ?? Uuid::uuid4()->toString();
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function aggregateVersion(): int
    {
        return $this->aggregateVersion;
    }

    /**
     * @return DomainEvent[]
     */
    public function recordedEvents(): array
    {
        return $this->recordedEvents;
    }

    public function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    /**
     * Apply an event to the aggregate
     */
    public function apply(DomainEvent $event): void
    {
        $this->recordEvent($event);
        $this->applyEvent($event);
    }

    /**
     * Record an event without applying it
     */
    protected function recordEvent(DomainEvent $event): void
    {
        $this->aggregateVersion++;
        $this->recordedEvents[] = $event;
    }

    /**
     * Apply the event to update the aggregate state
     */
    protected function applyEvent(DomainEvent $event): void
    {
        $eventClassParts = explode('\\', get_class($event));
        $eventName = end($eventClassParts);

        $method = 'apply' . $eventName;

        if (method_exists($this, $method)) {
            $this->$method($event);
        }
    }

    /**
     * Reconstitute an aggregate from a series of events
     */
    public static function fromEvents(string $aggregateId, array $events): static
    {
        $aggregate = new static($aggregateId);

        foreach ($events as $event) {
            $aggregate->applyEvent($event);
            $aggregate->aggregateVersion = $event->aggregateVersion;
        }

        return $aggregate;
    }
}
