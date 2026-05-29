<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Model;

class ContractExporter extends AbstractModuleExporter
{
    protected string $model = Contract::class;

    protected ?string $dateColumn = 'start_date';

    public function key(): string
    {
        return 'contracts';
    }

    public function label(): string
    {
        return 'Contracts';
    }

    public function headers(): array
    {
        return [
            'Title', 'Type', 'Counterparty', 'Start Date', 'End Date', 'Notice Period (days)',
            'Auto Renewal', 'Contract Value', 'Payment Terms', 'Status', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Contract  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->title,
            $model->contract_type,
            $model->counterparty,
            $this->date($model->start_date),
            $this->date($model->end_date),
            $model->notice_period_days,
            $this->bool($model->auto_renewal),
            $model->contract_value,
            $model->payment_terms,
            $model->status,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
