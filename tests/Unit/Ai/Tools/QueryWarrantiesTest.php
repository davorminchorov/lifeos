<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryWarranties;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryWarrantiesTest extends TestCase
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
        $tool = new QueryWarranties($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No warranties found', $result);
    }

    #[Test]
    public function it_filters_by_product_name(): void
    {
        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'MacBook Pro',
            'current_status' => 'active',
        ]);

        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'iPhone 15',
            'current_status' => 'active',
        ]);

        $tool = new QueryWarranties($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['product_name' => 'MacBook']));

        $this->assertStringContainsString('MacBook Pro', $result);
        $this->assertStringNotContainsString('iPhone', $result);
    }

    #[Test]
    public function it_filters_by_status(): void
    {
        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'Active Warranty',
            'current_status' => 'active',
        ]);

        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'Expired Warranty',
            'current_status' => 'expired',
        ]);

        $tool = new QueryWarranties($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['current_status' => 'active']));

        $this->assertStringContainsString('Active Warranty', $result);
        $this->assertStringNotContainsString('Expired Warranty', $result);
    }
}
