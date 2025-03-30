<?php

namespace App\Core\EventSourcing;

interface CommandHandler
{
    /**
     * Handle a command
     *
     * @throws \InvalidArgumentException if command validation fails
     */
    public function handle(Command $command): void;
}
