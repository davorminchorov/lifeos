<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warranty extends Model
{
    /** @use HasFactory<\Database\Factories\WarrantyFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'currency',
        'retailer',
        'warranty_duration_months',
        'warranty_type',
        'warranty_terms',
        'warranty_expiration_date',
        'claim_history',
        'receipt_attachments',
        'proof_of_purchase_attachments',
        'current_status',
        'transfer_history',
        'maintenance_reminders',
        'notes',
        'file_attachments',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
            'warranty_duration_months' => 'integer',
            'warranty_expiration_date' => 'date',
            'claim_history' => 'array',
            'receipt_attachments' => 'array',
            'proof_of_purchase_attachments' => 'array',
            'transfer_history' => 'array',
            'maintenance_reminders' => 'array',
            'file_attachments' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active warranties
    public function scopeActive($query)
    {
        return $query->where('current_status', 'active');
    }

    // Scope for warranties expiring soon
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('warranty_expiration_date', '<=', now()->addDays((int) $days))
            ->where('current_status', 'active');
    }

    // Scope for expired warranties
    public function scopeExpired($query)
    {
        return $query->where('warranty_expiration_date', '<', now());
    }

    // Check if warranty is expired
    public function getIsExpiredAttribute()
    {
        return $this->warranty_expiration_date->isPast();
    }

    // Get days until warranty expires
    public function getDaysUntilExpirationAttribute()
    {
        return (int) round(now()->startOfDay()->diffInDays($this->warranty_expiration_date->startOfDay(), false));
    }

    // Get warranty remaining percentage
    public function getWarrantyRemainingPercentageAttribute()
    {
        $totalDays = $this->purchase_date->startOfDay()->diffInDays($this->warranty_expiration_date->startOfDay());
        $remainingDays = max(0, now()->startOfDay()->diffInDays($this->warranty_expiration_date->startOfDay(), false));

        return $totalDays > 0 ? round(($remainingDays / $totalDays) * 100, 1) : 0;
    }

    // Check if warranty has claims
    public function getHasClaimsAttribute()
    {
        return ! empty($this->claim_history);
    }

    // Get total number of claims
    public function getTotalClaimsAttribute()
    {
        return is_array($this->claim_history) ? count($this->claim_history) : 0;
    }

    // Get formatted purchase price with currency
    public function getFormattedPurchasePriceAttribute()
    {
        if (!$this->purchase_price) {
            return null;
        }

        $currency = $this->currency ?? config('currency.default', 'MKD');
        return app(\App\Services\CurrencyService::class)->format($this->purchase_price, $currency);
    }
}
