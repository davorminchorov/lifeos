<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\InvestmentTransactionFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'investment_id',
        'transaction_type',
        'quantity',
        'price_per_share',
        'total_amount',
        'fees',
        'taxes',
        'transaction_date',
        'settlement_date',
        'order_id',
        'confirmation_number',
        'account_number',
        'broker',
        'currency',
        'exchange_rate',
        'order_type',
        'limit_price',
        'stop_price',
        'notes',
        'tax_lot_info',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'price_per_share' => 'decimal:8',
            'total_amount' => 'decimal:8',
            'fees' => 'decimal:2',
            'taxes' => 'decimal:2',
            'transaction_date' => 'date',
            'settlement_date' => 'date',
            'exchange_rate' => 'decimal:6',
            'limit_price' => 'decimal:8',
            'stop_price' => 'decimal:8',
            'tax_lot_info' => 'array',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    // Scope for transactions by type
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    // Scope for buy transactions
    public function scopeBuys($query)
    {
        return $query->whereIn('transaction_type', ['buy', 'dividend_reinvestment']);
    }

    // Scope for sell transactions
    public function scopeSells($query)
    {
        return $query->where('transaction_type', 'sell');
    }

    // Scope for transactions within date range
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Scope for transactions by broker
    public function scopeByBroker($query, $broker)
    {
        return $query->where('broker', $broker);
    }

    // Scope for transactions by year
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    // Calculate net amount after fees and taxes
    public function getNetAmountAttribute()
    {
        if (in_array($this->transaction_type, ['buy', 'dividend_reinvestment'])) {
            return $this->total_amount + $this->fees + $this->taxes;
        }

        return $this->total_amount - $this->fees - $this->taxes;
    }

    // Calculate effective price per share including fees
    public function getEffectivePricePerShareAttribute()
    {
        if ($this->quantity == 0) {
            return 0;
        }

        return $this->net_amount / $this->quantity;
    }

    // Check if transaction is a purchase
    public function getIsPurchaseAttribute()
    {
        return in_array($this->transaction_type, ['buy', 'dividend_reinvestment', 'transfer_in']);
    }

    // Check if transaction is a sale
    public function getIsSaleAttribute()
    {
        return in_array($this->transaction_type, ['sell', 'transfer_out']);
    }

    // Check if transaction affects share count
    public function getAffectsShareCountAttribute()
    {
        return ! in_array($this->transaction_type, ['stock_split', 'stock_dividend']);
    }

    // Get transaction impact on portfolio (positive for buys, negative for sells)
    public function getPortfolioImpactAttribute()
    {
        if ($this->is_purchase) {
            return $this->quantity;
        }

        if ($this->is_sale) {
            return -$this->quantity;
        }

        return 0;
    }
}
