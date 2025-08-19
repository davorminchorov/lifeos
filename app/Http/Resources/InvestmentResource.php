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
            'symbol_ticker' => $this->symbol_ticker,
            'company_name' => $this->company_name,
            'shares_owned' => $this->shares_owned,
            'average_buy_price' => $this->average_buy_price,
            'current_price' => $this->current_price,
            'total_invested' => $this->total_invested,
            'current_value' => $this->current_value,
            'unrealized_gain_loss' => $this->unrealized_gain_loss,
            'realized_gain_loss' => $this->realized_gain_loss,
            'total_dividends_received' => $this->total_dividends_received,
            'currency' => $this->currency,
            'purchase_date' => $this->purchase_date,
            'last_updated' => $this->last_updated,
            'investment_goals' => $this->investment_goals,
            'risk_level' => $this->risk_level,
            'sector' => $this->sector,
            'exchange' => $this->exchange,
            'investment_status' => $this->investment_status,
            'notes' => $this->notes,
            'tax_lot_method' => $this->tax_lot_method,
            'auto_reinvest_dividends' => $this->auto_reinvest_dividends,
            'stop_loss_price' => $this->stop_loss_price,
            'target_price' => $this->target_price,
            'portfolio_allocation_percentage' => $this->portfolio_allocation_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
