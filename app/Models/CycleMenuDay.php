<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CycleMenuDay extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'cycle_menu_id',
        'day_index',
        'notes',
    ];

    public function casts(): array
    {
        return [
            'day_index' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CycleMenu::class, 'cycle_menu_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CycleMenuItem::class)->orderBy('position');
    }
}
