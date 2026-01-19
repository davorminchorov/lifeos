<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer Model
 *
 * Represents a customer entity for the invoicing system.
 * Customers can have multiple invoices and credit notes.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $company_name
 * @property array|null $billing_address
 * @property string|null $tax_id
 * @property string|null $tax_country
 * @property string $currency
 * @property int|null $default_payment_method_id
 * @property array|null $metadata
 * @property string|null $notes
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company_name',
        'billing_address',
        'tax_id',
        'tax_country',
        'currency',
        'default_payment_method_id',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns this customer.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all invoices for this customer.
     *
     * @return HasMany
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all credit notes for this customer.
     *
     * @return HasMany
     */
    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Scope a query to search customers by name, email, or company name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    /**
     * Get the total outstanding balance for this customer.
     * Includes all issued, partially paid, and past due invoices.
     *
     * @return int Amount in cents
     */
    public function getOutstandingBalanceAttribute(): int
    {
        return $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->sum('amount_due');
    }

    /**
     * Get the total available credit balance for this customer.
     * Includes all issued credit notes with remaining amounts.
     *
     * @return int Amount in cents
     */
    public function getCreditBalanceAttribute(): int
    {
        return $this->creditNotes()
            ->where('status', 'issued')
            ->sum('amount_remaining');
    }
}
