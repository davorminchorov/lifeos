<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Budgets\BudgetSummaryTool;
use App\Ai\Tools\Expenses\AddExpenseTool;
use App\Ai\Tools\Expenses\ListExpensesTool;
use App\Ai\Tools\Expenses\SpendingSummaryTool;
use App\Ai\Tools\Investments\InvestmentSummaryTool;
use App\Ai\Tools\Ious\AddIouTool;
use App\Ai\Tools\Ious\ListIousTool;
use App\Ai\Tools\JobApplications\AddJobApplicationTool;
use App\Ai\Tools\JobApplications\ListJobApplicationsTool;
use App\Ai\Tools\Subscriptions\AddSubscriptionTool;
use App\Ai\Tools\Subscriptions\ListSubscriptionsTool;
use App\Ai\Tools\System\DailyBriefingTool;
use App\Ai\Tools\System\SyncAllTool;
use App\Ai\Tools\System\TodaysMenuTool;
use App\Ai\Tools\UtilityBills\ListUpcomingBillsTool;
use App\Ai\Tools\UtilityBills\MarkBillPaidTool;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

#[Provider('anthropic')]
class LifeOsAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        $date = now()->format('l, F j, Y');

        return <<<PROMPT
        You are LifeOS, a personal finance and life management assistant.

        Today's date: {$date}

        You help manage: expenses, subscriptions, utility bills, job applications,
        investments, budgets, IOUs/debts, and meal plans.

        RULES:
        - Default currency is MKD (Macedonian Denar) unless the user specifies otherwise.
        - Amounts are stored as decimal values (e.g. 2800.00, not cents).
        - Be concise. Confirm every action with a brief summary.
        - For expense categories, use common ones: groceries, dining, transport, entertainment, utilities, health, shopping, education, travel, other.
        - For subscription billing cycles: monthly, yearly, weekly, or custom.
        - When listing data, format it clearly with amounts and dates.
        - When the user asks about spending, use the spending_summary tool.
        - When adding entries, always confirm what was created.
        - Parse natural language amounts: "2.8k" = 2800, "1.5k" = 1500.
        - Understand common merchant/service shorthand (e.g. "evn" = EVN electricity, "a1" = A1 telecom).
        PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new AddExpenseTool,
            new ListExpensesTool,
            new SpendingSummaryTool,
            new AddSubscriptionTool,
            new ListSubscriptionsTool,
            new MarkBillPaidTool,
            new ListUpcomingBillsTool,
            new AddJobApplicationTool,
            new ListJobApplicationsTool,
            new InvestmentSummaryTool,
            new BudgetSummaryTool,
            new AddIouTool,
            new ListIousTool,
            new DailyBriefingTool,
            new TodaysMenuTool,
            new SyncAllTool,
        ];
    }
}
