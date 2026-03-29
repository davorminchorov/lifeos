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
import { formatCurrency, formatDate } from '@/lib/utils'

interface CapitalGainTransaction {
    investment_name: string
    symbol: string | null
    purchase_date: string
    sale_date: string | null
    purchase_price: number
    sale_price: number | null
    quantity: number
    cost_basis: number
    proceeds: number | null
    gain_loss: number
    holding_period: string
}

interface CapitalGainsReport {
    tax_year: string
    total_short_term_gains: number
    total_short_term_losses: number
    total_long_term_gains: number
    total_long_term_losses: number
    transactions: CapitalGainTransaction[]
}

interface CapitalGainsProps {
    report: CapitalGainsReport
}

export default function CapitalGains({ report }: CapitalGainsProps) {
    const netShortTerm = report.total_short_term_gains - report.total_short_term_losses
    const netLongTerm = report.total_long_term_gains - report.total_long_term_losses

    return (
        <AppLayout>
            <Head title="Capital Gains Report" />

            <PageHeader title="Capital Gains Report" description={`Tax year ${report.tax_year}`}>
                <Button variant="outline" asChild>
                    <Link href="/investments/tax-reports">Back to Tax Reports</Link>
                </Button>
            </PageHeader>

            {/* Summary */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Short-term Gains</p>
                        <p className="text-xl font-bold text-green-600">
                            {formatCurrency(report.total_short_term_gains, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Short-term Losses</p>
                        <p className="text-xl font-bold text-red-600">
                            {formatCurrency(report.total_short_term_losses, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Long-term Gains</p>
                        <p className="text-xl font-bold text-green-600">
                            {formatCurrency(report.total_long_term_gains, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Long-term Losses</p>
                        <p className="text-xl font-bold text-red-600">
                            {formatCurrency(report.total_long_term_losses, 'USD')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Net Summary */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Net Short-term</p>
                        <p className={`text-xl font-bold ${netShortTerm >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatCurrency(netShortTerm, 'USD')}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">Net Long-term</p>
                        <p className={`text-xl font-bold ${netLongTerm >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {formatCurrency(netLongTerm, 'USD')}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Transactions Table */}
            {report.transactions.length > 0 ? (
                <Card>
                    <CardHeader>
                        <CardTitle>Capital Gains Transactions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Investment</TableHead>
                                        <TableHead>Purchase Date</TableHead>
                                        <TableHead>Sale Date</TableHead>
                                        <TableHead>Quantity</TableHead>
                                        <TableHead>Cost Basis</TableHead>
                                        <TableHead>Proceeds</TableHead>
                                        <TableHead>Gain/Loss</TableHead>
                                        <TableHead>Holding Period</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {report.transactions.map((tx, index) => (
                                        <TableRow key={index}>
                                            <TableCell>
                                                <p className="font-medium">{tx.investment_name}</p>
                                                {tx.symbol ? <p className="text-xs text-muted-foreground">{tx.symbol}</p> : null}
                                            </TableCell>
                                            <TableCell>{formatDate(tx.purchase_date)}</TableCell>
                                            <TableCell>{tx.sale_date ? formatDate(tx.sale_date) : '\u2014'}</TableCell>
                                            <TableCell>{tx.quantity}</TableCell>
                                            <TableCell>{formatCurrency(tx.cost_basis, 'USD')}</TableCell>
                                            <TableCell>{tx.proceeds !== null ? formatCurrency(tx.proceeds, 'USD') : '\u2014'}</TableCell>
                                            <TableCell className={tx.gain_loss >= 0 ? 'text-green-600' : 'text-red-600'}>
                                                {formatCurrency(tx.gain_loss, 'USD')}
                                            </TableCell>
                                            <TableCell>
                                                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${
                                                    tx.holding_period === 'Long-term'
                                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                                        : 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'
                                                }`}>
                                                    {tx.holding_period}
                                                </span>
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
                        No capital gains transactions found for this tax year.
                    </CardContent>
                </Card>
            )}
        </AppLayout>
    )
}
