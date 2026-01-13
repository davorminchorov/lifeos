<?php

namespace App\Models;

use App\Enums\CreditNoteStatus;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    /** @use HasFactory<\Database\Factories\CreditNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'invoice_id',
        'number',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'amount_remaining',
        'reason',
        'reason_notes',
        'issued_at',
        'pdf_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => CreditNoteStatus::class,
            'subtotal' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'amount_remaining' => 'integer',
            'issued_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CreditNoteApplication::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', CreditNoteStatus::DRAFT);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', CreditNoteStatus::ISSUED);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total);
    }

    public function getFormattedAmountRemainingAttribute(): string
    {
        return $this->formatMoney($this->amount_remaining);
    }

    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
