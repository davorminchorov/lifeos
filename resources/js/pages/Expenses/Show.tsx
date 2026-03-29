import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Pencil, Trash2, ArrowLeft, Copy, CheckCircle } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Expense } from '@/types/models'

interface ExpenseShowProps {
    expense: Expense
}

export default function ExpenseShow({ expense }: ExpenseShowProps) {
    const [confirmAction, setConfirmAction] = useState<'delete' | 'reimburse' | 'duplicate' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/expenses/${expense.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'reimburse') {
            router.patch(`/expenses/${expense.id}/mark-reimbursed`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'duplicate') {
            router.post(`/expenses/${expense.id}/duplicate`, {}, {
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, expense.id])

    const confirmTitle = confirmAction === 'delete'
        ? 'Delete Expense'
        : confirmAction === 'reimburse'
            ? 'Mark as Reimbursed'
            : confirmAction === 'duplicate'
                ? 'Duplicate Expense'
                : ''

    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this expense? This action cannot be undone.'
        : confirmAction === 'reimburse'
            ? 'Are you sure you want to mark this expense as reimbursed?'
            : confirmAction === 'duplicate'
                ? 'This will create a copy of the expense with today\'s date and pending status.'
                : ''

    const tags = Array.isArray(expense.tags) ? expense.tags : []
    const currency = expense.currency ?? 'MKD'

    return (
        <AppLayout>
            <Head title={expense.description ?? 'Expense Details'} />

            <PageHeader title="Expense Details" description={expense.description ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/expenses">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/expenses/${expense.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="outline" size="sm" onClick={() => setConfirmAction('duplicate')}>
                    <Copy className="mr-2 h-4 w-4" /> Duplicate
                </Button>
                {expense.status !== 'reimbursed' ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('reimburse')}>
                        <CheckCircle className="mr-2 h-4 w-4" /> Mark Reimbursed
                    </Button>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Expense Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Description</p>
                                    <p className="font-medium">{expense.description ?? '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={expense.status ?? 'pending'} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Category</p>
                                    <p className="font-medium">{expense.category}</p>
                                </div>
                                {expense.subcategory ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Subcategory</p>
                                        <p className="font-medium">{expense.subcategory}</p>
                                    </div>
                                ) : null}
                                {expense.merchant ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Merchant</p>
                                        <p className="font-medium">{expense.merchant}</p>
                                    </div>
                                ) : null}
                                {expense.payment_method ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Payment Method</p>
                                        <p className="font-medium capitalize">{expense.payment_method.replace('_', ' ')}</p>
                                    </div>
                                ) : null}
                                {expense.expense_type ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Type</p>
                                        <p className="font-medium capitalize">{expense.expense_type}</p>
                                    </div>
                                ) : null}
                                {expense.location ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Location</p>
                                        <p className="font-medium">{expense.location}</p>
                                    </div>
                                ) : null}
                            </div>
                            {tags.length > 0 ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Tags</p>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {tags.map((tag) => (
                                                <span key={tag} className="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                                    {tag}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </>
                            ) : null}
                            {expense.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{expense.notes}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Dates</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Expense Date</p>
                                    <p className="font-medium">{formatDate(expense.expense_date)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Created</p>
                                    <p className="font-medium">{formatDate(expense.created_at)}</p>
                                </div>
                                {expense.updated_at !== expense.created_at ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Last Updated</p>
                                        <p className="font-medium">{formatDate(expense.updated_at)}</p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Amount</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(expense.amount, currency)}
                                </p>
                                <p className="text-sm text-muted-foreground">{currency}</p>
                            </div>
                            {expense.budget_allocated ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Budget Allocated</p>
                                        <p className="font-medium">{formatCurrency(expense.budget_allocated, currency)}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Attributes</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Tax Deductible</p>
                                <p className="font-medium">{expense.is_tax_deductible ? 'Yes' : 'No'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Recurring</p>
                                <p className="font-medium">{expense.is_recurring ? 'Yes' : 'No'}</p>
                            </div>
                            {expense.is_recurring && expense.recurring_schedule ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Recurring Schedule</p>
                                    <p className="font-medium capitalize">{expense.recurring_schedule}</p>
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
                confirmLabel={confirmAction === 'delete' ? 'Delete' : 'Confirm'}
                variant={confirmAction === 'delete' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
