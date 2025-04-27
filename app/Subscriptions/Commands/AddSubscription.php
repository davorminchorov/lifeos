<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\Command;
use App\Subscriptions\Domain\BillingCycle;
use Ramsey\Uuid\Uuid;

class AddSubscription extends Command
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $name,
        public readonly string $description,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $billingCycle,
        public readonly string $startDate,
        public readonly ?string $website = null,
        public readonly ?string $category = null
    ) {}

    public static function create(
        string $name,
        string $description,
        float $amount,
        string $currency,
        string $billingCycle,
        string $startDate,
        ?string $website = null,
        ?string $category = null
    ): self {
        return new self(
            Uuid::uuid4()->toString(),
            $name,
            $description,
            $amount,
            $currency,
            $billingCycle,
            $startDate,
            $website,
            $category
        );
    }

    public function validate(): void
    {
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

        if (empty($this->startDate)) {
            throw new \InvalidArgumentException('Start date cannot be empty');
        }

        // Validate the start date format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $this->startDate);
        if (!$date || $date->format('Y-m-d') !== $this->startDate) {
            throw new \InvalidArgumentException('Invalid start date format. Use YYYY-MM-DD');
        }
    }
}
