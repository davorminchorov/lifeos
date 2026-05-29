<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Model;

class WarrantyExporter extends AbstractModuleExporter
{
    protected string $model = Warranty::class;

    protected ?string $dateColumn = 'purchase_date';

    public function key(): string
    {
        return 'warranties';
    }

    public function label(): string
    {
        return 'Warranties';
    }

    public function headers(): array
    {
        return [
            'Product Name', 'Brand', 'Model', 'Serial Number', 'Purchase Date', 'Purchase Price',
            'Retailer', 'Warranty Duration (months)', 'Warranty Type', 'Expiration Date',
            'Status', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  Warranty  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->product_name,
            $model->brand,
            $model->model,
            $model->serial_number,
            $this->date($model->purchase_date),
            $model->purchase_price,
            $model->retailer,
            $model->warranty_duration_months,
            $model->warranty_type,
            $this->date($model->warranty_expiration_date),
            $model->current_status,
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
