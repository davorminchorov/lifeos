<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\CreditNote;
use Illuminate\Database\Eloquent\Model;

class CreditNoteExporter extends AbstractModuleExporter
{
    protected string $model = CreditNote::class;

    protected ?string $dateColumn = 'issued_at';

    protected array $with = ['customer'];

    public function key(): string
    {
        return 'credit-notes';
    }

    public function label(): string
    {
        return 'Credit Notes';
    }

    public function headers(): array
    {
        return [
            'Number', 'Customer', 'Status', 'Currency', 'Subtotal', 'Tax Total', 'Total',
            'Amount Remaining', 'Reason', 'Issued At', 'Created At',
        ];
    }

    /**
     * @param  CreditNote  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->number,
            $model->customer?->name,
            $this->enumLabel($model->status),
            $model->currency,
            $this->money($model->subtotal),
            $this->money($model->tax_total),
            $this->money($model->total),
            $this->money($model->amount_remaining),
            $model->reason,
            $this->date($model->issued_at),
            $this->datetime($model->created_at),
        ];
    }
}
