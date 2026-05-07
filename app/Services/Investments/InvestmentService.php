<?php

declare(strict_types=1);

namespace App\Services\Investments;

use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Models\InvestmentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvestmentService
{
    /**
     * Create a new Investment row (e.g. when an agent discovers a position
     * the user hasn't entered manually).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function create(User $user, array $data, array $attribution = []): Investment
    {
        return Investment::create([
            'user_id' => $user->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * Record a buy / sell / transfer / dividend-reinvestment transaction.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function recordTransaction(Investment $investment, array $data, array $attribution = []): InvestmentTransaction
    {
        $payload = [
            'investment_id' => $investment->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ];

        if (! isset($payload['total_amount']) && isset($payload['quantity'], $payload['price_per_share'])) {
            $payload['total_amount'] = (float) $payload['quantity'] * (float) $payload['price_per_share'];
        }

        if (! isset($payload['currency'])) {
            $payload['currency'] = $investment->currency ?? 'MKD';
        }

        return InvestmentTransaction::create($payload);
    }

    /**
     * Record a dividend payment.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attribution
     */
    public function recordDividend(Investment $investment, array $data, array $attribution = []): InvestmentDividend
    {
        return InvestmentDividend::create([
            'investment_id' => $investment->id,
            ...$data,
            'source' => $attribution['source'] ?? 'user',
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
        ]);
    }

    /**
     * Mark-to-market: update current_value (per-share) and last_price_update.
     * Quantity is preserved; the dashboard recomputes market value from
     * quantity * current_value.
     */
    public function repriceLot(Investment $investment, float $currentValue, ?\DateTimeInterface $asOf = null): Investment
    {
        $asOf ??= now();

        $investment->update([
            'current_value' => $currentValue,
            'last_price_update' => $asOf->format('Y-m-d'),
        ]);

        return $investment->refresh();
    }

    /**
     * Bulk-create transactions inside a single DB transaction.
     *
     * @param  array<int, array<string, mixed>>  $rows  Each row needs investment_id and the
     *   InvestmentTransaction fields. The applier resolves investment_id ahead
     *   of time so this method stays naive about resolution rules.
     * @param  array<string, mixed>  $attribution
     * @return array<int, InvestmentTransaction>
     */
    public function bulkRecordTransactions(array $rows, array $attribution = []): array
    {
        return DB::transaction(function () use ($rows, $attribution): array {
            $created = [];

            foreach ($rows as $row) {
                if (! isset($row['investment_id'])) {
                    throw new RuntimeException('investment_id missing from a bulk-import row.');
                }

                $investment = Investment::query()->find((int) $row['investment_id']);

                if ($investment === null) {
                    throw new RuntimeException("Investment {$row['investment_id']} not found in this tenant.");
                }

                $created[] = $this->recordTransaction($investment, $row, $attribution);
            }

            return $created;
        });
    }
}
