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
import { Pencil, Trash2, ArrowLeft, Plus } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Customer, Invoice } from '@/types/models'

interface CustomerShowProps {
    customer: Customer & {
        invoices?: Invoice[]
        outstanding_balance?: number
        credit_balance?: number
    }
}

export default function CustomerShow({ customer }: CustomerShowProps) {
    const [showDelete, setShowDelete] = useState(false)
    const invoices = customer.invoices ?? []

    const handleDelete = useCallback(() => {
        router.delete(`/invoicing/customers/${customer.id}`, {
            onFinish: () => setShowDelete(false),
        })
    }, [customer.id])

    return (
        <AppLayout>
            <Head title={customer.name} />

            <PageHeader title={customer.name} description={customer.company_name ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/invoicing/customers">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/invoicing/customers/${customer.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/invoicing/invoices/create?customer_id=${customer.id}`}>
                        <Plus className="mr-2 h-4 w-4" /> New Invoice
                    </Link>
                </Button>
                <Button variant="destructive" size="sm" onClick={() => setShowDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">{customer.name}</p>
                                </div>
                                {customer.email ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Email</p>
                                        <p className="font-medium">{customer.email}</p>
                                    </div>
                                ) : null}
                                {customer.phone ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Phone</p>
                                        <p className="font-medium">{customer.phone}</p>
                                    </div>
                                ) : null}
                                {customer.company_name ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Company</p>
                                        <p className="font-medium">{customer.company_name}</p>
                                    </div>
                                ) : null}
                                {customer.tax_id ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Tax ID</p>
                                        <p className="font-medium">{customer.tax_id}</p>
                                    </div>
                                ) : null}
                                {customer.tax_country ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Tax Country</p>
                                        <p className="font-medium">{customer.tax_country}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Currency</p>
                                    <p className="font-medium">{customer.currency}</p>
                                </div>
                            </div>
                            {customer.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{customer.notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {/* Recent Invoices */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Recent Invoices</CardTitle>
                            <Button variant="outline" size="sm" asChild>
                                <Link href={`/invoicing/invoices?customer_id=${customer.id}`}>View All</Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {invoices.length > 0 ? (
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Invoice</TableHead>
                                                <TableHead>Total</TableHead>
                                                <TableHead>Due</TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {invoices.map((invoice) => (
                                                <TableRow key={invoice.id}>
                                                    <TableCell>
                                                        <Link
                                                            href={`/invoicing/invoices/${invoice.id}`}
                                                            className="font-medium hover:underline"
                                                        >
                                                            {invoice.number ?? 'Draft'}
                                                        </Link>
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {formatCurrency(invoice.total / 100, invoice.currency)}
                                                    </TableCell>
                                                    <TableCell className="text-sm">
                                                        {invoice.due_at ? formatDate(invoice.due_at) : '\u2014'}
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
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Financial Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Outstanding Balance</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency((customer.outstanding_balance ?? 0) / 100, customer.currency)}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Available Credit</p>
                                <p className="font-medium">
                                    {formatCurrency((customer.credit_balance ?? 0) / 100, customer.currency)}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Total Invoices</p>
                                <p className="font-medium">{invoices.length}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Created</p>
                                <p className="font-medium">{formatDate(customer.created_at)}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={showDelete}
                onOpenChange={setShowDelete}
                title="Delete Customer"
                description="Are you sure you want to delete this customer? This action cannot be undone. Customers with existing invoices cannot be deleted."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
