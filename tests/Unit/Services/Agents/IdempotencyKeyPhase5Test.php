<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase5Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_record_transaction_anchors_on_order_id_when_present(): void
    {
        $a = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'order_id' => 'BRK-12345',
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 100,
            'transaction_date' => '2026-05-01',
        ]);

        // Different qty + date should still collide because order_id is set.
        $b = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'order_id' => 'BRK-12345',
            'transaction_type' => 'buy',
            'quantity' => 11,
            'price_per_share' => 99,
            'transaction_date' => '2026-05-02',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_record_transaction_falls_back_to_natural_fields(): void
    {
        $a = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 100,
            'transaction_date' => '2026-05-01',
        ]);

        $b = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 100,
            'transaction_date' => '2026-05-01',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_record_transaction_distinguishes_by_quantity_without_order_id(): void
    {
        $a = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'transaction_type' => 'buy',
            'quantity' => 10,
            'price_per_share' => 100,
            'transaction_date' => '2026-05-01',
        ]);

        $b = $this->keys->for('investments.recordTransaction', 1, [
            'investment_id' => 7,
            'transaction_type' => 'buy',
            'quantity' => 11,
            'price_per_share' => 100,
            'transaction_date' => '2026-05-01',
        ]);

        $this->assertNotSame($a, $b);
    }

    public function test_record_dividend_uses_payment_date_and_amount(): void
    {
        $a = $this->keys->for('investments.recordDividend', 1, [
            'investment_id' => 7,
            'amount' => 25.50,
            'payment_date' => '2026-04-30',
        ]);

        $b = $this->keys->for('investments.recordDividend', 1, [
            'investment_id' => 7,
            'amount' => 25.50,
            'payment_date' => '2026-04-30',
        ]);

        $this->assertSame($a, $b);

        $different = $this->keys->for('investments.recordDividend', 1, [
            'investment_id' => 7,
            'amount' => 25.50,
            'payment_date' => '2026-05-31',
        ]);

        $this->assertNotSame($a, $different);
    }

    public function test_reprice_lot_collapses_per_day(): void
    {
        $a = $this->keys->for('investments.repriceLot', 1, [
            'investment_id' => 7,
            'current_value' => 110,
            'as_of' => '2026-05-07',
        ]);

        // Same as_of, different current_value — same key (one reprice per day).
        $b = $this->keys->for('investments.repriceLot', 1, [
            'investment_id' => 7,
            'current_value' => 115,
            'as_of' => '2026-05-07',
        ]);

        $this->assertSame($a, $b);

        // Different day — different key.
        $c = $this->keys->for('investments.repriceLot', 1, [
            'investment_id' => 7,
            'current_value' => 115,
            'as_of' => '2026-05-08',
        ]);

        $this->assertNotSame($a, $c);
    }

    public function test_bulk_import_is_order_insensitive(): void
    {
        $a = $this->keys->for('investments.bulkImportTransactions', 1, [
            'items' => [
                ['investment_id' => 7, 'order_id' => 'A', 'transaction_type' => 'buy', 'quantity' => 1, 'price_per_share' => 1, 'transaction_date' => '2026-05-01'],
                ['investment_id' => 8, 'order_id' => 'B', 'transaction_type' => 'sell', 'quantity' => 1, 'price_per_share' => 1, 'transaction_date' => '2026-05-02'],
            ],
        ]);

        $b = $this->keys->for('investments.bulkImportTransactions', 1, [
            'items' => [
                ['investment_id' => 8, 'order_id' => 'B', 'transaction_type' => 'sell', 'quantity' => 1, 'price_per_share' => 1, 'transaction_date' => '2026-05-02'],
                ['investment_id' => 7, 'order_id' => 'A', 'transaction_type' => 'buy', 'quantity' => 1, 'price_per_share' => 1, 'transaction_date' => '2026-05-01'],
            ],
        ]);

        $this->assertSame($a, $b);
    }
}
