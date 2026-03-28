import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { ChartContainer } from '@/components/charts/chart-container'
import { PieChart } from '@/components/charts/pie-chart'
import { BarChart } from '@/components/charts/bar-chart'
import { formatCurrency } from '@/lib/utils'
import type { Investment } from '@/types/models'

interface AllocationData {
    percentage: number
    value: number
    count?: number
}

interface ByTypeData {
    count: number
    total_value: number
    total_cost: number
    gain_loss: number
    gain_loss_percentage: number
}

interface DividendData {
    total_dividends: number
    average_monthly_dividend: number
    projected_annual_dividend: number
    dividends_by_year: Record<string, number>
}

interface RiskData {
    percentage: number
    value: number
}

interface ProjectInvestmentData {
    total_projects: number
    total_invested: number
    active_projects: number
    completed_projects: number
    projects: Array<{
        id: number
        name: string
        project_type: string | null
        project_stage: string | null
        project_business_model: string | null
        invested_amount: number
        current_value: number
        gain_loss: number
        gain_loss_percentage: number
        equity_percentage: number | null
        currency: string
        project_website: string | null
        project_repository: string | null
    }>
    by_stage: Record<string, { count: number; total_invested: number }>
}

interface AnalyticsProps {
    analytics: {
        overview: {
            total_value: number
            total_cost: number
            unrealized_gain_loss: number
            unrealized_gain_loss_percentage: number
            total_return: number
            total_return_percentage: number
            currency: string
        }
        allocation: Record<string, AllocationData>
        top_performers: Investment[]
        worst_performers: Investment[]
        dividends: DividendData
        risk_analysis: Record<string, RiskData>
        by_type: Record<string, ByTypeData>
        project_investments: ProjectInvestmentData
    }
    investments: Investment[]
}

