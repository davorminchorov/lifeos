<?php

namespace App\Models;

use App\Enums\MealType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleMenuItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'cycle_menu_day_id',
        'title',
        'meal_type',
        'time_of_day',
        'quantity',
        'recipe_id',
        'position',
    ];

    public function casts(): array
    {
        return [
            'meal_type' => MealType::class,
            'position' => 'integer',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(CycleMenuDay::class, 'cycle_menu_day_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Recipe::class, 'recipe_id');
    }
}
