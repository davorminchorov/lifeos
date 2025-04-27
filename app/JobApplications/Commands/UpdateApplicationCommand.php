<?php

namespace App\JobApplications\Commands;

class UpdateApplicationCommand
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly array $data
    ) {
    }
}
