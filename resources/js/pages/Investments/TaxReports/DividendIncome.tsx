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
import { formatCurrency } from '@/lib/utils'

interface DividendDetail {
    investment_name: string
    symbol: string | null
    total_dividends: number
    qualified_dividends: number
    non_qualified_dividends: number
    dividend_history: Array<{ date: string; amount: number }>
}

interface DividendIncomeReport {
    tax_year: string
    total_dividend_income: number
    total_qualified_dividends: number
    total_non_qualified_dividends: number
    dividend_details: DividendDetail[]
}

interface DividendIncomeProps {
    report: DividendIncomeReport
}

export default function DividendIncome({ report }: DividendIncomeProps) {
    return (
        <AppLayout>
            <Head title="Dividend Income Report" />

            <PageHeader title="Dividend Income Report" description={`Tax year ${report.tax_year}`}>
                <Button variant="outline" asChild>
                    <Link href="/investments/tax-reports">Back to Tax Reports</Link>
                </Button>
            </PageHeader>

            {/* Summary */}
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Total Dividend Income</p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(report.total_dividend_income, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Qualified Dividends</p>
                        <p className="text-2xl font-bold text-green-600">
                            {formatCurrency(report.total_qualified_dividends, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Non-Qualified Dividends</p>
                        <p className="text-2xl font-bold">
                            {formatCurrency(report.total_non_qualified_dividends, 'USD')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Dividend Details Table */}
            {report.dividend_details.length > 0 ? (
                <Card>
                    <CardHeader>
                        <CardTitle>Dividend Income by Investment</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Investment</TableHead>
                                        <TableHead>Total Dividends</TableHead>
                                        <TableHead>Qualified</TableHead>
                                        <TableHead>Non-Qualified</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {report.dividend_details.map((detail, index) => (
                                        <TableRow key={index}>
                                            <TableCell>
                                                <p className="font-medium">{detail.investment_name}</p>
                                                {detail.symbol ? <p className="text-xs text-muted-foreground">{detail.symbol}</p> : null}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {formatCurrency(detail.total_dividends, 'USD')}
                                            </TableCell>
                                            <TableCell className="text-green-600">
                                                {formatCurrency(detail.qualified_dividends, 'USD')}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(detail.non_qualified_dividends, 'USD')}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            ) : (
                <Card>
                    <CardContent className="p-6 text-center text-muted-foreground">
                        No dividend income found for this tax year.
                    </CardContent>
                </Card>
            )}
        </AppLayout>
    )
}
