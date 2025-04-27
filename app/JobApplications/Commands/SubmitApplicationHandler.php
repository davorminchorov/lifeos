<?php

namespace App\JobApplications\Commands;

use App\JobApplications\Domain\JobApplication;
use App\JobApplications\Events\ApplicationSubmittedEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;

class SubmitApplicationHandler
{
    public function handle(SubmitApplicationCommand $command): string
    {
        $applicationId = (string) Str::uuid();

        $application = new JobApplication(
            $applicationId,
            $command->userId,
            $command->companyName,
            $command->position,
            $command->applicationDate,
            $command->jobDescription,
            $command->applicationUrl,
            $command->salaryRange,
            $command->contactPerson,
            $command->contactEmail,
            $command->status,
            $command->notes
        );

        Event::dispatch(new ApplicationSubmittedEvent(
            $applicationId,
            $command->userId,
            $command->companyName,
            $command->position,
            $command->applicationDate,
            $command->jobDescription,
            $command->applicationUrl,
            $command->salaryRange,
            $command->contactPerson,
            $command->contactEmail,
            $command->status,
            $command->notes
        ));

        return $applicationId;
    }
}
