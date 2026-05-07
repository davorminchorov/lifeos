<?php

declare(strict_types=1);

namespace App\Services\UtilityBills;

use App\Models\User;
use App\Models\UtilityBill;

class UtilityBillService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function create(User $user, array $data, array $attribution = []): UtilityBill
    {
        return UtilityBill::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UtilityBill $bill, array $data): UtilityBill
    {
        $bill->update($data);

        return $bill->refresh();
    }

    public function delete(UtilityBill $bill): bool
    {
        return (bool) $bill->delete();
    }

    public function markPaid(UtilityBill $bill, ?\DateTimeInterface $paymentDate = null): UtilityBill
    {
        return $this->update($bill, [
            'payment_status' => 'paid',
            'payment_date' => ($paymentDate ?? now())->format('Y-m-d'),
        ]);
    }
}
