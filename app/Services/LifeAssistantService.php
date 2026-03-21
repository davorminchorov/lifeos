<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Support\Facades\Auth;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class LifeAssistantService
{
    public function __construct(
        private readonly CurrencyService $currencyService,
    ) {}

    public function chat(string $userMessage, array $history = []): string
    {
        $tools = $this->buildTools();

        $messages = collect($history)->map(function ($msg) {
            return $msg['role'] === 'user'
                ? new UserMessage($msg['content'])
                : new AssistantMessage($msg['content']);
        })->all();

        $messages[] = new UserMessage($userMessage);

        $response = Prism::text()
            ->using(Provider::Anthropic, 'claude-sonnet-4-6')
            ->withSystemPrompt($this->systemPrompt())
            ->withMessages($messages)
            ->withTools($tools)
            ->withMaxSteps(8)
            ->asText();

        return $response->text;
    }

    private function systemPrompt(): string
    {
        $user = Auth::user();
        $now = now()->format('l, F j, Y');
        $currency = config('currency.default', 'MKD');

        return <<<PROMPT
        You are the LifeOS AI Assistant — a personal financial and life advisor with full read access
        to {$user->name}'s data. Today is {$now}. The default currency is {$currency}.

        Your role: surface insights, answer questions, and proactively identify opportunities and risks
        across all life modules — finances, investments, contracts, job search, subscriptions, etc.

        Guidelines:
        - Be concise but insightful. Lead with the key finding.
        - Always use the available tools to fetch live data before answering questions about numbers.
        - When you spot anomalies, risks, or opportunities, mention them unprompted.
        - Format currency values clearly. Use bullet points for lists.
        - Never fabricate numbers — always retrieve data using tools.
        - Cross-reference modules when relevant (e.g., if job search is active, note runway from expenses).
        PROMPT;
    }

    private function buildTools(): array
    {
        $userId = Auth::id();

        return [
            Tool::as('get_expense_summary')
                ->for('Get expense totals and breakdown by category for a given period (this_month, last_month, last_3_months, last_6_months, this_year)')
                ->withStringParameter('period', 'Time period: this_month, last_month, last_3_months, last_6_months, this_year')
                ->using(function (string $period) use ($userId): string {
                    [$start, $end] = $this->periodToDates($period);

                    $expenses = Expense::where('user_id', $userId)
                        ->whereBetween('expense_date', [$start, $end])
                        ->get();

                    if ($expenses->isEmpty()) {
                        return "No expenses found for {$period}.";
                    }

                    $total = $expenses->sum(fn ($e) => $this->toDefault($e->amount, $e->currency));

                    $byCategory = $expenses->groupBy('category')->map(fn ($group, $cat) => [
                        'category' => $cat ?: 'Uncategorized',
                        'count' => $group->count(),
                        'total' => round($this->sumToDefault($group, 'amount', 'currency'), 2),
                    ])->sortByDesc('total')->values();

                    return json_encode([
                        'period' => $period,
                        'total_expenses' => round($total, 2),
                        'currency' => config('currency.default', 'MKD'),
                        'transaction_count' => $expenses->count(),
                        'by_category' => $byCategory,
                        'largest_expense' => $expenses->sortByDesc('amount')->first()?->only(['description', 'merchant', 'amount', 'currency', 'expense_date', 'category']),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_subscriptions')
                ->for('Get all subscriptions with their costs, status, and upcoming renewal dates')
                ->using(function () use ($userId): string {
                    $subs = Subscription::where('user_id', $userId)->get();

                    if ($subs->isEmpty()) {
                        return 'No subscriptions found.';
                    }

                    $active = $subs->where('status', 'active');
                    $monthlyTotal = $active->sum(fn ($s) => $this->toDefault($s->cost, $s->currency));

                    $list = $active->map(fn ($s) => [
                        'name' => $s->service_name,
                        'cost' => $s->cost,
                        'currency' => $s->currency,
                        'billing_cycle' => $s->billing_cycle,
                        'next_billing_date' => $s->next_billing_date?->format('Y-m-d'),
                        'category' => $s->category,
                        'days_until_renewal' => $s->next_billing_date ? now()->diffInDays($s->next_billing_date, false) : null,
                    ])->sortBy('days_until_renewal')->values();

                    return json_encode([
                        'active_count' => $active->count(),
                        'total_count' => $subs->count(),
                        'monthly_total_default_currency' => round($monthlyTotal, 2),
                        'currency' => config('currency.default', 'MKD'),
                        'subscriptions' => $list,
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_investments')
                ->for('Get investment portfolio summary including current values, returns, and performance')
                ->using(function () use ($userId): string {
                    $investments = Investment::where('user_id', $userId)->active()->get();

                    if ($investments->isEmpty()) {
                        return 'No active investments found.';
                    }

                    $totalValue = $investments->sum('current_value');
                    $totalCost = $investments->sum('initial_investment');
                    $totalReturn = $totalValue - $totalCost;
                    $returnPct = $totalCost > 0 ? ($totalReturn / $totalCost) * 100 : 0;

                    $byType = $investments->groupBy('investment_type')->map(fn ($group, $type) => [
                        'type' => $type,
                        'count' => $group->count(),
                        'current_value' => round($group->sum('current_value'), 2),
                        'initial_investment' => round($group->sum('initial_investment'), 2),
                    ])->values();

                    return json_encode([
                        'portfolio_value' => round($totalValue, 2),
                        'total_invested' => round($totalCost, 2),
                        'total_return' => round($totalReturn, 2),
                        'return_percentage' => round($returnPct, 2),
                        'investment_count' => $investments->count(),
                        'by_type' => $byType,
                        'top_performers' => $investments->sortByDesc(fn ($i) => $i->current_value - $i->initial_investment)
                            ->take(3)
                            ->map(fn ($i) => [
                                'name' => $i->name ?? $i->symbol,
                                'type' => $i->investment_type,
                                'current_value' => $i->current_value,
                                'return' => round($i->current_value - $i->initial_investment, 2),
                            ])->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_utility_bills')
                ->for('Get utility bills — pending, overdue, and recent payment history')
                ->using(function () use ($userId): string {
                    $pending = UtilityBill::where('user_id', $userId)->pending()->orderBy('due_date')->get();
                    $overdue = UtilityBill::where('user_id', $userId)->overdue()->get();

                    $pendingTotal = $pending->sum(fn ($b) => $this->toDefault($b->bill_amount, $b->currency));
                    $overdueTotal = $overdue->sum(fn ($b) => $this->toDefault($b->bill_amount, $b->currency));

                    return json_encode([
                        'overdue_count' => $overdue->count(),
                        'overdue_total' => round($overdueTotal, 2),
                        'pending_count' => $pending->count(),
                        'pending_total' => round($pendingTotal, 2),
                        'currency' => config('currency.default', 'MKD'),
                        'overdue_bills' => $overdue->map(fn ($b) => [
                            'provider' => $b->service_provider,
                            'amount' => $b->bill_amount,
                            'currency' => $b->currency,
                            'due_date' => $b->due_date?->format('Y-m-d'),
                            'days_overdue' => $b->due_date ? now()->diffInDays($b->due_date) : null,
                        ])->values(),
                        'upcoming_bills' => $pending->take(5)->map(fn ($b) => [
                            'provider' => $b->service_provider,
                            'amount' => $b->bill_amount,
                            'currency' => $b->currency,
                            'due_date' => $b->due_date?->format('Y-m-d'),
                            'days_until_due' => $b->due_date ? now()->diffInDays($b->due_date, false) : null,
                        ])->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_budgets')
                ->for('Get active budgets and how much has been spent vs. allocated')
                ->using(function () use ($userId): string {
                    $budgets = Budget::where('user_id', $userId)->active()->get();

                    if ($budgets->isEmpty()) {
                        return 'No active budgets found.';
                    }

                    return json_encode([
                        'budget_count' => $budgets->count(),
                        'currency' => config('currency.default', 'MKD'),
                        'budgets' => $budgets->map(fn ($b) => [
                            'name' => $b->name,
                            'amount' => $b->amount,
                            'currency' => $b->currency,
                            'spent' => $b->spent_amount ?? 0,
                            'remaining' => ($b->amount ?? 0) - ($b->spent_amount ?? 0),
                            'period_type' => $b->period_type,
                            'start_date' => $b->start_date?->format('Y-m-d'),
                            'end_date' => $b->end_date?->format('Y-m-d'),
                        ])->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_ious')
                ->for('Get money owed to or from the user (IOUs and debts)')
                ->using(function () use ($userId): string {
                    $ious = Iou::where('user_id', $userId)->whereNotIn('status', ['settled'])->get();

                    if ($ious->isEmpty()) {
                        return 'No outstanding IOUs found.';
                    }

                    $owedToMe = $ious->where('type', 'owed_to_me');
                    $owedByMe = $ious->where('type', 'owed_by_me');

                    $owedToMeTotal = $owedToMe->sum(fn ($i) => $this->toDefault($i->amount, $i->currency));
                    $owedByMeTotal = $owedByMe->sum(fn ($i) => $this->toDefault($i->amount, $i->currency));

                    return json_encode([
                        'net_position' => round($owedToMeTotal - $owedByMeTotal, 2),
                        'owed_to_me_total' => round($owedToMeTotal, 2),
                        'owed_by_me_total' => round($owedByMeTotal, 2),
                        'currency' => config('currency.default', 'MKD'),
                        'owed_to_me' => $owedToMe->map(fn ($i) => [
                            'person' => $i->person_name,
                            'amount' => $i->amount,
                            'currency' => $i->currency,
                            'due_date' => $i->due_date?->format('Y-m-d'),
                            'description' => $i->description,
                            'days_overdue' => $i->due_date && $i->due_date->isPast() ? now()->diffInDays($i->due_date) : null,
                        ])->values(),
                        'owed_by_me' => $owedByMe->map(fn ($i) => [
                            'person' => $i->person_name,
                            'amount' => $i->amount,
                            'currency' => $i->currency,
                            'due_date' => $i->due_date?->format('Y-m-d'),
                            'description' => $i->description,
                        ])->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_contracts')
                ->for('Get active contracts and any expiring soon')
                ->using(function () use ($userId): string {
                    $contracts = Contract::where('user_id', $userId)->active()->get();
                    $expiring = Contract::where('user_id', $userId)->expiringSoon(60)->get();

                    return json_encode([
                        'active_count' => $contracts->count(),
                        'expiring_in_60_days' => $expiring->count(),
                        'contracts' => $contracts->map(fn ($c) => [
                            'title' => $c->title,
                            'counterparty' => $c->counterparty,
                            'value' => $c->contract_value,
                            'currency' => $c->currency,
                            'end_date' => $c->end_date?->format('Y-m-d'),
                            'days_until_expiry' => $c->end_date ? now()->diffInDays($c->end_date, false) : null,
                            'category' => $c->category,
                        ])->sortBy('days_until_expiry')->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_warranties')
                ->for('Get product warranties, especially those expiring soon')
                ->using(function () use ($userId): string {
                    $warranties = Warranty::where('user_id', $userId)->active()->get();
                    $expiring = Warranty::where('user_id', $userId)->expiringSoon(90)->get();

                    return json_encode([
                        'active_count' => $warranties->count(),
                        'expiring_in_90_days' => $expiring->count(),
                        'expiring_soon' => $expiring->map(fn ($w) => [
                            'product' => $w->product_name,
                            'expiry_date' => $w->warranty_expiration_date?->format('Y-m-d'),
                            'days_remaining' => $w->warranty_expiration_date ? now()->diffInDays($w->warranty_expiration_date, false) : null,
                        ])->sortBy('days_remaining')->values(),
                    ], JSON_PRETTY_PRINT);
                }),

            Tool::as('get_job_applications')
                ->for('Get job application pipeline — status, interviews, and offers')
                ->using(function () use ($userId): string {
                    $applications = JobApplication::where('user_id', $userId)
                        ->whereNotIn('status', ['rejected', 'withdrawn'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $all = JobApplication::where('user_id', $userId)->get();

                    return json_encode([
                        'active_applications' => $applications->count(),
                        'total_applications' => $all->count(),
                        'by_status' => $all->groupBy('status')->map->count(),
                        'active_pipeline' => $applications->map(fn ($a) => [
                            'company' => $a->company_name,
                            'role' => $a->job_title,
                            'status' => $a->status,
                            'applied_date' => $a->applied_at?->format('Y-m-d'),
                            'salary_expectation' => $a->expected_salary,
                            'currency' => $a->currency,
                            'priority' => $a->priority,
                        ])->values(),
                    ], JSON_PRETTY_PRINT);
                }),
        ];
    }

    private function periodToDates(string $period): array
    {
        return match ($period) {
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_3_months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
            'last_6_months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function toDefault(float|string|null $amount, ?string $currency): float
    {
        return $this->currencyService->convertToDefault((float) ($amount ?? 0), $currency ?? config('currency.default', 'MKD'));
    }

    private function sumToDefault($collection, string $amountField, string $currencyField): float
    {
        return $collection->sum(fn ($item) => $this->toDefault($item->$amountField, $item->$currencyField));
    }
}
