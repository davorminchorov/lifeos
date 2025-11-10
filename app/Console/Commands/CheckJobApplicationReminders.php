<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\JobApplicationOffer;
use App\Notifications\InterviewReminderNotification;
use App\Notifications\NextActionReminderNotification;
use App\Notifications\OfferDeadlineNotification;
use App\Notifications\StaleApplicationNotification;
use Illuminate\Console\Command;

class CheckJobApplicationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job-applications:check-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for job application reminders and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking job application reminders...');

        $interviewCount = $this->checkUpcomingInterviews();
        $offerCount = $this->checkOfferDeadlines();
        $actionCount = $this->checkOverdueActions();
        $staleCount = $this->checkStaleApplications();

        $this->newLine();
        $this->info('✅ Job application reminders processed');
        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Type', 'Count'],
            [
                ['Upcoming Interviews (24h)', $interviewCount],
                ['Offer Deadlines (3 days)', $offerCount],
                ['Overdue Actions', $actionCount],
                ['Stale Applications (14+ days)', $staleCount],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Check for interviews scheduled in the next 24 hours.
     */
    protected function checkUpcomingInterviews(): int
    {
        $interviews = JobApplicationInterview::query()
            ->with(['jobApplication.user'])
            ->where('completed', false)
            ->whereBetween('scheduled_at', [now(), now()->addDay()])
            ->get();

        foreach ($interviews as $interview) {
            $interview->jobApplication->user->notify(
                new InterviewReminderNotification($interview)
            );
        }

        return $interviews->count();
    }

    /**
     * Check for offer deadlines within 3 days.
     */
    protected function checkOfferDeadlines(): int
    {
        $offers = JobApplicationOffer::query()
            ->with(['jobApplication.user'])
            ->whereIn('status', ['pending', 'negotiating'])
            ->whereNotNull('decision_deadline')
            ->whereBetween('decision_deadline', [now(), now()->addDays(3)])
            ->get();

        foreach ($offers as $offer) {
            $offer->jobApplication->user->notify(
                new OfferDeadlineNotification($offer)
            );
        }

        return $offers->count();
    }

    /**
     * Check for overdue next actions.
     */
    protected function checkOverdueActions(): int
    {
        $applications = JobApplication::query()
            ->with('user')
            ->whereNotNull('next_action_at')
            ->where('next_action_at', '<', now())
            ->whereNull('archived_at')
            ->get();

        foreach ($applications as $application) {
            $application->user->notify(
                new NextActionReminderNotification($application)
            );
        }

        return $applications->count();
    }

    /**
     * Check for stale applications (no status change in 14+ days).
     */
    protected function checkStaleApplications(): int
    {
        $applications = JobApplication::query()
            ->with(['user', 'statusHistories'])
            ->whereNull('archived_at')
            ->whereNotIn('status', ['accepted', 'rejected', 'withdrawn', 'archived'])
            ->get()
            ->filter(function ($application) {
                // Check if no status change in last 14 days
                $latestHistory = $application->statusHistories()
                    ->latest('changed_at')
                    ->first();

                if ($latestHistory) {
                    return $latestHistory->changed_at->lt(now()->subDays(14));
                }

                // If no history, check created_at
                return $application->created_at->lt(now()->subDays(14));
            });

        foreach ($applications as $application) {
            $application->user->notify(
                new StaleApplicationNotification($application)
            );
        }

        return $applications->count();
    }
}
