<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution ['source' => 'agent'|'user', 'agent_token_id' => ?int]
     */
    public function create(User $user, array $data, array $attribution = []): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->refresh();
    }

    public function delete(Subscription $subscription): bool
    {
        return (bool) $subscription->delete();
    }

    public function cancel(Subscription $subscription): Subscription
    {
        return $this->update($subscription, [
            'status' => 'cancelled',
            'cancellation_date' => now(),
        ]);
    }

    public function pause(Subscription $subscription): Subscription
    {
        return $this->update($subscription, ['status' => 'paused']);
    }

    public function resume(Subscription $subscription): Subscription
    {
        return $this->update($subscription, ['status' => 'active']);
    }
}
