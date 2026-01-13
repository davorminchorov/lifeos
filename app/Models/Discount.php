<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    /** @use HasFactory<\Database\Factories\DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'description',
        'type',
        'value',
        'currency',
        'starts_at',
        'ends_at',
        'active',
        'max_redemptions',
        'current_redemptions',
        'max_redemptions_per_customer',
        'minimum_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'active' => 'boolean',
            'max_redemptions' => 'integer',
            'current_redemptions' => 'integer',
            'max_redemptions_per_customer' => 'integer',
            'minimum_amount' => 'integer',
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

    public function scopeValid($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_redemptions')
                  ->orWhereRaw('current_redemptions < max_redemptions');
            });
    }
}
