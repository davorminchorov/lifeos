<?php

namespace App\Core\EventSourcing\Store;

use App\Core\EventSourcing\DomainEvent;

interface EventStore
{
    /**
     * Store one or more events
     *
     * @param DomainEvent[] $events
     */
    public function store(array $events): void;

    /**
     * Retrieve all events for a specific aggregate ID
     *
     * @param string $aggregateId
     * @return DomainEvent[]
     */
    public function getEventsForAggregate(string $aggregateId): array;

    /**
     * Get all events of a specific type
     *
     * @param string $eventClass
     * @return DomainEvent[]
     */
    public function getEventsByType(string $eventClass): array;

    /**
     * Get all events
     *
     * @return DomainEvent[]
     */
    public function getAllEvents(): array;
}
