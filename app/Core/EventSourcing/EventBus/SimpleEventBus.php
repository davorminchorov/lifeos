<?php

namespace App\Core\EventSourcing\EventBus;

use App\Core\EventSourcing\DomainEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;

class SimpleEventBus implements EventBus
{
    /** @var array<string, callable[]> */
    protected array $handlers = [];

    /**
     * Dispatch an event to all registered handlers
     */
    public function dispatch(DomainEvent $event): void
    {
        $eventClass = get_class($event);

        if (!isset($this->handlers[$eventClass])) {
            return;
        }

        foreach ($this->handlers[$eventClass] as $handler) {
            if (is_object($handler) && $handler instanceof ShouldQueue) {
                // Queue the handler
                Queue::push(function() use ($handler, $event) {
                    $handler($event);
                });
            } else {
                // Execute immediately
                $handler($event);
            }
        }
    }

    /**
     * Register a handler for a specific event type
     */
    public function subscribe(string $eventClass, callable $handler): void
    {
        if (!isset($this->handlers[$eventClass])) {
            $this->handlers[$eventClass] = [];
        }

        $this->handlers[$eventClass][] = $handler;
    }
}
