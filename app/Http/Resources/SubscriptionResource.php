<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_name' => $this->service_name,
            'description' => $this->description,
            'category' => $this->category,
            'cost' => [
                'amount' => (float) $this->cost,
                'currency' => $this->currency,
                'formatted' => $this->currency . ' ' . number_format($this->cost, 2),
            ],
            'billing' => [
                'cycle' => $this->billing_cycle,
                'cycle_days' => $this->billing_cycle_days,
                'monthly_cost' => (float) $this->monthly_cost,
                'yearly_cost' => (float) $this->yearly_cost,
            ],
            'dates' => [
                'start_date' => $this->start_date?->toDateString(),
                'next_billing_date' => $this->next_billing_date?->toDateString(),
                'cancellation_date' => $this->cancellation_date?->toDateString(),
                'days_until_next_billing' => $this->next_billing_date ? now()->diffInDays($this->next_billing_date, false) : null,
            ],
            'payment' => [
                'method' => $this->payment_method,
                'merchant_info' => $this->merchant_info,
                'auto_renewal' => (bool) $this->auto_renewal,
            ],
            'management' => [
                'cancellation_difficulty' => $this->cancellation_difficulty,
                'cancellation_difficulty_text' => $this->cancellation_difficulty ? $this->getCancellationDifficultyText() : null,
            ],
            'price_history' => $this->price_history,
            'notes' => $this->notes,
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'is_active' => $this->status === 'active',
            'is_cancelled' => $this->status === 'cancelled',
            'is_paused' => $this->status === 'paused',
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }

    /**
     * Get the cancellation difficulty text representation.
     */
    private function getCancellationDifficultyText(): string
    {
        return match ($this->cancellation_difficulty) {
            1 => 'Very Easy',
            2 => 'Easy',
            3 => 'Moderate',
            4 => 'Hard',
            5 => 'Very Hard',
            default => 'Unknown',
        };
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'type' => 'subscription',
            ],
        ];
    }
}
