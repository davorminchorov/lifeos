<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\UpdateBudget;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateBudgetTest extends TestCase
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
    public function it_updates_budget_amount(): void
    {
        Budget::factory()->active()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'food',
            'amount' => 5000,
        ]);

        $tool = new UpdateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'category' => 'food',
            'new_amount' => 7000,
        ]));

        $this->assertStringContainsString('Updated budget', $result);
        $this->assertStringContainsString('amount to 7000', $result);
        $this->assertDatabaseHas('budgets', ['category' => 'food', 'amount' => 7000]);
    }

    #[Test]
    public function it_returns_error_for_unknown_budget(): void
    {
        $tool = new UpdateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'category' => 'nonexistent',
            'new_amount' => 100,
        ]));

        $this->assertStringContainsString('No budget found', $result);
    }

    #[Test]
    public function it_returns_error_when_no_changes(): void
    {
        Budget::factory()->active()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'category' => 'food',
        ]);

        $tool = new UpdateBudget($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['category' => 'food']));

        $this->assertStringContainsString('No changes provided', $result);
    }
}
