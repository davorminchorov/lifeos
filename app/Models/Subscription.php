<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'amount',
        'currency',
        'billing_cycle',
        'start_date',
        'end_date',
        'status',
        'website',
        'category',
        'next_payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_payment_date' => 'date',
    ];

    /**
     * Get the payments for the subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
