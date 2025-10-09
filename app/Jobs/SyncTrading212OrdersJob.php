<?php

namespace App\Jobs;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Services\Trading212Service;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTrading212OrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $userId = null // Optional: scope to a user, else default user
    ) {}

    public function handle(Trading212Service $t212): void
    {
        $now = now();
        $since = $t212->getLastSyncOrDefault($now);

        $orders = $t212->fetchFilledOrdersSince($since);
        if (empty($orders)) {
            Log::info('Trading212 sync: no new filled orders.', ['since' => $since->toIso8601String()]);
            $t212->setLastSync($now);

            return;
        }

        // In a real app we might have per-user brokerage accounts. For now, attach to the first user.
        $userId = $this->userId ?? \App\Models\User::query()->value('id');
        if (!$userId) {
            Log::warning('Trading212 sync aborted: no user available to attach investments.');
            return;
        }

        DB::transaction(function () use ($orders, $userId) {
            foreach ($orders as $o) {
                $symbol = $o['symbol'];
                $name = $o['name'] ?? $symbol;

                // Find or create Investment by user + symbol (stocks type by default)
                $investment = Investment::query()->firstOrCreate(
                    [
                        'user_id' => $userId,
                        'symbol_identifier' => $symbol,
                        'investment_type' => 'stocks',
                    ],
                    [
                        'name' => $name,
                        'quantity' => 0,
                        'purchase_date' => now()->toDateString(),
                        'purchase_price' => 0,
                        'account_broker' => config('trading212.broker_name', 'Trading212'),
                        'account_number' => config('trading212.account_number'),
                        'status' => 'active',
                    ]
                );

                // Idempotency: do not insert duplicate order_id
                $exists = InvestmentTransaction::query()
                    ->where('investment_id', $investment->id)
                    ->where('order_id', (string) $o['id'])
                    ->exists();
                if ($exists) {
                    continue;
                }

                $qty = (float) $o['quantity'];
                $price = (float) $o['price'];
                $total = $qty * $price;
                $fees = (float) ($o['fee'] ?? 0);
                $executedAt = Carbon::parse($o['executed_at'])->toDateString();

                $tx = new InvestmentTransaction([
                    'transaction_type' => $o['side'] === 'sell' ? 'sell' : 'buy',
                    'quantity' => $qty,
                    'price_per_share' => $price,
                    'total_amount' => $total,
                    'fees' => $fees,
                    'transaction_date' => $executedAt,
                    'order_id' => (string) $o['id'],
                    'account_number' => config('trading212.account_number'),
                    'broker' => config('trading212.broker_name', 'Trading212'),
                    'currency' => $o['currency'] ?? 'USD',
                    'order_type' => 'market',
                ]);

                $investment->transactions()->save($tx);

                // Update aggregate quantity and purchase metrics
                if ($tx->transaction_type === 'buy') {
                    // Weighted average price update
                    $oldQty = (float) $investment->quantity;
                    $oldCost = $oldQty * (float) $investment->purchase_price;
                    $newQty = $oldQty + $qty;
                    $newCost = $oldCost + $total + $fees;
                    $investment->quantity = $newQty;
                    $investment->purchase_price = $newQty > 0 ? $newCost / $newQty : 0;
                    $investment->purchase_date = $investment->purchase_date ?? $executedAt;
                    $investment->total_fees_paid = (float) $investment->total_fees_paid + $fees;
                    $investment->status = 'active';
                } else {
                    // Sell: reduce quantity, possibly mark sold
                    $investment->quantity = max(0, (float) $investment->quantity - $qty);
                    $investment->total_fees_paid = (float) $investment->total_fees_paid + $fees;
                    if ($investment->quantity == 0) {
                        $investment->status = 'sold';
                        $investment->sale_date = $executedAt;
                        $investment->sale_price = $price;
                        $investment->sale_proceeds = $total - $fees;
                    }
                }

                $investment->save();
            }
        });

        // Update last sync only after success
        $t212->setLastSync($now);
    }
}
