<?php

namespace App\JobApplications\Commands;

class ScheduleInterviewCommand
{
    public function __construct(
        public readonly string $applicationId,
        public readonly int $userId,
        public readonly string $interviewDate,
        public readonly string $interviewTime,
        public readonly string $interviewType,
        public readonly string $withPerson,
        public readonly ?string $location = null,
        public readonly ?string $notes = null
    ) {
    }
}
