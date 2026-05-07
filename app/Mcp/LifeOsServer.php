<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Mcp\Tools\Bank\LinkExpense as BankLinkExpense;
use App\Mcp\Tools\Bank\RecordLines as BankRecordLines;
use App\Mcp\Tools\Bank\UnmatchedLines as BankUnmatchedLines;
use App\Mcp\Tools\Bills\CreateUtilityBill;
use App\Mcp\Tools\Bills\UpcomingBills;
use App\Mcp\Tools\Contracts\CreateContract;
use App\Mcp\Tools\Contracts\ListContracts;
use App\Mcp\Tools\CycleMenu\CurrentWeekCycleMenu;
use App\Mcp\Tools\Dashboard\Summary;
use App\Mcp\Tools\Expenses\BulkImportExpenses;
use App\Mcp\Tools\Expenses\CategorizeExpense;
use App\Mcp\Tools\Expenses\CreateExpense;
use App\Mcp\Tools\Expenses\ListExpenses;
use App\Mcp\Tools\Investments\BulkImportTransactions;
use App\Mcp\Tools\Investments\Portfolio;
use App\Mcp\Tools\Investments\RecordDividend;
use App\Mcp\Tools\Investments\RecordTransaction;
use App\Mcp\Tools\Investments\RepriceLot;
use App\Mcp\Tools\Iou\CreateIou;
use App\Mcp\Tools\Iou\ListIou;
use App\Mcp\Tools\Jobs\AddInterview;
use App\Mcp\Tools\Jobs\Pipeline;
use App\Mcp\Tools\Jobs\UpdateJobStatus;
use App\Mcp\Tools\Notifications\ListNotifications;
use App\Mcp\Tools\Receipts\ProcessedFiles as ReceiptsProcessedFiles;
use App\Mcp\Tools\Subscriptions\CreateSubscription;
use App\Mcp\Tools\Subscriptions\ListSubscriptions;
use App\Mcp\Tools\Warranties\CreateWarranty;
use App\Mcp\Tools\Warranties\ListWarranties;
use Laravel\Mcp\Server;

class LifeOsServer extends Server
{
    protected string $name = 'LifeOS';

    protected string $version = '0.1.0';

    protected string $instructions = <<<'MD'
        LifeOS MCP server — read-only access to the authenticated tenant's data
        across Subscriptions, Contracts, Warranties, Investments, Expenses,
        Utility Bills, IOU/Debt, Job Applications, Cycle Menu, Notifications,
        and a dashboard summary. Every call is scoped to the (user, tenant)
        pair bound to the agent token used to authenticate.
        MD;

    /**
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        Summary::class,
        ListExpenses::class,
        ListSubscriptions::class,
        Portfolio::class,
        UpcomingBills::class,
        ListContracts::class,
        ListWarranties::class,
        ListIou::class,
        Pipeline::class,
        CurrentWeekCycleMenu::class,
        ListNotifications::class,
        CreateExpense::class,
        BulkImportExpenses::class,
        CategorizeExpense::class,
        CreateSubscription::class,
        CreateContract::class,
        CreateWarranty::class,
        CreateIou::class,
        CreateUtilityBill::class,
        UpdateJobStatus::class,
        AddInterview::class,
        RecordTransaction::class,
        RecordDividend::class,
        RepriceLot::class,
        BulkImportTransactions::class,
        BankRecordLines::class,
        BankLinkExpense::class,
        BankUnmatchedLines::class,
        ReceiptsProcessedFiles::class,
    ];
}
