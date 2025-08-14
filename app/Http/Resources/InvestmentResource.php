<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'investment_type' => $this->investment_type,
            'symbol_identifier' => $this->symbol_identifier,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'purchase_price' => $this->purchase_price,
            'current_value' => $this->current_value,
            'total_dividends_received' => $this->total_dividends_received,
            'total_fees_paid' => $this->total_fees_paid,
            'investment_goals' => $this->investment_goals,
            'risk_tolerance' => $this->risk_tolerance,
            'account_broker' => $this->account_broker,
            'account_number' => $this->account_number,
            'transaction_history' => $this->transaction_history,
            'tax_lots' => $this->tax_lots,
            'target_allocation_percentage' => $this->target_allocation_percentage,
            'last_price_update' => $this->last_price_update?->format('Y-m-d'),
            'notes' => $this->notes,
            'status' => $this->status ?? 'active',

            // Computed attributes
            'total_cost_basis' => $this->total_cost_basis,
            'current_market_value' => $this->current_market_value,
            'unrealized_gain_loss' => $this->unrealized_gain_loss,
            'unrealized_gain_loss_percentage' => round($this->unrealized_gain_loss_percentage, 2),
            'total_return' => $this->total_return,
            'total_return_percentage' => round($this->total_return_percentage, 2),
            'holding_period_days' => $this->holding_period_days,
            'annualized_return' => round($this->annualized_return, 2),

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
