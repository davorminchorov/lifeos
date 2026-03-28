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
import { Pencil, Pause, Play, X, Trash2, ArrowLeft } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Subscription } from '@/types/models'

interface SubscriptionShowProps {
    subscription: Subscription & { monthly_cost?: number; yearly_cost?: number }
}

const difficultyLabels: Record<number, string> = {
    1: 'Very Easy',
    2: 'Easy',
    3: 'Moderate',
    4: 'Hard',
    5: 'Very Hard',
}

export default function SubscriptionShow({ subscription }: SubscriptionShowProps) {
    const [confirmAction, setConfirmAction] = useState<'pause' | 'resume' | 'cancel' | 'delete' | null>(null)

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return

        if (confirmAction === 'delete') {
            router.delete(`/subscriptions/${subscription.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else {
            router.patch(`/subscriptions/${subscription.id}/${confirmAction}`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, subscription.id])

    const confirmTitle = confirmAction
        ? `${confirmAction.charAt(0).toUpperCase() + confirmAction.slice(1)} Subscription`
        : ''

    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this subscription? This action cannot be undone.'
        : confirmAction
            ? `Are you sure you want to ${confirmAction} this subscription?`
            : ''

    const priceHistory = Array.isArray(subscription.price_history) ? subscription.price_history as Array<{ date: string; cost: number; currency?: string }> : []
    const tags = Array.isArray(subscription.tags) ? subscription.tags : []
    const currency = subscription.currency ?? 'MKD'

    return (
        <AppLayout>
            <Head title={subscription.service_name} />

            <PageHeader title={subscription.service_name} description={subscription.description ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/subscriptions">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/subscriptions/${subscription.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {subscription.status === 'active' ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('pause')}>
                        <Pause className="mr-2 h-4 w-4" /> Pause
                    </Button>
                ) : null}
                {subscription.status === 'paused' ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('resume')}>
                        <Play className="mr-2 h-4 w-4" /> Resume
                    </Button>
                ) : null}
                {subscription.status !== 'cancelled' ? (
                    <Button variant="outline" size="sm" onClick={() => setConfirmAction('cancel')}>
                        <X className="mr-2 h-4 w-4" /> Cancel
                    </Button>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmAction('delete')}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                {/* Left Column - Details */}
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Subscription Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Service Name</p>
                                    <p className="font-medium">{subscription.service_name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={subscription.status} />
                                </div>
                                {subscription.category ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Category</p>
                                        <p className="font-medium">{subscription.category}</p>
                                    </div>
                                ) : null}
                                {subscription.merchant_info ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Merchant/Company</p>
                                        <p className="font-medium">{subscription.merchant_info}</p>
                                    </div>
                                ) : null}
                                {subscription.payment_method ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Payment Method</p>
                                        <p className="font-medium">{subscription.payment_method}</p>
                                    </div>
                                ) : null}
                            </div>
                            {subscription.description ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Description</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{subscription.description}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Important Dates</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Start Date</p>
                                    <p className="font-medium">{formatDate(subscription.start_date)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Next Billing Date</p>
                                    <p className="font-medium">
                                        {subscription.next_billing_date ? formatDate(subscription.next_billing_date) : '\u2014'}
                                    </p>
                                </div>
                                {subscription.cancellation_date ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Cancellation Date</p>
                                        <p className="font-medium">{formatDate(subscription.cancellation_date)}</p>
                                    </div>
                                ) : null}
                                <div>
                                    <p className="text-sm text-muted-foreground">Created</p>
                                    <p className="font-medium">{formatDate(subscription.created_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Management</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Auto-Renewal</p>
                                    <p className="font-medium">{subscription.auto_renewal ? 'Enabled' : 'Disabled'}</p>
                                </div>
                                {subscription.cancellation_difficulty ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Cancellation Difficulty</p>
                                        <p className="font-medium">
                                            {difficultyLabels[subscription.cancellation_difficulty] ?? subscription.cancellation_difficulty}
                                        </p>
                                    </div>
                                ) : null}
                                {tags.length > 0 ? (
                                    <div className="sm:col-span-2">
                                        <p className="text-sm text-muted-foreground">Tags</p>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {tags.map((tag) => (
                                                <span key={tag} className="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">
                                                    {tag}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}
                                {subscription.notes ? (
                                    <div className="sm:col-span-2">
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{subscription.notes}</p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    {priceHistory.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Price History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Cost</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {priceHistory.map((entry, i) => (
                                                <TableRow key={i}>
                                                    <TableCell>{formatDate(entry.date)}</TableCell>
                                                    <TableCell>{formatCurrency(entry.cost, entry.currency ?? currency)}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                {/* Right Column - Billing */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Billing Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Cost</p>
                                <p className="text-2xl font-semibold">
                                    {formatCurrency(subscription.cost, currency)}
                                    <span className="text-sm font-normal text-muted-foreground">/{subscription.billing_cycle}</span>
                                </p>
                            </div>
                            <Separator />
                            {subscription.monthly_cost !== undefined ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Monthly Equivalent</p>
                                    <p className="font-medium">{formatCurrency(subscription.monthly_cost, currency)}</p>
                                </div>
                            ) : null}
                            {subscription.yearly_cost !== undefined ? (
                                <div>
                                    <p className="text-sm text-muted-foreground">Yearly Equivalent</p>
                                    <p className="font-medium">{formatCurrency(subscription.yearly_cost, currency)}</p>
                                </div>
                            ) : null}
                            <div>
                                <p className="text-sm text-muted-foreground">Billing Cycle</p>
                                <p className="font-medium capitalize">
                                    {subscription.billing_cycle}
                                    {subscription.billing_cycle === 'custom' && subscription.billing_cycle_days
                                        ? ` (${subscription.billing_cycle_days} days)`
                                        : ''}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Currency</p>
                                <p className="font-medium">{currency}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Auto-Renewal</p>
                                <p className="font-medium">{subscription.auto_renewal ? 'Yes' : 'No'}</p>
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
                confirmLabel={confirmAction === 'delete' ? 'Delete' : confirmAction === 'cancel' ? 'Cancel Subscription' : 'Confirm'}
                variant={confirmAction === 'delete' || confirmAction === 'cancel' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
