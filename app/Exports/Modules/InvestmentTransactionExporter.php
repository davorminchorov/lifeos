<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Exports\ExportContext;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvestmentTransactionExporter extends AbstractModuleExporter
{
    protected string $model = InvestmentTransaction::class;

    protected bool $scopedToUser = false;

    protected ?string $dateColumn = 'transaction_date';

    protected array $with = ['investment'];

    public function key(): string
    {
        return 'investment-transactions';
    }

    public function label(): string
    {
        return 'Investment Transactions';
    }

    /**
     * Transactions have no user_id; scope them through the parent investments
     * the current user owns within the tenant.
     */
    public function query(ExportContext $context): Builder
    {
        return parent::query($context)->whereIn(
            'investment_id',
            Investment::query()
                ->withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $context->tenantId)
                ->where('user_id', $context->userId)
                ->select('id'),
        );
    }

    public function headers(): array
    {
        return [
            'Investment', 'Symbol', 'Transaction Type', 'Quantity', 'Price Per Share',
            'Total Amount', 'Fees', 'Taxes', 'Currency', 'Transaction Date', 'Settlement Date',
            'Order Type', 'Notes',
        ];
    }

    /**
     * @param  InvestmentTransaction  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->investment?->name,
            $model->investment?->symbol_identifier,
            $model->transaction_type,
            $model->quantity,
            $model->price_per_share,
            $model->total_amount,
            $model->fees,
            $model->taxes,
            $model->currency,
            $this->date($model->transaction_date),
            $this->date($model->settlement_date),
            $model->order_type,
            $model->notes,
        ];
    }
}
