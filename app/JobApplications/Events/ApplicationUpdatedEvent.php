<?php

namespace App\JobApplications\Events;

class ApplicationUpdatedEvent
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly array $data
    ) {
    }
}
