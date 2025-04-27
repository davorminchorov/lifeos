<?php

namespace App\JobApplications\Events;

class OutcomeRecordedEvent
{
    public function __construct(
        public readonly string $applicationId,
        public readonly int $userId,
        public readonly string $outcome,
        public readonly string $outcomeDate,
        public readonly ?string $salaryOffered,
        public readonly ?string $feedback,
        public readonly ?string $notes
    ) {
    }
}
