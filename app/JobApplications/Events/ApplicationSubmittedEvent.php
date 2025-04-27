<?php

namespace App\JobApplications\Events;

class ApplicationSubmittedEvent
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly string $companyName,
        public readonly string $position,
        public readonly string $applicationDate,
        public readonly ?string $jobDescription,
        public readonly ?string $applicationUrl,
        public readonly ?string $salaryRange,
        public readonly ?string $contactPerson,
        public readonly ?string $contactEmail,
        public readonly string $status,
        public readonly ?string $notes
    ) {
    }
}
