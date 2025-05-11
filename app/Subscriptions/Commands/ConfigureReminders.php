<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\Command;

class ConfigureReminders extends Command
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly int $daysBefore,
        public readonly bool $enabled,
        public readonly string $method
    ) {}

    public function validate(): void
    {
        if (empty($this->subscriptionId)) {
            throw new \InvalidArgumentException('Subscription ID cannot be empty');
        }

        if ($this->daysBefore < 1) {
            throw new \InvalidArgumentException('Days before payment must be at least 1');
        }

        if (!in_array($this->method, ['email', 'sms', 'push', 'in_app'])) {
            throw new \InvalidArgumentException('Invalid notification method');
        }
    }
}
