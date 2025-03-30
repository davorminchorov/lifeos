<?php

namespace App\Core\EventSourcing\Projectors;

use App\Core\EventSourcing\DomainEvent;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractProjector implements Projector
{
    /**
     * Handle an incoming event
     */
    public function handle(DomainEvent $event): void
    {
        $eventClass = get_class($event);
        $handlersMap = $this->handlesEvents();

        if (isset($handlersMap[$eventClass])) {
            $method = $handlersMap[$eventClass];
            $this->$method($event);
        }
    }

    /**
     * Get the event handlers map for this projector
     *
     * @return array<string, string> Map of event class names to handler method names
     */
    public function handlesEvents(): array
    {
        $handlers = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strpos($method->getName(), 'when') === 0) {
                $eventName = substr($method->getName(), 4);
                $parameters = $method->getParameters();

                if (count($parameters) === 1) {
                    $eventClass = $parameters[0]->getType()->getName();
                    $handlers[$eventClass] = $method->getName();
                }
            }
        }

        return $handlers;
    }

    /**
     * Reset the projection
     */
    abstract public function reset(): void;
}
