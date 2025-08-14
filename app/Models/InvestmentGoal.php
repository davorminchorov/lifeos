<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_amount',
        'current_progress',
        'target_date',
        'status',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_progress' => 'decimal:2',
            'target_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Calculate progress percentage
    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }

        return min(100, ($this->current_progress / $this->target_amount) * 100);
    }

    // Check if goal is achieved
    public function getIsAchievedAttribute()
    {
        return $this->current_progress >= $this->target_amount;
    }

    // Calculate remaining amount
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->target_amount - $this->current_progress);
    }

    // Scope for active goals
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for achieved goals
    public function scopeAchieved($query)
    {
        return $query->whereColumn('current_progress', '>=', 'target_amount');
    }
}
