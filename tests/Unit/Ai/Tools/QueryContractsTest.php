<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryContracts;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryContractsTest extends TestCase
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
        $tool = new QueryContracts($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No contracts found', $result);
    }

    #[Test]
    public function it_filters_by_title(): void
    {
        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Office Lease',
            'status' => 'active',
        ]);

        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Insurance Policy',
            'status' => 'active',
        ]);

        $tool = new QueryContracts($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['title' => 'Office']));

        $this->assertStringContainsString('Office Lease', $result);
        $this->assertStringNotContainsString('Insurance', $result);
    }

    #[Test]
    public function it_filters_by_status(): void
    {
        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Active Contract',
            'status' => 'active',
        ]);

        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Terminated Contract',
            'status' => 'terminated',
        ]);

        $tool = new QueryContracts($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['status' => 'active']));

        $this->assertStringContainsString('Active Contract', $result);
        $this->assertStringNotContainsString('Terminated Contract', $result);
    }
}
