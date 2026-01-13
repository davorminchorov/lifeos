<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    /** @use HasFactory<\Database\Factories\TaxRateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'percentage_basis_points',
        'country',
        'region',
        'inclusive',
        'active',
        'valid_from',
        'valid_to',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'percentage_basis_points' => 'integer',
            'inclusive' => 'boolean',
            'active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getPercentageAttribute(): float
    {
        return $this->percentage_basis_points / 100;
    }
}
