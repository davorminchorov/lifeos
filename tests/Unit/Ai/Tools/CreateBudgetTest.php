<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\CreateBudget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateBudgetTest extends TestCase
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
    public function it_creates_a_monthly_budget(): void
    {
        $tool = new CreateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'category' => 'food',
            'amount' => 5000,
            'budget_period' => 'monthly',
        ]));

        $this->assertStringContainsString('Created monthly budget', $result);
        $this->assertStringContainsString('food', $result);
        $this->assertDatabaseHas('budgets', [
            'tenant_id' => $this->tenantId,
            'category' => 'food',
            'budget_period' => 'monthly',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $tool = new CreateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('Validation failed', $result);
    }

    #[Test]
    public function it_validates_budget_period(): void
    {
        $tool = new CreateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'category' => 'test',
            'amount' => 100,
            'budget_period' => 'biweekly',
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
