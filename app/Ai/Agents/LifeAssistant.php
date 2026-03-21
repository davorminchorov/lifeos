<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetBudgetsTool;
use App\Ai\Tools\GetContractsTool;
use App\Ai\Tools\GetExpenseSummaryTool;
use App\Ai\Tools\GetInvestmentsTool;
use App\Ai\Tools\GetIousTool;
use App\Ai\Tools\GetJobApplicationsTool;
use App\Ai\Tools\GetSubscriptionsTool;
use App\Ai\Tools\GetUtilityBillsTool;
use App\Ai\Tools\GetWarrantiesTool;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-4-6')]
#[MaxSteps(8)]
class LifeAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function instructions(): Stringable|string
    {
        $now = now()->format('l, F j, Y');
        $currency = config('currency.default', 'MKD');

        return <<<PROMPT
        You are the LifeOS AI Assistant — a personal financial and life advisor with full read
        access to the user's data. Today is {$now}. The default currency is {$currency}.

        Your role: surface insights, answer questions, and proactively identify opportunities
        and risks across all life modules — finances, investments, contracts, job search,
        subscriptions, and more.

        Guidelines:
        - Be concise but insightful. Lead with the key finding.
        - Always use tools to fetch live data before answering questions about numbers.
        - When you spot anomalies, risks, or opportunities, mention them unprompted.
        - Format numbers and currency clearly. Use bullet points for lists.
        - Never fabricate data — always retrieve it using tools.
        - Cross-reference modules when relevant (e.g. if job search is active, mention runway).
        PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new GetExpenseSummaryTool,
            new GetSubscriptionsTool,
            new GetInvestmentsTool,
            new GetUtilityBillsTool,
            new GetBudgetsTool,
            new GetIousTool,
            new GetContractsTool,
            new GetWarrantiesTool,
            new GetJobApplicationsTool,
        ];
    }
}
