<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\Command;

class CancelSubscription extends Command
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $endDate
    ) {}

    public function validate(): void
    {
        if (empty($this->subscriptionId)) {
            throw new \InvalidArgumentException('Subscription ID cannot be empty');
        }

        if (empty($this->endDate)) {
            throw new \InvalidArgumentException('End date cannot be empty');
        }

        // Validate the end date format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $this->endDate);
        if (!$date || $date->format('Y-m-d') !== $this->endDate) {
            throw new \InvalidArgumentException('Invalid end date format. Use YYYY-MM-DD');
        }

        // Check if end date is not in the past
        if ($date < new \DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('End date cannot be in the past');
        }
    }
}
