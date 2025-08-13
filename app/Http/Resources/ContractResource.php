<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'contract_type' => $this->contract_type,
            'title' => $this->title,
            'counterparty' => $this->counterparty,
            'dates' => [
                'start_date' => $this->start_date?->toDateString(),
                'end_date' => $this->end_date?->toDateString(),
                'days_until_expiration' => $this->days_until_expiration,
                'notice_deadline' => $this->notice_deadline?->toDateString(),
            ],
            'terms' => [
                'notice_period_days' => $this->notice_period_days,
                'auto_renewal' => (bool) $this->auto_renewal,
                'contract_value' => $this->contract_value ? [
                    'amount' => (float) $this->contract_value,
                    'formatted' => '$' . number_format($this->contract_value, 2),
                ] : null,
                'payment_terms' => $this->payment_terms,
            ],
            'obligations' => [
                'key_obligations' => $this->key_obligations,
                'penalties' => $this->penalties,
                'termination_clauses' => $this->termination_clauses,
            ],
            'documents' => [
                'attachments' => $this->document_attachments ?? [],
                'attachment_count' => is_array($this->document_attachments) ? count($this->document_attachments) : 0,
            ],
            'performance' => [
                'rating' => $this->performance_rating,
                'rating_text' => $this->performance_rating ? $this->getPerformanceRatingText() : null,
            ],
            'history' => [
                'renewal_history' => $this->renewal_history ?? [],
                'amendments' => $this->amendments ?? [],
                'total_renewals' => is_array($this->renewal_history) ? count($this->renewal_history) : 0,
                'total_amendments' => is_array($this->amendments) ? count($this->amendments) : 0,
            ],
            'status' => $this->status,
            'is_active' => $this->status === 'active',
            'is_expired' => $this->is_expired,
            'requires_notice' => $this->notice_deadline && $this->notice_deadline->isFuture() && $this->notice_deadline->diffInDays(now()) <= 30,
            'notes' => $this->notes,
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
     * Get the performance rating text representation.
     */
    private function getPerformanceRatingText(): string
    {
        return match ($this->performance_rating) {
            1 => 'Very Poor',
            2 => 'Poor',
            3 => 'Average',
            4 => 'Good',
            5 => 'Excellent',
            default => 'Not Rated',
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
                'type' => 'contract',
            ],
        ];
    }
}
