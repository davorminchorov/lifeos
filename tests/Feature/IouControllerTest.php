<?php

namespace Tests\Feature;

use App\Models\Iou;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IouControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_displays_ious_for_authenticated_user(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'person_name' => 'Current User Person Name',
        ]);
        $otherUserIou = Iou::factory()->create([
            'person_name' => 'Other User Person Name - Should Not Be Visible',
        ]);

        $response = $this->actingAs($this->user)->get(route('ious.index'));

        $response->assertStatus(200);
        $response->assertViewHas('ious');
        $response->assertSee($iou->person_name);
        $response->assertDontSee($otherUserIou->person_name);
    }

    public function test_index_filters_by_type(): void
    {
        $oweIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'owe',
            'person_name' => 'Person I Owe',
        ]);

        $owedIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'owed',
            'person_name' => 'Person Who Owes Me',
        ]);

        $response = $this->actingAs($this->user)->get(route('ious.index', ['type' => 'owe']));

        $response->assertStatus(200);
        $response->assertSee('Person I Owe');
        $response->assertDontSee('Person Who Owes Me');
    }

    public function test_index_filters_by_status(): void
    {
        $pendingIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'person_name' => 'Pending Person',
        ]);

        $paidIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'person_name' => 'Paid Person',
        ]);

        $response = $this->actingAs($this->user)->get(route('ious.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Pending Person');
        $response->assertDontSee('Paid Person');
    }

    public function test_index_filters_by_person(): void
    {
        $johnIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'person_name' => 'John Doe',
        ]);

        $janeIou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'person_name' => 'Jane Smith',
        ]);

        $response = $this->actingAs($this->user)->get(route('ious.index', ['person' => 'John Doe']));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('ious.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('ious.create'));

        $response->assertStatus(200);
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->get(route('ious.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_new_iou(): void
    {
        $data = [
            'type' => 'owe',
            'person_name' => 'John Doe',
            'amount' => 500.00,
            'currency' => 'USD',
            'transaction_date' => '2024-01-15',
            'due_date' => '2024-12-31',
            'description' => 'Loan for car',
            'status' => 'pending',
            'category' => 'personal',
        ];

        $response = $this->actingAs($this->user)->post(route('ious.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ious', [
            'user_id' => $this->user->id,
            'person_name' => 'John Doe',
            'amount' => 500.00,
        ]);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->post(route('ious.store'), []);

        $response->assertRedirect(route('login'));
    }

    public function test_show_displays_iou_details(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'person_name' => 'John Doe',
        ]);

        $response = $this->actingAs($this->user)->get(route('ious.show', $iou));

        $response->assertStatus(200);
        $response->assertViewHas('iou');
        $response->assertSee('John Doe');
    }

    public function test_show_cannot_view_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)->get(route('ious.show', $otherUserIou));

        $response->assertStatus(403);
    }

    public function test_show_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->get(route('ious.show', $iou));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_displays_form_with_iou_data(): void
    {
        $iou = Iou::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('ious.edit', $iou));

        $response->assertStatus(200);
        $response->assertViewHas('iou');
    }

    public function test_edit_cannot_edit_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)->get(route('ious.edit', $otherUserIou));

        $response->assertStatus(403);
    }

    public function test_edit_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->get(route('ious.edit', $iou));

        $response->assertRedirect(route('login'));
    }

    public function test_update_modifies_iou(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'person_name' => 'John Doe',
            'amount' => 500.00,
        ]);

        $data = [
            'type' => 'owe',
            'person_name' => 'John Doe Updated',
            'amount' => 600.00,
            'currency' => 'USD',
            'transaction_date' => '2024-01-15',
            'due_date' => '2024-12-31',
            'description' => 'Updated loan description',
            'status' => 'pending',
            'category' => 'personal',
        ];

        $response = $this->actingAs($this->user)->put(route('ious.update', $iou), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ious', [
            'id' => $iou->id,
            'person_name' => 'John Doe Updated',
            'amount' => 600.00,
        ]);
    }

    public function test_update_cannot_modify_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)->put(route('ious.update', $otherUserIou), []);

        $response->assertStatus(403);
    }

    public function test_update_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->put(route('ious.update', $iou), []);

        $response->assertRedirect(route('login'));
    }

    public function test_destroy_deletes_iou(): void
    {
        $iou = Iou::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('ious.destroy', $iou));

        $response->assertRedirect(route('ious.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ious', ['id' => $iou->id]);
    }

    public function test_destroy_cannot_delete_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('ious.destroy', $otherUserIou));

        $response->assertStatus(403);
        $this->assertDatabaseHas('ious', ['id' => $otherUserIou->id]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->delete(route('ious.destroy', $iou));

        $response->assertRedirect(route('login'));
    }

    public function test_record_payment_updates_amount_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 0,
            'status' => 'pending',
        ]);

        $data = [
            'payment_amount' => 400.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('ious.record-payment', $iou), $data);

        $response->assertRedirect();

        $iou->refresh();
        $this->assertEquals(400.00, $iou->amount_paid);
        $this->assertEquals('partially_paid', $iou->status);
    }

    public function test_record_payment_marks_as_paid_when_full_amount_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 0,
            'status' => 'pending',
        ]);

        $data = [
            'payment_amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('ious.record-payment', $iou), $data);

        $response->assertRedirect();

        $iou->refresh();
        $this->assertEquals(1000.00, $iou->amount_paid);
        $this->assertEquals('paid', $iou->status);
    }

    public function test_record_payment_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->post(route('ious.record-payment', $iou), []);

        $response->assertRedirect(route('login'));
    }

    public function test_mark_paid_sets_iou_as_fully_paid(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'amount_paid' => 500.00,
            'status' => 'partially_paid',
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('ious.mark-paid', $iou));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $iou->refresh();
        $this->assertEquals(1000.00, $iou->amount_paid);
        $this->assertEquals('paid', $iou->status);
    }

    public function test_mark_paid_cannot_mark_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)
            ->patch(route('ious.mark-paid', $otherUserIou));

        $response->assertStatus(403);
    }

    public function test_mark_paid_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->patch(route('ious.mark-paid', $iou));

        $response->assertRedirect(route('login'));
    }

    public function test_cancel_sets_iou_status_to_cancelled(): void
    {
        $iou = Iou::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('ious.cancel', $iou));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $iou->refresh();
        $this->assertEquals('cancelled', $iou->status);
    }

    public function test_cancel_cannot_cancel_other_users_iou(): void
    {
        $otherUserIou = Iou::factory()->create();

        $response = $this->actingAs($this->user)
            ->patch(route('ious.cancel', $otherUserIou));

        $response->assertStatus(403);
    }

    public function test_cancel_requires_authentication(): void
    {
        $iou = Iou::factory()->create();

        $response = $this->patch(route('ious.cancel', $iou));

        $response->assertRedirect(route('login'));
    }
}
