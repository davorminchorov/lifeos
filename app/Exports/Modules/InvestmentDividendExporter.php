<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Exports\ExportContext;
use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvestmentDividendExporter extends AbstractModuleExporter
{
    protected string $model = InvestmentDividend::class;

    protected bool $scopedToUser = false;

    protected ?string $dateColumn = 'payment_date';

    protected array $with = ['investment'];

    public function key(): string
    {
        return 'investment-dividends';
    }

    public function label(): string
    {
        return 'Investment Dividends';
    }

    /**
     * Dividends have no user_id; scope them through the parent investments
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
            'Investment', 'Symbol', 'Amount', 'Record Date', 'Payment Date', 'Ex-Dividend Date',
            'Dividend Type', 'Frequency', 'Dividend Per Share', 'Shares Held', 'Tax Withheld',
            'Currency', 'Reinvested', 'Notes',
        ];
    }

    /**
     * @param  InvestmentDividend  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->investment?->name,
            $model->investment?->symbol_identifier,
            $model->amount,
            $this->date($model->record_date),
            $this->date($model->payment_date),
            $this->date($model->ex_dividend_date),
            $model->dividend_type,
            $model->frequency,
            $model->dividend_per_share,
            $model->shares_held,
            $model->tax_withheld,
            $model->currency,
            $this->bool($model->reinvested),
            $model->notes,
        ];
    }
}
