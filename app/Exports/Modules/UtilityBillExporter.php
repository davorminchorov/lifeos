<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\UtilityBill;
use Illuminate\Database\Eloquent\Model;

class UtilityBillExporter extends AbstractModuleExporter
{
    protected string $model = UtilityBill::class;

    protected ?string $dateColumn = 'due_date';

    public function key(): string
    {
        return 'utility-bills';
    }

    public function label(): string
    {
        return 'Utility Bills';
    }

    public function headers(): array
    {
        return [
            'Utility Type', 'Service Provider', 'Account Number', 'Bill Amount', 'Currency',
            'Usage Amount', 'Usage Unit', 'Bill Period Start', 'Bill Period End', 'Due Date',
            'Payment Status', 'Payment Date', 'Auto Pay', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  UtilityBill  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->utility_type,
            $model->service_provider,
            $model->account_number,
            $model->bill_amount,
            $model->currency,
            $model->usage_amount,
            $model->usage_unit,
            $this->date($model->bill_period_start),
            $this->date($model->bill_period_end),
            $this->date($model->due_date),
            $model->payment_status,
            $this->date($model->payment_date),
            $this->bool($model->auto_pay_enabled),
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
