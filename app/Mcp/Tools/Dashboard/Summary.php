<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Dashboard;

use App\Mcp\Tools\AbstractTool;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class Summary extends AbstractTool
{
    protected string $name = 'dashboard.summary';

    protected string $description = 'Cross-module snapshot of the authenticated tenant: counts, monthly spend, upcoming items, and alert flags.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'upcoming_window_days' => $schema->integer()->description('Day window for "upcoming" items (default 30).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $within = (int) ($request->get('upcoming_window_days') ?? 30);

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totals = [
            'subscriptions_active' => Subscription::query()->where('status', 'active')->count(),
            'contracts_active' => Contract::query()->where('status', 'active')->count(),
            'warranties_active' => Warranty::query()->where('current_status', 'active')->count(),
            'investments_total' => Investment::query()->count(),
            'jobs_active' => JobApplication::query()->whereNull('archived_at')->count(),
            'iou_pending_owe' => Iou::query()->where('type', 'owe')->where('status', '!=', 'paid')->count(),
            'iou_pending_owed' => Iou::query()->where('type', 'owed')->where('status', '!=', 'paid')->count(),
            'expenses_this_month_count' => Expense::query()->whereBetween('expense_date', [$startOfMonth, $endOfMonth])->count(),
            'expenses_this_month_amount' => (float) Expense::query()->whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount'),
        ];

        $upcoming = [
            'subscription_renewals' => Subscription::query()
                ->where('status', 'active')
                ->whereNotNull('next_billing_date')
                ->where('next_billing_date', '<=', now()->addDays($within))
                ->orderBy('next_billing_date')
                ->limit(10)
                ->get(['id', 'service_name', 'next_billing_date', 'cost', 'currency'])
                ->map(fn (Subscription $s): array => [
                    'id' => $s->id,
                    'service_name' => $s->service_name,
                    'next_billing_date' => $s->next_billing_date?->toDateString(),
                    'cost' => (float) $s->cost,
                    'currency' => $s->currency,
                ])->all(),
            'contracts_expiring' => Contract::query()
                ->expiringSoon($within)
                ->orderBy('end_date')
                ->limit(10)
                ->get(['id', 'title', 'counterparty', 'end_date'])
                ->map(fn (Contract $c): array => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'counterparty' => $c->counterparty,
                    'end_date' => $c->end_date?->toDateString(),
                ])->all(),
            'warranties_expiring' => Warranty::query()
                ->expiringSoon($within)
                ->orderBy('warranty_expiration_date')
                ->limit(10)
                ->get(['id', 'product_name', 'brand', 'warranty_expiration_date'])
                ->map(fn (Warranty $w): array => [
                    'id' => $w->id,
                    'product_name' => $w->product_name,
                    'brand' => $w->brand,
                    'warranty_expiration_date' => $w->warranty_expiration_date?->toDateString(),
                ])->all(),
            'bills_due' => UtilityBill::query()
                ->dueSoon($within)
                ->orderBy('due_date')
                ->limit(10)
                ->get(['id', 'service_provider', 'utility_type', 'bill_amount', 'currency', 'due_date'])
                ->map(fn (UtilityBill $b): array => [
                    'id' => $b->id,
                    'service_provider' => $b->service_provider,
                    'utility_type' => $b->utility_type,
                    'bill_amount' => (float) $b->bill_amount,
                    'currency' => $b->currency,
                    'due_date' => $b->due_date?->toDateString(),
                ])->all(),
        ];

        $alerts = [
            'overdue_bills' => UtilityBill::query()->overdue()->count(),
            'overdue_iou' => Iou::query()->overdue()->count(),
            'jobs_with_overdue_action' => JobApplication::query()
                ->whereNull('archived_at')
                ->whereNotNull('next_action_at')
                ->where('next_action_at', '<', now())
                ->count(),
        ];

        return Response::structured([
            'as_of' => now()->toIso8601String(),
            'window_days' => $within,
            'totals' => $totals,
            'upcoming' => $upcoming,
            'alerts' => $alerts,
        ]);
    }
}
