<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'category',
        'subcategory',
        'expense_date',
        'description',
        'merchant',
        'payment_method',
        'receipt_attachments',
        'tags',
        'location',
        'is_tax_deductible',
        'expense_type',
        'is_recurring',
        'recurring_schedule',
        'budget_allocated',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'receipt_attachments' => 'array',
            'tags' => 'array',
            'is_tax_deductible' => 'boolean',
            'is_recurring' => 'boolean',
            'budget_allocated' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for expenses in date range
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    // Scope by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope for business expenses
    public function scopeBusiness($query)
    {
        return $query->where('expense_type', 'business');
    }

    // Scope for personal expenses
    public function scopePersonal($query)
    {
        return $query->where('expense_type', 'personal');
    }

    // Scope for tax deductible expenses
    public function scopeTaxDeductible($query)
    {
        return $query->where('is_tax_deductible', true);
    }

    // Scope for recurring expenses
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    // Scope for current month expenses
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month);
    }

    // Scope for current year expenses
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    // Check if expense has receipts
    public function getHasReceiptsAttribute()
    {
        return ! empty($this->receipt_attachments);
    }

    // Get formatted amount with currency
    public function getFormattedAmountAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->amount, $this->currency);
    }

    // Check if expense is over budget (if budget allocated)
    public function getIsOverBudgetAttribute()
    {
        return $this->budget_allocated && $this->amount > $this->budget_allocated;
    }

    // Get budget variance
    public function getBudgetVarianceAttribute()
    {
        if (! $this->budget_allocated) {
            return null;
        }

        return $this->amount - $this->budget_allocated;
    }

    // Get age of expense in days
    public function getAgeDaysAttribute()
    {
        return (int) round($this->expense_date->startOfDay()->diffInDays(now()->startOfDay()));
    }

    // Check if expense is reimbursed
    public function getIsReimbursedAttribute()
    {
        return $this->status === 'reimbursed';
    }
}
