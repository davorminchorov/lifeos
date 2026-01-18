<?php

namespace Tests\Feature;

use App\Enums\ApplicationSource;
use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_index_displays_user_job_applications()
    {
        $applications = collect([
            JobApplication::factory()->create([
                'user_id' => $this->user->id,
                'company_name' => 'User Company Alpha',
            ]),
            JobApplication::factory()->create([
                'user_id' => $this->user->id,
                'company_name' => 'User Company Beta',
            ]),
            JobApplication::factory()->create([
                'user_id' => $this->user->id,
                'company_name' => 'User Company Gamma',
            ]),
        ]);

        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
            'company_name' => 'Other User Company - Should Not Be Visible',
        ]);

        $response = $this->actingAs($this->user)->get('/job-applications');

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.index');

        foreach ($applications as $application) {
            $response->assertSee($application->company_name);
            $response->assertSee($application->job_title);
        }

        $response->assertDontSee($otherApplication->company_name);
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get('/job-applications');

        $response->assertRedirect('/login');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get('/job-applications/create');

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.create');
    }

    public function test_create_requires_authentication()
    {
        $response = $this->get('/job-applications/create');

        $response->assertRedirect('/login');
    }

    public function test_store_creates_job_application()
    {
        $data = [
            'company_name' => 'Acme Corp',
            'job_title' => 'Senior Developer',
            'company_website' => 'https://acme.com',
            'job_url' => 'https://acme.com/jobs/123',
            'location' => 'New York, NY',
            'remote' => true,
            'salary_min' => 100000,
            'salary_max' => 150000,
            'currency' => 'USD',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => ApplicationSource::LINKEDIN->value,
            'applied_at' => now()->toDateString(),
            'priority' => 2,
            'contact_name' => 'John Recruiter',
            'contact_email' => 'john@acme.com',
            'contact_phone' => '555-1234',
            'notes' => 'Great opportunity',
            'tags' => ['remote', 'senior'],
        ];

        $response = $this->actingAs($this->user)->post('/job-applications', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_applications', [
            'user_id' => $this->user->id,
            'company_name' => 'Acme Corp',
            'job_title' => 'Senior Developer',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => ApplicationSource::LINKEDIN->value,
        ]);
    }

    public function test_store_requires_authentication()
    {
        $data = [
            'company_name' => 'Test Company',
            'job_title' => 'Test Job',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => ApplicationSource::LINKEDIN->value,
        ];

        $response = $this->post('/job-applications', $data);

        $response->assertRedirect('/login');
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post('/job-applications', []);

        $response->assertSessionHasErrors(['company_name', 'job_title', 'status', 'source']);
    }

    public function test_store_validates_status_enum()
    {
        $data = [
            'company_name' => 'Test Company',
            'job_title' => 'Test Job',
            'status' => 'invalid_status',
            'source' => ApplicationSource::LINKEDIN->value,
        ];

        $response = $this->actingAs($this->user)->post('/job-applications', $data);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_store_validates_source_enum()
    {
        $data = [
            'company_name' => 'Test Company',
            'job_title' => 'Test Job',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => 'invalid_source',
        ];

        $response = $this->actingAs($this->user)->post('/job-applications', $data);

        $response->assertSessionHasErrors(['source']);
    }

    public function test_show_displays_job_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Test Corp',
            'job_title' => 'Developer',
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$application->id}");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.show');
        $response->assertViewHas('application', $application);
        $response->assertSee('Test Corp');
        $response->assertSee('Developer');
    }

    public function test_show_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/job-applications/{$application->id}");

        $response->assertRedirect('/login');
    }

    public function test_show_prevents_viewing_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$application->id}");

        $response->assertStatus(403);
    }

    public function test_edit_displays_form()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$application->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.edit');
        $response->assertViewHas('application', $application);
    }

    public function test_edit_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/job-applications/{$application->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_edit_prevents_editing_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$application->id}/edit");

        $response->assertStatus(403);
    }

    public function test_update_modifies_job_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Old Company',
            'job_title' => 'Old Title',
        ]);

        $data = [
            'company_name' => 'New Company',
            'job_title' => 'New Title',
            'status' => ApplicationStatus::INTERVIEW->value,
            'source' => ApplicationSource::REFERRAL->value,
        ];

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}", $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertEquals('New Company', $application->company_name);
        $this->assertEquals('New Title', $application->job_title);
        $this->assertEquals(ApplicationStatus::INTERVIEW, $application->status);
    }

    public function test_update_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->patch("/job-applications/{$application->id}", [
            'company_name' => 'Updated',
            'job_title' => 'Updated',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => ApplicationSource::LINKEDIN->value,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_update_prevents_updating_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}", [
            'company_name' => 'Hacked',
            'job_title' => 'Hacked',
            'status' => ApplicationStatus::APPLIED->value,
            'source' => ApplicationSource::LINKEDIN->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_job_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$application->id}");

        $response->assertRedirect('/job-applications');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('job_applications', [
            'id' => $application->id,
        ]);
    }

    public function test_destroy_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete("/job-applications/{$application->id}");

        $response->assertRedirect('/login');
    }

    public function test_destroy_prevents_deleting_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$application->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'deleted_at' => null,
        ]);
    }

    public function test_archive_marks_application_as_archived()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'archived_at' => null,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}/archive");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertNotNull($application->archived_at);
    }

    public function test_archive_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->patch("/job-applications/{$application->id}/archive");

        $response->assertRedirect('/login');
    }

    public function test_archive_prevents_archiving_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}/archive");

        $response->assertStatus(403);
    }

    public function test_unarchive_removes_archived_status()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}/unarchive");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertNull($application->archived_at);
    }

    public function test_unarchive_requires_authentication()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'archived_at' => now(),
        ]);

        $response = $this->patch("/job-applications/{$application->id}/unarchive");

        $response->assertRedirect('/login');
    }

    public function test_unarchive_prevents_unarchiving_other_users_applications()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$application->id}/unarchive");

        $response->assertStatus(403);
    }

    public function test_index_can_filter_by_status()
    {
        $appliedApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::APPLIED,
            'company_name' => 'Applied Company',
        ]);

        $interviewApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'status' => ApplicationStatus::INTERVIEW,
            'company_name' => 'Interview Company',
        ]);

        $response = $this->actingAs($this->user)->get('/job-applications?status=applied');

        $response->assertStatus(200);
        $response->assertSee('Applied Company');
        $response->assertDontSee('Interview Company');
    }

    public function test_index_can_filter_by_source()
    {
        $linkedinApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'source' => ApplicationSource::LINKEDIN,
            'company_name' => 'LinkedIn Company',
        ]);

        $referralApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'source' => ApplicationSource::REFERRAL,
            'company_name' => 'Referral Company',
        ]);

        $response = $this->actingAs($this->user)->get('/job-applications?source=linkedin');

        $response->assertStatus(200);
        $response->assertSee('LinkedIn Company');
        $response->assertDontSee('Referral Company');
    }

    public function test_index_can_search_by_company_name()
    {
        $targetApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Acme Corporation',
        ]);

        $otherApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'company_name' => 'Other Corp',
        ]);

        $response = $this->actingAs($this->user)->get('/job-applications?search=Acme');

        $response->assertStatus(200);
        $response->assertSee('Acme Corporation');
        $response->assertDontSee('Other Corp');
    }

    public function test_index_can_search_by_job_title()
    {
        $targetApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'job_title' => 'Senior PHP Developer',
        ]);

        $otherApp = JobApplication::factory()->create([
            'user_id' => $this->user->id,
            'job_title' => 'Junior Designer',
        ]);

        $response = $this->actingAs($this->user)->get('/job-applications?search=PHP');

        $response->assertStatus(200);
        $response->assertSee('Senior PHP Developer');
        $response->assertDontSee('Junior Designer');
    }
}
