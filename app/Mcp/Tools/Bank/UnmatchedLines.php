<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Bank;

use App\Mcp\Tools\AbstractTool;
use App\Models\BankLine;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class UnmatchedLines extends AbstractTool
{
    protected string $name = 'bank.unmatched';

    protected string $description = 'List bank lines without an expense match, with the matcher\'s top-3 candidate suggestions per line. Use this after bank.recordLines to triage what still needs attention.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'within_days' => $schema->integer()->description('Only return unmatched lines posted within this many days (default 30).'),
            'account' => $schema->string()->description('Filter by account.'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $within = (int) ($request->get('within_days') ?? 30);
        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = BankLine::query()
            ->unmatched()
            ->where('posted_at', '>=', now()->subDays($within))
            ->orderByDesc('posted_at');

        if ($account = $request->get('account')) {
            $query->where('account', $account);
        }

        $items = $query->limit($limit)->get()->map(fn (BankLine $line): array => [
            'id' => $line->id,
            'account' => $line->account,
            'posted_at' => $line->posted_at->toDateString(),
            'amount_cents' => $line->amount_cents,
            'currency' => $line->currency,
            'merchant_raw' => $line->merchant_raw,
            'description' => $line->description,
            'match_confidence' => $line->match_confidence !== null ? (float) $line->match_confidence : null,
            'candidates' => $line->match_candidates ?? [],
        ])->all();

        return Response::structured([
            'count' => count($items),
            'within_days' => $within,
            'items' => $items,
        ]);
    }
}
