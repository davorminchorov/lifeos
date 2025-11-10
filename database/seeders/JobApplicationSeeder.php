<?php

namespace Database\Seeders;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\JobApplicationOffer;
use App\Models\JobApplicationStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JobApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');

            return;
        }

        $this->command->info('Seeding Job Applications...');

        // Create job applications for each user
        foreach ($users as $user) {
            $this->createJobApplicationsForUser($user);
        }

        // Create some additional random job applications
        JobApplication::factory()
            ->count(10)
            ->create();

        $this->command->info('Job Application seeding completed!');
    }

    /**
     * Create realistic job applications for a specific user.
     */
    private function createJobApplicationsForUser(User $user): void
    {
        // Wishlist applications - interested but not applied yet
        $wishlistScenarios = [
            [
                'company_name' => 'Google',
                'job_title' => 'Senior Software Engineer',
                'location' => 'Mountain View, CA',
                'remote' => false,
                'salary_min' => 150000,
                'salary_max' => 200000,
                'source' => ApplicationSource::LINKEDIN,
                'priority' => 2,
            ],
            [
                'company_name' => 'Apple',
                'job_title' => 'iOS Developer',
                'location' => 'Cupertino, CA',
                'remote' => false,
                'salary_min' => 140000,
                'salary_max' => 190000,
                'source' => ApplicationSource::COMPANY_WEBSITE,
                'priority' => 1,
            ],
        ];

        foreach ($wishlistScenarios as $scenario) {
            $application = JobApplication::factory()
                ->wishlist()
                ->create([
                    'user_id' => $user->id,
                    'currency' => 'USD',
                    ...$scenario,
                ]);

            // Create initial status history
            JobApplicationStatusHistory::factory()
                ->initial()
                ->create([
                    'user_id' => $user->id,
                    'job_application_id' => $application->id,
                    'changed_at' => $application->created_at,
                ]);
        }

        // Applied applications - recently submitted
        $appliedScenarios = [
            [
                'company_name' => 'Microsoft',
                'job_title' => 'Full Stack Developer',
                'location' => 'Remote',
                'remote' => true,
                'salary_min' => 130000,
                'salary_max' => 170000,
                'source' => ApplicationSource::JOB_BOARD,
                'priority' => 1,
                'applied_at' => Carbon::now()->subDays(5),
            ],
            [
                'company_name' => 'Amazon',
                'job_title' => 'Backend Engineer',
                'location' => 'Seattle, WA',
                'remote' => false,
                'salary_min' => 135000,
                'salary_max' => 175000,
                'source' => ApplicationSource::REFERRAL,
                'priority' => 2,
                'applied_at' => Carbon::now()->subDays(10),
            ],
        ];

        foreach ($appliedScenarios as $scenario) {
            $application = JobApplication::factory()
                ->applied()
                ->create([
                    'user_id' => $user->id,
                    'currency' => 'USD',
                    ...$scenario,
                ]);

            // Create status histories
            JobApplicationStatusHistory::factory()
                ->initial()
                ->create([
                    'user_id' => $user->id,
                    'job_application_id' => $application->id,
                    'changed_at' => $application->created_at,
                ]);

            JobApplicationStatusHistory::factory()
                ->toApplied()
                ->create([
                    'user_id' => $user->id,
                    'job_application_id' => $application->id,
                    'changed_at' => $application->applied_at,
                ]);
        }

        // Interview stage applications
        $interviewApp = JobApplication::factory()
            ->interview()
            ->create([
                'user_id' => $user->id,
                'company_name' => 'Meta',
                'job_title' => 'Frontend Engineer',
                'location' => 'Menlo Park, CA',
                'remote' => false,
                'salary_min' => 145000,
                'salary_max' => 185000,
                'currency' => 'USD',
                'source' => ApplicationSource::LINKEDIN,
                'priority' => 3,
                'applied_at' => Carbon::now()->subDays(30),
            ]);

        // Create status history for interview application
        JobApplicationStatusHistory::factory()
            ->initial()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'changed_at' => $interviewApp->created_at,
            ]);

        JobApplicationStatusHistory::factory()
            ->toApplied()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'changed_at' => $interviewApp->applied_at,
            ]);

        JobApplicationStatusHistory::factory()
            ->toInterview()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'changed_at' => Carbon::now()->subDays(15),
            ]);

        // Add interviews
        JobApplicationInterview::factory()
            ->phoneScreen()
            ->completed()
            ->positive()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'scheduled_at' => Carbon::now()->subDays(20),
            ]);

        JobApplicationInterview::factory()
            ->video()
            ->completed()
            ->positive()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'scheduled_at' => Carbon::now()->subDays(10),
            ]);

        JobApplicationInterview::factory()
            ->onsite()
            ->upcoming()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $interviewApp->id,
                'scheduled_at' => Carbon::now()->addDays(5),
            ]);

        // Offer application
        $offerApp = JobApplication::factory()
            ->offer()
            ->create([
                'user_id' => $user->id,
                'company_name' => 'Netflix',
                'job_title' => 'Senior Backend Engineer',
                'location' => 'Los Gatos, CA',
                'remote' => true,
                'salary_min' => 160000,
                'salary_max' => 210000,
                'currency' => 'USD',
                'source' => ApplicationSource::RECRUITER,
                'priority' => 3,
                'applied_at' => Carbon::now()->subDays(45),
            ]);

        // Create status history for offer application
        JobApplicationStatusHistory::factory()
            ->initial()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $offerApp->id,
                'changed_at' => $offerApp->created_at,
            ]);

        JobApplicationStatusHistory::factory()
            ->toApplied()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $offerApp->id,
                'changed_at' => $offerApp->applied_at,
            ]);

        JobApplicationStatusHistory::factory()
            ->toOffer()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $offerApp->id,
                'changed_at' => Carbon::now()->subDays(5),
            ]);

        // Add offer
        JobApplicationOffer::factory()
            ->pending()
            ->withEquity()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $offerApp->id,
                'base_salary' => 180000,
                'bonus' => 25000,
                'currency' => 'USD',
                'decision_deadline' => Carbon::now()->addDays(10),
            ]);

        // Rejected application
        $rejectedApp = JobApplication::factory()
            ->create([
                'user_id' => $user->id,
                'company_name' => 'Stripe',
                'job_title' => 'Software Engineer',
                'location' => 'San Francisco, CA',
                'remote' => false,
                'salary_min' => 140000,
                'salary_max' => 180000,
                'currency' => 'USD',
                'source' => ApplicationSource::COMPANY_WEBSITE,
                'status' => ApplicationStatus::REJECTED,
                'priority' => 1,
                'applied_at' => Carbon::now()->subDays(25),
            ]);

        JobApplicationStatusHistory::factory()
            ->toRejected()
            ->create([
                'user_id' => $user->id,
                'job_application_id' => $rejectedApp->id,
                'changed_at' => Carbon::now()->subDays(5),
                'notes' => 'Position filled by another candidate',
            ]);

        // Archived application
        JobApplication::factory()
            ->archived()
            ->create([
                'user_id' => $user->id,
                'company_name' => 'Airbnb',
                'job_title' => 'Product Manager',
                'location' => 'San Francisco, CA',
                'remote' => false,
                'salary_min' => 150000,
                'salary_max' => 200000,
                'currency' => 'USD',
                'source' => ApplicationSource::NETWORKING,
                'status' => ApplicationStatus::WITHDRAWN,
                'priority' => 0,
                'applied_at' => Carbon::now()->subDays(60),
            ]);
    }
}
