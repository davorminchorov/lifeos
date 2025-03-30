<?php

namespace App\Core\EventSourcing;

abstract class Command
{
    /**
     * Validate the command data
     *
     * @throws \InvalidArgumentException if validation fails
     */
    abstract public function validate(): void;
}
