<?php

namespace App\Core\EventSourcing;

use Illuminate\Container\Container;
use InvalidArgumentException;

class QueryBus
{
    /** @var array<string, string> */
    protected array $handlers = [];

    public function __construct(
        protected Container $container
    ) {
    }

    /**
     * Register a handler for a query
     */
    public function register(string $queryClass, string $handlerClass): void
    {
        $this->handlers[$queryClass] = $handlerClass;
    }

    /**
     * Dispatch a query to its handler and get the result
     *
     * @return mixed The query result
     * @throws InvalidArgumentException if no handler registered
     */
    public function dispatch(Query $query): mixed
    {
        // Get the query class
        $queryClass = get_class($query);

        // Check if we have a handler for this query
        if (!isset($this->handlers[$queryClass])) {
            throw new InvalidArgumentException("No handler registered for query: {$queryClass}");
        }

        // Resolve the handler from the container
        $handlerClass = $this->handlers[$queryClass];
        $handler = $this->container->make($handlerClass);

        // Handle the query and return result
        return $handler->handle($query);
    }
}
