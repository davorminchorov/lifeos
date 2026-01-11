<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvestment extends Model
{
    use HasFactory;

    /**
     * Cached CurrencyService instance.
     */
    private ?CurrencyService $currencyServiceCache = null;

    /**
     * Get the CurrencyService instance, caching it for reuse.
     */
    private function getCurrencyService(): CurrencyService
    {
        if ($this->currencyServiceCache === null) {
            $this->currencyServiceCache = app(CurrencyService::class);
        }

        return $this->currencyServiceCache;
    }

    protected $fillable = [
        'user_id',
        'name',
        'project_type',
        'stage',
        'business_model',
        'website_url',
        'repository_url',
        'equity_percentage',
        'investment_amount',
        'currency',
        'current_value',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'equity_percentage' => 'decimal:2',
            'investment_amount' => 'decimal:2',
            'current_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active projects
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope by stage
    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    // Scope by business model
    public function scopeByBusinessModel($query, $model)
    {
        return $query->where('business_model', $model);
    }

    // Calculate gain/loss
    public function getGainLossAttribute()
    {
        if ($this->current_value === null) {
            return 0;
        }

        return $this->current_value - $this->investment_amount;
    }

    // Calculate gain/loss percentage
    public function getGainLossPercentageAttribute(): float
    {
        $investmentAmount = (float) $this->investment_amount;
        if ($investmentAmount === 0.0) {
            return 0.0;
        }

        return ((float) $this->gain_loss / $investmentAmount) * 100;
    }

    // Get formatted investment amount with currency
    public function getFormattedInvestmentAmountAttribute()
    {
        return $this->getCurrencyService()->format($this->investment_amount, $this->currency);
    }

    // Get formatted current value with currency
    public function getFormattedCurrentValueAttribute()
    {
        return $this->getCurrencyService()->format($this->current_value ?? $this->investment_amount, $this->currency);
    }

    // Get formatted gain/loss with currency
    public function getFormattedGainLossAttribute()
    {
        return $this->getCurrencyService()->format($this->gain_loss, $this->currency);
    }

    // Check if project is active
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Get stage display label
    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'idea' => 'Idea',
            'prototype' => 'Prototype',
            'mvp' => 'MVP',
            'growth' => 'Growth',
            'mature' => 'Mature',
            default => ucfirst($this->stage ?? 'Unknown'),
        };
    }

    // Get business model display label
    public function getBusinessModelLabelAttribute(): string
    {
        return match ($this->business_model) {
            'subscription' => 'Subscription',
            'ads' => 'Advertising',
            'one-time' => 'One-time Purchase',
            'freemium' => 'Freemium',
            default => ucfirst($this->business_model ?? 'Unknown'),
        };
    }

    // Get status display label
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'completed' => 'Completed',
            'sold' => 'Sold',
            'abandoned' => 'Abandoned',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }
}
