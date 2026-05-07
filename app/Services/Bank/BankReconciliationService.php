<?php

declare(strict_types=1);

namespace App\Services\Bank;

use App\Models\BankLine;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BankReconciliationService
{
    /**
     * Auto-link threshold: a single candidate must score at least this and be at
     * least this much above the runner-up. Tuned conservatively because false
     * positives are user-visible.
     */
    public const AUTO_LINK_MIN_SCORE = 0.85;

    public const AUTO_LINK_MIN_DELTA = 0.10;

    public const DATE_WINDOW_DAYS = 3;

    /**
     * Compute a deterministic fingerprint from a parsed bank line. Re-importing
     * the same line collapses to the same row via the unique
     * (tenant_id, fingerprint) index.
     *
     * @param  array<string, mixed>  $line
     */
    public function fingerprint(int $tenantId, array $line): string
    {
        $account = strtolower(trim((string) ($line['account'] ?? '')));
        $posted = (string) ($line['posted_at'] ?? '');
        $amount = (int) ($line['amount_cents'] ?? 0);
        $currency = strtoupper((string) ($line['currency'] ?? ''));
        $description = $this->normalize((string) ($line['description'] ?? ''));
        $row = (string) ($line['statement_row'] ?? '');

        return hash(
            'sha256',
            "bank|{$tenantId}|{$account}|{$posted}|{$amount}|{$currency}|{$description}|{$row}",
        );
    }

    /**
     * Persist a single bank line (idempotent on fingerprint), run the matcher,
     * and return the final row.
     *
     * @param  array<string, mixed>  $line
     * @param  array<string, mixed>  $attribution
     */
    public function ingest(User $user, int $tenantId, array $line, array $attribution = []): BankLine
    {
        $fingerprint = $this->fingerprint($tenantId, $line);

        $existing = BankLine::query()
            ->where('tenant_id', $tenantId)
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        /** @var BankLine $bankLine */
        $bankLine = BankLine::create([
            'user_id' => $user->id,
            'created_by_agent_token_id' => $attribution['agent_token_id'] ?? null,
            'account' => (string) $line['account'],
            'posted_at' => (string) $line['posted_at'],
            'amount_cents' => (int) $line['amount_cents'],
            'currency' => strtoupper((string) $line['currency']),
            'merchant_raw' => $line['merchant_raw'] ?? null,
            'description' => $line['description'] ?? null,
            'balance_after_cents' => isset($line['balance_after_cents']) ? (int) $line['balance_after_cents'] : null,
            'statement_id' => $line['statement_id'] ?? null,
            'statement_row' => isset($line['statement_row']) ? (int) $line['statement_row'] : null,
            'fingerprint' => $fingerprint,
            'source' => $attribution['source'] ?? 'agent',
        ]);

        $this->reconcile($bankLine);

        return $bankLine->refresh();
    }

    /**
     * Score and link the best-matching expense for a bank line. Always saves the
     * candidate list (top 3) so a human reviewer can see what the matcher
     * considered, even when no auto-link happens.
     */
    public function reconcile(BankLine $line): BankLine
    {
        if (! $line->isDebit()) {
            // Credits (incoming money) don't map to expenses; skip until a
            // future phase models income reconciliation.
            return $line;
        }

        $candidates = $this->scoreCandidates($line);

        $line->match_candidates = $candidates->take(3)->map(fn ($c) => [
            'expense_id' => $c['expense']->id,
            'merchant' => $c['expense']->merchant,
            'amount' => (float) $c['expense']->amount,
            'expense_date' => $c['expense']->expense_date?->toDateString(),
            'score' => round($c['score'], 4),
            'reasons' => $c['reasons'],
        ])->all();

        $best = $candidates->first();
        $second = $candidates->skip(1)->first();

        $isAutoLink = $best !== null
            && $best['score'] >= self::AUTO_LINK_MIN_SCORE
            && ($second === null || ($best['score'] - $second['score']) >= self::AUTO_LINK_MIN_DELTA);

        if ($isAutoLink) {
            $line->matched_expense_id = $best['expense']->id;
            $line->match_status = BankLine::STATUS_MATCHED;
            $line->match_confidence = round($best['score'], 2);
        } else {
            $line->match_status = BankLine::STATUS_UNMATCHED;
            $line->match_confidence = $best !== null ? round($best['score'], 2) : null;
        }

        $line->save();

        return $line;
    }

    /**
     * Force a link between a bank line and an expense, regardless of the
     * matcher's confidence. Used when the user (or the agent) knows better.
     */
    public function linkExpense(BankLine $line, Expense $expense): BankLine
    {
        $line->forceFill([
            'matched_expense_id' => $expense->id,
            'match_status' => BankLine::STATUS_MATCHED,
            'match_confidence' => 1.00,
        ])->save();

        return $line;
    }

    /**
     * @return Collection<int, array{expense: Expense, score: float, reasons: array<int, string>}>
     */
    private function scoreCandidates(BankLine $line): Collection
    {
        $absAmount = abs($line->amount_cents);
        $start = $line->posted_at->copy()->subDays(self::DATE_WINDOW_DAYS);
        $end = $line->posted_at->copy()->addDays(self::DATE_WINDOW_DAYS);

        $expenses = Expense::query()
            ->whereBetween('expense_date', [$start, $end])
            ->where('currency', $line->currency)
            ->whereRaw('CAST(ROUND(amount * 100) AS UNSIGNED) = ?', [$absAmount])
            ->whereNotIn('id', $this->alreadyLinkedExpenseIds($line->tenant_id))
            ->get();

        $merchantNorm = $this->normalize((string) ($line->merchant_raw ?? $line->description ?? ''));

        return $expenses
            ->map(function (Expense $expense) use ($line, $merchantNorm) {
                $score = 0.5;
                $reasons = ['amount + currency match'];

                $dayDiff = (int) abs((int) $expense->expense_date->diffInDays($line->posted_at, false));
                if ($dayDiff === 0) {
                    $score += 0.30;
                    $reasons[] = 'same date';
                } elseif ($dayDiff === 1) {
                    $score += 0.20;
                    $reasons[] = '1 day off';
                } elseif ($dayDiff <= self::DATE_WINDOW_DAYS) {
                    $score += 0.10;
                    $reasons[] = "{$dayDiff} days off";
                }

                if ($merchantNorm !== '' && $expense->merchant !== null) {
                    $expMerchant = $this->normalize($expense->merchant);
                    if ($expMerchant !== '') {
                        $percent = 0.0;
                        similar_text($merchantNorm, $expMerchant, $percent);

                        if ($percent >= 80) {
                            $score += 0.20;
                            $reasons[] = 'merchant match (>=80%)';
                        } elseif ($percent >= 50) {
                            $score += 0.10;
                            $reasons[] = 'merchant match (>=50%)';
                        }
                    }
                }

                return [
                    'expense' => $expense,
                    'score' => min(1.0, $score),
                    'reasons' => $reasons,
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function alreadyLinkedExpenseIds(int $tenantId): array
    {
        return BankLine::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('matched_expense_id')
            ->pluck('matched_expense_id')
            ->all();
    }

    private function normalize(string $value): string
    {
        $stripped = preg_replace('/[^a-z0-9 ]/i', ' ', $value) ?? '';

        return strtolower(trim(preg_replace('/\s+/', ' ', $stripped) ?? ''));
    }
}
