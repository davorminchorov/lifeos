<?php

namespace Tests\Feature\Commands;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\JobApplicationOffer;
use App\Notifications\InterviewReminderNotification;
use App\Notifications\NextActionReminderNotification;
use App\Notifications\OfferDeadlineNotification;
use App\Notifications\StaleApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckJobApplicationRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
    }

    #[Test]
    public function it_sends_interview_reminder_for_upcoming_interviews(): void
    {
        $application = JobApplication::factory()->interview()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'scheduled_at' => now()->addHours(12),
            'completed' => false,
        ]);

        // Log out to simulate scheduler context (no auth)
        auth()->logout();

        $this->artisan('job-applications:check-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, InterviewReminderNotification::class);
    }

    #[Test]
    public function it_sends_offer_deadline_notification(): void
    {
        $application = JobApplication::factory()->offer()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        JobApplicationOffer::factory()->pending()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'decision_deadline' => now()->addDays(2),
        ]);

        auth()->logout();

        $this->artisan('job-applications:check-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, OfferDeadlineNotification::class);
    }

    #[Test]
    public function it_sends_overdue_action_reminder(): void
    {
        JobApplication::factory()->applied()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'next_action_at' => now()->subDay(),
            'archived_at' => null,
        ]);

        auth()->logout();

        $this->artisan('job-applications:check-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, NextActionReminderNotification::class);
    }

    #[Test]
    public function it_sends_stale_application_notification(): void
    {
        $application = JobApplication::factory()->applied()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'created_at' => now()->subDays(20),
            'archived_at' => null,
        ]);

        auth()->logout();

        $this->artisan('job-applications:check-reminders')
            ->assertSuccessful();

        Notification::assertSentTo($this->user, StaleApplicationNotification::class);
    }

    #[Test]
    public function it_does_not_send_reminders_for_archived_applications(): void
    {
        JobApplication::factory()->applied()->archived()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'next_action_at' => now()->subDay(),
        ]);

        auth()->logout();

        $this->artisan('job-applications:check-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
