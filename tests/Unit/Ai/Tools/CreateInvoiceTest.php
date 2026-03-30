<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\CreateInvoice;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;

    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
    }

    #[Test]
    public function it_creates_draft_invoice_with_items(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenantId,
            'name' => 'Acme Corp',
        ]);

        $tool = new CreateInvoice($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'customer_name' => 'Acme',
            'items' => [
                ['name' => 'Web Development', 'quantity' => 10, 'unit_amount' => 100],
                ['name' => 'Design Work', 'quantity' => 5, 'unit_amount' => 80],
            ],
        ]));

        $this->assertStringContainsString('Created draft invoice', $result);
        $this->assertStringContainsString('Acme Corp', $result);
        $this->assertStringContainsString('2 items', $result);
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $this->tenantId,
            'customer_id' => Customer::where('name', 'Acme Corp')->first()->id,
        ]);
        $this->assertDatabaseCount('invoice_items', 2);
    }

    #[Test]
    public function it_returns_error_for_unknown_customer(): void
    {
        $tool = new CreateInvoice($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'customer_name' => 'Nonexistent',
            'items' => [['name' => 'Test', 'quantity' => 1, 'unit_amount' => 100]],
        ]));

        $this->assertStringContainsString('No customer found', $result);
    }

    #[Test]
    public function it_returns_error_for_empty_items(): void
    {
        Customer::factory()->create([
            'tenant_id' => $this->tenantId,
            'name' => 'Test Customer',
        ]);

        $tool = new CreateInvoice($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'customer_name' => 'Test',
            'items' => [],
        ]));

        $this->assertStringContainsString('At least one line item', $result);
    }
}
