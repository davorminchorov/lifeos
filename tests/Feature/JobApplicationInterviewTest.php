<?php

namespace Tests\Feature;

use App\Enums\InterviewOutcome;
use App\Enums\InterviewType;
use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationInterviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    private JobApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_index_displays_application_interviews()
    {
        $interviews = JobApplicationInterview::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/interviews");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.interviews.index');
        $response->assertViewHas('application', $this->application);

        foreach ($interviews as $interview) {
            $response->assertSee($interview->type->label());
        }
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get("/job-applications/{$this->application->id}/interviews");

        $response->assertRedirect('/login');
    }

    public function test_index_prevents_viewing_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/interviews");

        $response->assertStatus(403);
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/interviews/create");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.interviews.create');
        $response->assertViewHas('application', $this->application);
    }

    public function test_create_requires_authentication()
    {
        $response = $this->get("/job-applications/{$this->application->id}/interviews/create");

        $response->assertRedirect('/login');
    }

    public function test_create_prevents_accessing_other_users_applications()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/interviews/create");

        $response->assertStatus(403);
    }

    public function test_store_creates_interview()
    {
        $data = [
            'type' => InterviewType::VIDEO->value,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'location' => 'Remote',
            'video_link' => 'https://zoom.us/j/123456',
            'interviewer_name' => 'Jane Hiring Manager',
            'notes' => 'Prepare portfolio presentation',
            'outcome' => InterviewOutcome::PENDING->value,
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/interviews", $data);

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_application_interviews', [
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'type' => InterviewType::VIDEO->value,
            'interviewer_name' => 'Jane Hiring Manager',
        ]);
    }

    public function test_store_requires_authentication()
    {
        $data = [
            'type' => InterviewType::PHONE->value,
            'scheduled_at' => now()->addDays(1)->format('Y-m-d H:i:s'),
        ];

        $response = $this->post("/job-applications/{$this->application->id}/interviews", $data);

        $response->assertRedirect('/login');
    }

    public function test_store_prevents_creating_for_other_users_applications()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $data = [
            'type' => InterviewType::PHONE->value,
            'scheduled_at' => now()->addDays(1)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$otherApplication->id}/interviews", $data);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/interviews", []);

        $response->assertSessionHasErrors(['type', 'scheduled_at']);
    }

    public function test_store_validates_type_enum()
    {
        $data = [
            'type' => 'invalid_type',
            'scheduled_at' => now()->addDays(1)->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/interviews", $data);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_show_displays_interview()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'interviewer_name' => 'Test Interviewer',
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/interviews/{$interview->id}");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.interviews.show');
        $response->assertViewHas('interview', $interview);
        $response->assertSee('Test Interviewer');
    }

    public function test_show_requires_authentication()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->get("/job-applications/{$this->application->id}/interviews/{$interview->id}");

        $response->assertRedirect('/login');
    }

    public function test_show_prevents_viewing_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherInterview = JobApplicationInterview::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/interviews/{$otherInterview->id}");

        $response->assertStatus(403);
    }

    public function test_show_prevents_viewing_interview_from_wrong_application()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/interviews/{$interview->id}");

        $response->assertStatus(403);
    }

    public function test_edit_displays_form()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/interviews/{$interview->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.interviews.edit');
        $response->assertViewHas('interview', $interview);
    }

    public function test_edit_requires_authentication()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->get("/job-applications/{$this->application->id}/interviews/{$interview->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_edit_prevents_editing_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherInterview = JobApplicationInterview::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/interviews/{$otherInterview->id}/edit");

        $response->assertStatus(403);
    }

    public function test_update_modifies_interview()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'type' => InterviewType::PHONE,
            'interviewer_name' => 'Old Name',
        ]);

        $data = [
            'type' => InterviewType::VIDEO->value,
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'interviewer_name' => 'New Name',
            'duration_minutes' => 90,
            'location' => 'Updated Location',
        ];

        $response = $this->actingAs($this->user)->patch("/job-applications/{$this->application->id}/interviews/{$interview->id}", $data);

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $interview->refresh();
        $this->assertEquals('New Name', $interview->interviewer_name);
        $this->assertEquals(90, $interview->duration_minutes);
    }

    public function test_update_requires_authentication()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->patch("/job-applications/{$this->application->id}/interviews/{$interview->id}", [
            'interviewer_name' => 'Updated',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_update_prevents_updating_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherInterview = JobApplicationInterview::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$otherApplication->id}/interviews/{$otherInterview->id}", [
            'interviewer_name' => 'Hacked',
        ]);

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_interview()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$this->application->id}/interviews/{$interview->id}");

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('job_application_interviews', [
            'id' => $interview->id,
        ]);
    }

    public function test_destroy_requires_authentication()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->delete("/job-applications/{$this->application->id}/interviews/{$interview->id}");

        $response->assertRedirect('/login');
    }

    public function test_destroy_prevents_deleting_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherInterview = JobApplicationInterview::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$otherApplication->id}/interviews/{$otherInterview->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('job_application_interviews', [
            'id' => $otherInterview->id,
            'deleted_at' => null,
        ]);
    }

    public function test_complete_marks_interview_as_completed()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$this->application->id}/interviews/{$interview->id}/complete");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $interview->refresh();
        $this->assertTrue($interview->completed);
    }

    public function test_complete_requires_authentication()
    {
        $interview = JobApplicationInterview::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'completed' => false,
        ]);

        $response = $this->patch("/job-applications/{$this->application->id}/interviews/{$interview->id}/complete");

        $response->assertRedirect('/login');
    }

    public function test_complete_prevents_completing_other_users_interviews()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherInterview = JobApplicationInterview::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
            'completed' => false,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$otherApplication->id}/interviews/{$otherInterview->id}/complete");

        $response->assertStatus(403);

        $otherInterview->refresh();
        $this->assertFalse($otherInterview->completed);
    }
}
