<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\AddCycleMenuItem;
use App\Ai\Tools\AddInterview;
use App\Ai\Tools\CancelSubscription;
use App\Ai\Tools\CreateBudget;
use App\Ai\Tools\CreateContract;
use App\Ai\Tools\CreateCycleMenu;
use App\Ai\Tools\CreateExpense;
use App\Ai\Tools\CreateInvoice;
use App\Ai\Tools\CreateIou;
use App\Ai\Tools\CreateJobApplication;
use App\Ai\Tools\CreateWarranty;
use App\Ai\Tools\FileWarrantyClaim;
use App\Ai\Tools\GenerateBriefing;
use App\Ai\Tools\GetUpcoming;
use App\Ai\Tools\LogPayment;
use App\Ai\Tools\QueryBudgets;
use App\Ai\Tools\QueryContracts;
use App\Ai\Tools\QueryCycleMenus;
use App\Ai\Tools\QueryExpenses;
use App\Ai\Tools\QueryHolidays;
use App\Ai\Tools\QueryInvestmentGoals;
use App\Ai\Tools\QueryInvestments;
use App\Ai\Tools\QueryInvoices;
use App\Ai\Tools\QueryJobApplications;
use App\Ai\Tools\QueryRecurringInvoices;
use App\Ai\Tools\QuerySubscriptions;
use App\Ai\Tools\QueryWarranties;
use App\Ai\Tools\RecordInvestmentTransaction;
use App\Ai\Tools\SummarizePortfolio;
use App\Ai\Tools\SummarizeRevenue;
use App\Ai\Tools\SummarizeSpending;
use App\Ai\Tools\UpdateBudget;
use App\Ai\Tools\UpdateContract;
use App\Ai\Tools\UpdateSubscription;
use App\Models\User;
use App\Services\AssistantContextService;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;

final class LifeOsAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    private string $pageContext;

    public function __construct(
        private User $user,
        private AssistantContextService $contextService,
    ) {
        $this->pageContext = '';
    }

    public function withPage(string $page): static
    {
        $this->pageContext = $page;

        return $this;
    }

    public function instructions(): string
    {
        $context = $this->contextService->loadForPage($this->user, $this->pageContext);

        return <<<PROMPT
        You are LifeOS Assistant, a personal life management helper.

        You can help the user with:
        - Creating and tracking expenses
        - Tracking job applications and interviews
        - Managing subscriptions
        - Logging payments
        - Managing cycle menus and meal planning
        - Tracking investments, transactions, and portfolio goals
        - Creating and querying invoices and recurring invoices
        - Managing budgets and tracking spending against limits
        - Tracking contracts and their terms
        - Managing product warranties and filing claims
        - Querying holidays
        - Querying data across all modules

        == USER CONTEXT ==
        {$context}
        == END CONTEXT ==

        Rules:
        - Default currency is MKD unless the user specifies otherwise.
        - After creating a record, confirm exactly what was created.
        - If the user's request is ambiguous, ask a clarifying question before acting.
        - Keep responses concise — 1-2 sentences unless more detail is requested.
        - When the user asks for a "briefing", "what's happening today", or similar, use the GenerateBriefing tool.

        Formatting rules:
        - NEVER use markdown tables. They don't render well in chat.
        - For lists of items, use numbered lists or bullet points instead.
        - Use **bold** for key terms and names.
        - Use short lines. Don't write walls of text.
        - Use --- between sections if grouping multiple topics.
        PROMPT;
    }

    /**
     * @return array<int, Tool>
     */
    public function tools(): array
    {
        $userId = $this->user->id;
        $tenantId = $this->user->current_tenant_id;

        if ($tenantId === null) {
            return [];
        }

        return [
            new CreateExpense($userId, $tenantId),
            new CreateJobApplication($userId, $tenantId),
            new AddInterview($userId, $tenantId),
            new GenerateBriefing($userId, $tenantId),
            new LogPayment($userId, $tenantId),
            new CreateIou($userId, $tenantId),
            new UpdateSubscription($userId, $tenantId),
            new CancelSubscription($userId, $tenantId),
            new QueryExpenses($userId, $tenantId),
            new QuerySubscriptions($userId, $tenantId),
            new SummarizeSpending($userId, $tenantId),
            new GetUpcoming($userId, $tenantId),
            new QueryJobApplications($userId, $tenantId),
            new QueryCycleMenus($userId, $tenantId),
            new CreateCycleMenu($userId, $tenantId),
            new AddCycleMenuItem($userId, $tenantId),
            new QueryInvestments($userId, $tenantId),
            new QueryInvestmentGoals($userId, $tenantId),
            new SummarizePortfolio($userId, $tenantId),
            new RecordInvestmentTransaction($userId, $tenantId),
            new QueryInvoices($userId, $tenantId),
            new QueryRecurringInvoices($userId, $tenantId),
            new SummarizeRevenue($userId, $tenantId),
            new CreateInvoice($userId, $tenantId),
            new QueryBudgets($userId, $tenantId),
            new CreateBudget($userId, $tenantId),
            new UpdateBudget($userId, $tenantId),
            new QueryContracts($userId, $tenantId),
            new CreateContract($userId, $tenantId),
            new UpdateContract($userId, $tenantId),
            new QueryWarranties($userId, $tenantId),
            new CreateWarranty($userId, $tenantId),
            new FileWarrantyClaim($userId, $tenantId),
            new QueryHolidays($userId, $tenantId),
        ];
    }
}
