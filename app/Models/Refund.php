<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    /** @use HasFactory<\Database\Factories\RefundFactory> */
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'amount',
        'currency',
        'provider',
        'provider_refund_id',
        'status',
        'reason',
        'reason_notes',
        'processed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'processed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function getFormattedAmountAttribute(): string
    {
        $amount = $this->amount / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
