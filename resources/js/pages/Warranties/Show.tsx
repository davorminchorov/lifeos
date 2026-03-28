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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Pencil, Trash2, ArrowLeft, AlertTriangle, ArrowRightLeft } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Warranty } from '@/types/models'

interface WarrantyShowProps {
    warranty: Warranty
}

export default function WarrantyShow({ warranty }: WarrantyShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)
    const [showFileClaim, setShowFileClaim] = useState(false)
    const [showTransfer, setShowTransfer] = useState(false)

    const claimForm = useForm({
        issue_description: '',
        claim_date: '',
    })

    const transferForm = useForm({
        new_owner_name: '',
        transfer_reason: '',
        transfer_date: '',
    })

    const handleDelete = useCallback(() => {
        router.delete(`/warranties/${warranty.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [warranty.id])

    const handleFileClaim = useCallback((e: FormEvent) => {
        e.preventDefault()
        claimForm.post(`/warranties/${warranty.id}/file-claim`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowFileClaim(false)
                claimForm.reset()
            },
        })
    }, [warranty.id, claimForm])

    const handleTransfer = useCallback((e: FormEvent) => {
        e.preventDefault()
        transferForm.post(`/warranties/${warranty.id}/transfer`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowTransfer(false)
                transferForm.reset()
            },
        })
    }, [warranty.id, transferForm])

    const claimHistory = Array.isArray(warranty.claim_history)
        ? warranty.claim_history as Array<{ date: string; issue: string; status: string; resolution: string | null }>
        : []
    const transferHistory = Array.isArray(warranty.transfer_history)
        ? warranty.transfer_history as Array<{ date: string; to: string; reason: string | null }>
        : []
    const maintenanceReminders = Array.isArray(warranty.maintenance_reminders)
        ? warranty.maintenance_reminders as Array<{ type: string; frequency: string; next_due: string }>
        : []

    return (
        <AppLayout>
            <Head title={warranty.product_name} />

            <PageHeader title={warranty.product_name} description={[warranty.brand, warranty.model].filter(Boolean).join(' - ') || undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/warranties">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/warranties/${warranty.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {warranty.current_status === 'active' ? (
                    <>
                        <Button variant="outline" size="sm" onClick={() => setShowFileClaim(true)}>
                            <AlertTriangle className="mr-2 h-4 w-4" /> File Claim
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setShowTransfer(true)}>
                            <ArrowRightLeft className="mr-2 h-4 w-4" /> Transfer
                        </Button>
                    </>
                ) : null}
                <Button variant="destructive" size="sm" onClick={() => setConfirmDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Product Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Product Name</p>
                                    <p className="font-medium">{warranty.product_name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={warranty.current_status} />
                                </div>
                                {warranty.brand ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Brand</p>
                                        <p className="font-medium">{warranty.brand}</p>
                                    </div>
                                ) : null}
                                {warranty.model ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Model</p>
                                        <p className="font-medium">{warranty.model}</p>
                                    </div>
                                ) : null}
                                {warranty.serial_number ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Serial Number</p>
                                        <p className="font-medium font-mono text-sm">{warranty.serial_number}</p>
                                    </div>
                                ) : null}
                                {warranty.retailer ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Retailer</p>
                                        <p className="font-medium">{warranty.retailer}</p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Warranty Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Warranty Type</p>
                                    <p className="font-medium capitalize">{warranty.warranty_type ?? '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Duration</p>
                                    <p className="font-medium">
                                        {warranty.warranty_duration_months ? `${warranty.warranty_duration_months} months` : '\u2014'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Purchase Date</p>
                                    <p className="font-medium">{warranty.purchase_date ? formatDate(warranty.purchase_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Expiration Date</p>
                                    <p className="font-medium">
                                        {warranty.warranty_expiration_date ? formatDate(warranty.warranty_expiration_date) : '\u2014'}
                                    </p>
                                </div>
                            </div>
                            {warranty.warranty_terms ? (
                                <>
                                    <Separator className="my-4" />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Warranty Terms</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{warranty.warranty_terms}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {claimHistory.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Claim History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Issue</TableHead>
                                                <TableHead>Status</TableHead>
                                                <TableHead>Resolution</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {claimHistory.map((claim, i) => (
                                                <TableRow key={i}>
                                                    <TableCell>{formatDate(claim.date)}</TableCell>
                                                    <TableCell className="text-sm">{claim.issue}</TableCell>
                                                    <TableCell>
                                                        <StatusBadge status={claim.status} />
                                                    </TableCell>
                                                    <TableCell className="text-sm text-muted-foreground">
                                                        {claim.resolution ?? '\u2014'}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {transferHistory.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Transfer History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {transferHistory.map((transfer, i) => (
                                        <div key={i} className="rounded-lg border border-border p-3">
                                            <div className="flex items-center justify-between">
                                                <p className="text-sm font-medium">Transferred to {transfer.to}</p>
                                                <p className="text-xs text-muted-foreground">{formatDate(transfer.date)}</p>
                                            </div>
                                            {transfer.reason ? (
                                                <p className="mt-1 text-sm text-muted-foreground">{transfer.reason}</p>
                                            ) : null}
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {maintenanceReminders.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Maintenance Reminders</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {maintenanceReminders.map((reminder, i) => (
                                        <div key={i} className="flex items-center justify-between rounded-lg border border-border p-3">
                                            <div>
                                                <p className="text-sm font-medium">{reminder.type}</p>
                                                <p className="text-xs text-muted-foreground">Every {reminder.frequency}</p>
                                            </div>
                                            <p className="text-sm text-muted-foreground">Next: {formatDate(reminder.next_due)}</p>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {warranty.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{warranty.notes}</p>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase Summary</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Purchase Price</p>
                                <p className="text-2xl font-semibold">
                                    {warranty.purchase_price != null ? formatCurrency(warranty.purchase_price, 'MKD') : '\u2014'}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Created</p>
                                <p className="font-medium">{formatDate(warranty.created_at)}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {showFileClaim ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>File Claim</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleFileClaim} className="space-y-4">
                                    <FormField
                                        label="Issue Description"
                                        name="issue_description"
                                        value={claimForm.data.issue_description}
                                        onChange={e => claimForm.setData('issue_description', e.target.value)}
                                        error={claimForm.errors.issue_description}
                                        multiline
                                        required
                                        placeholder="Describe the issue"
                                    />
                                    <FormField label="Claim Date" name="claim_date" error={claimForm.errors.claim_date}>
                                        <DatePicker value={claimForm.data.claim_date} onChange={v => claimForm.setData('claim_date', v)} />
                                    </FormField>
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={claimForm.processing}>File Claim</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowFileClaim(false)}>Cancel</Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    ) : null}

                    {showTransfer ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Transfer Warranty</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleTransfer} className="space-y-4">
                                    <FormField
                                        label="New Owner Name"
                                        name="new_owner_name"
                                        value={transferForm.data.new_owner_name}
                                        onChange={e => transferForm.setData('new_owner_name', e.target.value)}
                                        error={transferForm.errors.new_owner_name}
                                        required
                                        placeholder="Full name"
                                    />
                                    <FormField
                                        label="Reason"
                                        name="transfer_reason"
                                        value={transferForm.data.transfer_reason}
                                        onChange={e => transferForm.setData('transfer_reason', e.target.value)}
                                        error={transferForm.errors.transfer_reason}
                                        placeholder="Optional reason"
                                    />
                                    <FormField label="Transfer Date" name="transfer_date" error={transferForm.errors.transfer_date}>
                                        <DatePicker value={transferForm.data.transfer_date} onChange={v => transferForm.setData('transfer_date', v)} />
                                    </FormField>
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={transferForm.processing}>Transfer</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowTransfer(false)}>Cancel</Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    ) : null}
                </div>
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Warranty"
                description="Are you sure you want to delete this warranty? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
