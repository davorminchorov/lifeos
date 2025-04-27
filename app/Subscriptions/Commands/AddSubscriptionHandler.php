<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\CommandHandler;
use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\Store\EventStore;
use App\Subscriptions\Domain\BillingCycle;
use App\Subscriptions\Domain\Subscription;
use DateTimeImmutable;

class AddSubscriptionHandler implements CommandHandler
{
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventBus $eventBus
    ) {}

    public function handle(AddSubscription $command): void
    {
        // Validate the command
        $command->validate();

        // Create the subscription aggregate
        $subscription = Subscription::create(
            $command->subscriptionId,
            $command->name,
            $command->description,
            $command->amount,
            $command->currency,
            BillingCycle::from($command->billingCycle),
            new DateTimeImmutable($command->startDate),
            $command->website,
            $command->category
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
