<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Payment Model
 *
 * Represents a payment transaction against an invoice.
 * Tracks payment status, provider details, and supports refunds.
 *
 * @property int $id
 * @property int $user_id
 * @property int $invoice_id
 * @property string $provider Payment provider (e.g., 'manual', 'stripe')
 * @property string|null $provider_payment_id External payment ID from provider
 * @property int $amount Payment amount in cents
 * @property string $currency Three-letter currency code
 * @property PaymentStatus $status Payment status (pending, succeeded, failed)
 * @property \Carbon\Carbon|null $attempted_at When payment was attempted
 * @property \Carbon\Carbon|null $succeeded_at When payment succeeded
 * @property \Carbon\Carbon|null $failed_at When payment failed
 * @property string|null $failure_code Provider failure code
 * @property string|null $failure_message Human-readable failure message
 * @property string|null $payment_method Payment method type
 * @property array|null $payment_method_details Additional payment method details
 * @property \Carbon\Carbon|null $payment_date Date payment was made
 * @property string|null $reference External reference number
 * @property array|null $metadata Additional metadata
 * @property string|null $notes Internal notes about the payment
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'provider',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
        'attempted_at',
        'succeeded_at',
        'failed_at',
        'failure_code',
        'failure_message',
        'payment_method',
        'payment_method_details',
        'payment_date',
        'reference',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'attempted_at' => 'datetime',
            'succeeded_at' => 'datetime',
            'failed_at' => 'datetime',
            'payment_date' => 'date',
            'payment_method_details' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns this payment.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice this payment belongs to.
     *
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get all refunds issued for this payment.
     *
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Scope a query to only include succeeded payments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSucceeded($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    /**
     * Scope a query to only include failed payments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    /**
     * Get the formatted payment amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        $amount = $this->amount / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }

    /**
     * Check if this payment can be refunded.
     * Payments must be succeeded and have a positive amount.
     *
     * @return bool
     */
    public function getIsRefundableAttribute(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED && $this->amount > 0;
    }
}
