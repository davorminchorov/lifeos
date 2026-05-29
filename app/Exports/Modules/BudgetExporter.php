<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Budget;
use Illuminate\Database\Eloquent\Model;

class BudgetExporter extends AbstractModuleExporter
{
    protected string $model = Budget::class;

    protected ?string $dateColumn = 'start_date';

    public function key(): string
    {
        return 'budgets';
    }

    public function label(): string
    {
        return 'Budgets';
    }

    public function headers(): array
    {
        return [
            'Category', 'Period', 'Amount', 'Currency', 'Start Date', 'End Date',
            'Active', 'Rollover Unused', 'Alert Threshold', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Budget  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->category,
            $model->budget_period,
            $model->amount,
            $model->currency,
            $this->date($model->start_date),
            $this->date($model->end_date),
            $this->bool($model->is_active),
            $this->bool($model->rollover_unused),
            $model->alert_threshold,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
