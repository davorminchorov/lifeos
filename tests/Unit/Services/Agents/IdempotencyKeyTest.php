<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use InvalidArgumentException;
use Tests\TestCase;

class IdempotencyKeyTest extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_expenses_create_keys_are_deterministic(): void
    {
        $payload = [
            'amount' => 12.5,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
        ];

        $this->assertSame(
            $this->keys->for('expenses.create', 1, $payload),
            $this->keys->for('expenses.create', 1, $payload),
        );
    }

    public function test_expenses_create_keys_normalize_merchant_whitespace_and_case(): void
    {
        $a = $this->keys->for('expenses.create', 1, [
            'amount' => 5,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'LIDL ',
        ]);

        $b = $this->keys->for('expenses.create', 1, [
            'amount' => 5,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => '  lidl',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_expenses_create_keys_differ_across_tenants(): void
    {
        $payload = [
            'amount' => 10,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Acme',
        ];

        $this->assertNotSame(
            $this->keys->for('expenses.create', 1, $payload),
            $this->keys->for('expenses.create', 2, $payload),
        );
    }

    public function test_expenses_create_amount_drives_distinct_keys(): void
    {
        $this->assertNotSame(
            $this->keys->for('expenses.create', 1, [
                'amount' => 9.99,
                'currency' => 'EUR',
                'expense_date' => '2026-05-01',
                'merchant' => 'Acme',
            ]),
            $this->keys->for('expenses.create', 1, [
                'amount' => 10.00,
                'currency' => 'EUR',
                'expense_date' => '2026-05-01',
                'merchant' => 'Acme',
            ]),
        );
    }

    public function test_bulk_import_is_order_insensitive(): void
    {
        $a = $this->keys->for('expenses.bulkImport', 1, [
            'items' => [
                ['amount' => 1, 'currency' => 'EUR', 'expense_date' => '2026-05-01', 'merchant' => 'A'],
                ['amount' => 2, 'currency' => 'EUR', 'expense_date' => '2026-05-02', 'merchant' => 'B'],
            ],
        ]);

        $b = $this->keys->for('expenses.bulkImport', 1, [
            'items' => [
                ['amount' => 2, 'currency' => 'EUR', 'expense_date' => '2026-05-02', 'merchant' => 'B'],
                ['amount' => 1, 'currency' => 'EUR', 'expense_date' => '2026-05-01', 'merchant' => 'A'],
            ],
        ]);

        $this->assertSame($a, $b);
    }

    public function test_categorize_keys_depend_on_target_and_category(): void
    {
        $a = $this->keys->for('expenses.categorize', 1, ['expense_id' => 7, 'category' => 'groceries']);
        $b = $this->keys->for('expenses.categorize', 1, ['expense_id' => 7, 'category' => 'travel']);
        $c = $this->keys->for('expenses.categorize', 1, ['expense_id' => 8, 'category' => 'groceries']);

        $this->assertNotSame($a, $b);
        $this->assertNotSame($a, $c);
    }

    public function test_unknown_tool_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->keys->for('unknown.tool', 1, []);
    }
}
