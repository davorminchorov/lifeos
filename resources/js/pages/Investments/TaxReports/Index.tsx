import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { formatCurrency } from '@/lib/utils'
import { FileText, TrendingUp, DollarSign } from 'lucide-react'

interface TaxReportsIndexProps {
    taxSummary: {
        total_realized_gains: number
        total_realized_losses: number
        total_dividend_income: number
        tax_year: string
    }
}

export default function TaxReportsIndex({ taxSummary }: TaxReportsIndexProps) {
    const netGainLoss = taxSummary.total_realized_gains - taxSummary.total_realized_losses

    return (
        <AppLayout>
            <Head title="Tax Reports" />

            <PageHeader title="Tax Reports" description={`Tax year ${taxSummary.tax_year} overview`}>
                <Button variant="outline" asChild>
                    <Link href="/investments">Back to Investments</Link>
                </Button>
            </PageHeader>

            {/* Summary Cards */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Realized Gains</p>
                        <p className="text-2xl font-bold text-green-600">
                            {formatCurrency(taxSummary.total_realized_gains, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Realized Losses</p>
                        <p className="text-2xl font-bold text-red-600">
                            {formatCurrency(taxSummary.total_realized_losses, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Net Gain/Loss</p>
                        <p className={`text-2xl font-bold ${netGainLoss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatCurrency(netGainLoss, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Dividend Income</p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(taxSummary.total_dividend_income, 'USD')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Report Links */}
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card className="transition-colors hover:bg-muted/50">
                    <Link href={`/investments/tax-reports/capital-gains?tax_year=${taxSummary.tax_year}`}>
                        <CardHeader className="flex flex-row items-center gap-4">
                            <div className="rounded-lg bg-primary/10 p-3">
                                <TrendingUp className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle className="text-base">Capital Gains Report</CardTitle>
                                <p className="text-sm text-muted-foreground">
                                    Short-term and long-term capital gains breakdown
                                </p>
                            </div>
                        </CardHeader>
                    </Link>
                </Card>

                <Card className="transition-colors hover:bg-muted/50">
                    <Link href={`/investments/tax-reports/dividend-income?tax_year=${taxSummary.tax_year}`}>
                        <CardHeader className="flex flex-row items-center gap-4">
                            <div className="rounded-lg bg-primary/10 p-3">
                                <DollarSign className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle className="text-base">Dividend Income Report</CardTitle>
                                <p className="text-sm text-muted-foreground">
                                    Qualified and non-qualified dividend breakdown
                                </p>
                            </div>
                        </CardHeader>
                    </Link>
                </Card>

                <Card className="transition-colors hover:bg-muted/50">
                    <Link href="/investments/rebalancing/alerts">
                        <CardHeader className="flex flex-row items-center gap-4">
                            <div className="rounded-lg bg-primary/10 p-3">
                                <FileText className="h-6 w-6 text-primary" />
                            </div>
                            <div>
                                <CardTitle className="text-base">Rebalancing Alerts</CardTitle>
                                <p className="text-sm text-muted-foreground">
                                    Portfolio rebalancing recommendations
                                </p>
                            </div>
                        </CardHeader>
                    </Link>
                </Card>
            </div>
        </AppLayout>
    )
}
