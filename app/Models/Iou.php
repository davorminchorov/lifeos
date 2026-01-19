<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Iou extends Model
{
    /** @use HasFactory<\Database\Factories\IouFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'person_name',
        'amount',
        'currency',
        'transaction_date',
        'due_date',
        'description',
        'notes',
        'status',
        'amount_paid',
        'payment_method',
        'category',
        'attachments',
        'is_recurring',
        'recurring_schedule',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'transaction_date' => 'date',
            'due_date' => 'date',
            'attachments' => 'array',
            'is_recurring' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for IOUs where I owe money
    public function scopeOwe($query)
    {
        return $query->where('type', 'owe');
    }

    // Scope for IOUs where someone owes me
    public function scopeOwed($query)
    {
        return $query->where('type', 'owed');
    }

    // Scope for pending IOUs
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for paid IOUs
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Scope for overdue IOUs
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partially_paid']);
    }

    // Scope for IOUs by person
    public function scopeByPerson($query, $personName)
    {
        return $query->where('person_name', $personName);
    }

    // Get remaining balance
    public function getRemainingBalanceAttribute()
    {
        return $this->amount - ($this->amount_paid ?? 0);
    }

    // Get remaining amount (alias for remaining_balance)
    public function getRemainingAmountAttribute()
    {
        return $this->remaining_balance;
    }

    // Get formatted amount with currency
    public function getFormattedAmountAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->amount, $this->currency);
    }

    // Get formatted amount paid
    public function getFormattedAmountPaidAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->amount_paid ?? 0, $this->currency);
    }

    // Get formatted remaining balance
    public function getFormattedRemainingBalanceAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);

        return $currencyService->format($this->remaining_balance, $this->currency);
    }

    // Get formatted remaining amount (alias for formatted_remaining_balance)
    public function getFormattedRemainingAmountAttribute()
    {
        return $this->formatted_remaining_balance;
    }

    // Get formatted amount in MKD
    public function getFormattedAmountMkdAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);
        $amountInMKD = $currencyService->convertToDefault($this->amount, $this->currency ?? 'MKD');

        return $currencyService->format($amountInMKD);
    }

    // Get formatted remaining balance in MKD
    public function getFormattedRemainingBalanceMkdAttribute()
    {
        $currencyService = app(\App\Services\CurrencyService::class);
        $balanceInMKD = $currencyService->convertToDefault($this->remaining_balance, $this->currency ?? 'MKD');

        return $currencyService->format($balanceInMKD);
    }

    // Check if IOU is overdue
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && in_array($this->status, ['pending', 'partially_paid']);
    }

    // Check if IOU is fully paid
    public function getIsFullyPaidAttribute()
    {
        return $this->amount_paid >= $this->amount;
    }

    // Get payment progress percentage
    public function getPaymentProgressAttribute()
    {
        if ($this->amount == 0) {
            return 0;
        }

        return min(100, round((($this->amount_paid ?? 0) / $this->amount) * 100, 2));
    }

    // Get payment percentage (alias for payment_progress)
    public function getPaymentPercentageAttribute()
    {
        return $this->payment_progress;
    }

    // Get days until due
    public function getDaysUntilDueAttribute()
    {
        if (! $this->due_date) {
            return null;
        }

        return (int) round(now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false));
    }

    // Get status badge color
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => $this->is_overdue ? 'red' : 'yellow',
            'partially_paid' => 'blue',
            'paid' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    // Get type label
    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'owe' => 'I Owe',
            'owed' => 'Owed to Me',
            default => $this->type,
        };
    }
}
