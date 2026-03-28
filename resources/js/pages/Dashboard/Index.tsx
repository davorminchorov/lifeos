import { Head, Link } from '@inertiajs/react'
import { lazy, Suspense, useCallback, useState } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatCard } from '@/components/shared/stat-card'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Skeleton } from '@/components/ui/skeleton'
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

const quickActions = [
    { label: 'Add Subscription', href: '/subscriptions/create', icon: CreditCard },
    { label: 'Add Expense', href: '/expenses/create', icon: Receipt },
    { label: 'Add Contract', href: '/contracts/create', icon: FileSignature },
    { label: 'Add Warranty', href: '/warranties/create', icon: Shield },
    { label: 'Add Investment', href: '/investments/create', icon: TrendingUp },
    { label: 'Add Utility Bill', href: '/utility-bills/create', icon: Zap },
]

export default function DashboardIndex({ stats, alerts, recent_expenses, upcoming_bills }: DashboardProps) {
    const [chartData, setChartData] = useState<ChartData | null>(null)
    const [chartPeriod, setChartPeriod] = useState('6m')
    const [chartLoading, setChartLoading] = useState(false)

    const fetchChartData = useCallback(async (period: string) => {
        setChartLoading(true)
        try {
            const response = await fetch(`/dashboard/chart-data?period=${period}`)
            const data = await response.json()
            setChartData(data)
        } finally {
            setChartLoading(false)
        }
    }, [])

    const handlePeriodChange = useCallback((period: string) => {
        setChartPeriod(period)
        fetchChartData(period)
    }, [fetchChartData])

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

            {/* Charts Section */}
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <Suspense fallback={<Skeleton className="h-[350px] rounded-lg" />}>
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
                            <div className="flex h-[250px] items-center justify-center text-sm text-muted-foreground">
                                Click refresh to load chart data
                            </div>
                        )}
                    </ChartContainer>
                </Suspense>

                <Suspense fallback={<Skeleton className="h-[350px] rounded-lg" />}>
                    <ChartContainer title="Category Breakdown">
                        {chartData?.categoryBreakdown ? (
                            <PieChart data={chartData.categoryBreakdown} height={250} />
                        ) : (
                            <div className="flex h-[250px] items-center justify-center text-sm text-muted-foreground">
                                Click refresh to load chart data
                            </div>
                        )}
                    </ChartContainer>
                </Suspense>
            </div>

            {/* Alerts */}
            {alerts.length > 0 ? (
                <div className="mt-6 space-y-3">
                    <h2 className="text-lg font-semibold">Alerts & Notifications</h2>
                    {alerts.map((alert, index) => {
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

            {/* Recent Activity */}
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Recent Expenses</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recent_expenses.length > 0 ? (
                            <div className="space-y-3">
                                {recent_expenses.map((expense) => (
                                    <div key={expense.id} className="flex items-center justify-between text-sm">
                                        <div>
                                            <p className="font-medium">{expense.description}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {expense.category ? `${expense.category} · ` : ''}{formatDate(expense.date)}
                                            </p>
                                        </div>
                                        <p className="font-medium">{formatCurrency(expense.amount, expense.currency)}</p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No recent expenses</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Upcoming Bills</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {upcoming_bills.length > 0 ? (
                            <div className="space-y-3">
                                {upcoming_bills.map((bill) => (
                                    <div key={bill.id} className="flex items-center justify-between text-sm">
                                        <div>
                                            <p className="font-medium">{bill.provider}</p>
                                            <p className="text-xs text-muted-foreground">
                                                {bill.type ? `${bill.type} · ` : ''}Due {formatDate(bill.due_date)}
                                            </p>
                                        </div>
                                        <p className="font-medium">{formatCurrency(bill.amount, bill.currency)}</p>
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
