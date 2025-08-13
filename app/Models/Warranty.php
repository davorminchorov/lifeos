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
        return $query->where('warranty_expiration_date', '<=', now()->addDays($days))
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
        return now()->diffInDays($this->warranty_expiration_date, false);
    }

    // Get warranty remaining percentage
    public function getWarrantyRemainingPercentageAttribute()
    {
        $totalDays = $this->purchase_date->diffInDays($this->warranty_expiration_date);
        $remainingDays = max(0, now()->diffInDays($this->warranty_expiration_date, false));

        return $totalDays > 0 ? ($remainingDays / $totalDays) * 100 : 0;
    }

    // Check if warranty has claims
    public function getHasClaimsAttribute()
    {
        return !empty($this->claim_history);
    }

    // Get total number of claims
    public function getTotalClaimsAttribute()
    {
        return is_array($this->claim_history) ? count($this->claim_history) : 0;
    }
}
