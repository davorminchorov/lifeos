<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\TaxBehavior;
use App\Services\CurrencyService;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice Model
 *
 * Represents an invoice in the invoicing system with full lifecycle management.
 * Supports draft creation, issuing, payment tracking, and various status transitions.
 *
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property string|null $number Invoice number (e.g., INV-2024-00001)
 * @property int|null $sequence_year Year of invoice numbering sequence
 * @property int|null $sequence_no Sequential number within year
 * @property string|null $hash Short verification hash
 * @property InvoiceStatus $status Current invoice status
 * @property string $currency Three-letter currency code (e.g., USD, MKD)
 * @property int $subtotal Subtotal in cents before discounts and taxes
 * @property int $discount_total Total discount amount in cents
 * @property int $tax_total Total tax amount in cents
 * @property int $total Final total in cents
 * @property int $amount_due Amount still owed in cents
 * @property int $amount_paid Amount already paid in cents
 * @property TaxBehavior $tax_behavior Tax calculation mode (inclusive or exclusive)
 * @property \Carbon\Carbon|null $issued_at Timestamp when invoice was issued
 * @property \Carbon\Carbon|null $due_at Payment due date
 * @property \Carbon\Carbon|null $paid_at Timestamp when fully paid
 * @property \Carbon\Carbon|null $voided_at Timestamp when voided
 * @property \Carbon\Carbon|null $last_sent_at Last email sent timestamp
 * @property int $net_terms_days Number of days until payment is due
 * @property string|null $notes Customer-facing notes
 * @property string|null $internal_notes Internal notes (not visible to customer)
 * @property string|null $pdf_path Path to generated PDF file
 * @property int|null $subscription_id ID of recurring invoice if applicable
 * @property array|null $metadata Additional metadata
 */
class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
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

    /**
     * Get the user that owns this invoice.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer this invoice belongs to.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all line items for this invoice.
     * Items are automatically ordered by sort_order.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Get all payments recorded against this invoice.
     *
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all credit notes associated with this invoice.
     *
     * @return HasMany
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Get the recurring invoice template if this was auto-generated.
     *
     * @return BelongsTo
     */
    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class, 'subscription_id');
    }

    /**
     * Get all payment reminders sent for this invoice.
     * Ordered by sent date, most recent first.
     *
     * @return HasMany
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(InvoiceReminder::class)->orderBy('sent_at', 'desc');
    }

    /**
     * Scope a query to only include draft invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    /**
     * Scope a query to only include issued invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIssued($query)
    {
        return $query->where('status', InvoiceStatus::ISSUED);
    }

    /**
     * Scope a query to only include unpaid invoices.
     * Includes issued, partially paid, and past due statuses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            InvoiceStatus::ISSUED,
            InvoiceStatus::PARTIALLY_PAID,
            InvoiceStatus::PAST_DUE,
        ]);
    }

    /**
     * Scope a query to only include past due invoices.
     * Includes both marked as past_due and issued invoices past their due date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePastDue($query)
    {
        return $query->where('status', InvoiceStatus::PAST_DUE)
            ->orWhere(function ($q) {
                $q->where('status', InvoiceStatus::ISSUED)
                  ->where('due_at', '<', now());
            });
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
     * Get the formatted amount due with currency symbol.
     *
     * @return string
     */
    public function getFormattedAmountDueAttribute(): string
    {
        return $this->formatMoney($this->amount_due);
    }

    /**
     * Get the formatted subtotal with currency symbol.
     *
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return $this->formatMoney($this->subtotal);
    }

    /**
     * Get the formatted tax total with currency symbol.
     *
     * @return string
     */
    public function getFormattedTaxTotalAttribute(): string
    {
        return $this->formatMoney($this->tax_total);
    }

    /**
     * Get the formatted discount total with currency symbol.
     *
     * @return string
     */
    public function getFormattedDiscountTotalAttribute(): string
    {
        return $this->formatMoney($this->discount_total);
    }

    /**
     * Check if the invoice is fully paid.
     *
     * @return bool
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    /**
     * Check if the invoice is overdue.
     * Returns true if past due date and not fully paid.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && $this->due_at->isPast() && !$this->is_paid;
    }

    /**
     * Get the number of days the invoice is overdue.
     * Returns null if not overdue.
     *
     * @return int|null
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }
        return now()->diffInDays($this->due_at);
    }

    /**
     * Format an amount in cents to a currency string.
     *
     * @param int $cents Amount in cents
     * @return string Formatted money string (e.g., "$10.00", "100,00 MKD")
     */
    protected function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return app(CurrencyService::class)->format($amount, $this->currency);
    }
}
