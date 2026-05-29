<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Iou;
use Illuminate\Database\Eloquent\Model;

class IouExporter extends AbstractModuleExporter
{
    protected string $model = Iou::class;

    protected ?string $dateColumn = 'transaction_date';

    public function key(): string
    {
        return 'ious';
    }

    public function label(): string
    {
        return 'IOUs';
    }

    public function headers(): array
    {
        return [
            'Type', 'Person', 'Amount', 'Currency', 'Transaction Date', 'Due Date', 'Description',
            'Status', 'Amount Paid', 'Payment Method', 'Category', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Iou  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->type,
            $model->person_name,
            $model->amount,
            $model->currency,
            $this->date($model->transaction_date),
            $this->date($model->due_date),
            $model->description,
            $model->status,
            $model->amount_paid,
            $model->payment_method,
            $model->category,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
