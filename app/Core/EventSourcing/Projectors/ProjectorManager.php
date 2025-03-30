<?php

namespace App\Core\EventSourcing\Projectors;

use App\Core\EventSourcing\DomainEvent;
use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\Store\EventStore;

class ProjectorManager
{
    /** @var Projector[] */
    protected array $projectors = [];

    public function __construct(
        protected EventBus $eventBus,
        protected EventStore $eventStore
    ) {
    }

    /**
     * Register a projector
     */
    public function register(Projector $projector): void
    {
        $this->projectors[] = $projector;

        // Subscribe projector to relevant event types
        foreach ($projector->handlesEvents() as $eventClass => $handlerMethod) {
            $this->eventBus->subscribe($eventClass, function (DomainEvent $event) use ($projector) {
                $projector->handle($event);
            });
        }
    }

    /**
     * Reset all projectors
     */
    public function resetAll(): void
    {
        foreach ($this->projectors as $projector) {
            $projector->reset();
        }
    }

    /**
     * Rebuild all projections from scratch
     */
    public function rebuild(): void
    {
        // Reset all projections
        $this->resetAll();

        // Replay all events
        $events = $this->eventStore->getAllEvents();

        foreach ($events as $event) {
            foreach ($this->projectors as $projector) {
                $projector->handle($event);
            }
        }
    }

    /**
     * Get all registered projectors
     *
     * @return Projector[]
     */
    public function getProjectors(): array
    {
        return $this->projectors;
    }
}
