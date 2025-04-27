<?php

namespace App\JobApplications\Events;

class InterviewScheduledEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $applicationId,
        public readonly int $userId,
        public readonly string $interviewDate,
        public readonly string $interviewTime,
        public readonly string $interviewType,
        public readonly string $withPerson,
        public readonly ?string $location,
        public readonly ?string $notes
    ) {
    }
}
