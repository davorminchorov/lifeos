<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    public function getOutstandingBalanceAttribute(): int
    {
        return $this->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'past_due'])
            ->sum('amount_due');
    }

    public function getCreditBalanceAttribute(): int
    {
        return $this->creditNotes()
            ->where('status', 'issued')
            ->sum('amount_remaining');
    }
}
