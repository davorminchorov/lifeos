<?php

namespace App\Models;

use App\Enums\CreditNoteStatus;
use App\Services\CurrencyService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Credit Note Model
 *
 * Represents a credit note issued to a customer.
 * Credit notes can be applied to invoices to reduce amounts due.
 *
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property int|null $invoice_id Original invoice this credit note references
 * @property string $number Credit note number (e.g., CN-2024-00001)
 * @property CreditNoteStatus $status Current status (draft, issued, applied, void)
 * @property string $currency Three-letter currency code
 * @property int $subtotal Subtotal in cents
 * @property int $tax_total Tax amount in cents
 * @property int $total Total credit amount in cents
 * @property int $amount_remaining Remaining unapplied credit in cents
 * @property string|null $reason Reason for credit note issuance
 * @property string|null $reason_notes Additional notes about the reason
 * @property \Carbon\Carbon|null $issued_at When the credit note was issued
 * @property string|null $pdf_path Path to generated PDF file
 * @property array|null $metadata Additional metadata
 */
class CreditNote extends Model
{
    /** @use HasFactory<\Database\Factories\CreditNoteFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
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

    /**
     * Get the user that owns this credit note.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer this credit note belongs to.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the original invoice this credit note references.
     * May be null if the credit note is not tied to a specific invoice.
     *
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get all applications where this credit note was used.
     * Applications track when credit notes are applied to invoices.
     *
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CreditNoteApplication::class);
    }

    /**
     * Scope a query to only include draft credit notes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', CreditNoteStatus::DRAFT);
    }

    /**
     * Scope a query to only include issued credit notes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIssued($query)
    {
        return $query->where('status', CreditNoteStatus::ISSUED);
    }

    /**
     * Get the formatted total amount with currency symbol.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->total);
    }

    /**
     * Get the formatted remaining amount with currency symbol.
     * This is the amount available to apply to future invoices.
     *
     * @return string
     */
    public function getFormattedAmountRemainingAttribute(): string
    {
        return $this->formatMoney($this->amount_remaining);
    }

    /**
     * Format an amount in cents to a currency string.
     *
     * @param int $cents Amount in cents
     * @return string Formatted money string
     */
    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
