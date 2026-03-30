<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryInvestments;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryInvestmentsTest extends TestCase
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
        $tool = new QueryInvestments($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No investments found', $result);
    }

    #[Test]
    public function it_queries_by_name(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Apple Inc',
            'symbol_identifier' => 'AAPL',
            'status' => 'active',
        ]);

        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Google LLC',
            'symbol_identifier' => 'GOOGL',
            'status' => 'active',
        ]);

        $tool = new QueryInvestments($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['name' => 'Apple']));

        $this->assertStringContainsString('Apple Inc', $result);
        $this->assertStringNotContainsString('Google', $result);
    }

    #[Test]
    public function it_queries_by_symbol(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Apple Inc',
            'symbol_identifier' => 'AAPL',
        ]);

        $tool = new QueryInvestments($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['name' => 'AAPL']));

        $this->assertStringContainsString('Apple Inc', $result);
    }

    #[Test]
    public function it_filters_by_status(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Active Stock',
            'status' => 'active',
        ]);

        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Sold Stock',
            'status' => 'sold',
        ]);

        $tool = new QueryInvestments($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['status' => 'active']));

        $this->assertStringContainsString('Active Stock', $result);
        $this->assertStringNotContainsString('Sold Stock', $result);
    }
}
