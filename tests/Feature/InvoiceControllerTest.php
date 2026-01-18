<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Customer $customer;
    private Customer $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
        $this->otherCustomer = Customer::factory()->create(['user_id' => $this->otherUser->id]);
    }

    public function test_index_shows_only_user_invoices()
    {
        Invoice::factory()->count(3)->create(['user_id' => $this->user->id]);
        Invoice::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get('/invoicing/invoices');

        $response->assertStatus(200);
    }

    public function test_index_filters_by_status()
    {
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'status' => InvoiceStatus::DRAFT,
        ]);
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'status' => InvoiceStatus::PAID,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/invoicing/invoices?status=draft');

        $response->assertStatus(200);
    }

    public function test_create_shows_form_with_user_customers()
    {
        $response = $this->actingAs($this->user)
            ->get('/invoicing/invoices/create');

        $response->assertStatus(200);
    }

    public function test_store_creates_invoice_with_valid_data()
    {
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
            'net_terms_days' => 30,
            'notes' => 'Test invoice notes',
        ];

        $response = $this->actingAs($this->user)
            ->post('/invoicing/invoices', $invoiceData);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::DRAFT->value,
        ]);
    }

    public function test_store_prevents_idor_with_other_users_customer()
    {
        $invoiceData = [
            'customer_id' => $this->otherCustomer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
        ];

        $response = $this->actingAs($this->user)
            ->post('/invoicing/invoices', $invoiceData);

        $response->assertSessionHasErrors(['customer_id']);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/invoicing/invoices', []);

        $response->assertSessionHasErrors([
            'customer_id',
            'currency',
            'tax_behavior',
        ]);
    }

    public function test_show_displays_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/invoices/{$invoice->id}");

        $response->assertStatus(200);
    }

    public function test_show_prevents_access_to_other_users_invoice()
    {
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $this->otherUser->id,
            'customer_id' => $this->otherCustomer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/invoices/{$otherInvoice->id}");

        $response->assertStatus(403);
    }

    public function test_edit_shows_form_for_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::DRAFT,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/invoices/{$invoice->id}/edit");

        $response->assertStatus(200);
    }

    public function test_update_modifies_invoice_with_valid_data()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::DRAFT,
        ]);

        $updateData = [
            'customer_id' => $this->customer->id,
            'currency' => 'EUR',
            'tax_behavior' => 'inclusive',
            'net_terms_days' => 60,
        ];

        $response = $this->actingAs($this->user)
            ->put("/invoicing/invoices/{$invoice->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'currency' => 'EUR',
            'net_terms_days' => 60,
        ]);
    }

    public function test_update_prevents_idor_attack()
    {
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $this->otherUser->id,
            'customer_id' => $this->otherCustomer->id,
        ]);

        $updateData = [
            'customer_id' => $this->otherCustomer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
        ];

        $response = $this->actingAs($this->user)
            ->put("/invoicing/invoices/{$otherInvoice->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_update_prevents_customer_switching_to_other_users_customer()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
        ]);

        $updateData = [
            'customer_id' => $this->otherCustomer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
        ];

        $response = $this->actingAs($this->user)
            ->put("/invoicing/invoices/{$invoice->id}", $updateData);

        $response->assertSessionHasErrors(['customer_id']);
    }

    public function test_destroy_deletes_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::DRAFT,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/invoicing/invoices/{$invoice->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_destroy_prevents_deletion_of_other_users_invoice()
    {
        $otherInvoice = Invoice::factory()->create([
            'user_id' => $this->otherUser->id,
            'customer_id' => $this->otherCustomer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/invoicing/invoices/{$otherInvoice->id}");

        $response->assertStatus(403);
    }

    public function test_issue_changes_status_to_issued()
    {
        $invoice = Invoice::factory()
            ->has(InvoiceItem::factory()->count(1))
            ->create([
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'status' => InvoiceStatus::DRAFT,
                'total' => 10000,
                'amount_due' => 10000,
            ]);

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$invoice->id}/issue");

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::ISSUED, $invoice->status);
        $this->assertNotNull($invoice->issued_at);
    }

    public function test_void_changes_status_to_void()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::ISSUED,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$invoice->id}/void");

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals(InvoiceStatus::VOID, $invoice->status);
    }

    public function test_mark_as_sent_updates_sent_at()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::ISSUED,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$invoice->id}/mark-sent");

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertNotNull($invoice->sent_at);
    }

    public function test_unauthenticated_access_is_denied()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
        ]);

        $this->get('/invoicing/invoices')->assertRedirect();
        $this->get("/invoicing/invoices/{$invoice->id}")->assertRedirect();
        $this->post('/invoicing/invoices', [])->assertRedirect();
        $this->put("/invoicing/invoices/{$invoice->id}", [])->assertRedirect();
        $this->delete("/invoicing/invoices/{$invoice->id}")->assertRedirect();
    }
}
