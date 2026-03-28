import { Head, Link } from '@inertiajs/react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
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
import {
    FileText,
    Users,
    DollarSign,
    AlertCircle,
    CreditCard,
    Plus,
    TrendingUp,
} from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Invoice, Customer, Payment } from '@/types/models'

interface DashboardProps {
    summary: {
        total_invoices: number
        total_customers: number
        total_revenue: number
        outstanding_amount: number
        draft_invoices: number
        overdue_invoices: number
        available_credit: number
    }
    revenueByMonth: Record<string, number>
    topCustomers: Array<Customer & { total_revenue: number }>
    recentInvoices: Array<Invoice & { customer?: Customer }>
    recentPayments: Array<Payment & { invoice?: Invoice & { customer?: Customer } }>
    statusBreakdown: Record<string, number>
    startDate: string
    endDate: string
}

export default function InvoicingDashboard({
    summary,
    revenueByMonth,
    topCustomers,
    recentInvoices,
    recentPayments,
    statusBreakdown,
}: DashboardProps) {
    const revenueMonths = Object.keys(revenueByMonth)
    const revenueValues = Object.values(revenueByMonth)
    const maxRevenue = Math.max(...revenueValues, 1)

    return (
        <AppLayout>
            <Head title="Invoicing Dashboard" />

            <PageHeader title="Invoicing Dashboard" description="Overview of your invoicing activity">
                <Button asChild>
                    <Link href="/invoicing/invoices/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Invoice
                    </Link>
                </Button>
            </PageHeader>

            {/* Summary Stats */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="rounded-md bg-blue-100 p-2 dark:bg-blue-900">
                                <FileText className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Total Invoices</p>
                                <p className="text-xl font-semibold">{summary.total_invoices}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="rounded-md bg-green-100 p-2 dark:bg-green-900">
                                <DollarSign className="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Total Revenue</p>
                                <p className="text-xl font-semibold">{formatCurrency(summary.total_revenue / 100)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="rounded-md bg-orange-100 p-2 dark:bg-orange-900">
                                <TrendingUp className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Outstanding</p>
                                <p className="text-xl font-semibold">{formatCurrency(summary.outstanding_amount / 100)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="rounded-md bg-red-100 p-2 dark:bg-red-900">
                                <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Overdue</p>
                                <p className="text-xl font-semibold">{summary.overdue_invoices}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <Users className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p className="text-sm text-muted-foreground">Customers</p>
                                <p className="text-xl font-semibold">{summary.total_customers}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <FileText className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p className="text-sm text-muted-foreground">Draft Invoices</p>
                                <p className="text-xl font-semibold">{summary.draft_invoices}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-3">
                            <CreditCard className="h-5 w-5 text-muted-foreground" />
                            <div>
                                <p className="text-sm text-muted-foreground">Available Credit</p>
                                <p className="text-xl font-semibold">{formatCurrency(summary.available_credit / 100)}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Revenue by Month */}
            {revenueMonths.length > 0 ? (
                <Card className="mb-6">
                    <CardHeader>
                        <CardTitle>Revenue by Month</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-end gap-2" style={{ height: 200 }}>
                            {revenueMonths.map((month) => {
                                const value = revenueByMonth[month] ?? 0
                                const height = Math.max((value / maxRevenue) * 100, 2)
                                return (
                                    <div key={month} className="flex flex-1 flex-col items-center gap-1">
                                        <span className="text-xs text-muted-foreground">
                                            {formatCurrency(value / 100)}
                                        </span>
                                        <div
                                            className="w-full rounded-t bg-primary"
                                            style={{ height: `${height}%` }}
                                        />
                                        <span className="text-xs text-muted-foreground">{month}</span>
                                    </div>
                                )
                            })}
                        </div>
                    </CardContent>
                </Card>
            ) : null}

            <div className="grid gap-6 lg:grid-cols-2">
                {/* Recent Invoices */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Recent Invoices</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/invoicing/invoices">View All</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {recentInvoices.length > 0 ? (
                            <div className="rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Invoice</TableHead>
                                            <TableHead>Customer</TableHead>
                                            <TableHead>Total</TableHead>
                                            <TableHead>Status</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recentInvoices.map((invoice) => (
                                            <TableRow key={invoice.id}>
                                                <TableCell>
                                                    <Link
                                                        href={`/invoicing/invoices/${invoice.id}`}
                                                        className="font-medium hover:underline"
                                                    >
                                                        {invoice.number ?? 'Draft'}
                                                    </Link>
                                                </TableCell>
                                                <TableCell className="text-sm text-muted-foreground">
                                                    {invoice.customer?.name ?? '\u2014'}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {formatCurrency(invoice.total / 100, invoice.currency)}
                                                </TableCell>
                                                <TableCell>
                                                    <StatusBadge status={invoice.status} />
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No invoices yet.</p>
                        )}
                    </CardContent>
                </Card>

                {/* Top Customers */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>Top Customers</CardTitle>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/invoicing/customers">View All</Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        {topCustomers.length > 0 ? (
                            <div className="rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Customer</TableHead>
                                            <TableHead>Revenue</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {topCustomers.map((customer) => (
                                            <TableRow key={customer.id}>
                                                <TableCell>
                                                    <Link
                                                        href={`/invoicing/customers/${customer.id}`}
                                                        className="font-medium hover:underline"
                                                    >
                                                        {customer.name}
                                                    </Link>
                                                    {customer.company_name ? (
                                                        <p className="text-xs text-muted-foreground">
                                                            {customer.company_name}
                                                        </p>
                                                    ) : null}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {formatCurrency((customer.total_revenue ?? 0) / 100, customer.currency)}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No customers yet.</p>
                        )}
                    </CardContent>
                </Card>

                {/* Recent Payments */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Payments</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentPayments.length > 0 ? (
                            <div className="rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Date</TableHead>
                                            <TableHead>Invoice</TableHead>
                                            <TableHead>Amount</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recentPayments.map((payment) => (
                                            <TableRow key={payment.id}>
                                                <TableCell className="text-sm">
                                                    {payment.payment_date ? formatDate(payment.payment_date) : '\u2014'}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {payment.invoice ? (
                                                        <Link
                                                            href={`/invoicing/invoices/${payment.invoice.id}`}
                                                            className="hover:underline"
                                                        >
                                                            {payment.invoice.number ?? 'Draft'}
                                                        </Link>
                                                    ) : '\u2014'}
                                                </TableCell>
                                                <TableCell className="text-sm font-medium">
                                                    {formatCurrency(payment.amount / 100, payment.currency)}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No payments yet.</p>
                        )}
                    </CardContent>
                </Card>

                {/* Status Breakdown */}
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice Status Breakdown</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {Object.keys(statusBreakdown).length > 0 ? (
                            <div className="space-y-3">
                                {Object.entries(statusBreakdown).map(([status, count]) => (
                                    <div key={status} className="flex items-center justify-between">
                                        <StatusBadge status={status} />
                                        <span className="text-sm font-medium">{count}</span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">No data yet.</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    )
}
