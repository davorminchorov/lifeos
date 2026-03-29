import { Head, Link } from '@inertiajs/react'
import { lazy, Suspense, useCallback, useEffect, useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatCard } from '@/components/shared/stat-card'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Skeleton } from '@/components/ui/skeleton'
import { Progress } from '@/components/ui/progress'
import { Badge } from '@/components/ui/badge'
import {
    CreditCard,
    FileSignature,
    TrendingUp,
    Receipt,
    Zap,
    AlertTriangle,
    Info,
    AlertCircle,
    Shield,
    PieChart as PieChartIcon,
    Target,
    type LucideIcon,
} from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'

// Lazy load chart components for bundle optimization
const AreaChart = lazy(() => import('@/components/charts/area-chart').then(m => ({ default: m.AreaChart })))
const PieChart = lazy(() => import('@/components/charts/pie-chart').then(m => ({ default: m.PieChart })))
const ChartContainer = lazy(() => import('@/components/charts/chart-container').then(m => ({ default: m.ChartContainer })))

interface DashboardStats {
    active_subscriptions: number
    monthly_subscription_cost_formatted: string
    active_contracts: number
    contracts_expiring_soon: number
    total_contract_value_formatted: string
    portfolio_value_formatted: string
    total_return_formatted: string
    total_warranties: number
    total_expenses: number
    total_expenses_formatted: string
    pending_bills: number
    pending_bills_formatted: string
}

interface DashboardAlert {
    type: 'warning' | 'info' | 'error'
    title: string
    message: string
    action_url: string
    action_text: string
}

interface RecentExpense {
    id: number
    description: string
    category: string | null
    date: string
    amount: number
    currency: string
}

interface UpcomingBill {
    id: number
    provider: string
    type: string | null
    due_date: string
    amount: number
    currency: string
}

interface BudgetUtilization {
    id: number
    category: string
    amount: number
    amount_formatted: string
    spent: number
    spent_formatted: string
    remaining_formatted: string
    utilization: number
    status: 'exceeded' | 'warning' | 'on_track'
}

interface TopSubscription {
    id: number
    service_name: string
    monthly_cost: number
    monthly_cost_formatted: string
    billing_cycle: string
    next_billing_date: string | null
    category: string | null
}

interface PortfolioAllocationItem {
    type: string
    value: number
    value_formatted: string
    percentage: number
    count: number
}

interface ChartData {
    spendingTrends: Record<string, unknown>[]
    categoryBreakdown: { name: string; value: number }[]
    portfolioPerformance: Record<string, unknown>[]
    monthlyComparison: Record<string, unknown>[]
}

interface DashboardProps {
    stats: DashboardStats
    alerts: DashboardAlert[]
    insights: Record<string, unknown>
    recent_expenses: RecentExpense[]
    upcoming_bills: UpcomingBill[]
    budget_utilization: BudgetUtilization[]
    top_subscriptions: TopSubscription[]
    portfolio_allocation: PortfolioAllocationItem[]
}

const alertIcons: Record<string, LucideIcon> = {
    warning: AlertTriangle,
    info: Info,
    error: AlertCircle,
}

const alertVariants: Record<string, 'default' | 'destructive'> = {
    warning: 'default',
    info: 'default',
    error: 'destructive',
}

const budgetStatusColors: Record<string, string> = {
    exceeded: 'text-red-600 dark:text-red-400',
    warning: 'text-amber-600 dark:text-amber-400',
    on_track: 'text-emerald-600 dark:text-emerald-400',
}

const budgetProgressColors: Record<string, string> = {
    exceeded: '[&>div]:bg-red-500',
    warning: '[&>div]:bg-amber-500',
    on_track: '[&>div]:bg-emerald-500',
}

const quickActions = [
    { label: 'Add Subscription', href: '/subscriptions/create', icon: CreditCard },
    { label: 'Add Expense', href: '/expenses/create', icon: Receipt },
    { label: 'Add Contract', href: '/contracts/create', icon: FileSignature },
    { label: 'Add Warranty', href: '/warranties/create', icon: Shield },
    { label: 'Add Investment', href: '/investments/create', icon: TrendingUp },
    { label: 'Add Utility Bill', href: '/utility-bills/create', icon: Zap },
]

