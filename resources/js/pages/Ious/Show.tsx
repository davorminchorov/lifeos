import { Head, Link, router, useForm } from '@inertiajs/react'
import { useState, useCallback, type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { FormField } from '@/components/shared/form-field'
import { DatePicker } from '@/components/shared/date-picker'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Pencil, Trash2, ArrowLeft, DollarSign, CheckCircle, XCircle } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Iou } from '@/types/models'

interface IouShowProps {
    iou: Iou
}

const paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'PayPal', 'Venmo', 'Other']

export default function IouShow({ iou }: IouShowProps) {
    const [confirmAction, setConfirmAction] = useState<'delete' | 'mark_paid' | 'cancel' | null>(null)
    const [showRecordPayment, setShowRecordPayment] = useState(false)

    const remaining = iou.amount - (iou.amount_paid ?? 0)

    const paymentForm = useForm({
        payment_amount: '',
        payment_date: '',
        payment_method: '',
    })

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        if (confirmAction === 'delete') {
            router.delete(`/ious/${iou.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'mark_paid') {
            router.patch(`/ious/${iou.id}/mark-paid`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'cancel') {
            router.patch(`/ious/${iou.id}/cancel`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, iou.id])

    const handleRecordPayment = useCallback((e: FormEvent) => {
        e.preventDefault()
        paymentForm.post(`/ious/${iou.id}/record-payment`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowRecordPayment(false)
                paymentForm.reset()
            },
        })
    }, [iou.id, paymentForm])

    const confirmTitle = confirmAction === 'delete'
        ? 'Delete IOU'
        : confirmAction === 'mark_paid'
            ? 'Mark as Paid'
            : 'Cancel IOU'

    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this IOU? This action cannot be undone.'
        : confirmAction === 'mark_paid'
            ? 'Are you sure you want to mark this IOU as fully paid?'
            : 'Are you sure you want to cancel this IOU?'

    const isActive = iou.status === 'pending' || iou.status === 'partially_paid'

    return (
        <AppLayout>
            <Head title={`IOU - ${iou.person_name}`} />

            <PageHeader
                title={`IOU - ${iou.person_name}`}
                description={iou.type === 'owe' ? 'I owe this person' : 'This person owes me'}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/ious">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/ious/${iou.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {isActive ? (
                    <>
                        <Button variant="outline" size="sm" onClick={() => setShowRecordPayment(true)}>
                            <DollarSign className="mr-2 h-4 w-4" /> Record Payment
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setConfirmAction('mark_paid')}>
                            <CheckCircle className="mr-2 h-4 w-4" /> Mark Paid
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setConfirmAction('cancel')}>
                            <XCircle className="mr-2 h-4 w-4" /> Cancel
                        </Button>
                    </>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>IOU Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Person</p>
                                    <p className="font-medium">{iou.person_name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={iou.status} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Type</p>
                                    <p className={`font-medium ${iou.type === 'owe' ? 'text-red-600' : 'text-green-600'}`}>
                                        {iou.type === 'owe' ? 'I Owe' : 'Owed to Me'}
                                    </p>
                                </div>
                                {iou.category ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Category</p>
                                        <p className="font-medium">{iou.category}</p>
                                    </div>
                                ) : null}
                                {iou.payment_method ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Payment Method</p>
                                        <p className="font-medium">{iou.payment_method}</p>
                                    </div>
                                ) : null}
                                {iou.is_recurring ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Recurring</p>
                                        <p className="font-medium">{iou.recurring_schedule ?? 'Yes'}</p>
                                    </div>
                                ) : null}
                            </div>
                            {iou.description ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Description</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{iou.description}</p>
                                    </div>
                                </>
                            ) : null}
                            {iou.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{iou.notes}</p>
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
                                    <p className="text-sm text-muted-foreground">Transaction Date</p>
                                    <p className="font-medium">{iou.transaction_date ? formatDate(iou.transaction_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Due Date</p>
                                    <p className="font-medium">{iou.due_date ? formatDate(iou.due_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Created</p>
                                    <p className="font-medium">{formatDate(iou.created_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Total Amount</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(iou.amount, iou.currency ?? 'MKD')}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Amount Paid</p>
                                <p className="font-semibold text-green-600">
                                    {formatCurrency(iou.amount_paid ?? 0, iou.currency ?? 'MKD')}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Remaining</p>
                                <p className={`font-semibold ${remaining > 0 ? 'text-red-600' : 'text-green-600'}`}>
                                    {formatCurrency(remaining, iou.currency ?? 'MKD')}
                                </p>
                            </div>
                            {iou.amount > 0 ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Progress</p>
                                        <div className="mt-2 h-2 rounded-full bg-secondary">
                                            <div
                                                className="h-2 rounded-full bg-primary"
                                                style={{ width: `${Math.min(((iou.amount_paid ?? 0) / iou.amount) * 100, 100)}%` }}
                                            />
                                        </div>
                                        <p className="mt-1 text-xs text-muted-foreground">
                                            {Math.round(((iou.amount_paid ?? 0) / iou.amount) * 100)}% paid
                                        </p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {showRecordPayment ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Record Payment</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleRecordPayment} className="space-y-4">
                                    <FormField
                                        label="Payment Amount"
                                        name="payment_amount"
                                        type="number"
                                        value={paymentForm.data.payment_amount}
                                        onChange={e => paymentForm.setData('payment_amount', e.target.value)}
                                        error={paymentForm.errors.payment_amount}
                                        required
                                        min="0.01"
                                        max={String(remaining)}
                                        step="0.01"
                                        placeholder="0.00"
                                    />
                                    <FormField label="Payment Date" name="payment_date" error={paymentForm.errors.payment_date} required>
                                        <DatePicker
                                            value={paymentForm.data.payment_date}
                                            onChange={v => paymentForm.setData('payment_date', v)}
                                        />
                                    </FormField>
                                    <FormField label="Payment Method" name="payment_method" error={paymentForm.errors.payment_method}>
                                        <Select
                                            value={paymentForm.data.payment_method}
                                            onValueChange={v => paymentForm.setData('payment_method', v)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select method" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {paymentMethods.map(m => (
                                                    <SelectItem key={m} value={m}>{m}</SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={paymentForm.processing}>Record</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowRecordPayment(false)}>
                                            Cancel
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>
            </div>

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmTitle}
                description={confirmDescription}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction === 'delete' ? 'Delete' : confirmAction === 'mark_paid' ? 'Mark Paid' : 'Cancel IOU'}
                variant={confirmAction === 'delete' || confirmAction === 'cancel' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
