<?php

namespace App\JobApplications\Commands;

use App\JobApplications\Domain\JobApplication;
use App\JobApplications\Events\ApplicationUpdatedEvent;
use App\JobApplications\Queries\GetJobApplicationByIdQuery;
use Illuminate\Support\Facades\Event;

class UpdateApplicationHandler
{
    public function handle(UpdateApplicationCommand $command): void
    {
        $applicationData = app(GetJobApplicationByIdQuery::class)->execute([
            'id' => $command->id,
            'user_id' => $command->userId,
        ]);

        if (!$applicationData) {
            throw new \Exception('Job application not found');
        }

        $updatedData = array_merge($applicationData->toArray(), $command->data);

        Event::dispatch(new ApplicationUpdatedEvent(
            $command->id,
            $command->userId,
            $updatedData
        ));
    }
}
