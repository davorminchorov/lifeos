import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { formatCurrency } from '@/lib/utils'
import { ArrowLeft } from 'lucide-react'

interface CategoryAnalysis {
    category: string
    budget_amount: number
    spent_amount: number
    remaining_amount: number
    utilization_percentage: number
    status: string
    days_remaining: number
}

interface MonthlyTrend {
    month: string
    total_budgeted: number
    total_spent: number
    budgets_count: number
    exceeded_count: number
}

interface Analytics {
    total_budgeted: number
    total_spent: number
    budgets_on_track: number
    budgets_warning: number
    budgets_exceeded: number
    category_analysis: CategoryAnalysis[]
    monthly_trends: MonthlyTrend[]
}

interface BudgetAnalyticsProps {
    analytics: Analytics
}

const statusLabel: Record<string, string> = {
    exceeded: 'Over Budget',
    warning: 'Warning',
    on_track: 'On Track',
}

const statusColor: Record<string, string> = {
    exceeded: 'bg-destructive',
    warning: 'bg-yellow-500',
    on_track: 'bg-green-500',
}

const statusTextColor: Record<string, string> = {
    exceeded: 'text-destructive',
    warning: 'text-yellow-600',
    on_track: 'text-green-600',
}

function formatMonth(yearMonth: string): string {
    const [year, month] = yearMonth.split('-')
    const date = new Date(Number(year), Number(month) - 1)
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
}

