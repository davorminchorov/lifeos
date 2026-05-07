<?php

declare(strict_types=1);

namespace App\Services\Iou;

use App\Models\Iou;
use App\Models\User;

class IouService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function create(User $user, array $data, array $attribution = []): Iou
    {
        return Iou::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Iou $iou, array $data): Iou
    {
        $iou->update($data);

        return $iou->refresh();
    }

    public function delete(Iou $iou): bool
    {
        return (bool) $iou->delete();
    }

    /**
     * Record a partial or full payment toward this IOU. Caller passes the
     * absolute new amount_paid (i.e. cumulative) so the same call is
     * idempotent under retries.
     */
    public function recordPayment(Iou $iou, float $amountPaid, ?string $paymentMethod = null): Iou
    {
        $payload = ['amount_paid' => $amountPaid];

        if ($paymentMethod !== null) {
            $payload['payment_method'] = $paymentMethod;
        }

        if ($amountPaid >= (float) $iou->amount) {
            $payload['status'] = 'paid';
        } else {
            $payload['status'] = 'partially_paid';
        }

        return $this->update($iou, $payload);
    }

    public function markPaid(Iou $iou): Iou
    {
        return $this->update($iou, [
            'status' => 'paid',
            'amount_paid' => $iou->amount,
        ]);
    }

    public function cancel(Iou $iou): Iou
    {
        return $this->update($iou, ['status' => 'cancelled']);
    }
}
