<?php

namespace App\Jobs;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportInvestmentsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Map of header aliases to support different broker exports.
     */
    private const HEADER_ALIASES = [
        'no. of shares' => ['shares', 'quantity', 'no of shares', 'number of shares'],
        'price / share' => ['price per share', 'price'],
        'currency (price / share)' => ['currency', 'currency (price)'],
        'total' => ['amount', 'gross amount'],
        'time' => ['date', 'transaction date'],
        'id' => ['order id', 'order_id'],
        'ticker' => ['symbol', 'symbol_identifier'],
    ];

    /**
     * Mapping of inbound actions to internal transaction types.
     */
    private const TYPE_MAP = [
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
        if (! Storage::exists($this->storedPath)) {
            Log::error('ImportInvestmentsCsv: CSV file does not exist', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $content = Storage::get($this->storedPath);
        $lines = array_values(array_filter(explode("\n", $content), fn ($line) => trim($line) !== ''));

        if (empty($lines)) {
            Log::warning('ImportInvestmentsCsv: Empty CSV file', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        // Read header row
        $header = str_getcsv(array_shift($lines));
        if (! $header) {
            Log::warning('ImportInvestmentsCsv: Empty CSV header', [
                'user_id' => $this->userId,
                'path' => $this->storedPath,
            ]);

            return;
        }

        $index = $this->buildHeaderIndex($header);

        $created = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            // Skip empty lines
            if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            try {
                $action = strtolower((string) $this->getValue($row, $index, 'action', 'buy'));
                $dateRaw = (string) $this->getValue($row, $index, 'time', now()->toDateString());
                $ticker = (string) $this->getValue($row, $index, 'ticker');
                if (! $ticker || trim($ticker) === '') {
                    $skipped++;
                    Log::warning('ImportInvestmentsCsv: skipped row due to missing ticker/symbol_identifier');

                    continue;
                }
                $name = (string) $this->getValue($row, $index, 'name');
                $isin = (string) $this->getValue($row, $index, 'isin');
                $notes = (string) $this->getValue($row, $index, 'notes');
                $orderId = (string) $this->getValue($row, $index, 'id');
                $shares = $this->parseDecimal((string) $this->getValue($row, $index, 'no. of shares', '0'));
                $pricePerShare = $this->parseDecimal((string) $this->getValue($row, $index, 'price / share', '0'));
                $currencyPrice = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency (price / share)', 'USD'));
                $exchangeRate = (string) $this->getValue($row, $index, 'exchange rate');
                $currencyResult = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency (result)', $currencyPrice));
                $total = $this->parseDecimal((string) $this->getValue($row, $index, 'total', (string) ($shares * $pricePerShare)));
                $currencyTotal = $this->normalizeCurrency((string) $this->getValue($row, $index, 'currency (total) deposit', $currencyResult));

                $transactionType = self::TYPE_MAP[$action] ?? 'buy';

                // Find or create the Investment for this user/symbol (do not include name in the lookup to avoid duplicates)
                $investment = Investment::firstOrCreate(
                    [
                        'user_id' => $this->userId,
                        'symbol_identifier' => $ticker,
                    ],
                    [
                        'name' => $name ?: ($ticker ?: 'Unknown'),
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
                    'transaction_date' => $this->parseDate($dateRaw),
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
     * Build a case-insensitive header index map.
     *
     * @param  array<int, string>  $header
     * @return array<string, int>
     */
    private function buildHeaderIndex(array $header): array
    {
        $index = [];
        foreach ($header as $i => $col) {
            $normalized = strtolower(trim((string) $col));
            $index[$normalized] = $i;
        }

        return $index;
    }

    /**
     * Retrieve a value by header name or alias from the given row.
     *
     * @param  array<int, string|null>  $row
     * @param  array<string, int>  $index
     */
    private function getValue(array $row, array $index, string $name, mixed $default = null): mixed
    {
        foreach ([$name, trim($name), strtolower($name)] as $key) {
            if (array_key_exists($key, $index)) {
                $pos = $index[$key];

                return isset($row[$pos]) ? trim((string) $row[$pos]) : $default;
            }
        }

        foreach ((self::HEADER_ALIASES[$name] ?? []) as $alias) {
            $alias = strtolower($alias);
            if (array_key_exists($alias, $index)) {
                $pos = $index[$alias];

                return isset($row[$pos]) ? trim((string) $row[$pos]) : $default;
            }
        }

        return $default;
    }

    /**
     * Parse a decimal number from a user/broker formatted string.
     */
    private function parseDecimal(?string $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $v = trim($value);
        if ($v === '') {
            return 0.0;
        }

        // Handle parentheses for negatives and common thousand separators
        $negative = false;
        if (str_starts_with($v, '(') && str_ends_with($v, ')')) {
            $negative = true;
            $v = substr($v, 1, -1);
        }
        $v = str_replace([',', ' '], ['', ''], $v);

        $num = (float) $v;

        return $negative ? -$num : $num;
    }

    /**
     * Normalize a currency code to 3-letter uppercase.
     */
    private function normalizeCurrency(?string $code): string
    {
        $code = strtoupper(substr((string) ($code ?: 'USD'), 0, 3));

        return $code ?: 'USD';
    }

    /**
     * Robustly parse a date string into Y-m-d, with fallback to today.
     */
    private function parseDate(?string $value): string
    {
        if ($value) {
            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable $e) {
                // fall back below
            }

            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        return now()->toDateString();
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
