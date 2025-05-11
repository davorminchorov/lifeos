<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionTag extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Get the subscriptions for the tag.
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Subscriptions\Models\Subscription::class,
            'subscription_tag',
            'tag_id',
            'subscription_id'
        )->withTimestamps();
    }
}
