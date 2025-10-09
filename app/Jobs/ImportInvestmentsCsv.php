<?php

namespace App\Jobs;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportInvestmentsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The queue the job should run on.
     *
     * Keeping both property and explicit ->onQueue('imports') in dispatch for clarity.
     */
    public string $queue = 'imports';

    /**
     * Path in storage where the uploaded CSV is stored.
     */
    public string $storedPath;

    /**
     * The ID of the user performing the import.
     */
    public int $userId;

    /**
     * Create a new ImportInvestmentsCsv job instance for importing a user's CSV file.
     *
     * @param  int  $userId  ID of the user who initiated the import.
     * @param  string  $storedPath  Storage path to the uploaded CSV file.
     */
    public function __construct(int $userId, string $storedPath)
    {
        $this->userId = $userId;
        $this->storedPath = $storedPath;
    }

    /**
     * Import investments and their transactions from the CSV file at the job's stored path.
     *
     * Reads and parses the CSV, maps columns by header names (with common aliases), and for each data row
     * creates or finds the user's Investment and creates an InvestmentTransaction. After saving each transaction
     * the corresponding investment aggregates are updated. Invalid rows are skipped and logged; at completion
     * the stored CSV is removed and an import summary is logged.
     */
    public function handle(): void
    {
        $absolutePath = Storage::path($this->storedPath);

        if (! is_readable($absolutePath)) {
            Log::error('ImportInvestmentsCsv: CSV file is not readable', [
                'user_id' => $this->userId,
                'path' => $absolutePath,
            ]);

            return;
        }

        $handle = fopen($absolutePath, 'r');
        if (! $handle) {
            Log::error('ImportInvestmentsCsv: Unable to open CSV', [
                'user_id' => $this->userId,
                'path' => $absolutePath,
            ]);

            return;
        }

        // Read header row
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            Log::warning('ImportInvestmentsCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $absolutePath,
            ]);

            return;
        }

        // Normalize headers to map values by name
        $index = [];
        foreach ($header as $i => $col) {
            $normalized = strtolower(trim($col));
            $index[$normalized] = $i;
        }

        // Helper to get column by name safely
        $get = function (array $row, string $name, $default = null) use ($index) {
            foreach ([$name, trim($name), strtolower($name)] as $key) {
                if (array_key_exists($key, $index)) {
                    $pos = $index[$key];

                    return isset($row[$pos]) ? trim($row[$pos]) : $default;
                }
            }

            // Try alternative synonyms
            $aliases = [
                'no. of shares' => ['shares', 'quantity', 'no of shares', 'number of shares'],
                'price / share' => ['price per share', 'price'],
                'currency (price / share)' => ['currency', 'currency (price)'],
                'total' => ['amount', 'gross amount'],
                'time' => ['date', 'transaction date'],
                'id' => ['order id', 'order_id'],
            ];
            foreach (($aliases[$name] ?? []) as $alias) {
                $alias = strtolower($alias);
                if (array_key_exists($alias, $index)) {
                    $pos = $index[$alias];

                    return isset($row[$pos]) ? trim($row[$pos]) : $default;
                }
            }

            return $default;
        };

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty lines
            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $action = strtolower($get($row, 'action', 'buy'));
                $dateRaw = $get($row, 'time', now()->toDateString());
                $ticker = $get($row, 'ticker');
                if (! $ticker || trim($ticker) === '') {
                    $skipped++;
                    Log::warning('ImportInvestmentsCsv: skipped row due to missing ticker/symbol_identifier');

                    continue;
                }
                $name = $get($row, 'name');
                $isin = $get($row, 'isin');
                $notes = $get($row, 'notes');
                $orderId = $get($row, 'id');
                $shares = (float) str_replace([','], [''], $get($row, 'no. of shares', 0));
                $pricePerShare = (float) str_replace([','], [''], $get($row, 'price / share', 0));
                $currencyPrice = strtoupper(substr($get($row, 'currency (price / share)', 'USD'), 0, 3));
                $exchangeRate = $get($row, 'exchange rate');
                $currencyResult = strtoupper(substr($get($row, 'currency (result)', $currencyPrice), 0, 3));
                $total = (float) str_replace([','], [''], $get($row, 'total', $shares * $pricePerShare));
                $currencyTotal = strtoupper(substr($get($row, 'currency (total) deposit', $currencyResult), 0, 3));

                // Map action to our supported transaction types
                $typeMap = [
                    'buy' => 'buy',
                    'purchase' => 'buy',
                    'sell' => 'sell',
                    'dividend' => 'dividend_reinvestment',
                    'reinvest' => 'dividend_reinvestment',
                    'transfer in' => 'transfer_in',
                    'transfer out' => 'transfer_out',
                    'deposit' => 'transfer_in',
                    'withdrawal' => 'transfer_out',
                ];
                $transactionType = $typeMap[$action] ?? 'buy';

                // Find or create the Investment for this user/symbol (do not include name in the lookup to avoid duplicates)
                $investment = Investment::firstOrCreate(
                    [
                        'user_id' => $this->userId,
                        'symbol_identifier' => $ticker,
                    ],
                    [
                        'name' => $name ?? ($ticker ?: 'Unknown'),
                        'investment_type' => 'stock',
                        'quantity' => 0,
                        'purchase_date' => now()->toDateString(),
                        'purchase_price' => $pricePerShare ?: 0,
                        'current_value' => null,
                        'currency' => $currencyPrice,
                        'notes' => null,
                    ]
                );

                // If the investment already existed and the incoming name differs, update it
                if ($name && $investment->name !== $name) {
                    $investment->update(['name' => $name]);
                }

                // Build notes (append ISIN if present)
                $mergedNotes = trim(collect([
                    $notes,
                    $isin ? 'ISIN: '.$isin : null,
                ])->filter()->implode(' | '));

                // Create transaction
                $transaction = new InvestmentTransaction([
                    'investment_id' => $investment->id,
                    'transaction_type' => $transactionType,
                    'quantity' => $shares,
                    'price_per_share' => $pricePerShare,
                    'total_amount' => $total,
                    'fees' => 0,
                    'taxes' => 0,
                    'transaction_date' => ($dateRaw && ($timestamp = strtotime($dateRaw)) !== false)
                                            ? date('Y-m-d', $timestamp)
                                            : now()->toDateString(),
                    'order_id' => $orderId,
                    'currency' => $currencyTotal ?: $currencyPrice,
                    'exchange_rate' => $exchangeRate ?: null,
                    'notes' => $mergedNotes ?: null,
                ]);
                $transaction->save();

                // Update investment aggregates/quantity
                $this->updateInvestmentFromTransaction($investment->fresh(), $transaction);

                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                Log::error('ImportInvestmentsCsv: row import failed', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        fclose($handle);

        // Optionally, cleanup the stored file
        try {
            Storage::delete($this->storedPath);
        } catch (\Throwable $e) {
            // Ignore cleanup errors
        }

        Log::info('ImportInvestmentsCsv: import finished', [
            'user_id' => $this->userId,
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Update an investment's aggregate fields to reflect a processed transaction and persist the changes.
     *
     * Adjusts the investment's quantity based on purchase or sale, accumulates fees from the transaction,
     * sets the investment status to "sold" when the quantity becomes zero or less due to a sale, and saves the investment.
     *
     * @param  Investment  $investment  The investment record to update.
     * @param  InvestmentTransaction  $transaction  The transaction whose values are applied to the investment.
     */
    private function updateInvestmentFromTransaction(Investment $investment, InvestmentTransaction $transaction): void
    {
        if ($transaction->is_purchase) {
            $investment->quantity += $transaction->quantity;
        } elseif ($transaction->is_sale) {
            $investment->quantity -= $transaction->quantity;
        }

        $investment->total_fees_paid += $transaction->fees;

        if ($investment->quantity <= 0 && $transaction->is_sale) {
            $investment->status = 'sold';
        }

        $investment->save();
    }
}
