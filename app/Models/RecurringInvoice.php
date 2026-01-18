<?php

namespace App\Models;

use App\Enums\BillingInterval;
use App\Enums\RecurringStatus;
use App\Enums\TaxBehavior;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoice extends Model
{
    use HasFactory;

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
        return $this->hasMany(RecurringInvoiceItem::class)->orderBy('sort_order');
    }

    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    /**
     * Scope: Active recurring invoices
     */
    public function scopeActive($query)
    {
        return $query->where('status', RecurringStatus::ACTIVE);
    }

    /**
     * Scope: Recurring invoices due for generation
     */
    public function scopeDueForGeneration($query)
    {
        return $query->where('status', RecurringStatus::ACTIVE)
                    ->whereDate('next_billing_date', '<=', now());
    }

    /**
     * Check if recurring invoice is active
     */
    public function isActive(): bool
    {
        return $this->status === RecurringStatus::ACTIVE;
    }

    /**
     * Check if recurring invoice has reached occurrence limit
     */
    public function hasReachedLimit(): bool
    {
        if ($this->occurrences_limit === null) {
            return false;
        }

        return $this->occurrences_count >= $this->occurrences_limit;
    }

    /**
     * Check if recurring invoice has passed end date
     */
    public function hasPassedEndDate(): bool
    {
        if ($this->end_date === null) {
            return false;
        }

        return now()->gt($this->end_date);
    }

    /**
     * Pause recurring invoice
     */
    public function pause(): void
    {
        $this->update([
            'status' => RecurringStatus::PAUSED,
            'paused_at' => now(),
        ]);
    }

    /**
     * Resume recurring invoice
     */
    public function resume(): void
    {
        $this->update([
            'status' => RecurringStatus::ACTIVE,
            'paused_at' => null,
        ]);
    }

    /**
     * Cancel recurring invoice
     */
    public function cancel(): void
    {
        $this->update([
            'status' => RecurringStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Mark recurring invoice as completed
     */
    public function complete(): void
    {
        $this->update([
            'status' => RecurringStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }
}
