<?php

namespace App\Core\EventSourcing\EventBus;

use App\Core\EventSourcing\DomainEvent;

interface EventBus
{
    /**
     * Dispatch an event to all registered handlers
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * Register a handler for a specific event type
     */
    public function subscribe(string $eventClass, callable $handler): void;
}
