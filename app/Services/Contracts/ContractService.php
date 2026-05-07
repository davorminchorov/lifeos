<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\User;

class ContractService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function create(User $user, array $data, array $attribution = []): Contract
    {
        return Contract::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Contract $contract, array $data): Contract
    {
        $contract->update($data);

        return $contract->refresh();
    }

    public function delete(Contract $contract): bool
    {
        return (bool) $contract->delete();
    }

    public function terminate(Contract $contract): Contract
    {
        return $this->update($contract, [
            'status' => 'terminated',
            'end_date' => now(),
        ]);
    }

    /**
     * Renew a contract: stamps a new end_date and appends to renewal_history.
     */
    public function renew(Contract $contract, \DateTimeInterface $newEndDate): Contract
    {
        $history = $contract->renewal_history ?? [];
        $history[] = [
            'previous_end_date' => $contract->end_date?->toDateString(),
            'new_end_date' => $newEndDate->format('Y-m-d'),
            'renewed_at' => now()->toDateTimeString(),
        ];

        return $this->update($contract, [
            'renewal_history' => $history,
            'end_date' => $newEndDate->format('Y-m-d'),
            'status' => 'active',
        ]);
    }
}
