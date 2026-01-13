<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
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
            'payment_method_details' => 'array',
            'metadata' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    public function getFormattedAmountAttribute(): string
    {
        $amount = $this->amount / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }

    public function getIsRefundableAttribute(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED && $this->amount > 0;
    }
}
