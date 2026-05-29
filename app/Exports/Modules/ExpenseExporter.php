<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;

class ExpenseExporter extends AbstractModuleExporter
{
    protected string $model = Expense::class;

    protected ?string $dateColumn = 'expense_date';

    public function key(): string
    {
        return 'expenses';
    }

    public function label(): string
    {
        return 'Expenses';
    }

    public function headers(): array
    {
        return [
            'Date', 'Amount', 'Currency', 'Category', 'Subcategory', 'Description',
            'Merchant', 'Payment Method', 'Expense Type', 'Tax Deductible', 'Recurring',
            'Tags', 'Status', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Expense  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $this->date($model->expense_date),
            $model->amount,
            $model->currency,
            $model->category,
            $model->subcategory,
            $model->description,
            $model->merchant,
            $model->payment_method,
            $model->expense_type,
            $this->bool($model->is_tax_deductible),
            $this->bool($model->is_recurring),
            $this->list($model->tags),
            $model->status,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
