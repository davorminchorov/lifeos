<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\Command;
use Ramsey\Uuid\Uuid;

class RecordPayment extends Command
{
    public function __construct(
        public readonly string $subscriptionId,
        public readonly string $paymentId,
        public readonly float $amount,
        public readonly string $paymentDate,
        public readonly ?string $notes = null
    ) {}

    public static function create(
        string $subscriptionId,
        float $amount,
        string $paymentDate,
        ?string $notes = null
    ): self {
        return new self(
            $subscriptionId,
            Uuid::uuid4()->toString(),
            $amount,
            $paymentDate,
            $notes
        );
    }

    public function validate(): void
    {
        if (empty($this->subscriptionId)) {
            throw new \InvalidArgumentException('Subscription ID cannot be empty');
        }

        if (empty($this->paymentId)) {
            throw new \InvalidArgumentException('Payment ID cannot be empty');
        }

        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }

        if (empty($this->paymentDate)) {
            throw new \InvalidArgumentException('Payment date cannot be empty');
        }

        // Validate the payment date format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $this->paymentDate);
        if (!$date || $date->format('Y-m-d') !== $this->paymentDate) {
            throw new \InvalidArgumentException('Invalid payment date format. Use YYYY-MM-DD');
        }
    }
}
