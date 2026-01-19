<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tax Rate Model
 *
 * Represents a tax rate that can be applied to invoice line items.
 * Supports region-specific taxes with validity periods.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name Display name (e.g., "VAT", "Sales Tax")
 * @property string|null $code Short code for the tax rate
 * @property int $percentage_basis_points Tax percentage in basis points (e.g., 2000 for 20%)
 * @property string|null $country Two-letter country code
 * @property string|null $region State/province/region code
 * @property bool $inclusive Whether tax is inclusive or exclusive
 * @property bool $active Whether tax rate is currently active
 * @property \Carbon\Carbon|null $valid_from Start date for validity period
 * @property \Carbon\Carbon|null $valid_to End date for validity period
 * @property string|null $description Additional description
 * @property array|null $metadata Additional metadata
 */
class TaxRate extends Model
{
    /** @use HasFactory<\Database\Factories\TaxRateFactory> */
    use BelongsToTenant, HasFactory;

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

    /**
     * Get the user that owns this tax rate.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoice items using this tax rate.
     *
     * @return HasMany
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include active tax rates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get the tax percentage as a decimal value.
     * Converts basis points to percentage (e.g., 2000 → 20.00).
     *
     * @return float
     */
    public function getPercentageAttribute(): float
    {
        return $this->percentage_basis_points / 100;
    }
}
