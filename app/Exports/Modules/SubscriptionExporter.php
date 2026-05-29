<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

class SubscriptionExporter extends AbstractModuleExporter
{
    protected string $model = Subscription::class;

    protected ?string $dateColumn = 'start_date';

    public function key(): string
    {
        return 'subscriptions';
    }

    public function label(): string
    {
        return 'Subscriptions';
    }

    public function headers(): array
    {
        return [
            'Service Name', 'Description', 'Category', 'Cost', 'Currency', 'Billing Cycle',
            'Start Date', 'Next Billing Date', 'Cancellation Date', 'Payment Method',
            'Auto Renewal', 'Status', 'Tags', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Subscription  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->service_name,
            $model->description,
            $model->category,
            $model->cost,
            $model->currency,
            $model->billing_cycle,
            $this->date($model->start_date),
            $this->date($model->next_billing_date),
            $this->date($model->cancellation_date),
            $model->payment_method,
            $this->bool($model->auto_renewal),
            $model->status,
            $this->list($model->tags),
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
