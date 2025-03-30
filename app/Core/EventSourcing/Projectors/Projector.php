<?php

namespace App\Core\EventSourcing\Projectors;

use App\Core\EventSourcing\DomainEvent;

interface Projector
{
    /**
     * Handle an incoming event
     */
    public function handle(DomainEvent $event): void;

    /**
     * Reset the projection
     */
    public function reset(): void;

    /**
     * Get the event handlers map for this projector
     *
     * @return array<string, string> Map of event class names to handler method names
     */
    public function handlesEvents(): array;
}
