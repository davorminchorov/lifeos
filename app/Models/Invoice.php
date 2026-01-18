<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\TaxBehavior;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'number',
        'sequence_year',
        'sequence_no',
        'hash',
        'status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'amount_due',
        'amount_paid',
        'tax_behavior',
        'issued_at',
        'due_at',
        'paid_at',
        'voided_at',
        'last_sent_at',
        'net_terms_days',
        'notes',
        'internal_notes',
        'pdf_path',
        'subscription_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'tax_behavior' => TaxBehavior::class,
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'amount_due' => 'integer',
            'amount_paid' => 'integer',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'last_sent_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class, 'subscription_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', InvoiceStatus::ISSUED);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            InvoiceStatus::ISSUED,
            InvoiceStatus::PARTIALLY_PAID,
            InvoiceStatus::PAST_DUE,
        ]);
    }

    public function scopePastDue($query)
    {
        return $query->where('status', InvoiceStatus::PAST_DUE)
            ->orWhere(function ($q) {
                $q->where('status', InvoiceStatus::ISSUED)
                  ->where('due_at', '<', now());
            });
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total);
    }

    public function getFormattedAmountDueAttribute(): string
    {
        return $this->formatMoney($this->amount_due);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return $this->formatMoney($this->subtotal);
    }

    public function getFormattedTaxTotalAttribute(): string
    {
        return $this->formatMoney($this->tax_total);
    }

    public function getFormattedDiscountTotalAttribute(): string
    {
        return $this->formatMoney($this->discount_total);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && $this->due_at->isPast() && !$this->is_paid;
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }
        return now()->diffInDays($this->due_at);
    }

    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
