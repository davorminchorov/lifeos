<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentDividend extends Model
{
    /** @use HasFactory<\Database\Factories\InvestmentDividendFactory> */
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'amount',
        'record_date',
        'payment_date',
        'ex_dividend_date',
        'dividend_type',
        'frequency',
        'dividend_per_share',
        'shares_held',
        'tax_withheld',
        'currency',
        'reinvested',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'record_date' => 'date',
            'payment_date' => 'date',
            'ex_dividend_date' => 'date',
            'dividend_per_share' => 'decimal:8',
            'shares_held' => 'decimal:8',
            'tax_withheld' => 'decimal:2',
            'reinvested' => 'boolean',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    // Scope for dividends by type
    public function scopeByType($query, $type)
    {
        return $query->where('dividend_type', $type);
    }

    // Scope for dividends by year
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('payment_date', $year);
    }

    // Scope for dividends within date range
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Scope for reinvested dividends
    public function scopeReinvested($query, $reinvested = true)
    {
        return $query->where('reinvested', $reinvested);
    }

    // Calculate net dividend amount after taxes
    public function getNetAmountAttribute()
    {
        return $this->amount - $this->tax_withheld;
    }

    // Calculate dividend yield based on shares held and dividend per share
    public function getDividendYieldAttribute()
    {
        if ($this->shares_held == 0 || !$this->investment) {
            return 0;
        }

        $costBasis = $this->shares_held * $this->investment->purchase_price;

        if ($costBasis == 0) {
            return 0;
        }

        return ($this->amount / $costBasis) * 100;
    }

    // Check if dividend is qualified for tax purposes
    public function getIsQualifiedAttribute()
    {
        return in_array($this->dividend_type, ['ordinary', 'qualified']);
    }
}
