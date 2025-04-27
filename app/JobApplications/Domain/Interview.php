<?php

namespace App\JobApplications\Domain;

use Illuminate\Contracts\Support\Arrayable;

class Interview implements Arrayable
{
    public function __construct(
        public string $id,
        public string $applicationId,
        public string $interviewDate,
        public string $interviewTime,
        public string $interviewType,
        public string $withPerson,
        public ?string $location = null,
        public ?string $notes = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
        $this->createdAt = $this->createdAt ?? now()->toDateTimeString();
        $this->updatedAt = $this->updatedAt ?? now()->toDateTimeString();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->applicationId,
            'interview_date' => $this->interviewDate,
            'interview_time' => $this->interviewTime,
            'interview_type' => $this->interviewType,
            'with_person' => $this->withPerson,
            'location' => $this->location,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
