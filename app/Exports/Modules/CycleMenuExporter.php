<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\CycleMenu;
use Illuminate\Database\Eloquent\Model;

class CycleMenuExporter extends AbstractModuleExporter
{
    protected string $model = CycleMenu::class;

    protected ?string $dateColumn = 'starts_on';

    public function key(): string
    {
        return 'cycle-menus';
    }

    public function label(): string
    {
        return 'Cycle Menus';
    }

    public function headers(): array
    {
        return [
            'Name', 'Starts On', 'Cycle Length (days)', 'Active', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  CycleMenu  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->name,
            $this->date($model->starts_on),
            $model->cycle_length_days,
            $this->bool($model->is_active),
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
