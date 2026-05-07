<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase7Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    private function expensePayload(array $overrides = []): array
    {
        return array_merge([
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
        ], $overrides);
    }

    public function test_legacy_email_only_keys_are_stable_across_phase7(): void
    {
        // The Phase 7 key generator must produce the same key for inputs that
        // don't carry source_file_id, so existing pending_action rows continue
        // to dedupe across agent runs.
        $a = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_email_id' => 'gmail-msg-abc',
        ]));

        $expected = hash(
            'sha256',
            'expenses.create|1|lidl|1250|EUR|2026-05-01|gmail-msg-abc',
        );

        $this->assertSame($expected, $a);
    }

    public function test_no_source_at_all_uses_legacy_empty_suffix(): void
    {
        $a = $this->keys->for('expenses.create', 1, $this->expensePayload());

        $expected = hash(
            'sha256',
            'expenses.create|1|lidl|1250|EUR|2026-05-01|',
        );

        $this->assertSame($expected, $a);
    }

    public function test_file_id_changes_the_key_when_email_id_is_absent(): void
    {
        $emailOnly = $this->keys->for('expenses.create', 1, $this->expensePayload());
        $withFile = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_file_id' => 'drive-abc',
        ]));

        $this->assertNotSame($emailOnly, $withFile);
    }

    public function test_same_file_id_collapses_resubmits(): void
    {
        $a = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_file_id' => 'drive-abc',
        ]));
        $b = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_file_id' => 'drive-abc',
        ]));

        $this->assertSame($a, $b);
    }

    public function test_different_file_ids_distinguish_otherwise_identical_payloads(): void
    {
        $a = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_file_id' => 'drive-abc',
        ]));
        $b = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_file_id' => 'drive-xyz',
        ]));

        $this->assertNotSame($a, $b);
    }

    public function test_email_id_plus_file_id_appends_file_suffix(): void
    {
        $a = $this->keys->for('expenses.create', 1, $this->expensePayload([
            'source_email_id' => 'gmail-1',
            'source_file_id' => 'drive-1',
        ]));

        $expected = hash(
            'sha256',
            'expenses.create|1|lidl|1250|EUR|2026-05-01|gmail-1|file:drive-1',
        );

        $this->assertSame($expected, $a);
    }

    public function test_warranties_create_honours_file_id(): void
    {
        $a = $this->keys->for('warranties.create', 1, [
            'product_name' => 'Laptop',
            'serial_number' => 'ABC',
            'purchase_date' => '2026-05-01',
            'source_file_id' => 'drive-receipt-1',
        ]);
        $b = $this->keys->for('warranties.create', 1, [
            'product_name' => 'Laptop',
            'serial_number' => 'ABC',
            'purchase_date' => '2026-05-01',
            'source_file_id' => 'drive-receipt-1',
        ]);
        $c = $this->keys->for('warranties.create', 1, [
            'product_name' => 'Laptop',
            'serial_number' => 'ABC',
            'purchase_date' => '2026-05-01',
            'source_file_id' => 'drive-receipt-2',
        ]);

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
    }

    public function test_utility_bills_create_honours_file_id(): void
    {
        $a = $this->keys->for('utilityBills.create', 1, [
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 100,
            'currency' => 'EUR',
            'due_date' => '2026-05-15',
            'source_file_id' => 'drive-bill-1',
        ]);
        $b = $this->keys->for('utilityBills.create', 1, [
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 100,
            'currency' => 'EUR',
            'due_date' => '2026-05-15',
            'source_file_id' => 'drive-bill-1',
        ]);

        $this->assertSame($a, $b);
    }
}
