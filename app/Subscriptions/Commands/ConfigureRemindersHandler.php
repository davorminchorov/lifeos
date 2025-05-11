<?php

namespace App\Subscriptions\Commands;

use App\Core\EventSourcing\CommandHandler;
use App\Core\EventSourcing\EventBus\EventBus;
use App\Core\EventSourcing\Store\EventStore;
use App\Subscriptions\Domain\Subscription;
use App\Subscriptions\Events\ReminderConfigured;

class ConfigureRemindersHandler implements CommandHandler
{
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventBus $eventBus
    ) {}

    public function handle(ConfigureReminders $command): void
    {
        // Validate the command
        $command->validate();

        // Get existing events for the subscription
        $events = $this->eventStore->getEventsForAggregate($command->subscriptionId);

        // If no events found, the subscription doesn't exist
        if (empty($events)) {
            throw new \InvalidArgumentException("Subscription with ID {$command->subscriptionId} not found");
        }

        // Reconstruct the subscription from events
        $subscription = Subscription::fromEvents($command->subscriptionId, $events);

        // Configure the reminder settings
        $subscription->configureReminders(
            $command->daysBefore,
            $command->enabled,
            $command->method
        );

        // Store the new events
        $this->eventStore->storeEvents($subscription->recordedEvents());

        // Dispatch events
        foreach ($subscription->recordedEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        // Clear recorded events from the aggregate
        $subscription->clearRecordedEvents();
    }
}
