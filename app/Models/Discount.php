<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Discount Model
 *
 * Represents a discount code that can be applied to invoice line items.
 * Supports both percentage and fixed-amount discounts with usage limits.
 *
 * @property int $id
 * @property int $user_id
 * @property string $code Unique discount code
 * @property string $name Display name for the discount
 * @property string|null $description Discount description
 * @property DiscountType $type Type of discount (percentage or fixed)
 * @property int $value Discount value (basis points for percentage, cents for fixed)
 * @property string|null $currency Currency for fixed-amount discounts
 * @property \Carbon\Carbon|null $starts_at When discount becomes valid
 * @property \Carbon\Carbon|null $ends_at When discount expires
 * @property bool $active Whether discount is currently active
 * @property int|null $max_redemptions Maximum total redemptions allowed
 * @property int $current_redemptions Number of times discount has been used
 * @property int|null $max_redemptions_per_customer Maximum redemptions per customer
 * @property int|null $minimum_amount Minimum invoice amount required (in cents)
 * @property array|null $metadata Additional metadata
 */
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

    /**
     * Get the user that owns this discount.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoice items using this discount.
     *
     * @return HasMany
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include active discounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include valid discounts.
     * Checks active status, validity period, and redemption limits.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
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
