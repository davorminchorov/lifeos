<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\Command;
use App\Subscriptions\Domain\BillingCycle;

class UpdateSubscription extends Command
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $name,
        public readonly string $description,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $billingCycle,
        public readonly ?string $website = null,
        public readonly ?string $category = null
    ) {}

    public function validate(): void
    {
        if (empty($this->subscriptionId)) {
            throw new \InvalidArgumentException('Subscription ID cannot be empty');
        }

        if (empty($this->name)) {
            throw new \InvalidArgumentException('Subscription name cannot be empty');
        }

        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }

        if (empty($this->currency)) {
            throw new \InvalidArgumentException('Currency cannot be empty');
        }

        if (!BillingCycle::tryFrom($this->billingCycle)) {
            throw new \InvalidArgumentException('Invalid billing cycle');
        }
    }
}