const allocationColors = [
    'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500',
    'bg-rose-500', 'bg-cyan-500', 'bg-orange-500', 'bg-indigo-500',
]

export default function DashboardIndex({
    stats,
    alerts = [],
    recent_expenses = [],
    upcoming_bills = [],
    budget_utilization = [],
    top_subscriptions = [],
    portfolio_allocation = [],
}: DashboardProps) {
    // Ensure arrays (PHP may serialize empty collections as objects)
    const alertsList = Array.isArray(alerts) ? alerts : Object.values(alerts)
    const expensesList = Array.isArray(recent_expenses) ? recent_expenses : Object.values(recent_expenses)
    const billsList = Array.isArray(upcoming_bills) ? upcoming_bills : Object.values(upcoming_bills)
    const budgetList = Array.isArray(budget_utilization) ? budget_utilization : Object.values(budget_utilization)
    const subscriptionList = Array.isArray(top_subscriptions) ? top_subscriptions : Object.values(top_subscriptions)
    const allocationList = Array.isArray(portfolio_allocation) ? portfolio_allocation : Object.values(portfolio_allocation)
    const [chartData, setChartData] = useState<ChartData | null>(null)
    const [chartPeriod, setChartPeriod] = useState('6m')
    const [chartLoading, setChartLoading] = useState(false)

    const fetchChartData = useCallback(async (period: string) => {
        setChartLoading(true)
        try {
            const response = await fetch(`/dashboard/chart-data?period=${period}`)
            const raw = await response.json()

            // Transform column-based API data into row-based format for Recharts
            const spendingTrends = (raw.spendingTrends?.labels ?? []).map((label: string, i: number) => ({
                month: label,
                spending: raw.spendingTrends.spending?.[i] ?? 0,
                budget: raw.spendingTrends.budget?.[i] ?? 0,
            }))

            const categoryBreakdown = (raw.categoryBreakdown?.labels ?? []).map((label: string, i: number) => ({
                name: label,
                value: raw.categoryBreakdown.values?.[i] ?? 0,
            }))

            const portfolioPerformance = (raw.portfolioPerformance?.labels ?? []).map((label: string, i: number) => ({
                month: label,
                value: raw.portfolioPerformance.values?.[i] ?? 0,
                returns: raw.portfolioPerformance.returns?.[i] ?? 0,
            }))

            setChartData({
                spendingTrends,
                categoryBreakdown,
                portfolioPerformance,
                monthlyComparison: raw.monthlyComparison ?? [],
            })
        } finally {
            setChartLoading(false)
        }
    }, [])

    const handlePeriodChange = useCallback((period: string) => {
        setChartPeriod(period)
        fetchChartData(period)
    }, [fetchChartData])

    useEffect(() => {
        fetchChartData(chartPeriod)
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <AppLayout>
            <Head title="Dashboard" />
            <PageHeader title="Dashboard" description="Your financial overview at a glance" />

            {/* Quick Stats */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <StatCard
                    label="Subscriptions"
                    value={stats.active_subscriptions}
                    icon={CreditCard}
                    description={`${stats.monthly_subscription_cost_formatted}/mo`}
                />
                <StatCard
                    label="Contracts"
                    value={stats.active_contracts}
                    icon={FileSignature}
                    description={`${stats.contracts_expiring_soon} expiring soon`}
                />
                <StatCard
                    label="Portfolio"
                    value={stats.portfolio_value_formatted}
                    icon={TrendingUp}
                    description={`Return: ${stats.total_return_formatted}`}
                />
                <StatCard
                    label="Expenses"
                    value={stats.total_expenses_formatted}
                    icon={Receipt}
                    description={`${stats.total_expenses} this month`}
                />
                <StatCard
                    label="Pending Bills"
                    value={stats.pending_bills_formatted}
                    icon={Zap}
                    description={`${stats.pending_bills} bills due`}
                />
            </div>

            {/* Alerts */}
            {alertsList.length > 0 ? (
                <div className="mt-6 space-y-3">
                    <h2 className="text-lg font-semibold">Alerts & Notifications</h2>
                    {alertsList.map((alert, index) => {
                        const Icon = alertIcons[alert.type] ?? Info
                        return (
                            <Alert key={index} variant={alertVariants[alert.type]}>
                                <Icon className="h-4 w-4" />
                                <AlertTitle>{alert.title}</AlertTitle>
                                <AlertDescription className="flex items-center justify-between">
                                    <span>{alert.message}</span>
                                    {alert.action_url ? (
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={alert.action_url}>{alert.action_text}</Link>
                                        </Button>
                                    ) : null}
                                </AlertDescription>
                            </Alert>
                        )
                    })}
                </div>
            ) : null}

            {/* Charts Section */}
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <Suspense fallback={<ChartSkeleton />}>
                    <ChartContainer
                        title="Spending Trends"
                        period={chartPeriod}
                        onPeriodChange={handlePeriodChange}
                        onRefresh={() => fetchChartData(chartPeriod)}
                        isLoading={chartLoading}
                    >
                        {chartData?.spendingTrends ? (
                            <AreaChart
                                data={chartData.spendingTrends}
                                dataKeys={[
                                    { key: 'spending', color: 'var(--chart-1)', name: 'Spending' },
                                    { key: 'budget', color: 'var(--chart-2)', name: 'Budget' },
                                ]}
                                xAxisKey="month"
                                height={250}
                            />
                        ) : (
                            <ChartLoadingPlaceholder />
                        )}
                    </ChartContainer>
                </Suspense>

                <Suspense fallback={<ChartSkeleton />}>
                    <ChartContainer title="Category Breakdown">
                        {chartData?.categoryBreakdown ? (
                            <PieChart data={chartData.categoryBreakdown} height={250} />
                        ) : (
                            <ChartLoadingPlaceholder />
                        )}
                    </ChartContainer>
                </Suspense>
            </div>

            {/* Budget Utilization & Top Subscriptions */}
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                {/* Budget Utilization */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <Target className="h-4 w-4" />
                            Budget Utilization
                        </CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/budgets">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {budgetList.length > 0 ? (
                            <div className="space-y-4">
                                {budgetList.map((budget) => (
                                    <div key={budget.id} className="space-y-1.5">
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="font-medium capitalize">{budget.category}</span>
                                            <span className={budgetStatusColors[budget.status]}>
                                                {budget.spent_formatted} / {budget.amount_formatted}
                                            </span>
                                        </div>
                                        <Progress
                                            value={Math.min(budget.utilization, 100)}
                                            className={`h-2 ${budgetProgressColors[budget.status]}`}
                                        />
                                        <div className="flex items-center justify-between text-xs text-muted-foreground">
                                            <span>{budget.utilization.toFixed(0)}% used</span>
                                            <span>{budget.remaining_formatted} remaining</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <Target className="mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">No active budgets</p>
                                <Button variant="outline" size="sm" className="mt-2" asChild>
                                    <Link href="/budgets/create">Create a budget</Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Top Subscriptions */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <CreditCard className="h-4 w-4" />
                            Top Subscriptions
                        </CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/subscriptions">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {subscriptionList.length > 0 ? (
                            <div className="space-y-3">
                                {subscriptionList.map((sub, index) => (
                                    <div key={sub.id} className="flex items-center gap-3 text-sm">
                                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-medium">
                                            {index + 1}
                                        </span>
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">{sub.service_name}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {sub.category ? `${sub.category} · ` : ''}{sub.billing_cycle}
                                                {sub.next_billing_date ? ` · Renews ${sub.next_billing_date}` : ''}
                                            </p>
                                        </div>
                                        <p className="shrink-0 font-semibold">{sub.monthly_cost_formatted}<span className="text-xs font-normal text-muted-foreground">/mo</span></p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <CreditCard className="mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">No active subscriptions</p>
                                <Button variant="outline" size="sm" className="mt-2" asChild>
                                    <Link href="/subscriptions/create">Add a subscription</Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Portfolio Allocation & Recent Activity */}
            <div className="mt-6 grid gap-4 lg:grid-cols-3">
                {/* Portfolio Allocation */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2 text-base">
                            <PieChartIcon className="h-4 w-4" />
                            Portfolio Allocation
                        </CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/investments">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {allocationList.length > 0 ? (
                            <div className="space-y-3">
                                {allocationList.map((item, index) => (
                                    <div key={item.type} className="space-y-1.5">
                                        <div className="flex items-center justify-between text-sm">
                                            <div className="flex items-center gap-2">
                                                <span className={`inline-block h-3 w-3 rounded-full ${allocationColors[index % allocationColors.length]}`} />
                                                <span className="font-medium">{item.type}</span>
                                                <Badge variant="secondary" className="text-[10px] px-1.5 py-0">
                                                    {item.count}
                                                </Badge>
                                            </div>
                                            <span className="text-muted-foreground">{item.percentage}%</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                                <div
                                                    className={`h-full rounded-full ${allocationColors[index % allocationColors.length]}`}
                                                    style={{ width: `${item.percentage}%` }}
                                                />
                                            </div>
                                            <span className="text-xs text-muted-foreground">{item.value_formatted}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <TrendingUp className="mb-2 h-8 w-8 text-muted-foreground/50" />
                                <p className="text-sm text-muted-foreground">No investments yet</p>
                                <Button variant="outline" size="sm" className="mt-2" asChild>
                                    <Link href="/investments/create">Add an investment</Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Recent Expenses */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">Recent Expenses</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/expenses">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {expensesList.length > 0 ? (
                            <div className="space-y-3">
                                {expensesList.map((expense) => (
                                    <div key={expense.id} className="flex items-center justify-between text-sm">
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">{expense.description}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {expense.category ? `${expense.category} · ` : ''}{formatDate(expense.date)}
                                            </p>
                                        </div>
                                        <p className="shrink-0 font-medium">{formatCurrency(expense.amount, expense.currency)}</p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No recent expenses</p>
                        )}
                    </CardContent>
                </Card>

                {/* Upcoming Bills */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">Upcoming Bills</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/utility-bills">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {billsList.length > 0 ? (
                            <div className="space-y-3">
                                {billsList.map((bill) => (
                                    <div key={bill.id} className="flex items-center justify-between text-sm">
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium">{bill.provider}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {bill.type ? `${bill.type} · ` : ''}Due {formatDate(bill.due_date)}
                                            </p>
                                        </div>
                                        <p className="shrink-0 font-medium">{formatCurrency(bill.amount, bill.currency)}</p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No upcoming bills</p>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Quick Actions */}
            <div className="mt-6">
                <h2 className="mb-3 text-lg font-semibold">Quick Actions</h2>
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    {quickActions.map((action) => (
                        <Button key={action.href} variant="outline" asChild className="h-auto flex-col gap-2 py-4">
                            <Link href={action.href}>
                                <action.icon className="h-5 w-5" />
                                <span className="text-xs">{action.label}</span>
                            </Link>
                        </Button>
                    ))}
                </div>
            </div>
        </AppLayout>
    )
}

function ChartSkeleton() {
    return (
        <Card>
            <CardHeader className="pb-2">
                <Skeleton className="h-4 w-32" />
            </CardHeader>
            <CardContent>
                <Skeleton className="h-[250px] animate-pulse rounded" />
            </CardContent>
        </Card>
    )
}

function ChartLoadingPlaceholder() {
    return (
        <div className="flex h-[250px] items-center justify-center">
            <div className="flex flex-col items-center gap-2 text-sm text-muted-foreground">
                <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted-foreground/30 border-t-muted-foreground" />
                Loading chart data...
            </div>
        </div>
    )
}
