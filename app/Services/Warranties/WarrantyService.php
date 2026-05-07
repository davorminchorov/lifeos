<?php

declare(strict_types=1);

namespace App\Services\Warranties;

use App\Models\User;
use App\Models\Warranty;

class WarrantyService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function create(User $user, array $data, array $attribution = []): Warranty
    {
        return Warranty::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Warranty $warranty, array $data): Warranty
    {
        $warranty->update($data);

        return $warranty->refresh();
    }

    public function delete(Warranty $warranty): bool
    {
        return (bool) $warranty->delete();
    }

    /**
     * @param  array<string, mixed>  $claim
     */
    public function recordClaim(Warranty $warranty, array $claim): Warranty
    {
        $history = $warranty->claim_history ?? [];
        $history[] = array_merge(['filed_at' => now()->toDateTimeString()], $claim);

        return $this->update($warranty, [
            'claim_history' => $history,
            'current_status' => 'claim_pending',
        ]);
    }

    /**
     * @param  array<string, mixed>  $transfer
     */
    public function transfer(Warranty $warranty, array $transfer): Warranty
    {
        $history = $warranty->transfer_history ?? [];
        $history[] = array_merge(['transferred_at' => now()->toDateTimeString()], $transfer);

        return $this->update($warranty, [
            'transfer_history' => $history,
            'current_status' => 'transferred',
        ]);
    }
}
