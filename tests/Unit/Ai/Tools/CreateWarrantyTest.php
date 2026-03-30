<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\CreateWarranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateWarrantyTest extends TestCase
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
    public function it_creates_a_warranty(): void
    {
        $tool = new CreateWarranty($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'product_name' => 'MacBook Pro',
            'brand' => 'Apple',
            'model' => 'M3 Max',
            'purchase_date' => '2026-01-15',
            'purchase_price' => 3499,
            'warranty_expiration_date' => '2028-01-15',
            'warranty_type' => 'manufacturer',
        ]));

        $this->assertStringContainsString('Created warranty', $result);
        $this->assertStringContainsString('MacBook Pro', $result);
        $this->assertDatabaseHas('warranties', [
            'tenant_id' => $this->tenantId,
            'product_name' => 'MacBook Pro',
            'current_status' => 'active',
        ]);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $tool = new CreateWarranty($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('Validation failed', $result);
    }

    #[Test]
    public function it_validates_expiration_after_purchase(): void
    {
        $tool = new CreateWarranty($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'product_name' => 'Test',
            'purchase_date' => '2026-06-01',
            'warranty_expiration_date' => '2026-01-01',
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
