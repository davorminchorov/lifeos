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
import { Pencil, Trash2, ArrowLeft, XCircle, RefreshCw, FileEdit } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Contract } from '@/types/models'

interface ContractShowProps {
    contract: Contract
}

const performanceLabels: Record<number, string> = {
    1: 'Poor',
    2: 'Below Average',
    3: 'Average',
    4: 'Good',
    5: 'Excellent',
}

export default function ContractShow({ contract }: ContractShowProps) {
    const [confirmAction, setConfirmAction] = useState<'terminate' | 'delete' | null>(null)
    const [showRenew, setShowRenew] = useState(false)
    const [showAmendment, setShowAmendment] = useState(false)

    const renewForm = useForm({ new_end_date: '' })
    const amendForm = useForm({ amendment_description: '' })

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        if (confirmAction === 'delete') {
            router.delete(`/contracts/${contract.id}`, {
                onFinish: () => setConfirmAction(null),
            })
        } else {
            router.post(`/contracts/${contract.id}/terminate`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction, contract.id])

    const handleRenew = useCallback((e: FormEvent) => {
        e.preventDefault()
        renewForm.post(`/contracts/${contract.id}/renew`, {
            preserveScroll: true,
            onSuccess: () => setShowRenew(false),
        })
    }, [contract.id, renewForm])

    const handleAmendment = useCallback((e: FormEvent) => {
        e.preventDefault()
        amendForm.post(`/contracts/${contract.id}/add-amendment`, {
            preserveScroll: true,
            onSuccess: () => {
                setShowAmendment(false)
                amendForm.reset()
            },
        })
    }, [contract.id, amendForm])

    const renewalHistory = Array.isArray(contract.renewal_history)
        ? contract.renewal_history as Array<{ date: string; previous_end_date?: string; new_end_date?: string; action: string }>
        : []
    const amendments = Array.isArray(contract.amendments)
        ? contract.amendments as Array<{ date: string; description: string }>
        : []

    const confirmTitle = confirmAction === 'delete' ? 'Delete Contract' : 'Terminate Contract'
    const confirmDescription = confirmAction === 'delete'
        ? 'Are you sure you want to delete this contract? This action cannot be undone.'
        : 'Are you sure you want to terminate this contract? The end date will be set to today.'

    return (
        <AppLayout>
            <Head title={contract.title} />

            <PageHeader title={contract.title} description={contract.counterparty ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/contracts">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/contracts/${contract.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                {contract.status === 'active' ? (
                    <>
                        <Button variant="outline" size="sm" onClick={() => setShowRenew(true)}>
                            <RefreshCw className="mr-2 h-4 w-4" /> Renew
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setShowAmendment(true)}>
                            <FileEdit className="mr-2 h-4 w-4" /> Add Amendment
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => setConfirmAction('terminate')}>
                            <XCircle className="mr-2 h-4 w-4" /> Terminate
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
                            <CardTitle>Contract Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Title</p>
                                    <p className="font-medium">{contract.title}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={contract.status} />
                                </div>
                                {contract.contract_type ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Type</p>
                                        <p className="font-medium">{contract.contract_type}</p>
                                    </div>
                                ) : null}
                                {contract.counterparty ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Counterparty</p>
                                        <p className="font-medium">{contract.counterparty}</p>
                                    </div>
                                ) : null}
                                {contract.performance_rating ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Performance Rating</p>
                                        <p className="font-medium">
                                            {performanceLabels[contract.performance_rating] ?? contract.performance_rating}
                                        </p>
                                    </div>
                                ) : null}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Dates & Renewal</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Start Date</p>
                                    <p className="font-medium">{contract.start_date ? formatDate(contract.start_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">End Date</p>
                                    <p className="font-medium">{contract.end_date ? formatDate(contract.end_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Notice Period</p>
                                    <p className="font-medium">
                                        {contract.notice_period_days ? `${contract.notice_period_days} days` : '\u2014'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Auto-Renewal</p>
                                    <p className="font-medium">{contract.auto_renewal ? 'Yes' : 'No'}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {contract.key_obligations || contract.penalties || contract.termination_clauses ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Terms & Obligations</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {contract.key_obligations ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Key Obligations</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{contract.key_obligations}</p>
                                    </div>
                                ) : null}
                                {contract.penalties ? (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Penalties</p>
                                            <p className="mt-1 whitespace-pre-wrap text-sm">{contract.penalties}</p>
                                        </div>
                                    </>
                                ) : null}
                                {contract.termination_clauses ? (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm text-muted-foreground">Termination Clauses</p>
                                            <p className="mt-1 whitespace-pre-wrap text-sm">{contract.termination_clauses}</p>
                                        </div>
                                    </>
                                ) : null}
                            </CardContent>
                        </Card>
                    ) : null}

                    {renewalHistory.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Renewal History</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Action</TableHead>
                                                <TableHead>New End Date</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {renewalHistory.map((entry, i) => (
                                                <TableRow key={i}>
                                                    <TableCell>{formatDate(entry.date)}</TableCell>
                                                    <TableCell className="capitalize">{entry.action}</TableCell>
                                                    <TableCell>{entry.new_end_date ? formatDate(entry.new_end_date) : '\u2014'}</TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {amendments.length > 0 ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Amendments</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {amendments.map((amendment, i) => (
                                        <div key={i} className="rounded-lg border border-border p-3">
                                            <p className="text-xs text-muted-foreground">{formatDate(amendment.date)}</p>
                                            <p className="mt-1 text-sm">{amendment.description}</p>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    ) : null}

                    {contract.notes ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Notes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="whitespace-pre-wrap text-sm">{contract.notes}</p>
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
                                <p className="text-sm text-muted-foreground">Contract Value</p>
                                <p className="text-2xl font-semibold">
                                    {contract.contract_value != null ? formatCurrency(contract.contract_value, 'MKD') : '\u2014'}
                                </p>
                            </div>
                            {contract.payment_terms ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Payment Terms</p>
                                        <p className="font-medium">{contract.payment_terms}</p>
                                    </div>
                                </>
                            ) : null}
                        </CardContent>
                    </Card>

                    {showRenew ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Renew Contract</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleRenew} className="space-y-4">
                                    <FormField label="New End Date" name="new_end_date" error={renewForm.errors.new_end_date} required>
                                        <DatePicker value={renewForm.data.new_end_date} onChange={v => renewForm.setData('new_end_date', v)} />
                                    </FormField>
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={renewForm.processing}>Renew</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowRenew(false)}>Cancel</Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    ) : null}

                    {showAmendment ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>Add Amendment</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleAmendment} className="space-y-4">
                                    <FormField
                                        label="Description"
                                        name="amendment_description"
                                        value={amendForm.data.amendment_description}
                                        onChange={e => amendForm.setData('amendment_description', e.target.value)}
                                        error={amendForm.errors.amendment_description}
                                        multiline
                                        required
                                        placeholder="Describe the amendment"
                                    />
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={amendForm.processing}>Add</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowAmendment(false)}>Cancel</Button>
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
                confirmLabel={confirmAction === 'delete' ? 'Delete' : 'Terminate'}
                variant="danger"
            />
        </AppLayout>
    )
}
