<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    /** @use HasFactory<\Database\Factories\ContractFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'contract_type',
        'title',
        'counterparty',
        'start_date',
        'end_date',
        'notice_period_days',
        'auto_renewal',
        'contract_value',
        'payment_terms',
        'key_obligations',
        'penalties',
        'termination_clauses',
        'document_attachments',
        'performance_rating',
        'renewal_history',
        'amendments',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'notice_period_days' => 'integer',
            'auto_renewal' => 'boolean',
            'contract_value' => 'float',
            'performance_rating' => 'integer',
            'document_attachments' => 'array',
            'renewal_history' => 'array',
            'amendments' => 'array',
            'file_attachments' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active contracts
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for contracts expiring soon
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('end_date', '<=', now()->addDays((int) $days))
            ->where('status', 'active')
            ->whereNotNull('end_date');
    }

    // Scope for contracts requiring notice
    public function scopeRequiringNotice($query)
    {
        // Get all active contracts with notice periods and filter in PHP
        // This approach is needed because the date calculation varies by contract
        $contracts = $query->whereNotNull('notice_period_days')
            ->whereNotNull('end_date')
            ->where('status', 'active')
            ->get();

        $contractIds = $contracts->filter(function ($contract) {
            return $contract->end_date <= now()->addDays($contract->notice_period_days);
        })->pluck('id');

        return $query->whereIn('id', $contractIds);
    }

    // Check if contract is expired
    public function getIsExpiredAttribute()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    // Get days until expiration
    public function getDaysUntilExpirationAttribute()
    {
        return $this->end_date ? (int) round(now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false)) : null;
    }

    // Get notice deadline
    public function getNoticeDeadlineAttribute()
    {
        if (! $this->end_date || ! $this->notice_period_days) {
            return null;
        }

        return $this->end_date->subDays($this->notice_period_days);
    }

    // Get formatted contract value with currency
    public function getFormattedContractValueAttribute()
    {
        if (! $this->contract_value) {
            return null;
        }

        $currency = $this->currency ?? config('currency.default', 'MKD');

        return app(\App\Services\CurrencyService::class)->format($this->contract_value, $currency);
    }
}
