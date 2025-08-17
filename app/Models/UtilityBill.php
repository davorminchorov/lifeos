<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityBill extends Model
{
    /** @use HasFactory<\Database\Factories\UtilityBillFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'utility_type',
        'service_provider',
        'account_number',
        'service_address',
        'bill_amount',
        'usage_amount',
        'usage_unit',
        'rate_per_unit',
        'bill_period_start',
        'bill_period_end',
        'due_date',
        'payment_status',
        'payment_date',
        'meter_readings',
        'bill_attachments',
        'service_plan',
        'contract_terms',
        'auto_pay_enabled',
        'usage_history',
        'budget_alert_threshold',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'bill_amount' => 'decimal:2',
            'usage_amount' => 'decimal:4',
            'rate_per_unit' => 'decimal:6',
            'bill_period_start' => 'date',
            'bill_period_end' => 'date',
            'due_date' => 'date',
            'payment_date' => 'date',
            'meter_readings' => 'array',
            'bill_attachments' => 'array',
            'auto_pay_enabled' => 'boolean',
            'usage_history' => 'array',
            'budget_alert_threshold' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope by utility type
    public function scopeByType($query, $type)
    {
        return $query->where('utility_type', $type);
    }

    // Scope for pending bills
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    // Scope for overdue bills
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('payment_status', 'pending')
                    ->where('due_date', '<', now());
            });
    }

    // Scope for paid bills
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Scope for bills due soon
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->where('payment_status', 'pending');
    }

    // Scope for current month bills
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('bill_period_start', now()->year)
            ->whereMonth('bill_period_start', now()->month);
    }

    // Check if bill is overdue
    public function getIsOverdueAttribute()
    {
        return $this->payment_status === 'pending' && $this->due_date->isPast();
    }

    // Get days until due date
    public function getDaysUntilDueAttribute()
    {
        return (int) round(now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false));
    }

    // Check if usage is over budget threshold
    public function getIsOverBudgetAttribute()
    {
        return $this->budget_alert_threshold && $this->bill_amount > $this->budget_alert_threshold;
    }

    // Calculate cost per day for the billing period
    public function getCostPerDayAttribute()
    {
        $periodDays = $this->bill_period_start->diffInDays($this->bill_period_end);

        return $periodDays > 0 ? $this->bill_amount / $periodDays : 0;
    }

    // Get usage efficiency (cost per unit if available)
    public function getUsageEfficiencyAttribute()
    {
        if (! $this->usage_amount || $this->usage_amount == 0) {
            return null;
        }

        return $this->bill_amount / $this->usage_amount;
    }

    // Compare with previous month usage (if history available)
    public function getUsageComparisonAttribute()
    {
        if (! $this->usage_history || ! is_array($this->usage_history) || count($this->usage_history) < 2) {
            return null;
        }

        $history = $this->usage_history;
        $previousUsage = end($history);
        $currentUsage = $this->usage_amount;

        if (! $previousUsage || $previousUsage == 0) {
            return null;
        }

        return (($currentUsage - $previousUsage) / $previousUsage) * 100;
    }

    // Get billing period length in days
    public function getBillingPeriodDaysAttribute()
    {
        return $this->bill_period_start->diffInDays($this->bill_period_end);
    }
}
