<?php

namespace App\JobApplications\Domain;

use Illuminate\Contracts\Support\Arrayable;

class JobApplication implements Arrayable
{
    public function __construct(
        public string $id,
        public int $userId,
        public string $companyName,
        public string $position,
        public string $applicationDate,
        public ?string $jobDescription = null,
        public ?string $applicationUrl = null,
        public ?string $salaryRange = null,
        public ?string $contactPerson = null,
        public ?string $contactEmail = null,
        public string $status = 'applied',
        public ?string $notes = null,
        public array $interviews = [],
        public ?array $outcome = null,
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
            'user_id' => $this->userId,
            'company_name' => $this->companyName,
            'position' => $this->position,
            'application_date' => $this->applicationDate,
            'job_description' => $this->jobDescription,
            'application_url' => $this->applicationUrl,
            'salary_range' => $this->salaryRange,
            'contact_person' => $this->contactPerson,
            'contact_email' => $this->contactEmail,
            'status' => $this->status,
            'notes' => $this->notes,
            'interviews' => $this->interviews,
            'outcome' => $this->outcome,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
