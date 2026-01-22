<?php

namespace Tests\Unit;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\DiscountService;
use App\Services\InvoicingService;
use App\Services\NumberingService;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicingServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoicingService $service;
    protected $user;
    protected $tenant;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoicingService(
            app(NumberingService::class),
            app(TaxService::class),
            app(DiscountService::class)
        );

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id, 'tenant_id' => $this->tenant->id]);
    }

    public function test_create_draft_creates_invoice_with_zero_totals()
    {
        $data = [
            'customer_id' => $this->customer->id,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
        ];

        $invoice = $this->service->createDraft($this->user, $data);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(InvoiceStatus::DRAFT, $invoice->status);
        $this->assertEquals($this->user->id, $invoice->user_id);
        $this->assertEquals($this->customer->id, $invoice->customer_id);
        $this->assertEquals(0, $invoice->total);
        $this->assertEquals(0, $invoice->amount_due);
    }

    public function test_record_payment_updates_invoice_to_partially_paid()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::ISSUED,
            'total' => 100000,
            'amount_due' => 100000,
            'amount_paid' => 0,
        ]);

        $this->service->recordPayment($invoice, 50000, [
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'TEST123',
        ]);

        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::PARTIALLY_PAID, $invoice->status);
        $this->assertEquals(50000, $invoice->amount_paid);
        $this->assertEquals(50000, $invoice->amount_due);
        $this->assertCount(1, $invoice->payments);
    }

    public function test_record_payment_updates_invoice_to_paid_when_fully_paid()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::ISSUED,
            'total' => 100000,
            'amount_due' => 100000,
            'amount_paid' => 0,
        ]);

        $this->service->recordPayment($invoice, 100000, [
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::PAID, $invoice->status);
        $this->assertEquals(100000, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->amount_due);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_record_payment_creates_payment_record()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::ISSUED,
            'total' => 100000,
            'amount_due' => 100000,
        ]);

        $this->service->recordPayment($invoice, 50000, [
            'payment_date' => '2026-01-15',
            'payment_method' => 'credit_card',
            'reference' => 'REF123',
            'notes' => 'Test payment',
        ]);

        $payment = $invoice->payments()->first();

        $this->assertNotNull($payment);
        $this->assertEquals(50000, $payment->amount);
        $this->assertEquals('credit_card', $payment->payment_method);
        $this->assertEquals('REF123', $payment->reference);
        $this->assertEquals('2026-01-15', $payment->payment_date->toDateString());
        $this->assertEquals($invoice->user_id, $payment->user_id);
    }

    public function test_issue_invoice_sets_status_and_date()
    {
        $invoice = Invoice::factory()
            ->has(InvoiceItem::factory()->count(1), 'items')
            ->create([
                'user_id' => $this->user->id,
                'customer_id' => $this->customer->id,
                'status' => InvoiceStatus::DRAFT,
                'total' => 10000,
                'amount_due' => 10000,
                'tenant_id' => $this->tenant->id,
            ]);

        $this->service->issue($invoice);

        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::ISSUED, $invoice->status);
        $this->assertNotNull($invoice->issued_at);
        $this->assertNotNull($invoice->due_at);
        $this->assertNotNull($invoice->number);
    }

    public function test_void_invoice_sets_status()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::ISSUED,
        ]);

        $this->service->void($invoice);

        $invoice->refresh();

        $this->assertEquals(InvoiceStatus::VOID, $invoice->status);
    }

    public function test_cannot_add_item_to_non_draft_invoice()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::ISSUED,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can only add items to draft invoices');

        $this->service->addItem($invoice, [
            'description' => 'Test item',
            'quantity' => 1,
            'unit_price' => 10000,
        ]);
    }

    public function test_recalculate_totals_with_tax_exclusive()
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'tenant_id' => $this->tenant->id,
            'status' => InvoiceStatus::DRAFT,
            'tax_behavior' => 'exclusive',
        ]);

        // Add an item manually
        $invoice->items()->create([
            'name' => 'Test Item',
            'description' => 'Test Item Description',
            'quantity' => 2,
            'unit_amount' => 10000, // $100 each
            'currency' => 'USD',
            'amount' => 20000, // $200 total
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 20000,
            'sort_order' => 1,
        ]);

        $this->service->recalculateTotals($invoice);

        $invoice->refresh();

        $this->assertEquals(20000, $invoice->subtotal);
        $this->assertGreaterThanOrEqual(0, $invoice->total);
    }

    // TODO: Implement deletePayment method in InvoicingService
    // public function test_delete_payment_recalculates_invoice_amounts()
    // {
    //     $invoice = Invoice::factory()->create([
    //         'user_id' => $this->user->id,
    //         'customer_id' => $this->customer->id,
    //         'status' => InvoiceStatus::PARTIALLY_PAID,
    //         'total' => 100000,
    //         'amount_paid' => 50000,
    //         'amount_due' => 50000,
    //     ]);

    //     $payment = $invoice->payments()->create([
    //         'user_id' => $this->user->id,
    //         'amount' => 50000,
    //         'currency' => $invoice->currency,
    //         'status' => 'succeeded',
    //         'succeeded_at' => now(),
    //         'provider' => 'manual',
    //         'payment_date' => now(),
    //         'payment_method' => 'bank_transfer',
    //     ]);

    //     $this->service->deletePayment($invoice, $payment);

    //     $invoice->refresh();

    //     $this->assertEquals(InvoiceStatus::ISSUED, $invoice->status);
    //     $this->assertEquals(0, $invoice->amount_paid);
    //     $this->assertEquals(100000, $invoice->amount_due);
    //     $this->assertNull($invoice->paid_at);
    // }
}
