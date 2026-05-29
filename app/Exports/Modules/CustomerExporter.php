<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerExporter extends AbstractModuleExporter
{
    protected string $model = Customer::class;

    protected ?string $dateColumn = 'created_at';

    public function key(): string
    {
        return 'customers';
    }

    public function label(): string
    {
        return 'Customers';
    }

    public function headers(): array
    {
        return [
            'Name', 'Email', 'Phone', 'Company', 'Tax ID', 'Tax Country', 'Currency',
            'Notes', 'Created At',
        ];
    }

    /**
     * @param  Customer  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->name,
            $model->email,
            $model->phone,
            $model->company_name,
            $model->tax_id,
            $model->tax_country,
            $model->currency,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
