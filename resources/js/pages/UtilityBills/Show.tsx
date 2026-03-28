import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Pencil, ArrowLeft, Check, Copy, Trash2, ToggleLeft, ToggleRight } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { UtilityBill } from '@/types/models'

interface UtilityBillShowProps {
    utilityBill: UtilityBill
}

function formatUtilityType(type: string): string {
    return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

export default function UtilityBillShow({ utilityBill }: UtilityBillShowProps) {
    const [confirmAction, setConfirmAction] = useState<'mark-paid' | 'auto-pay-on' | 'auto-pay-off' | 'duplicate' | 'delete' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'mark-paid') {
            router.patch(`/utility-bills/${utilityBill.id}/mark-paid`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'auto-pay-on') {
            router.patch(`/utility-bills/${utilityBill.id}/set-auto-pay`, { auto_pay_enabled: true }, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'auto-pay-off') {
            router.patch(`/utility-bills/${utilityBill.id}/set-auto-pay`, { auto_pay_enabled: false }, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'duplicate') {
            router.post(`/utility-bills/${utilityBill.id}/duplicate`, {}, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (confirmAction === 'delete') {
            router.delete(`/utility-bills/${utilityBill.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, utilityBill.id])

    const confirmTitle = confirmAction
        ? ({
            'mark-paid': 'Mark as Paid',
            'auto-pay-on': 'Enable Auto-Pay',
            'auto-pay-off': 'Disable Auto-Pay',
            'duplicate': 'Duplicate Bill',
            'delete': 'Delete Bill',
        })[confirmAction]
        : ''

    const confirmDescription = confirmAction
        ? ({
            'mark-paid': 'Are you sure you want to mark this bill as paid?',
            'auto-pay-on': 'Are you sure you want to enable auto-pay for this bill?',
            'auto-pay-off': 'Are you sure you want to disable auto-pay for this bill?',
            'duplicate': 'This will create a copy of the bill for the next billing period. You can update the amount and usage afterward.',
            'delete': 'Are you sure you want to delete this bill? This action cannot be undone.',
        })[confirmAction]
        : ''

    const currency = utilityBill.currency ?? 'MKD'
    const isOverdue = utilityBill.payment_status === 'pending' && new Date(utilityBill.due_date) < new Date()
    const displayStatus = isOverdue ? 'overdue' : utilityBill.payment_status

    return (
        <AppLayout>
            <Head title={`${formatUtilityType(utilityBill.utility_type)} Bill`} />

            <PageHeader
                title={`${formatUtilityType(utilityBill.utility_type)} Bill`}
                description={utilityBill.service_provider}
            >
                <Button variant="outline" size="sm" asChild>
                    <Link href="/utility-bills">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/utility-bills/${utilityBill.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {utilityBill.payment_status !== 'paid' ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('mark-paid')}>
                        <Check className="mr-2 h-4 w-4" /> Mark Paid
                    </Button>
                ) : null}
                {utilityBill.auto_pay_enabled ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('auto-pay-off')}>
                        <ToggleLeft className="mr-2 h-4 w-4" /> Disable Auto-Pay
                    </Button>
                ) : (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('auto-pay-on')}>
                        <ToggleRight className="mr-2 h-4 w-4" /> Enable Auto-Pay
                    </Button>
                )}
                <Button variant="outline" size="sm" onClick={() => setConfirmAction('duplicate')}>
                    <Copy className="mr-2 h-4 w-4" /> Duplicate
                </Button>
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                {/* Left Column - Details */}
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Utility Type</p>
                                    <p className="font-medium">{formatUtilityType(utilityBill.utility_type)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Payment Status</p>
                                    <StatusBadge status={displayStatus} />
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Service Provider</p>
                                    <p className="font-medium">{utilityBill.service_provider}</p>
                                </div>
                                {utilityBill.account_number ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Account Number</p>
                                        <p className="font-medium">{utilityBill.account_number}</p>
                                    </div>
                                ) : null}
                                {utilityBill.service_address ? (
                                    <div className="sm:col-span-2">
                                        <p className="text-sm text-muted-foreground">Service Address</p>
                                        <p className="font-medium">{utilityBill.service_address}</p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Bill Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p className="text-sm text-muted-foreground">Bill Amount</p>
                                    <p className="text-2xl font-semibold">{formatCurrency(utilityBill.bill_amount, currency)}</p>
                                </div>
                                {utilityBill.usage_amount ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Usage</p>
                                        <p className="text-lg font-medium">
                                            {Number(utilityBill.usage_amount).toFixed(4)}
                                            {utilityBill.usage_unit ? ` ${utilityBill.usage_unit}` : ''}
                                        </p>
                                    </div>
                                ) : null}
                                {utilityBill.rate_per_unit ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Rate per Unit</p>
                                        <p className="text-lg font-medium">
                                            {formatCurrency(utilityBill.rate_per_unit, currency)}
                                            {utilityBill.usage_unit ? `/${utilityBill.usage_unit}` : ''}
                                        </p>
                                    </div>
                                ) : null}
                                {utilityBill.budget_alert_threshold ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Budget Threshold</p>
                                        <p className="font-medium">
                                            {formatCurrency(utilityBill.budget_alert_threshold, currency)}
                                            {Number(utilityBill.bill_amount) > Number(utilityBill.budget_alert_threshold) ? (
                                                <span className="ml-2 rounded-full bg-destructive/10 px-2 py-0.5 text-xs font-medium text-destructive">
                                                    Over Budget
                                                </span>
                                            ) : null}
                                        </p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Billing Period</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Period Start</p>
                                    <p className="font-medium">{formatDate(utilityBill.bill_period_start)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Period End</p>
                                    <p className="font-medium">{formatDate(utilityBill.bill_period_end)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Due Date</p>
                                    <p className="font-medium">{formatDate(utilityBill.due_date)}</p>
                                </div>
                                {utilityBill.payment_date ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Payment Date</p>
                                        <p className="font-medium">{formatDate(utilityBill.payment_date)}</p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    {utilityBill.service_plan || utilityBill.contract_terms || utilityBill.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Additional Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {utilityBill.service_plan ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Service Plan</p>
                                        <p className="font-medium">{utilityBill.service_plan}</p>
                                    </div>
                                ) : null}
                                {utilityBill.contract_terms ? (
                                    <>
                                        {utilityBill.service_plan ? <Separator /> : null}
                                        <div>
                                            <p className="text-sm text-muted-foreground">Contract Terms</p>
                                            <p className="mt-1 whitespace-pre-wrap text-sm">{utilityBill.contract_terms}</p>
                                        </div>
                                    </>
                                ) : null}
                                {utilityBill.notes ? (
                                    <>
                                        {utilityBill.service_plan || utilityBill.contract_terms ? <Separator /> : null}
                                        <div>
                                            <p className="text-sm text-muted-foreground">Notes</p>
                                            <p className="mt-1 whitespace-pre-wrap text-sm">{utilityBill.notes}</p>
                                        </div>
                                    </>
                                ) : null}
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                {/* Right Column - Summary */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Billing Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Amount</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(utilityBill.bill_amount, currency)}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Utility Type</p>
                                <p className="font-medium">{formatUtilityType(utilityBill.utility_type)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Currency</p>
                                <p className="font-medium">{currency}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Auto-Pay</p>
                                <p className="font-medium">{utilityBill.auto_pay_enabled ? 'Enabled' : 'Disabled'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Status</p>
                                <StatusBadge status={displayStatus} />
                            </div>
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
