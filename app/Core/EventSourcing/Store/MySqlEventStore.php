<?php

namespace App\Core\EventSourcing\Store;

use App\Core\EventSourcing\DomainEvent;
use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class MySqlEventStore implements EventStore
{
    protected $eventMap = [];

    public function __construct(
        protected ConnectionInterface $connection
    ) {
    }

    /**
     * Register event class for reconstitution
     */
    public function registerEventClass(string $eventName, string $eventClass): void
    {
        $this->eventMap[$eventName] = $eventClass;
    }

    /**
     * Store one or more events
     *
     * @param DomainEvent[] $events
     */
    public function store(array $events): void
    {
        foreach ($events as $event) {
            $this->connection->table('event_store')->insert([
                'event_id' => $event->eventId,
                'aggregate_id' => $event->aggregateId,
                'aggregate_type' => $this->getAggregateType($event),
                'event_type' => $event->eventName(),
                'occurred_at' => $event->occurredAt->format('Y-m-d H:i:s.u'),
                'version' => $event->aggregateVersion,
                'payload' => json_encode($event->toPayload()),
            ]);
        }
    }

    /**
     * Retrieve all events for a specific aggregate ID
     *
     * @param string $aggregateId
     * @return DomainEvent[]
     */
    public function getEventsForAggregate(string $aggregateId): array
    {
        $rows = $this->connection->table('event_store')
            ->where('aggregate_id', $aggregateId)
            ->orderBy('version', 'asc')
            ->get();

        return $this->hydrateEvents($rows);
    }

    /**
     * Get all events of a specific type
     *
     * @param string $eventClass
     * @return DomainEvent[]
     */
    public function getEventsByType(string $eventClass): array
    {
        // Get event name from class
        $instance = new $eventClass('temp-id');
        $eventName = $instance->eventName();

        $rows = $this->connection->table('event_store')
            ->where('event_type', $eventName)
            ->orderBy('occurred_at', 'asc')
            ->get();

        return $this->hydrateEvents($rows);
    }

    /**
     * Get all events
     *
     * @return DomainEvent[]
     */
    public function getAllEvents(): array
    {
        $rows = $this->connection->table('event_store')
            ->orderBy('occurred_at', 'asc')
            ->get();

        return $this->hydrateEvents($rows);
    }

    /**
     * Get the aggregate type from the event
     */
    protected function getAggregateType(DomainEvent $event): string
    {
        $class = get_class($event);
        $parts = explode('\\', $class);

        // We expect the event to be in a namespace like App\SomeFeature\Events
        // So we take the part just after "App\"
        return isset($parts[1]) ? $parts[1] : 'Unknown';
    }

    /**
     * Hydrate events from database rows
     */
    protected function hydrateEvents($rows): array
    {
        $events = [];

        foreach ($rows as $row) {
            $eventType = $row->event_type;
            $eventClass = $this->getEventClass($eventType);

            $payload = json_decode($row->payload, true);
            $occurredAt = new DateTimeImmutable($row->occurred_at);

            $events[] = $eventClass::fromPayload(
                $row->aggregate_id,
                $payload,
                $row->event_id,
                $occurredAt,
                $row->version
            );
        }

        return $events;
    }

    /**
     * Get the event class for reconstitution
     */
    protected function getEventClass(string $eventType): string
    {
        if (!isset($this->eventMap[$eventType])) {
            throw new RuntimeException("Event class not registered for event type: {$eventType}");
        }

        return $this->eventMap[$eventType];
    }
}