export default function BudgetAnalytics({ analytics }: BudgetAnalyticsProps) {
    const totalBudgets = analytics.budgets_on_track + analytics.budgets_warning + analytics.budgets_exceeded
    const onTrackPercentage = totalBudgets > 0 ? ((analytics.budgets_on_track / totalBudgets) * 100).toFixed(1) : '0.0'
    const warningPercentage = totalBudgets > 0 ? ((analytics.budgets_warning / totalBudgets) * 100).toFixed(1) : '0.0'
    const exceededPercentage = totalBudgets > 0 ? ((analytics.budgets_exceeded / totalBudgets) * 100).toFixed(1) : '0.0'
    const overallUtilization = analytics.total_budgeted > 0
        ? ((analytics.total_spent / analytics.total_budgeted) * 100).toFixed(1)
        : '0.0'

    return (
        <AppLayout>
            <Head title="Budget Analytics" />

            <PageHeader title="Budget Analytics" description="Insights and performance analysis of your budgets">
                <Button variant="outline" asChild>
                    <Link href="/budgets">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back to Budgets
                    </Link>
                </Button>
            </PageHeader>

            <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Budgeted</p>
                        <p className="text-xl font-semibold">{formatCurrency(analytics.total_budgeted, 'MKD')}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Spent</p>
                        <p className="text-xl font-semibold">{formatCurrency(analytics.total_spent, 'MKD')}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Budgets On Track</p>
                        <p className="text-xl font-semibold">{analytics.budgets_on_track} / {totalBudgets}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">
                            {analytics.budgets_exceeded > 0 ? 'Over Budget' : 'Warnings'}
                        </p>
                        <p className="text-xl font-semibold">
                            {analytics.budgets_exceeded > 0 ? analytics.budgets_exceeded : analytics.budgets_warning}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid gap-8 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Budget Performance by Category</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {analytics.category_analysis.length > 0 ? (
                            <div className="space-y-5">
                                {analytics.category_analysis.map((cat) => {
                                    const barWidth = Math.min(cat.utilization_percentage, 100)
                                    return (
                                        <div key={cat.category}>
                                            <div className="mb-2 flex items-center justify-between">
                                                <span className="text-sm font-medium">{cat.category}</span>
                                                <div className="text-right">
                                                    <span className="text-sm font-semibold">
                                                        {formatCurrency(cat.spent_amount, 'MKD')} / {formatCurrency(cat.budget_amount, 'MKD')}
                                                    </span>
                                                    <span className="ml-1 text-xs text-muted-foreground">
                                                        ({cat.utilization_percentage}%)
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="h-2 w-full rounded-full bg-muted">
                                                <div
                                                    className={`h-2 rounded-full transition-all ${statusColor[cat.status] ?? 'bg-green-500'}`}
                                                    style={{ width: `${barWidth}%` }}
                                                />
                                            </div>
                                            <div className="mt-1 flex items-center justify-between">
                                                <span className={`text-xs font-medium ${statusTextColor[cat.status] ?? ''}`}>
                                                    {statusLabel[cat.status] ?? cat.status}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {formatCurrency(cat.remaining_amount, 'MKD')} remaining
                                                    {cat.days_remaining > 0 ? ` \u00B7 ${cat.days_remaining} days left` : ''}
                                                </span>
                                            </div>
                                        </div>
                                    )
                                })}
                            </div>
                        ) : (
                            <p className="py-8 text-center text-muted-foreground">No active budgets found</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Budget Performance Trends</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {analytics.monthly_trends.length > 0 ? (
                            <div className="space-y-4">
                                {analytics.monthly_trends.slice(0, 6).map((trend) => {
                                    const utilization = trend.total_budgeted > 0
                                        ? Math.round((trend.total_spent / trend.total_budgeted) * 100 * 10) / 10
                                        : 0
                                    const barColor = utilization >= 100
                                        ? 'bg-destructive'
                                        : utilization >= 80
                                            ? 'bg-yellow-500'
                                            : 'bg-green-500'
                                    return (
                                        <div key={trend.month}>
                                            <div className="flex items-center justify-between">
                                                <span className="text-sm font-medium">{formatMonth(trend.month)}</span>
                                                <div className="text-right">
                                                    <div className="text-sm font-semibold">
                                                        {formatCurrency(trend.total_spent, 'MKD')} / {formatCurrency(trend.total_budgeted, 'MKD')}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {utilization}% utilized
                                                        {trend.exceeded_count > 0 ? ` \u00B7 ${trend.exceeded_count} exceeded` : ''}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="mt-1 h-1 w-full rounded-full bg-muted">
                                                <div
                                                    className={`h-1 rounded-full transition-all ${barColor}`}
                                                    style={{ width: `${Math.min(utilization, 100)}%` }}
                                                />
                                            </div>
                                        </div>
                                    )
                                })}
                            </div>
                        ) : (
                            <p className="py-8 text-center text-muted-foreground">No trend data available</p>
                        )}
                    </CardContent>
                </Card>
            </div>

            <div className="mt-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Budget Status Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-6 sm:grid-cols-3">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {analytics.budgets_on_track}
                                </div>
                                <div className="text-sm text-muted-foreground">Budgets On Track</div>
                                <div className="mt-2">
                                    <div className="h-2 w-full rounded-full bg-muted">
                                        <div
                                            className="h-2 rounded-full bg-green-500"
                                            style={{ width: `${onTrackPercentage}%` }}
                                        />
                                    </div>
                                    <div className="mt-1 text-xs text-muted-foreground">{onTrackPercentage}%</div>
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {analytics.budgets_warning}
                                </div>
                                <div className="text-sm text-muted-foreground">Budgets at Warning</div>
                                <div className="mt-2">
                                    <div className="h-2 w-full rounded-full bg-muted">
                                        <div
                                            className="h-2 rounded-full bg-yellow-500"
                                            style={{ width: `${warningPercentage}%` }}
                                        />
                                    </div>
                                    <div className="mt-1 text-xs text-muted-foreground">{warningPercentage}%</div>
                                </div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-destructive">
                                    {analytics.budgets_exceeded}
                                </div>
                                <div className="text-sm text-muted-foreground">Budgets Exceeded</div>
                                <div className="mt-2">
                                    <div className="h-2 w-full rounded-full bg-muted">
                                        <div
                                            className="h-2 rounded-full bg-destructive"
                                            style={{ width: `${exceededPercentage}%` }}
                                        />
                                    </div>
                                    <div className="mt-1 text-xs text-muted-foreground">{exceededPercentage}%</div>
                                </div>
                            </div>
                        </div>

                        {analytics.total_budgeted > 0 ? (
                            <div className="mt-6 rounded-lg bg-muted/50 p-4">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h4 className="text-sm font-medium">Overall Budget Performance</h4>
                                        <p className="text-sm text-muted-foreground">
                                            You've spent {overallUtilization}% of your total budget
                                        </p>
                                    </div>
                                    <div className="text-right">
                                        <div className="text-lg font-bold">
                                            {formatCurrency(analytics.total_spent, 'MKD')} / {formatCurrency(analytics.total_budgeted, 'MKD')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}
