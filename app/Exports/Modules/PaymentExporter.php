<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentExporter extends AbstractModuleExporter
{
    protected string $model = Payment::class;

    protected ?string $dateColumn = 'payment_date';

    protected array $with = ['invoice.customer'];

    public function key(): string
    {
        return 'payments';
    }

    public function label(): string
    {
        return 'Payments';
    }

    public function headers(): array
    {
        return [
            'Payment Date', 'Invoice Number', 'Customer', 'Amount', 'Currency', 'Status',
            'Payment Method', 'Provider', 'Reference', 'Notes',
        ];
    }

    /**
     * @param  Payment  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $this->date($model->payment_date),
            $model->invoice?->number ?? 'Draft',
            $model->invoice?->customer?->name,
            $this->money($model->amount),
            $model->currency,
            $this->enumLabel($model->status),
            $model->payment_method,
            $model->provider,
            $model->reference,
            $model->notes,
        ];
    }
}
