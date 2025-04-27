<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\CommandHandler;
use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\Store\EventStore;
use App\Subscriptions\Domain\BillingCycle;
use App\Subscriptions\Domain\Subscription;

class UpdateSubscriptionHandler implements CommandHandler
{
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventBus $eventBus
    ) {}

    public function handle(UpdateSubscription $command): void
    {
        // Validate the command
        $command->validate();

        // Load the subscription aggregate
        $events = $this->eventStore->getEventsForAggregate($command->subscriptionId);

        if (empty($events)) {
            throw new \InvalidArgumentException('Subscription not found');
        }

        $subscription = Subscription::fromEvents($command->subscriptionId, $events);

        // Update the subscription
        $subscription->update(
            $command->name,
            $command->description,
            $command->amount,
            $command->currency,
            BillingCycle::from($command->billingCycle),
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
