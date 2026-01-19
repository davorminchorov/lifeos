<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Invoice Item Model
 *
 * Represents a line item on an invoice.
 * Each item tracks quantity, pricing, taxes, and discounts.
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $name Item name/description
 * @property string|null $description Detailed description
 * @property float $quantity Quantity (supports decimals like 2.5 hours)
 * @property int $unit_amount Price per unit in cents
 * @property string $currency Three-letter currency code
 * @property int|null $tax_rate_id Applied tax rate
 * @property int $tax_amount Calculated tax in cents
 * @property int|null $discount_id Applied discount
 * @property int $discount_amount Calculated discount in cents
 * @property int $amount Line total before tax and discount (quantity * unit_amount)
 * @property int $total_amount Final line total after discount and tax
 * @property array|null $metadata Additional metadata
 * @property int $sort_order Display order within invoice
 */
class InvoiceItem extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceItemFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'quantity',
        'unit_amount',
        'currency',
        'tax_rate_id',
        'tax_amount',
        'discount_id',
        'discount_amount',
        'amount',
        'total_amount',
        'metadata',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_amount' => 'integer',
            'tax_amount' => 'integer',
            'discount_amount' => 'integer',
            'amount' => 'integer',
            'total_amount' => 'integer',
            'metadata' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the invoice this item belongs to.
     *
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the tax rate applied to this item.
     *
     * @return BelongsTo
     */
    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    /**
     * Get the discount applied to this item.
     *
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the formatted unit amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedUnitAmountAttribute(): string
    {
        return $this->formatMoney($this->unit_amount);
    }

    /**
     * Get the formatted total amount with currency symbol.
     * This is the final line total after all calculations.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total_amount);
    }

    /**
     * Get the formatted line amount with currency symbol.
     * This is the amount before discounts and taxes.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->formatMoney($this->amount);
    }

    /**
     * Format an amount in cents to a currency string.
     *
     * @param int $cents Amount in cents
     * @return string Formatted money string
     */
    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
