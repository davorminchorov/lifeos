<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Investments\BulkImportTransactions;
use App\Mcp\Tools\Investments\RecordDividend;
use App\Mcp\Tools\Investments\RecordTransaction;
use App\Mcp\Tools\Investments\RepriceLot;
use App\Models\AgentToken;
use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Models\InvestmentTransaction;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class Phase5InvestmentsToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    private Investment $investment;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);

        $this->investment = Investment::factory()->create([
            'name' => 'VWCE',
            'symbol_identifier' => 'VWCE',
            'investment_type' => 'etf',
            'currency' => 'EUR',
            'quantity' => 10,
            'purchase_price' => 100,
            'current_value' => 100,
        ]);
    }

    public function test_record_transaction_queues_pending_action(): void
    {
        LifeOsServer::tool(RecordTransaction::class, [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 5,
            'price_per_share' => 110,
            'transaction_date' => '2026-05-01',
            'order_id' => 'BRK-001',
        ])->assertOk()->assertStructuredContent(function (AssertableJson $json) {
            $json->where('status', PendingAction::STATUS_PENDING)->etc();
        });

        $this->assertSame(1, PendingAction::query()->where('tool', 'investments.recordTransaction')->count());
        $this->assertSame(0, InvestmentTransaction::query()->count());
    }

    public function test_record_transaction_idempotent_by_order_id(): void
    {
        $args = [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 5,
            'price_per_share' => 110,
            'transaction_date' => '2026-05-01',
            'order_id' => 'BRK-001',
        ];

        LifeOsServer::tool(RecordTransaction::class, $args);
        LifeOsServer::tool(RecordTransaction::class, $args);

        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_apply_record_transaction_writes_with_attribution(): void
    {
        LifeOsServer::tool(RecordTransaction::class, [
            'investment_id' => $this->investment->id,
            'transaction_type' => 'buy',
            'quantity' => 5,
            'price_per_share' => 110,
            'transaction_date' => '2026-05-01',
            'order_id' => 'BRK-002',
        ]);

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(1, InvestmentTransaction::query()->count());
        $tx = InvestmentTransaction::query()->first();
        $this->assertSame($this->investment->id, $tx->investment_id);
        $this->assertSame('agent', $tx->source);
        $this->assertNotNull($tx->created_by_agent_token_id);
        $this->assertEquals(550.0, (float) $tx->total_amount, 'total_amount auto-computed.');
    }

    public function test_record_dividend_queues_pending_action(): void
    {
        LifeOsServer::tool(RecordDividend::class, [
            'investment_id' => $this->investment->id,
            'amount' => 12.50,
            'payment_date' => '2026-04-30',
            'tax_withheld' => 1.25,
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'investments.recordDividend')->count());
        $this->assertSame(0, InvestmentDividend::query()->count());
    }

    public function test_reprice_lot_queues_pending_action(): void
    {
        LifeOsServer::tool(RepriceLot::class, [
            'investment_id' => $this->investment->id,
            'current_value' => 115,
            'as_of' => '2026-05-07',
        ])->assertOk();

        $action = PendingAction::query()->firstOrFail();
        $this->assertSame('investments.repriceLot', $action->tool);
        $this->assertSame((float) 100, (float) $this->investment->fresh()->current_value, 'Investment unchanged before approval.');
    }

    public function test_apply_reprice_lot_updates_current_value(): void
    {
        LifeOsServer::tool(RepriceLot::class, [
            'investment_id' => $this->investment->id,
            'current_value' => 115,
            'as_of' => '2026-05-07',
        ]);

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->investment->refresh();
        $this->assertEquals(115.0, (float) $this->investment->current_value);
        $this->assertSame('2026-05-07', $this->investment->last_price_update?->toDateString());
    }

    public function test_revert_reprice_restores_previous_value(): void
    {
        LifeOsServer::tool(RepriceLot::class, [
            'investment_id' => $this->investment->id,
            'current_value' => 115,
            'as_of' => '2026-05-07',
        ]);
        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);

        app(PendingActionApplier::class)->revert($applied, $this->user);

        $this->investment->refresh();
        $this->assertEquals(100.0, (float) $this->investment->current_value);
    }

    public function test_record_transaction_rejects_unknown_investment(): void
    {
        LifeOsServer::tool(RecordTransaction::class, [
            'investment_id' => 999999,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'price_per_share' => 1,
            'transaction_date' => '2026-05-01',
        ])->assertHasErrors(['Investment [999999] not found in this tenant.']);
    }

    public function test_record_transaction_blocks_cross_tenant_target(): void
    {
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();

        $this->actingAs($other);
        $foreign = Investment::factory()->create();

        $this->actingAs($this->user);

        LifeOsServer::tool(RecordTransaction::class, [
            'investment_id' => $foreign->id,
            'transaction_type' => 'buy',
            'quantity' => 1,
            'price_per_share' => 1,
            'transaction_date' => '2026-05-01',
        ])->assertHasErrors([
            "Investment [{$foreign->id}] not found in this tenant.",
        ]);
    }

    public function test_bulk_import_queues_single_pending_action(): void
    {
        LifeOsServer::tool(BulkImportTransactions::class, [
            'items' => [
                [
                    'investment_id' => $this->investment->id,
                    'transaction_type' => 'buy',
                    'quantity' => 5,
                    'price_per_share' => 100,
                    'transaction_date' => '2026-05-01',
                    'order_id' => 'BRK-1',
                ],
                [
                    'investment_id' => $this->investment->id,
                    'transaction_type' => 'sell',
                    'quantity' => 2,
                    'price_per_share' => 110,
                    'transaction_date' => '2026-05-05',
                    'order_id' => 'BRK-2',
                ],
            ],
        ])->assertOk()->assertStructuredContent(function (AssertableJson $json) {
            $json->where('item_count', 2)->etc();
        });

        $this->assertSame(1, PendingAction::query()->where('tool', 'investments.bulkImportTransactions')->count());
    }
}
