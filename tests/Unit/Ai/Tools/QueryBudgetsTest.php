<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryBudgets;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryBudgetsTest extends TestCase
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
        $tool = new QueryBudgets($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No budgets found', $result);
    }

    #[Test]
    public function it_filters_by_category(): void
    {
        Budget::factory()->active()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'food',
        ]);

        Budget::factory()->active()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'transport',
        ]);

        $tool = new QueryBudgets($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['category' => 'food']));

        $this->assertStringContainsString('food', $result);
        $this->assertStringNotContainsString('transport', $result);
    }

    #[Test]
    public function it_filters_by_active_status(): void
    {
        Budget::factory()->active()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'Active Budget',
        ]);

        Budget::factory()->inactive()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'Inactive Budget',
        ]);

        $tool = new QueryBudgets($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['is_active' => true]));

        $this->assertStringContainsString('Active Budget', $result);
        $this->assertStringNotContainsString('Inactive Budget', $result);
    }
}
