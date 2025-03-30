<?php

namespace App\Core\EventSourcing;

use Illuminate\Container\Container;
use InvalidArgumentException;

class CommandBus
{
    /** @var array<string, string> */
    protected array $handlers = [];

    public function __construct(
        protected Container $container
    ) {
    }

    /**
     * Register a handler for a command
     */
    public function register(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    /**
     * Dispatch a command to its handler
     *
     * @throws InvalidArgumentException if command validation fails
     */
    public function dispatch(Command $command): void
    {
        // Validate the command
        $command->validate();

        // Get the command class
        $commandClass = get_class($command);

        // Check if we have a handler for this command
        if (!isset($this->handlers[$commandClass])) {
            throw new InvalidArgumentException("No handler registered for command: {$commandClass}");
        }

        // Resolve the handler from the container
        $handlerClass = $this->handlers[$commandClass];
        $handler = $this->container->make($handlerClass);

        // Handle the command
        $handler->handle($command);
    }
}
