<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CycleMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'starts_on',
        'cycle_length_days',
        'is_active',
        'notes',
    ];

    public function casts(): array
    {
        return [
            'starts_on' => 'date',
            'is_active' => 'boolean',
            'cycle_length_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(CycleMenuDay::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
