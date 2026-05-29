<?php

namespace Tests\Unit\Exports;

use App\Exports\Modules\ExpenseExporter;
use App\Models\Expense;
use Tests\TestCase;

class ExpenseExporterTest extends TestCase
{
    public function test_row_is_aligned_with_headers(): void
    {
        $exporter = new ExpenseExporter;

        $expense = new Expense([
            'amount' => 12.50,
            'currency' => 'USD',
            'category' => 'food',
            'description' => 'Lunch',
            'is_tax_deductible' => true,
            'is_recurring' => false,
            'tags' => ['work', 'reimbursable'],
            'status' => 'confirmed',
            'expense_date' => '2026-01-15',
        ]);

        $row = $exporter->mapRow($expense);

        $this->assertCount(count($exporter->headers()), $row);
    }

    public function test_it_formats_dates_booleans_and_tags(): void
    {
        $exporter = new ExpenseExporter;

        $expense = new Expense([
            'category' => 'food',
            'is_tax_deductible' => true,
            'is_recurring' => false,
            'tags' => ['work', 'reimbursable'],
            'expense_date' => '2026-01-15',
        ]);

        $row = $exporter->mapRow($expense);

        $this->assertSame('2026-01-15', $row[0]);
        $this->assertSame('Yes', $row[9]);
        $this->assertSame('No', $row[10]);
        $this->assertSame('work, reimbursable', $row[11]);
    }
}
