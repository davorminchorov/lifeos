<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class HolidayExporter extends AbstractModuleExporter
{
    protected string $model = Holiday::class;

    protected bool $scopedToUser = false;

    protected ?string $dateColumn = 'date';

    public function key(): string
    {
        return 'holidays';
    }

    public function label(): string
    {
        return 'Holidays';
    }

    public function headers(): array
    {
        return ['Country', 'Date', 'Name', 'Description'];
    }

    /**
     * @param  Holiday  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->country,
            $this->date($model->date),
            $model->name,
            $model->description,
        ];
    }
}
