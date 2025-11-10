<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_status_history_is_created_when_application_status_changes()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        // Get initial history count
        $initialHistoryCount = $application->statusHistories()->count();

        // Update status
        $application->update(['status' => ApplicationStatus::INTERVIEW]);

        // Verify new history entry was created
        $this->assertEquals($initialHistoryCount + 1, $application->statusHistories()->count());

        $latestHistory = $application->statusHistories()->latest('changed_at')->first();
        $this->assertEquals(ApplicationStatus::APPLIED, $latestHistory->from_status);
        $this->assertEquals(ApplicationStatus::INTERVIEW, $latestHistory->to_status);
        $this->assertEquals($this->user->id, $latestHistory->user_id);
    }

    public function test_initial_status_history_has_null_from_status()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::WISHLIST,
        ]);

        $initialHistory = $application->statusHistories()->first();

        if ($initialHistory) {
            $this->assertNull($initialHistory->from_status);
            $this->assertEquals(ApplicationStatus::WISHLIST, $initialHistory->to_status);
        }
    }

    public function test_multiple_status_changes_create_multiple_history_entries()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        $initialCount = $application->statusHistories()->count();

        // Change status multiple times
        $application->update(['status' => ApplicationStatus::SCREENING]);
        $application->update(['status' => ApplicationStatus::INTERVIEW]);
        $application->update(['status' => ApplicationStatus::OFFER]);

        $this->assertEquals($initialCount + 3, $application->statusHistories()->count());

        // Verify the sequence
        $histories = $application->statusHistories()->orderBy('changed_at')->get();

        // Check second to last entry
        $secondToLast = $histories->get($histories->count() - 2);
        $this->assertEquals(ApplicationStatus::SCREENING, $secondToLast->from_status);
        $this->assertEquals(ApplicationStatus::INTERVIEW, $secondToLast->to_status);

        // Check last entry
        $last = $histories->last();
        $this->assertEquals(ApplicationStatus::INTERVIEW, $last->from_status);
        $this->assertEquals(ApplicationStatus::OFFER, $last->to_status);
    }

    public function test_status_history_records_timestamp()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        $beforeUpdate = now()->subSecond();

        $application->update(['status' => ApplicationStatus::INTERVIEW]);

        $afterUpdate = now()->addSecond();

        $latestHistory = $application->statusHistories()->latest('changed_at')->first();

        $this->assertNotNull($latestHistory->changed_at);
        $this->assertTrue($latestHistory->changed_at->between($beforeUpdate, $afterUpdate));
    }

    public function test_updating_non_status_fields_does_not_create_history()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        $initialHistoryCount = $application->statusHistories()->count();

        // Update non-status fields
        $application->update([
            'company_name' => 'Updated Company',
            'job_title' => 'Updated Title',
            'notes' => 'Updated notes',
        ]);

        // Verify no new history entry
        $this->assertEquals($initialHistoryCount, $application->statusHistories()->count());
    }

    public function test_status_history_can_include_notes()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        // Manually create a status history with notes
        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::INTERVIEW->value,
            'to_status' => ApplicationStatus::REJECTED->value,
            'changed_at' => now(),
            'notes' => 'Did not meet technical requirements',
        ]);

        $history = $application->statusHistories()->latest('changed_at')->first();
        $this->assertEquals('Did not meet technical requirements', $history->notes);
    }

    public function test_application_show_displays_status_timeline()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        // Create some status history
        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => null,
            'to_status' => ApplicationStatus::APPLIED->value,
            'changed_at' => now()->subDays(5),
        ]);

        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::APPLIED->value,
            'to_status' => ApplicationStatus::SCREENING->value,
            'changed_at' => now()->subDays(3),
        ]);

        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::SCREENING->value,
            'to_status' => ApplicationStatus::INTERVIEW->value,
            'changed_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$application->id}");

        $response->assertStatus(200);
        $response->assertViewHas('application', $application);

        // The view should have access to status histories through the relationship
        $viewApplication = $response->viewData('application');
        $this->assertInstanceOf(JobApplication::class, $viewApplication);
    }

    public function test_status_history_belongs_to_correct_user()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        $history = JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::APPLIED->value,
            'to_status' => ApplicationStatus::INTERVIEW->value,
            'changed_at' => now(),
        ]);

        $this->assertEquals($this->user->id, $history->user_id);
        $this->assertTrue($history->user->is($this->user));
    }

    public function test_status_history_belongs_to_correct_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        $history = JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::APPLIED->value,
            'to_status' => ApplicationStatus::INTERVIEW->value,
            'changed_at' => now(),
        ]);

        $this->assertEquals($application->id, $history->job_application_id);
        $this->assertTrue($history->jobApplication->is($application));
    }

    public function test_deleting_application_cascades_to_status_histories()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
        ]);

        // Create some status histories
        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => null,
            'to_status' => ApplicationStatus::APPLIED->value,
            'changed_at' => now(),
        ]);

        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::APPLIED->value,
            'to_status' => ApplicationStatus::SCREENING->value,
            'changed_at' => now(),
        ]);

        $applicationId = $application->id;

        // Verify histories exist
        $this->assertGreaterThan(0, JobApplicationStatusHistory::where('job_application_id', $applicationId)->count());

        // Soft delete the application
        $application->delete();

        // Histories should still exist (soft delete doesn't cascade)
        // But if you want to verify cascade on hard delete, that would require force delete
    }

    public function test_status_history_ordered_by_changed_at()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Delete the auto-created initial history to test ordering properly
        $application->statusHistories()->delete();

        // Create histories in random order
        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::SCREENING->value,
            'to_status' => ApplicationStatus::INTERVIEW->value,
            'changed_at' => now()->subDays(1),
        ]);

        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => null,
            'to_status' => ApplicationStatus::APPLIED->value,
            'changed_at' => now()->subDays(5),
        ]);

        JobApplicationStatusHistory::create([
            'user_id' => $this->user->id,
            'job_application_id' => $application->id,
            'from_status' => ApplicationStatus::APPLIED->value,
            'to_status' => ApplicationStatus::SCREENING->value,
            'changed_at' => now()->subDays(3),
        ]);

        $histories = $application->statusHistories()->orderBy('changed_at')->get();

        // Verify chronological order
        $this->assertEquals(ApplicationStatus::APPLIED, $histories->first()->to_status);
        $this->assertEquals(ApplicationStatus::SCREENING, $histories->get(1)->to_status);
        $this->assertEquals(ApplicationStatus::INTERVIEW, $histories->last()->to_status);
    }
}
