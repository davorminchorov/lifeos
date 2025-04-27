<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\CommandHandler;
use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\Store\EventStore;
use App\Subscriptions\Domain\Subscription;
use DateTimeImmutable;

class RecordPaymentHandler implements CommandHandler
{
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventBus $eventBus
    ) {}

    public function handle(RecordPayment $command): void
    {
        // Validate the command
        $command->validate();

        // Load the subscription aggregate
        $events = $this->eventStore->getEventsForAggregate($command->subscriptionId);

        if (empty($events)) {
            throw new \InvalidArgumentException('Subscription not found');
        }

        $subscription = Subscription::fromEvents($command->subscriptionId, $events);

        // Record the payment
        $subscription->recordPayment(
            $command->paymentId,
            $command->amount,
            new DateTimeImmutable($command->paymentDate),
            $command->notes
        );

        // Store the events
        $this->eventStore->storeEvents($subscription->recordedEvents());

        // Dispatch the events
        foreach ($subscription->recordedEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        // Clear recorded events
        $subscription->clearRecordedEvents();
    }
}
