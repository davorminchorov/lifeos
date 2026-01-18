<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Customer $customer;
    private Invoice $invoice;
    private Invoice $otherInvoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);

        $this->invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'status' => InvoiceStatus::ISSUED,
            'total' => 100000, // $1000
            'amount_due' => 100000,
        ]);

        $this->otherInvoice = Invoice::factory()->create([
            'user_id' => $this->otherUser->id,
            'status' => InvoiceStatus::ISSUED,
        ]);
    }

    public function test_store_creates_payment_with_valid_data()
    {
        $paymentData = [
            'amount' => 50000, // $500
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'REF123',
            'notes' => 'Test payment',
        ];

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", $paymentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'reference' => 'REF123',
        ]);
    }

    public function test_store_updates_invoice_status_to_partially_paid()
    {
        $paymentData = [
            'amount' => 50000, // $500
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", $paymentData);

        $this->invoice->refresh();
        $this->assertEquals(InvoiceStatus::PARTIALLY_PAID, $this->invoice->status);
        $this->assertEquals(50000, $this->invoice->amount_paid);
        $this->assertEquals(50000, $this->invoice->amount_due);
    }

    public function test_store_updates_invoice_status_to_paid_when_full_amount()
    {
        $paymentData = [
            'amount' => 100000, // Full amount
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", $paymentData);

        $this->invoice->refresh();
        $this->assertEquals(InvoiceStatus::PAID, $this->invoice->status);
        $this->assertEquals(100000, $this->invoice->amount_paid);
        $this->assertEquals(0, $this->invoice->amount_due);
        $this->assertNotNull($this->invoice->paid_at);
    }

    public function test_store_prevents_payment_on_other_users_invoice()
    {
        $paymentData = [
            'amount' => 50000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->otherInvoice->id}/payments", $paymentData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", []);

        $response->assertSessionHasErrors([
            'amount',
            'payment_date',
            'payment_method',
        ]);
    }

    public function test_store_validates_amount_not_exceeding_amount_due()
    {
        $paymentData = [
            'amount' => 200000, // More than amount due
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", $paymentData);

        $response->assertSessionHasErrors();
    }

    public function test_store_validates_positive_amount()
    {
        $paymentData = [
            'amount' => -1000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ];

        $response = $this->actingAs($this->user)
            ->post("/invoicing/invoices/{$this->invoice->id}/payments", $paymentData);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_show_displays_payment()
    {
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
            'amount' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/invoices/{$this->invoice->id}/payments/{$payment->id}");

        $response->assertStatus(200);
    }

    public function test_show_prevents_access_to_other_users_payment()
    {
        $otherPayment = Payment::factory()->create([
            'invoice_id' => $this->otherInvoice->id,
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/invoices/{$this->otherInvoice->id}/payments/{$otherPayment->id}");

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_payment_and_updates_invoice()
    {
        // Create a payment first
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
            'amount' => 50000,
        ]);

        // Update invoice to reflect payment
        $this->invoice->update([
            'amount_paid' => 50000,
            'amount_due' => 50000,
            'status' => InvoiceStatus::PARTIALLY_PAID,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/invoicing/invoices/{$this->invoice->id}/payments/{$payment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);

        // Verify invoice was updated
        $this->invoice->refresh();
        $this->assertEquals(0, $this->invoice->amount_paid);
        $this->assertEquals(100000, $this->invoice->amount_due);
    }

    public function test_destroy_prevents_deletion_of_other_users_payment()
    {
        $otherPayment = Payment::factory()->create([
            'invoice_id' => $this->otherInvoice->id,
            'user_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/invoicing/invoices/{$this->otherInvoice->id}/payments/{$otherPayment->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_access_is_denied()
    {
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->user->id,
        ]);

        $this->post("/invoicing/invoices/{$this->invoice->id}/payments", [])->assertRedirect();
        $this->get("/invoicing/invoices/{$this->invoice->id}/payments/{$payment->id}")->assertRedirect();
        $this->delete("/invoicing/invoices/{$this->invoice->id}/payments/{$payment->id}")->assertRedirect();
    }
}
