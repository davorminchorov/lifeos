import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Pencil, Trash2, ArrowLeft, Send, FileDown, CheckCircle, XCircle } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Invoice, Customer, InvoiceItem, Payment } from '@/types/models'

interface InvoiceShowProps {
    invoice: Invoice & {
        customer?: Customer
        items?: InvoiceItem[]
        payments?: Payment[]
    }
}

export default function InvoiceShow({ invoice }: InvoiceShowProps) {
    const [confirmAction, setConfirmAction] = useState<'delete' | 'issue' | 'void' | null>(null)
    const items = invoice.items ?? []
    const payments = invoice.payments ?? []

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/invoicing/invoices/${invoice.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'issue') {
            router.post(`/invoicing/invoices/${invoice.id}/issue`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'void') {
            router.post(`/invoicing/invoices/${invoice.id}/void`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, invoice.id])

    const confirmTitle = confirmAction === 'delete'
        ? 'Delete Invoice'
        : confirmAction === 'issue'
            ? 'Issue Invoice'
            : confirmAction === 'void'
                ? 'Void Invoice'
                : ''

    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this draft invoice? This action cannot be undone.'
        : confirmAction === 'issue'
            ? 'Are you sure you want to issue this invoice? It will be sent to the customer.'
            : confirmAction === 'void'
                ? 'Are you sure you want to void this invoice? This action cannot be undone.'
                : ''

    return (
        <AppLayout>
            <Head title={`Invoice ${invoice.number ?? 'Draft'}`} />

            <PageHeader
                title={`Invoice ${invoice.number ?? 'Draft'}`}
                description={invoice.customer?.name ?? undefined}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/invoicing/invoices">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                {invoice.status === 'draft' ? (
                    <>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/invoicing/invoices/${invoice.id}/edit`}>
                                <Pencil className="mr-2 h-4 w-4" /> Edit
                            </Link>
                        </Button>
                        <Button variant="default" size="sm" onClick={() => setConfirmAction('issue')}>
                            <Send className="mr-2 h-4 w-4" /> Issue
                        </Button>
                        <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                            <Trash2 className="mr-2 h-4 w-4" /> Delete
                        </Button>
                    </>
                ) : null}
                {invoice.status === 'issued' || invoice.status === 'partially_paid' || invoice.status === 'past_due' ? (
                    <>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={`/invoicing/invoices/${invoice.id}/pdf`}>
                                <FileDown className="mr-2 h-4 w-4" /> PDF
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setConfirmAction('void')}>
                            <XCircle className="mr-2 h-4 w-4" /> Void
                        </Button>
                    </>
                ) : null}
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    {/* Invoice Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Invoice Number</p>
                                    <p className="font-medium">{invoice.number ?? 'Draft'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={invoice.status} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Customer</p>
                                    {invoice.customer ? (
                                        <Link
                                            href={`/invoicing/customers/${invoice.customer.id}`}
                                            className="font-medium hover:underline"
                                        >
                                            {invoice.customer.name}
                                        </Link>
                                    ) : (
                                        <p className="font-medium">{'\u2014'}</p>
                                    )}
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Currency</p>
                                    <p className="font-medium">{invoice.currency}</p>
                                </div>
                                {invoice.issued_at ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Issued Date</p>
                                        <p className="font-medium">{formatDate(invoice.issued_at)}</p>
                                    </div>
                                ) : null}
                                {invoice.due_at ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Due Date</p>
                                        <p className="font-medium">{formatDate(invoice.due_at)}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Net Terms</p>
                                    <p className="font-medium">{invoice.net_terms_days} days</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Tax Behavior</p>
                                    <p className="font-medium capitalize">{invoice.tax_behavior}</p>
                                </div>
                            </div>
                            {invoice.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{invoice.notes}</p>
                                    </div>
                                </>
                            ) : null}
                            {invoice.internal_notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Internal Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{invoice.internal_notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {/* Line Items */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Line Items</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {items.length > 0 ? (
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Description</TableHead>
                                                <TableHead className="w-[80px]">Qty</TableHead>
                                                <TableHead className="w-[120px]">Unit Price</TableHead>
                                                <TableHead className="w-[100px]">Tax</TableHead>
                                                <TableHead className="w-[120px] text-right">Total</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {items.map((item) => (
                                                <TableRow key={item.id}>
                                                    <TableCell>
                                                        <p className="font-medium">{item.name}</p>
                                                        {item.description ? (
                                                            <p className="text-xs text-muted-foreground">{item.description}</p>
                                                        ) : null}
                                                    </TableCell>
                                                    <TableCell className="text-sm">{item.quantity}</TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatCurrency(item.unit_amount / 100, invoice.currency)}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatCurrency(item.tax_amount / 100, invoice.currency)}
                                                    </TableCell>
                                                    <TableCell className="text-right font-medium">
                                                        {formatCurrency(item.total_amount / 100, invoice.currency)}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No line items.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Payments */}
                    {payments.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Payments</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Method</TableHead>
                                                <TableHead>Reference</TableHead>
                                                <TableHead className="text-right">Amount</TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {payments.map((payment) => (
                                                <TableRow key={payment.id}>
                                                    <TableCell className="text-sm">
                                                        {payment.payment_date ? formatDate(payment.payment_date) : '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-sm capitalize">
                                                        {payment.payment_method?.replace(/_/g, ' ') ?? '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {payment.reference ?? '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-right text-sm font-medium">
                                                        {formatCurrency(payment.amount / 100, payment.currency)}
                                                    </TableCell>
                                                    <TableCell>
                                                        <StatusBadge status={payment.status} />
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                {/* Right Column - Financial Summary */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Subtotal</p>
                                <p className="font-medium">{formatCurrency(invoice.subtotal / 100, invoice.currency)}</p>
                            </div>
                            {invoice.discount_total > 0 ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Discount</p>
                                    <p className="font-medium text-green-600">
                                        -{formatCurrency(invoice.discount_total / 100, invoice.currency)}
                                    </p>
                                </div>
                            ) : null}
                            <div>
                                <p className="text-sm text-muted-foreground">Tax</p>
                                <p className="font-medium">{formatCurrency(invoice.tax_total / 100, invoice.currency)}</p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Total</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(invoice.total / 100, invoice.currency)}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Amount Paid</p>
                                <p className="font-medium text-green-600">
                                    {formatCurrency(invoice.amount_paid / 100, invoice.currency)}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Amount Due</p>
                                <p className="text-xl font-semibold">
                                    {formatCurrency(invoice.amount_due / 100, invoice.currency)}
                                </p>
                            </div>
                            {invoice.paid_at ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Paid At</p>
                                    <p className="font-medium">{formatDate(invoice.paid_at)}</p>
                                </div>
                            ) : null}
                            {invoice.voided_at ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Voided At</p>
                                    <p className="font-medium">{formatDate(invoice.voided_at)}</p>
                                </div>
                            ) : null}
                            {invoice.last_sent_at ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Last Sent</p>
                                    <p className="font-medium">{formatDate(invoice.last_sent_at)}</p>
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmTitle}
                description={confirmDescription}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction === 'delete' ? 'Delete' : confirmAction === 'issue' ? 'Issue Invoice' : 'Void Invoice'}
                variant={confirmAction === 'delete' || confirmAction === 'void' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
