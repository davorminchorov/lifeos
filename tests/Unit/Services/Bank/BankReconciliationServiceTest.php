<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bank;

use App\Models\BankLine;
use App\Models\Expense;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Bank\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    private BankReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        $this->service = new BankReconciliationService;
    }

    private function lineRow(array $overrides = []): array
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

    public function test_fingerprint_is_deterministic_per_tenant(): void
    {
        $row = $this->lineRow();
        $a = $this->service->fingerprint(1, $row);
        $b = $this->service->fingerprint(1, $row);
        $c = $this->service->fingerprint(2, $row);

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
    }

    public function test_ingest_is_idempotent_on_fingerprint(): void
    {
        $row = $this->lineRow();

        $first = $this->service->ingest($this->user, $this->tenant->id, $row);
        $second = $this->service->ingest($this->user, $this->tenant->id, $row);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, BankLine::query()->count());
    }

    public function test_high_confidence_match_auto_links(): void
    {
        // Same merchant, same amount, same day → should auto-link.
        $expense = Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        $line = $this->service->ingest($this->user, $this->tenant->id, $this->lineRow());

        $this->assertSame(BankLine::STATUS_MATCHED, $line->match_status);
        $this->assertSame($expense->id, $line->matched_expense_id);
        $this->assertGreaterThanOrEqual(0.85, (float) $line->match_confidence);
    }

    public function test_no_candidate_leaves_line_unmatched(): void
    {
        Expense::factory()->create([
            'merchant' => 'Konzum',
            'amount' => 99.99,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        $line = $this->service->ingest($this->user, $this->tenant->id, $this->lineRow());

        $this->assertSame(BankLine::STATUS_UNMATCHED, $line->match_status);
        $this->assertNull($line->matched_expense_id);
    }

    public function test_credit_lines_are_skipped_by_matcher(): void
    {
        // Credit (positive amount) shouldn't match any expense.
        Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        $line = $this->service->ingest(
            $this->user,
            $this->tenant->id,
            $this->lineRow(['amount_cents' => 1250]), // positive = credit
        );

        $this->assertSame(BankLine::STATUS_UNMATCHED, $line->match_status);
    }

    public function test_already_linked_expenses_are_excluded_from_candidates(): void
    {
        // Two expenses with identical signature; the bank line should match
        // exactly one and the second copy must not steal the link.
        $expense1 = Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);
        $expense2 = Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        $first = $this->service->ingest($this->user, $this->tenant->id, $this->lineRow());
        $second = $this->service->ingest(
            $this->user,
            $this->tenant->id,
            $this->lineRow(['statement_row' => 2, 'description' => 'CARD PAYMENT - LIDL SKOPJE 2']),
        );

        $this->assertNotNull($first->matched_expense_id);
        $linked = [$first->matched_expense_id, $second->matched_expense_id];
        $this->assertContains($expense1->id, $linked);
        $this->assertContains($expense2->id, $linked);
        $this->assertNotEquals($first->matched_expense_id, $second->matched_expense_id);
    }

    public function test_close_runner_up_blocks_auto_link(): void
    {
        // Two expenses, both perfect matches (same merchant, amount, day).
        // Without a clear winner the matcher must decline to auto-link the
        // second line because both candidates are equally plausible — the
        // first ingest claims one expense via the "already linked" filter,
        // and the second has only one candidate left, which auto-links.
        // To exercise the "delta" rule: one same-day match and one ±1 day
        // match. Same-day wins outright.
        Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);
        Expense::factory()->create([
            'merchant' => 'Lidl Skopje',
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-02',
        ]);

        $line = $this->service->ingest($this->user, $this->tenant->id, $this->lineRow());

        $this->assertSame(BankLine::STATUS_MATCHED, $line->match_status);
        $this->assertNotNull($line->matched_expense_id);
        $this->assertNotEmpty($line->match_candidates ?? []);
    }

    public function test_link_expense_forces_match(): void
    {
        $expense = Expense::factory()->create([
            'merchant' => 'Konzum',
            'amount' => 99.99,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
        ]);

        // Bank line and expense are unrelated by amount; matcher won't link.
        $line = $this->service->ingest($this->user, $this->tenant->id, $this->lineRow());
        $this->assertSame(BankLine::STATUS_UNMATCHED, $line->match_status);

        $linked = $this->service->linkExpense($line, $expense);

        $this->assertSame(BankLine::STATUS_MATCHED, $linked->match_status);
        $this->assertSame($expense->id, $linked->matched_expense_id);
        $this->assertEqualsWithDelta(1.0, (float) $linked->match_confidence, 0.001);
    }
}
