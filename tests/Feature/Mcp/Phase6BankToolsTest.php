<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Bank\LinkExpense as BankLinkExpense;
use App\Mcp\Tools\Bank\RecordLines as BankRecordLines;
use App\Mcp\Tools\Bank\UnmatchedLines as BankUnmatchedLines;
use App\Models\AgentToken;
use App\Models\BankLine;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class Phase6BankToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);
    }

    private function lineArgs(array $overrides = []): array
    {
        return array_merge([
            'account' => 'Komercijalna ****1234',
            'posted_at' => '2026-05-01',
            'amount_cents' => -1250,
            'currency' => 'EUR',
            'merchant_raw' => 'LIDL SKOPJE',
            'description' => 'CARD PAYMENT - LIDL SKOPJE',
            'statement_id' => 'STMT-2026-04',
            'statement_row' => 1,
        ], $overrides);
    }

    public function test_record_lines_queues_pending_action(): void
    {
        LifeOsServer::tool(BankRecordLines::class, [
            'lines' => [$this->lineArgs()],
        ])->assertOk()->assertStructuredContent(function (array $content): bool {
            $this->assertSame(PendingAction::STATUS_PENDING, $content['status']);
            $this->assertSame(1, $content['line_count']);

            return true;
        });

        $this->assertSame(1, PendingAction::query()->where('tool', 'bank.recordLines')->count());
        $this->assertSame(0, BankLine::query()->count(), 'No bank lines created before approval.');
    }

    public function test_record_lines_idempotent_on_resubmit(): void
    {
        $args = ['lines' => [$this->lineArgs()]];

        LifeOsServer::tool(BankRecordLines::class, $args);
        LifeOsServer::tool(BankRecordLines::class, $args);

        $this->assertSame(1, PendingAction::query()->where('tool', 'bank.recordLines')->count());
    }

    public function test_apply_creates_bank_lines_and_auto_links_high_confidence_matches(): void
    {
        // Existing expense that should auto-link.
        $expense = Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        LifeOsServer::tool(BankRecordLines::class, [
            'lines' => [
                $this->lineArgs(),
                $this->lineArgs([
                    'statement_row' => 2,
                    'amount_cents' => -9999,
                    'merchant_raw' => 'KONZUM AERODROM',
                    'description' => 'CARD - KONZUM AERODROM',
                ]),
            ],
        ]);

        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(2, BankLine::query()->count());

        $matched = BankLine::query()->where('match_status', BankLine::STATUS_MATCHED)->first();
        $this->assertNotNull($matched);
        $this->assertSame($expense->id, $matched->matched_expense_id);

        $unmatched = BankLine::query()->where('match_status', BankLine::STATUS_UNMATCHED)->first();
        $this->assertNotNull($unmatched);
        $this->assertSame('KONZUM AERODROM', $unmatched->merchant_raw);

        $this->assertSame(2, $applied->applied_diff['after']['matched'] + $applied->applied_diff['after']['unmatched']);
    }

    public function test_unmatched_tool_lists_only_unmatched(): void
    {
        // Two bank lines: one auto-linked, one unmatched.
        $expense = Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        BankLine::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'merchant_raw' => 'KONZUM AERODROM',
            'amount_cents' => -9999,
            'currency' => 'EUR',
            'posted_at' => now()->subDays(2),
            'match_status' => BankLine::STATUS_UNMATCHED,
            'match_confidence' => 0.5,
            'fingerprint' => hash('sha256', 'unmatched-1'),
        ]);

        BankLine::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'merchant_raw' => 'LIDL SKOPJE',
            'amount_cents' => -1250,
            'currency' => 'EUR',
            'posted_at' => now()->subDays(1),
            'match_status' => BankLine::STATUS_MATCHED,
            'matched_expense_id' => $expense->id,
            'fingerprint' => hash('sha256', 'matched-1'),
        ]);

        LifeOsServer::tool(BankUnmatchedLines::class, ['within_days' => 7])
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertSame(1, $content['count']);
                $this->assertSame('KONZUM AERODROM', $content['items'][0]['merchant_raw']);

                return true;
            });
    }

    public function test_link_expense_queues_pending_action_then_applies(): void
    {
        $expense = Expense::factory()->create([
            'merchant' => 'Konzum',
            'amount' => 99.99,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        $bankLine = BankLine::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'match_status' => BankLine::STATUS_UNMATCHED,
            'fingerprint' => hash('sha256', 'unmatched-link'),
        ]);

        LifeOsServer::tool(BankLinkExpense::class, [
            'bank_line_id' => $bankLine->id,
            'expense_id' => $expense->id,
        ])->assertOk();

        $action = PendingAction::query()->firstOrFail();
        $this->assertSame('bank.linkExpense', $action->tool);

        app(PendingActionApplier::class)->apply($action, $this->user);

        $bankLine->refresh();
        $this->assertSame(BankLine::STATUS_MATCHED, $bankLine->match_status);
        $this->assertSame($expense->id, $bankLine->matched_expense_id);
    }

    public function test_revert_record_lines_removes_created_bank_lines(): void
    {
        LifeOsServer::tool(BankRecordLines::class, [
            'lines' => [$this->lineArgs()],
        ]);

        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);
        $this->assertSame(1, BankLine::query()->count());

        app(PendingActionApplier::class)->revert($applied, $this->user);

        $this->assertSame(0, BankLine::query()->count());
    }

    public function test_link_expense_rejects_unknown_targets(): void
    {
        LifeOsServer::tool(BankLinkExpense::class, [
            'bank_line_id' => 999,
            'expense_id' => 999,
        ])->assertHasErrors(['Bank line [999] not found in this tenant.']);
    }

    public function test_link_expense_blocks_cross_tenant_targets(): void
    {
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();
        $this->actingAs($other);
        $foreignExpense = Expense::factory()->create();
        $foreignBankLine = BankLine::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $other->id,
            'fingerprint' => hash('sha256', 'foreign'),
        ]);

        $this->actingAs($this->user);

        LifeOsServer::tool(BankLinkExpense::class, [
            'bank_line_id' => $foreignBankLine->id,
            'expense_id' => $foreignExpense->id,
        ])->assertHasErrors([
            "Bank line [{$foreignBankLine->id}] not found in this tenant.",
        ]);
    }
}
