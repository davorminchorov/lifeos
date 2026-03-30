<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\RecordInvestmentTransaction;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordInvestmentTransactionTest extends TestCase
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
    public function it_records_a_buy_transaction(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Apple Inc',
            'symbol_identifier' => 'AAPL',
            'currency' => 'USD',
        ]);

        $tool = new RecordInvestmentTransaction($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'investment_name' => 'Apple',
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 150.00,
            'fees' => 5.00,
        ]));

        $this->assertStringContainsString('Recorded buy', $result);
        $this->assertStringContainsString('Apple Inc', $result);
        $this->assertDatabaseHas('investment_transactions', [
            'transaction_type' => 'buy',
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function it_returns_error_for_unknown_investment(): void
    {
        $tool = new RecordInvestmentTransaction($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'investment_name' => 'Nonexistent',
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 100,
        ]));

        $this->assertStringContainsString('No investment found', $result);
    }

    #[Test]
    public function it_validates_transaction_type(): void
    {
        Investment::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Test Stock',
        ]);

        $tool = new RecordInvestmentTransaction($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'investment_name' => 'Test Stock',
            'transaction_type' => 'invalid',
            'quantity' => 10,
            'price_per_share' => 100,
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
