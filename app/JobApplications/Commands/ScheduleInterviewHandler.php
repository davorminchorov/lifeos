<?php

namespace App\JobApplications\Commands;

use App\JobApplications\Domain\JobApplication;
use App\JobApplications\Domain\Interview;
use App\JobApplications\Events\InterviewScheduledEvent;
use App\JobApplications\Queries\GetJobApplicationByIdQuery;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class ScheduleInterviewHandler
{
    public function handle(ScheduleInterviewCommand $command): void
    {
        $application = app(GetJobApplicationByIdQuery::class)->execute([
            'id' => $command->applicationId,
            'user_id' => $command->userId,
        ]);

        if (!$application) {
            throw new \Exception('Job application not found');
        }

        $interviewId = (string) Str::uuid();

        $interview = new Interview(
            $interviewId,
            $command->applicationId,
            $command->interviewDate,
            $command->interviewTime,
            $command->interviewType,
            $command->withPerson,
            $command->location,
            $command->notes
        );

        Event::dispatch(new InterviewScheduledEvent(
            $interviewId,
            $command->applicationId,
            $command->userId,
            $command->interviewDate,
            $command->interviewTime,
            $command->interviewType,
            $command->withPerson,
            $command->location,
            $command->notes
        ));

        // Also update the application status if it's still in 'applied'
        if ($application->status === 'applied') {
            Event::dispatch(new ApplicationUpdatedEvent(
                $command->applicationId,
                $command->userId,
                ['status' => 'interviewing']
            ));
        }
    }
}
