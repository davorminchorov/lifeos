<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\SummarizePortfolio;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SummarizePortfolioTest extends TestCase
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
    public function it_returns_empty_portfolio_message(): void
    {
        $tool = new SummarizePortfolio($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No active investments', $result);
    }

    #[Test]
    public function it_summarizes_active_portfolio(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'investment_type' => 'stocks',
            'status' => 'active',
            'quantity' => 10,
            'purchase_price' => 100,
            'current_value' => 150,
        ]);

        $tool = new SummarizePortfolio($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('PORTFOLIO SUMMARY', $result);
        $this->assertStringContainsString('ALLOCATION BY TYPE', $result);
        $this->assertStringContainsString('stocks', $result);
    }
}
