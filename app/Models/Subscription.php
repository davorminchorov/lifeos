<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_name',
        'description',
        'category',
        'cost',
        'billing_cycle',
        'billing_cycle_days',
        'currency',
        'start_date',
        'next_billing_date',
        'cancellation_date',
        'payment_method',
        'merchant_info',
        'auto_renewal',
        'cancellation_difficulty',
        'price_history',
        'notes',
        'tags',
        'status',
        'file_attachments',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'float',
            'billing_cycle_days' => 'integer',
            'start_date' => 'date',
            'next_billing_date' => 'date',
            'cancellation_date' => 'date',
            'auto_renewal' => 'boolean',
            'cancellation_difficulty' => 'integer',
            'price_history' => 'array',
            'tags' => 'array',
            'file_attachments' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active subscriptions
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for subscriptions due soon
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('next_billing_date', '<=', now()->addDays((int) $days))
            ->where('status', 'active');
    }

    // Calculate monthly cost for different billing cycles
    public function getMonthlyCostAttribute()
    {
        return match ($this->billing_cycle) {
            'monthly' => $this->cost,
            'yearly' => $this->cost / 12,
            'weekly' => $this->cost * 4.33,
            'custom' => $this->billing_cycle_days ? ($this->cost * 30.44) / $this->billing_cycle_days : 0,
            default => 0,
        };
    }

    // Calculate yearly cost
    public function getYearlyCostAttribute()
    {
        return $this->monthly_cost * 12;
    }
}
