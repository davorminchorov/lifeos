<?php

namespace App\JobApplications\Commands;

class SubmitApplicationCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $companyName,
        public readonly string $position,
        public readonly string $applicationDate,
        public readonly ?string $jobDescription = null,
        public readonly ?string $applicationUrl = null,
        public readonly ?string $salaryRange = null,
        public readonly ?string $contactPerson = null,
        public readonly ?string $contactEmail = null,
        public readonly string $status = 'applied',
        public readonly ?string $notes = null
    ) {
    }
}
