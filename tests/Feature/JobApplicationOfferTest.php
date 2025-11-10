<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Models\JobApplication;
use App\Models\JobApplicationOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationOfferTest extends TestCase
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

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/offers/create");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.offers.create');
        $response->assertViewHas('application', $this->application);
    }

    public function test_create_redirects_if_offer_already_exists()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/offers/create");

        $response->assertRedirect("/job-applications/{$this->application->id}/offers/{$offer->id}/edit");
        $response->assertSessionHas('info');
    }

    public function test_create_requires_authentication()
    {
        $response = $this->get("/job-applications/{$this->application->id}/offers/create");

        $response->assertRedirect('/login');
    }

    public function test_create_prevents_accessing_other_users_applications()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/offers/create");

        $response->assertStatus(403);
    }

    public function test_store_creates_offer()
    {
        $data = [
            'base_salary' => 120000,
            'bonus' => 15000,
            'equity' => '1000 RSUs vesting over 4 years',
            'currency' => 'USD',
            'benefits' => 'Health, dental, 401k matching',
            'start_date' => now()->addMonth()->toDateString(),
            'decision_deadline' => now()->addWeeks(2)->toDateString(),
            'status' => OfferStatus::PENDING->value,
            'notes' => 'Strong offer, considering negotiation',
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/offers", $data);

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_application_offers', [
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'base_salary' => 120000,
            'bonus' => 15000,
            'status' => OfferStatus::PENDING->value,
        ]);
    }

    public function test_store_prevents_duplicate_offers()
    {
        JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $data = [
            'base_salary' => 100000,
            'currency' => 'USD',
            'status' => OfferStatus::PENDING->value,
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/offers", $data);

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('error');

        $this->assertEquals(1, JobApplicationOffer::where('job_application_id', $this->application->id)->count());
    }

    public function test_store_requires_authentication()
    {
        $data = [
            'base_salary' => 100000,
            'currency' => 'USD',
            'status' => OfferStatus::PENDING->value,
        ];

        $response = $this->post("/job-applications/{$this->application->id}/offers", $data);

        $response->assertRedirect('/login');
    }

    public function test_store_prevents_creating_for_other_users_applications()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $data = [
            'base_salary' => 100000,
            'currency' => 'USD',
            'status' => OfferStatus::PENDING->value,
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$otherApplication->id}/offers", $data);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/offers", []);

        $response->assertSessionHasErrors(['base_salary', 'status']);
    }

    public function test_store_validates_status_enum()
    {
        $data = [
            'base_salary' => 100000,
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->post("/job-applications/{$this->application->id}/offers", $data);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_show_displays_offer()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'base_salary' => 130000,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/offers/{$offer->id}");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.offers.show');
        $response->assertViewHas('offer', $offer);
        $response->assertSee('130,000.00');
    }

    public function test_show_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->get("/job-applications/{$this->application->id}/offers/{$offer->id}");

        $response->assertRedirect('/login');
    }

    public function test_show_prevents_viewing_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}");

        $response->assertStatus(403);
    }

    public function test_show_prevents_viewing_offer_from_wrong_application()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/offers/{$offer->id}");

        $response->assertStatus(403);
    }

    public function test_edit_displays_form()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$this->application->id}/offers/{$offer->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('job-applications.offers.edit');
        $response->assertViewHas('offer', $offer);
    }

    public function test_edit_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->get("/job-applications/{$this->application->id}/offers/{$offer->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_edit_prevents_editing_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->get("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}/edit");

        $response->assertStatus(403);
    }

    public function test_update_modifies_offer()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'base_salary' => 100000,
            'bonus' => 10000,
        ]);

        $data = [
            'base_salary' => 120000,
            'bonus' => 15000,
            'equity' => 'Updated equity package',
            'status' => OfferStatus::NEGOTIATING->value,
        ];

        $response = $this->actingAs($this->user)->patch("/job-applications/{$this->application->id}/offers/{$offer->id}", $data);

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $offer->refresh();
        $this->assertEquals(120000, $offer->base_salary);
        $this->assertEquals(15000, $offer->bonus);
    }

    public function test_update_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->patch("/job-applications/{$this->application->id}/offers/{$offer->id}", [
            'base_salary' => 150000,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_update_prevents_updating_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}", [
            'base_salary' => 999999,
        ]);

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_offer()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$this->application->id}/offers/{$offer->id}");

        $response->assertRedirect("/job-applications/{$this->application->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('job_application_offers', [
            'id' => $offer->id,
        ]);
    }

    public function test_destroy_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
        ]);

        $response = $this->delete("/job-applications/{$this->application->id}/offers/{$offer->id}");

        $response->assertRedirect('/login');
    }

    public function test_destroy_prevents_deleting_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('job_application_offers', [
            'id' => $otherOffer->id,
        ]);
    }

    public function test_accept_marks_offer_as_accepted_and_updates_application_status()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$this->application->id}/offers/{$offer->id}/accept");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $offer->refresh();
        $this->assertEquals(OfferStatus::ACCEPTED, $offer->status);

        $this->application->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $this->application->status);
    }

    public function test_accept_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->patch("/job-applications/{$this->application->id}/offers/{$offer->id}/accept");

        $response->assertRedirect('/login');
    }

    public function test_accept_prevents_accepting_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}/accept");

        $response->assertStatus(403);

        $otherOffer->refresh();
        $this->assertEquals(OfferStatus::PENDING, $otherOffer->status);
    }

    public function test_decline_marks_offer_as_declined()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$this->application->id}/offers/{$offer->id}/decline");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $offer->refresh();
        $this->assertEquals(OfferStatus::DECLINED, $offer->status);
    }

    public function test_decline_requires_authentication()
    {
        $offer = JobApplicationOffer::factory()->create([
            'user_id' => $this->user->id,
            'job_application_id' => $this->application->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->patch("/job-applications/{$this->application->id}/offers/{$offer->id}/decline");

        $response->assertRedirect('/login');
    }

    public function test_decline_prevents_declining_other_users_offers()
    {
        $otherApplication = JobApplication::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $otherOffer = JobApplicationOffer::factory()->create([
            'user_id' => $this->otherUser->id,
            'job_application_id' => $otherApplication->id,
            'status' => OfferStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->patch("/job-applications/{$otherApplication->id}/offers/{$otherOffer->id}/decline");

        $response->assertStatus(403);

        $otherOffer->refresh();
        $this->assertEquals(OfferStatus::PENDING, $otherOffer->status);
    }
}
