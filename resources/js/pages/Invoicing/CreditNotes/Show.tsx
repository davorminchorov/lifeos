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
import { ArrowLeft, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { CreditNote, Customer, Invoice, CreditNoteApplication } from '@/types/models'

interface CreditNoteShowProps {
    creditNote: CreditNote & {
        customer?: Customer
        invoice?: Invoice
        applications?: Array<CreditNoteApplication & { invoice?: Invoice }>
    }
    availableInvoices: Array<Invoice & { customer?: Customer }>
}

export default function CreditNoteShow({ creditNote, availableInvoices }: CreditNoteShowProps) {
    const [showDelete, setShowDelete] = useState(false)
    const applications = creditNote.applications ?? []

    const handleDelete = useCallback(() => {
        router.delete(`/invoicing/credit-notes/${creditNote.id}`, {
            onFinish: () => setShowDelete(false),
        })
    }, [creditNote.id])

    const handleApply = useCallback((invoiceId: number) => {
        const maxAmount = Math.min(
            creditNote.amount_remaining,
            availableInvoices.find(inv => inv.id === invoiceId)?.amount_due ?? 0
        )
        if (maxAmount <= 0) return

        router.post(`/invoicing/credit-notes/${creditNote.id}/apply`, {
            invoice_id: invoiceId,
            amount: maxAmount,
        }, { preserveScroll: true })
    }, [creditNote.id, creditNote.amount_remaining, availableInvoices])

    return (
        <AppLayout>
            <Head title={`Credit Note ${creditNote.number}`} />

            <PageHeader
                title={`Credit Note ${creditNote.number}`}
                description={creditNote.customer?.name ?? undefined}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/invoicing/credit-notes">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                {applications.length === 0 ? (
                    <Button variant="destructive" size="sm" onClick={() => setShowDelete(true)}>
                        <Trash2 className="mr-2 h-4 w-4" /> Delete
                    </Button>
                ) : null}
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Credit Note Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Number</p>
                                    <p className="font-medium">{creditNote.number}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={creditNote.status} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Customer</p>
                                    {creditNote.customer ? (
                                        <Link
                                            href={`/invoicing/customers/${creditNote.customer.id}`}
                                            className="font-medium hover:underline"
                                        >
                                            {creditNote.customer.name}
                                        </Link>
                                    ) : (
                                        <p className="font-medium">{'\u2014'}</p>
                                    )}
                                </div>
                                {creditNote.invoice ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Related Invoice</p>
                                        <Link
                                            href={`/invoicing/invoices/${creditNote.invoice.id}`}
                                            className="font-medium hover:underline"
                                        >
                                            {creditNote.invoice.number ?? `Draft #${creditNote.invoice.id}`}
                                        </Link>
                                    </div>
                                ) : null}
                                {creditNote.reason ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Reason</p>
                                        <p className="font-medium capitalize">{creditNote.reason.replace(/_/g, ' ')}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Currency</p>
                                    <p className="font-medium">{creditNote.currency}</p>
                                </div>
                                {creditNote.issued_at ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Issued At</p>
                                        <p className="font-medium">{formatDate(creditNote.issued_at)}</p>
                                    </div>
                                ) : null}
                            </div>
                            {creditNote.reason_notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{creditNote.reason_notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {/* Applications */}
                    {applications.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Applications</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Invoice</TableHead>
                                                <TableHead>Amount Applied</TableHead>
                                                <TableHead>Date</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {applications.map((app) => (
                                                <TableRow key={app.id}>
                                                    <TableCell>
                                                        {app.invoice ? (
                                                            <Link
                                                                href={`/invoicing/invoices/${app.invoice.id}`}
                                                                className="font-medium hover:underline"
                                                            >
                                                                {app.invoice.number ?? `Draft #${app.invoice.id}`}
                                                            </Link>
                                                        ) : '\u2014'}
                                                    </TableCell>
                                                    <TableCell className="text-sm font-medium">
                                                        {formatCurrency(app.amount_applied / 100, creditNote.currency)}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {app.applied_at ? formatDate(app.applied_at) : formatDate(app.created_at)}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {/* Apply to Invoice */}
                    {creditNote.status === 'issued' && creditNote.amount_remaining > 0 && availableInvoices.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Apply to Invoice</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Invoice</TableHead>
                                                <TableHead>Amount Due</TableHead>
                                                <TableHead className="w-[100px]" />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {availableInvoices.map((inv) => (
                                                <TableRow key={inv.id}>
                                                    <TableCell>
                                                        <Link
                                                            href={`/invoicing/invoices/${inv.id}`}
                                                            className="font-medium hover:underline"
                                                        >
                                                            {inv.number ?? `Draft #${inv.id}`}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatCurrency(inv.amount_due / 100, inv.currency)}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleApply(inv.id)}
                                                        >
                                                            Apply
                                                        </Button>
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

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Total Amount</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(creditNote.total / 100, creditNote.currency)}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Amount Remaining</p>
                                <p className="text-xl font-semibold">
                                    {formatCurrency(creditNote.amount_remaining / 100, creditNote.currency)}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Amount Applied</p>
                                <p className="font-medium">
                                    {formatCurrency((creditNote.total - creditNote.amount_remaining) / 100, creditNote.currency)}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Created</p>
                                <p className="font-medium">{formatDate(creditNote.created_at)}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={showDelete}
                onOpenChange={setShowDelete}
                title="Delete Credit Note"
                description="Are you sure you want to delete this credit note? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
