<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryInvoices;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryInvoicesTest extends TestCase
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
    public function it_returns_no_results_when_empty(): void
    {
        $tool = new QueryInvoices($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No invoices found', $result);
    }

    #[Test]
    public function it_filters_by_status(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenantId]);

        Invoice::factory()->issued()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::ISSUED,
        ]);

        Invoice::factory()->paid()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer->id,
            'status' => InvoiceStatus::PAID,
        ]);

        $tool = new QueryInvoices($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['status' => 'issued']));

        $this->assertStringContainsString('issued', $result);
        $this->assertStringContainsString('Found 1', $result);
    }

    #[Test]
    public function it_filters_by_customer_name(): void
    {
        $customer1 = Customer::factory()->create([
            'tenant_id' => $this->tenantId,
            'name' => 'Acme Corp',
        ]);

        $customer2 = Customer::factory()->create([
            'tenant_id' => $this->tenantId,
            'name' => 'Beta Inc',
        ]);

        Invoice::factory()->issued()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer1->id,
        ]);

        Invoice::factory()->issued()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'customer_id' => $customer2->id,
        ]);

        $tool = new QueryInvoices($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['customer' => 'Acme']));

        $this->assertStringContainsString('Acme Corp', $result);
        $this->assertStringNotContainsString('Beta Inc', $result);
    }
}
