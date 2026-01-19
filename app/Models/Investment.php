<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    /** @use HasFactory<\Database\Factories\InvestmentFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'investment_type',
        'symbol_identifier',
        'name',
        'quantity',
        'purchase_date',
        'purchase_price',
        'currency',
        'current_value',
        'total_dividends_received',
        'total_fees_paid',
        'investment_goals',
        'risk_tolerance',
        'account_broker',
        'account_number',
        'transaction_history',
        'tax_lots',
        'target_allocation_percentage',
        'last_price_update',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:8',
            'current_value' => 'decimal:8',
            'total_dividends_received' => 'decimal:2',
            'total_fees_paid' => 'decimal:2',
            'investment_goals' => 'array',
            'transaction_history' => 'array',
            'tax_lots' => 'array',
            'target_allocation_percentage' => 'decimal:2',
            'last_price_update' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(InvestmentDividend::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    // Scope for active investments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope by investment type
    public function scopeByType($query, $type)
    {
        return $query->where('investment_type', $type);
    }

    // Scope by risk tolerance
    public function scopeByRiskTolerance($query, $risk)
    {
        return $query->where('risk_tolerance', $risk);
    }

    // Calculate total investment cost basis
    public function getTotalCostBasisAttribute()
    {
        $quantity = $this->quantity ?? 0;
        $purchasePrice = $this->purchase_price ?? 0;

        return ($quantity * $purchasePrice) + $this->total_fees_paid;
    }

    // Calculate current market value
    public function getCurrentMarketValueAttribute()
    {
        $quantity = $this->quantity ?? 0;

        return $quantity * ($this->current_value ?? $this->purchase_price ?? 0);
    }

    // Calculate unrealized gain/loss
    public function getUnrealizedGainLossAttribute()
    {
        return $this->current_market_value - $this->total_cost_basis;
    }

    // Calculate unrealized gain/loss percentage
    public function getUnrealizedGainLossPercentageAttribute()
    {
        if ($this->total_cost_basis == 0) {
            return 0;
        }

        return ($this->unrealized_gain_loss / $this->total_cost_basis) * 100;
    }

    // Calculate total return including dividends
    public function getTotalReturnAttribute()
    {
        return $this->unrealized_gain_loss + $this->total_dividends_received;
    }

    // Calculate total return percentage
    public function getTotalReturnPercentageAttribute()
    {
        if ($this->total_cost_basis == 0) {
            return 0;
        }

        return ($this->total_return / $this->total_cost_basis) * 100;
    }

    // Calculate holding period in days
    public function getHoldingPeriodDaysAttribute()
    {
        return $this->purchase_date->diffInDays(now());
    }

    // Calculate annualized return
    public function getAnnualizedReturnAttribute()
    {
        $holdingPeriodYears = $this->holding_period_days / 365.25;

        if ($holdingPeriodYears <= 0) {
            return 0;
        }

        return (pow(1 + ($this->total_return_percentage / 100), 1 / $holdingPeriodYears) - 1) * 100;
    }

    // Get formatted purchase price with currency
    public function getFormattedPurchasePriceAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $this->purchase_price !== null ? $currencyService->format($this->purchase_price) : 'N/A';
    }

    // Get formatted current value with currency
    public function getFormattedCurrentValueAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->current_value ?? $this->purchase_price ?? 0);
    }

    // Get formatted current market value with currency
    public function getFormattedCurrentMarketValueAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->current_market_value);
    }

    // Get formatted unrealized gain/loss with currency
    public function getFormattedUnrealizedGainLossAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->unrealized_gain_loss);
    }

    // Get formatted total return with currency
    public function getFormattedTotalReturnAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->total_return);
    }
}
