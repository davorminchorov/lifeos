<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class InvoiceExporter extends AbstractModuleExporter
{
    protected string $model = Invoice::class;

    protected ?string $dateColumn = 'issued_at';

    protected array $with = ['customer'];

    public function key(): string
    {
        return 'invoices';
    }

    public function label(): string
    {
        return 'Invoices';
    }

    public function headers(): array
    {
        return [
            'Invoice Number', 'Customer', 'Status', 'Currency', 'Subtotal', 'Discount Total',
            'Tax Total', 'Total', 'Amount Paid', 'Amount Due', 'Issued At', 'Due At', 'Paid At',
            'Notes', 'Created At',
        ];
    }

    /**
     * @param  Invoice  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->number ?? 'Draft',
            $model->customer?->name,
            $this->enumLabel($model->status),
            $model->currency,
            $this->money($model->subtotal),
            $this->money($model->discount_total),
            $this->money($model->tax_total),
            $this->money($model->total),
            $this->money($model->amount_paid),
            $this->money($model->amount_due),
            $this->date($model->issued_at),
            $this->date($model->due_at),
            $this->date($model->paid_at),
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
