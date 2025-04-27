<?php

namespace App\JobApplications\Subscribers;

use App\JobApplications\Events\ApplicationSubmittedEvent;
use App\JobApplications\Events\ApplicationUpdatedEvent;
use App\JobApplications\Events\InterviewScheduledEvent;
use App\JobApplications\Events\OutcomeRecordedEvent;
use App\JobApplications\Projections\JobApplicationProjector;
use Illuminate\Events\Dispatcher;

class JobApplicationEventSubscriber
{
    private JobApplicationProjector $projector;

    public function __construct(JobApplicationProjector $projector)
    {
        $this->projector = $projector;
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            ApplicationSubmittedEvent::class,
            [JobApplicationEventSubscriber::class, 'handleApplicationSubmitted']
        );

        $events->listen(
            ApplicationUpdatedEvent::class,
            [JobApplicationEventSubscriber::class, 'handleApplicationUpdated']
        );

        $events->listen(
            InterviewScheduledEvent::class,
            [JobApplicationEventSubscriber::class, 'handleInterviewScheduled']
        );

        $events->listen(
            OutcomeRecordedEvent::class,
            [JobApplicationEventSubscriber::class, 'handleOutcomeRecorded']
        );
    }

    public function handleApplicationSubmitted(ApplicationSubmittedEvent $event): void
    {
        $this->projector->handleApplicationSubmitted($event);
    }

    public function handleApplicationUpdated(ApplicationUpdatedEvent $event): void
    {
        $this->projector->handleApplicationUpdated($event);
    }

    public function handleInterviewScheduled(InterviewScheduledEvent $event): void
    {
        $this->projector->handleInterviewScheduled($event);
    }

    public function handleOutcomeRecorded(OutcomeRecordedEvent $event): void
    {
        $this->projector->handleOutcomeRecorded($event);
    }
}
