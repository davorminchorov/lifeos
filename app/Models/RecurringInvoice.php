<?php

namespace App\Models;

use App\Enums\BillingInterval;
use App\Enums\RecurringStatus;
use App\Enums\TaxBehavior;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recurring Invoice Model
 *
 * Represents a template for automatically generating invoices on a schedule.
 * Supports various billing intervals and lifecycle management (active, paused, cancelled).
 *
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property string $name Recurring invoice name
 * @property string|null $description Description
 * @property BillingInterval $billing_interval Frequency (daily, weekly, monthly, yearly)
 * @property int $interval_count Number of intervals between invoices (e.g., 2 for bi-weekly)
 * @property RecurringStatus $status Current status (active, paused, cancelled, completed)
 * @property string $currency Three-letter currency code
 * @property TaxBehavior $tax_behavior Tax calculation mode
 * @property int $net_terms_days Payment terms in days
 * @property \Carbon\Carbon $start_date When recurring billing starts
 * @property \Carbon\Carbon|null $end_date Optional end date
 * @property \Carbon\Carbon|null $next_billing_date Next scheduled invoice generation
 * @property int|null $billing_day_of_month Specific day for monthly billing
 * @property int|null $occurrences_limit Maximum number of invoices to generate
 * @property int $occurrences_count Number of invoices generated so far
 * @property bool $auto_send_email Automatically email generated invoices
 * @property int|null $days_before_due Days before due date to send reminder
 * @property string|null $notes Customer-facing notes
 * @property array|null $metadata Additional metadata
 * @property \Carbon\Carbon|null $last_generated_at When last invoice was generated
 * @property \Carbon\Carbon|null $paused_at When recurring invoice was paused
 * @property \Carbon\Carbon|null $cancelled_at When recurring invoice was cancelled
 * @property \Carbon\Carbon|null $completed_at When recurring invoice completed
 */
class RecurringInvoice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'name',
        'description',
        'billing_interval',
        'interval_count',
        'status',
        'currency',
        'tax_behavior',
        'net_terms_days',
        'start_date',
        'end_date',
        'next_billing_date',
        'billing_day_of_month',
        'occurrences_limit',
        'occurrences_count',
        'auto_send_email',
        'days_before_due',
        'notes',
        'metadata',
        'last_generated_at',
        'paused_at',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_interval' => BillingInterval::class,
            'status' => RecurringStatus::class,
            'tax_behavior' => TaxBehavior::class,
            'interval_count' => 'integer',
            'net_terms_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_billing_date' => 'date',
            'billing_day_of_month' => 'integer',
            'occurrences_limit' => 'integer',
            'occurrences_count' => 'integer',
            'auto_send_email' => 'boolean',
            'days_before_due' => 'integer',
            'metadata' => 'array',
            'last_generated_at' => 'datetime',
            'paused_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this recurring invoice.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer this recurring invoice bills.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all line items for this recurring invoice template.
     * Items are automatically ordered by sort_order.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Get all invoices that were generated from this recurring invoice.
     *
     * @return HasMany
     */
    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    /**
     * Scope a query to only include active recurring invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', RecurringStatus::ACTIVE);
    }

    /**
     * Scope a query to only include recurring invoices ready for generation.
     * Checks for active status and next_billing_date <= today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForGeneration($query)
    {
        return $query->where('status', RecurringStatus::ACTIVE)
                    ->whereDate('next_billing_date', '<=', now());
    }

    /**
     * Check if this recurring invoice is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === RecurringStatus::ACTIVE;
    }

    /**
     * Check if the occurrence limit has been reached.
     * Returns false if no limit is set.
     *
     * @return bool
     */
    public function hasReachedLimit(): bool
    {
        if ($this->occurrences_limit === null) {
            return false;
        }

        return $this->occurrences_count >= $this->occurrences_limit;
    }

    /**
     * Check if the recurring invoice has passed its end date.
     * Returns false if no end date is set.
     *
     * @return bool
     */
    public function hasPassedEndDate(): bool
    {
        if ($this->end_date === null) {
            return false;
        }

        return now()->gt($this->end_date);
    }

    /**
     * Pause this recurring invoice.
     * Prevents new invoices from being generated until resumed.
     *
     * @return void
     */
    public function pause(): void
    {
        $this->update([
            'status' => RecurringStatus::PAUSED,
            'paused_at' => now(),
        ]);
    }

    /**
     * Resume this recurring invoice from paused state.
     * Re-enables automatic invoice generation.
     *
     * @return void
     */
    public function resume(): void
    {
        $this->update([
            'status' => RecurringStatus::ACTIVE,
            'paused_at' => null,
        ]);
    }

    /**
     * Cancel this recurring invoice.
     * Prevents any future invoice generation. Cannot be resumed.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->update([
            'status' => RecurringStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Mark this recurring invoice as completed.
     * Used when all scheduled invoices have been generated.
     *
     * @return void
     */
    public function complete(): void
    {
        $this->update([
            'status' => RecurringStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
