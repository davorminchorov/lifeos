<?php

namespace App\JobApplications\Commands;

class RecordOutcomeCommand
{
    public function __construct(
        public readonly string $applicationId,
        public readonly int $userId,
        public readonly string $outcome,
        public readonly string $outcomeDate,
        public readonly ?string $salaryOffered = null,
        public readonly ?string $feedback = null,
        public readonly ?string $notes = null
    ) {
    }
}
