<?php

namespace App\JobApplications\Commands;

use App\JobApplications\Domain\JobApplication;
use App\JobApplications\Events\OutcomeRecordedEvent;
use App\JobApplications\Events\ApplicationUpdatedEvent;
use App\JobApplications\Queries\GetJobApplicationByIdQuery;
use Illuminate\Support\Facades\Event;

class RecordOutcomeHandler
{
    public function handle(RecordOutcomeCommand $command): void
    {
        $application = app(GetJobApplicationByIdQuery::class)->execute([
            'id' => $command->applicationId,
            'user_id' => $command->userId,
        ]);

        if (!$application) {
            throw new \Exception('Job application not found');
        }

        Event::dispatch(new OutcomeRecordedEvent(
            $command->applicationId,
            $command->userId,
            $command->outcome,
            $command->outcomeDate,
            $command->salaryOffered,
            $command->feedback,
            $command->notes
        ));

        // Also update the application status
        Event::dispatch(new ApplicationUpdatedEvent(
            $command->applicationId,
            $command->userId,
            ['status' => $command->outcome]
        ));
    }
}
