<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function getFormattedUnitAmountAttribute(): string
    {
        return $this->formatMoney($this->unit_amount);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total_amount);
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->formatMoney($this->amount);
    }

    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
