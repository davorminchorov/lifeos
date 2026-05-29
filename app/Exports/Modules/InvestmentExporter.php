<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Model;

class InvestmentExporter extends AbstractModuleExporter
{
    protected string $model = Investment::class;

    protected ?string $dateColumn = 'purchase_date';

    public function key(): string
    {
        return 'investments';
    }

    public function label(): string
    {
        return 'Investments';
    }

    public function headers(): array
    {
        return [
            'Type', 'Symbol', 'Name', 'Quantity', 'Purchase Date', 'Purchase Price', 'Currency',
            'Current Value', 'Total Dividends', 'Total Fees', 'Risk Tolerance', 'Account / Broker',
            'Status', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Investment  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->investment_type,
            $model->symbol_identifier,
            $model->name,
            $model->quantity,
            $this->date($model->purchase_date),
            $model->purchase_price,
            $model->currency,
            $model->current_value,
            $model->total_dividends_received,
            $model->total_fees_paid,
            $model->risk_tolerance,
            $model->account_broker,
            $model->status,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
