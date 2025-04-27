<?php

namespace App\JobApplications\Projections;

use App\JobApplications\Events\ApplicationSubmittedEvent;
use App\JobApplications\Events\ApplicationUpdatedEvent;
use App\JobApplications\Events\InterviewScheduledEvent;
use App\JobApplications\Events\OutcomeRecordedEvent;
use Illuminate\Support\Facades\DB;

class JobApplicationProjector
{
    public function handleApplicationSubmitted(ApplicationSubmittedEvent $event): void
    {
        DB::table('job_applications')->insert([
            'id' => $event->id,
            'user_id' => $event->userId,
            'company_name' => $event->companyName,
            'position' => $event->position,
            'application_date' => $event->applicationDate,
            'job_description' => $event->jobDescription,
            'application_url' => $event->applicationUrl,
            'salary_range' => $event->salaryRange,
            'contact_person' => $event->contactPerson,
            'contact_email' => $event->contactEmail,
            'status' => $event->status,
            'notes' => $event->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function handleApplicationUpdated(ApplicationUpdatedEvent $event): void
    {
        $updateData = collect($event->data)
            ->filter(function ($value, $key) {
                return in_array($key, [
                    'company_name', 'position', 'application_date',
                    'job_description', 'application_url', 'salary_range',
                    'contact_person', 'contact_email', 'status', 'notes'
                ]);
            })
            ->toArray();

        if (empty($updateData)) {
            return;
        }

        $updateData['updated_at'] = now();

        DB::table('job_applications')
            ->where('id', $event->id)
            ->where('user_id', $event->userId)
            ->update($updateData);
    }

    public function handleInterviewScheduled(InterviewScheduledEvent $event): void
    {
        DB::table('job_application_interviews')->insert([
            'id' => $event->id,
            'application_id' => $event->applicationId,
            'interview_date' => $event->interviewDate,
            'interview_time' => $event->interviewTime,
            'interview_type' => $event->interviewType,
            'with_person' => $event->withPerson,
            'location' => $event->location,
            'notes' => $event->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function handleOutcomeRecorded(OutcomeRecordedEvent $event): void
    {
        DB::table('job_application_outcomes')->insert([
            'application_id' => $event->applicationId,
            'outcome' => $event->outcome,
            'outcome_date' => $event->outcomeDate,
            'salary_offered' => $event->salaryOffered,
            'feedback' => $event->feedback,
            'notes' => $event->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