export default function InvestmentAnalytics({ analytics, investments }: AnalyticsProps) {
    const currency = analytics.overview.currency

    const allocationChartData = Object.entries(analytics.allocation).map(([type, data]) => ({
        name: type.replace('_', ' '),
        value: data.value,
    }))

    const riskChartData = Object.entries(analytics.risk_analysis).map(([risk, data]) => ({
        name: risk,
        value: data.value,
    }))

    const byTypeChartData = Object.entries(analytics.by_type).map(([type, data]) => ({
        type: type.replace('_', ' '),
        value: data.total_value,
        cost: data.total_cost,
    }))

    const dividendsByYearData = Object.entries(analytics.dividends.dividends_by_year).map(([year, amount]) => ({
        year,
        amount,
    }))

    return (
        <AppLayout>
            <Head title="Investment Analytics" />

            <PageHeader title="Investment Analytics" description="Comprehensive insights into your investment portfolio">
                <Button variant="outline" asChild>
                    <Link href="/investments">Back to Investments</Link>
                </Button>
            </PageHeader>

            {/* Portfolio Overview */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Value</p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(analytics.overview.total_value, currency)}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Cost</p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(analytics.overview.total_cost, currency)}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Unrealized Gain/Loss</p>
                        <p className={`text-2xl font-bold ${analytics.overview.unrealized_gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatCurrency(analytics.overview.unrealized_gain_loss, currency)}
                            <span className="ml-1 text-sm">({analytics.overview.unrealized_gain_loss_percentage.toFixed(2)}%)</span>
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Return</p>
                        <p className={`text-2xl font-bold ${analytics.overview.total_return >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatCurrency(analytics.overview.total_return, currency)}
                            <span className="ml-1 text-sm">({analytics.overview.total_return_percentage.toFixed(2)}%)</span>
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Asset Allocation + Risk */}
            <div className="mb-6 grid gap-6 lg:grid-cols-2">
                {allocationChartData.length > 0 ? (
                    <ChartContainer title="Asset Allocation">
                        <PieChart data={allocationChartData} />
                    </ChartContainer>
                ) : null}

                {riskChartData.length > 0 ? (
                    <ChartContainer title="Risk Analysis">
                        <PieChart data={riskChartData} />
                    </ChartContainer>
                ) : null}
            </div>

            {/* Performance */}
            <div className="mb-6 grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Top Performers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {analytics.top_performers.length > 0 ? (
                            <div className="space-y-3">
                                {analytics.top_performers.map((inv) => (
                                    <div key={inv.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium">{inv.name}</p>
                                            <p className="text-xs text-muted-foreground">{inv.symbol_identifier}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm font-semibold text-green-600">
                                                +{((inv.current_value ?? 0) - inv.purchase_price * inv.quantity > 0
                                                    ? (((inv.current_value ?? 0) - inv.purchase_price * inv.quantity) / (inv.purchase_price * inv.quantity) * 100)
                                                    : 0).toFixed(2)}%
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No top performers data available.</p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Underperformers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {analytics.worst_performers.length > 0 ? (
                            <div className="space-y-3">
                                {analytics.worst_performers.map((inv) => (
                                    <div key={inv.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium">{inv.name}</p>
                                            <p className="text-xs text-muted-foreground">{inv.symbol_identifier}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm font-semibold text-red-600">
                                                {((inv.current_value ?? 0) - inv.purchase_price * inv.quantity < 0
                                                    ? (((inv.current_value ?? 0) - inv.purchase_price * inv.quantity) / (inv.purchase_price * inv.quantity) * 100)
                                                    : 0).toFixed(2)}%
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No underperformer data available.</p>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Dividend Analytics */}
            <Card className="mb-6">
                <CardHeader>
                    <CardTitle>Dividend Analytics</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="mb-6 grid gap-4 sm:grid-cols-3">
                        <div className="rounded-md bg-muted p-4">
                            <p className="text-sm text-muted-foreground">Total Dividends</p>
                            <p className="mt-1 text-xl font-bold">
                                {formatCurrency(analytics.dividends.total_dividends, currency)}
                            </p>
                        </div>
                        <div className="rounded-md bg-muted p-4">
                            <p className="text-sm text-muted-foreground">Avg Monthly Dividend</p>
                            <p className="mt-1 text-xl font-bold">
                                {formatCurrency(analytics.dividends.average_monthly_dividend, currency)}
                            </p>
                        </div>
                        <div className="rounded-md bg-muted p-4">
                            <p className="text-sm text-muted-foreground">Projected Annual</p>
                            <p className="mt-1 text-xl font-bold">
                                {formatCurrency(analytics.dividends.projected_annual_dividend, currency)}
                            </p>
                        </div>
                    </div>
                    {dividendsByYearData.length > 0 ? (
                        <ChartContainer title="Dividends by Year">
                            <BarChart
                                data={dividendsByYearData}
                                dataKeys={[{ key: 'amount', color: 'var(--chart-1)', name: 'Dividends' }]}
                                xAxisKey="year"
                            />
                        </ChartContainer>
                    ) : null}
                </CardContent>
            </Card>

            {/* By Type Table */}
            {Object.keys(analytics.by_type).length > 0 ? (
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle>Analytics by Investment Type</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {byTypeChartData.length > 0 ? (
                            <div className="mb-6">
                                <BarChart
                                    data={byTypeChartData}
                                    dataKeys={[
                                        { key: 'value', color: 'var(--chart-1)', name: 'Value' },
                                        { key: 'cost', color: 'var(--chart-2)', name: 'Cost' },
                                    ]}
                                    xAxisKey="type"
                                    showLegend
                                />
                            </div>
                        ) : null}
                        <div className="rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Count</TableHead>
                                        <TableHead>Total Value</TableHead>
                                        <TableHead>Total Cost</TableHead>
                                        <TableHead>Gain/Loss</TableHead>
                                        <TableHead>Return %</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {Object.entries(analytics.by_type).map(([type, data]) => (
                                        <TableRow key={type}>
                                            <TableCell className="font-medium capitalize">{type.replace('_', ' ')}</TableCell>
                                            <TableCell>{data.count}</TableCell>
                                            <TableCell>{formatCurrency(data.total_value, currency)}</TableCell>
                                            <TableCell>{formatCurrency(data.total_cost, currency)}</TableCell>
                                            <TableCell className={data.gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}>
                                                {formatCurrency(data.gain_loss, currency)}
                                            </TableCell>
                                            <TableCell className={`font-semibold ${data.gain_loss_percentage >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                {data.gain_loss_percentage.toFixed(2)}%
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            ) : null}

            {/* Project Investments */}
            {analytics.project_investments.total_projects > 0 ? (
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle>Project Investment Analytics</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="rounded-md bg-muted p-4">
                                <p className="text-sm text-muted-foreground">Total Projects</p>
                                <p className="mt-1 text-xl font-bold">{analytics.project_investments.total_projects}</p>
                            </div>
                            <div className="rounded-md bg-muted p-4">
                                <p className="text-sm text-muted-foreground">Total Invested</p>
                                <p className="mt-1 text-xl font-bold">
                                    {formatCurrency(analytics.project_investments.total_invested, currency)}
                                </p>
                            </div>
                            <div className="rounded-md bg-muted p-4">
                                <p className="text-sm text-muted-foreground">Active Projects</p>
                                <p className="mt-1 text-xl font-bold">{analytics.project_investments.active_projects}</p>
                            </div>
                            <div className="rounded-md bg-muted p-4">
                                <p className="text-sm text-muted-foreground">Completed Projects</p>
                                <p className="mt-1 text-xl font-bold">{analytics.project_investments.completed_projects}</p>
                            </div>
                        </div>

                        {analytics.project_investments.projects.length > 0 ? (
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold">Individual Projects</h3>
                                {analytics.project_investments.projects.map((project) => (
                                    <Card key={project.id}>
                                        <CardContent className="p-4">
                                            <div className="mb-3 flex items-start justify-between">
                                                <div>
                                                    <h4 className="font-semibold">{project.name}</h4>
                                                    <div className="mt-1 flex flex-wrap gap-2">
                                                        {project.project_type ? (
                                                            <span className="rounded bg-secondary px-2 py-0.5 text-xs capitalize">{project.project_type}</span>
                                                        ) : null}
                                                        {project.project_stage ? (
                                                            <span className="rounded bg-secondary px-2 py-0.5 text-xs capitalize">{project.project_stage}</span>
                                                        ) : null}
                                                    </div>
                                                </div>
                                                <Link href={`/investments/${project.id}`} className="text-sm text-primary hover:underline">
                                                    View Details
                                                </Link>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                                                <div>
                                                    <p className="text-xs text-muted-foreground">Invested</p>
                                                    <p className="text-sm font-semibold">{formatCurrency(project.invested_amount, project.currency)}</p>
                                                </div>
                                                <div>
                                                    <p className="text-xs text-muted-foreground">Current Value</p>
                                                    <p className="text-sm font-semibold">{formatCurrency(project.current_value, project.currency)}</p>
                                                </div>
                                                <div>
                                                    <p className="text-xs text-muted-foreground">Gain/Loss</p>
                                                    <p className={`text-sm font-semibold ${project.gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                        {project.gain_loss >= 0 ? '+' : ''}{formatCurrency(project.gain_loss, project.currency)}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs text-muted-foreground">Return</p>
                                                    <p className={`text-sm font-semibold ${project.gain_loss_percentage >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                        {project.gain_loss_percentage >= 0 ? '+' : ''}{project.gain_loss_percentage.toFixed(2)}%
                                                    </p>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            ) : null}
        </AppLayout>
    )
}
