<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'category',
        'budget_period',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'is_active',
        'rollover_unused',
        'alert_threshold',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'alert_threshold' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'rollover_unused' => 'boolean',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get current period spending for this budget.
     */
    public function getCurrentSpending()
    {
        return Expense::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('user_id', $this->user_id)
            ->where('tenant_id', $this->tenant_id)
            ->where('category', $this->category)
            ->whereBetween('expense_date', [$this->start_date, $this->end_date])
            ->sum('amount');
    }

    /**
     * Get remaining budget amount.
     */
    public function getRemainingAmount()
    {
        return max(0, $this->amount - $this->getCurrentSpending());
    }

    /**
     * Get budget utilization percentage.
     */
    public function getUtilizationPercentage()
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->getCurrentSpending() / $this->amount) * 100, 2);
    }

    /**
     * Check if budget is over the alert threshold.
     */
    public function isOverThreshold()
    {
        return $this->getUtilizationPercentage() >= $this->alert_threshold;
    }

    /**
     * Check if budget is exceeded.
     */
    public function isExceeded()
    {
        return $this->getCurrentSpending() > $this->amount;
    }

    /**
     * Get budget status.
     */
    public function getStatus()
    {
        $utilization = $this->getUtilizationPercentage();

        if ($utilization >= 100) {
            return 'exceeded';
        } elseif ($utilization >= $this->alert_threshold) {
            return 'warning';
        } else {
            return 'on_track';
        }
    }

    /**
     * Scope for active budgets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current period budgets.
     */
    public function scopeCurrent($query)
    {
        $now = now();

        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope for specific category.
     */
    public function scopeForCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
