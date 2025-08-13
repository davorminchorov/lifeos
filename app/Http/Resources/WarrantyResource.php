<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyResource extends JsonResource
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
            'product' => [
                'name' => $this->product_name,
                'brand' => $this->brand,
                'model' => $this->model,
                'serial_number' => $this->serial_number,
            ],
            'purchase' => [
                'date' => $this->purchase_date?->toDateString(),
                'price' => [
                    'amount' => (float) $this->purchase_price,
                    'formatted' => '$' . number_format($this->purchase_price, 2),
                ],
                'retailer' => $this->retailer,
            ],
            'warranty' => [
                'duration_months' => $this->warranty_duration_months,
                'type' => $this->warranty_type,
                'terms' => $this->warranty_terms,
                'expiration_date' => $this->warranty_expiration_date?->toDateString(),
                'days_until_expiration' => $this->days_until_expiration,
                'remaining_percentage' => round($this->warranty_remaining_percentage, 1),
            ],
            'claims' => [
                'history' => $this->claim_history ?? [],
                'has_claims' => $this->has_claims,
                'total_claims' => $this->total_claims,
            ],
            'documents' => [
                'receipts' => $this->receipt_attachments ?? [],
                'proof_of_purchase' => $this->proof_of_purchase_attachments ?? [],
                'receipt_count' => is_array($this->receipt_attachments) ? count($this->receipt_attachments) : 0,
                'proof_count' => is_array($this->proof_of_purchase_attachments) ? count($this->proof_of_purchase_attachments) : 0,
            ],
            'status' => [
                'current_status' => $this->current_status,
                'is_active' => $this->current_status === 'active',
                'is_expired' => $this->is_expired,
                'is_claimed' => $this->current_status === 'claimed',
                'is_transferred' => $this->current_status === 'transferred',
            ],
            'transfer_history' => $this->transfer_history ?? [],
            'maintenance' => [
                'reminders' => $this->maintenance_reminders ?? [],
                'reminder_count' => is_array($this->maintenance_reminders) ? count($this->maintenance_reminders) : 0,
            ],
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
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'type' => 'warranty',
            ],
        ];
    }
}
