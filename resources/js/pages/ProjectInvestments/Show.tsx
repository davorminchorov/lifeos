import { Head, Link, router, useForm } from '@inertiajs/react'
import { useState, useCallback, type FormEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { FormField } from '@/components/shared/form-field'
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
import { Pencil, Trash2, ArrowLeft, Plus, ExternalLink } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { ProjectInvestment, ProjectInvestmentTransaction } from '@/types/models'

interface ProjectInvestmentShowProps {
    projectInvestment: ProjectInvestment & {
        transactions?: ProjectInvestmentTransaction[]
        total_invested?: number
    }
}

export default function ProjectInvestmentShow({ projectInvestment }: ProjectInvestmentShowProps) {
    const [confirmDelete, setConfirmDelete] = useState(false)
    const [showUpdateValue, setShowUpdateValue] = useState(false)
    const [confirmDeleteTx, setConfirmDeleteTx] = useState<number | null>(null)

    const valueForm = useForm({
        current_value: projectInvestment.current_value != null ? String(projectInvestment.current_value) : '',
    })

    const handleDelete = useCallback(() => {
        router.delete(`/project-investments/${projectInvestment.id}`, {
            onFinish: () => setConfirmDelete(false),
        })
    }, [projectInvestment.id])

    const handleUpdateValue = useCallback((e: FormEvent) => {
        e.preventDefault()
        valueForm.post(`/project-investments/${projectInvestment.id}/update-value`, {
            onSuccess: () => setShowUpdateValue(false),
        })
    }, [projectInvestment.id, valueForm])

    const handleDeleteTransaction = useCallback(() => {
        if (!confirmDeleteTx) return
        router.delete(`/project-investment-transactions/${confirmDeleteTx}`, {
            preserveScroll: true,
            onFinish: () => setConfirmDeleteTx(null),
        })
    }, [confirmDeleteTx])

    const transactions = projectInvestment.transactions ?? []
    const totalInvested = transactions.reduce((sum, t) => sum + t.amount, 0)
    const gainLoss = (projectInvestment.current_value ?? totalInvested) - totalInvested

    return (
        <AppLayout>
            <Head title={projectInvestment.name} />

            <PageHeader title={projectInvestment.name} description={projectInvestment.project_type ?? undefined}>
                <Button variant="outline" size="sm" asChild>
                    <Link href="/project-investments">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Back
                    </Link>
                </Button>
                <Button variant="outline" size="sm" asChild>
                    <Link href={`/project-investments/${projectInvestment.id}/edit`}>
                        <Pencil className="mr-2 h-4 w-4" /> Edit
                    </Link>
                </Button>
                <Button variant="destructive" size="sm" onClick={() => setConfirmDelete(true)}>
                    <Trash2 className="mr-2 h-4 w-4" /> Delete
                </Button>
            </PageHeader>

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Project Details</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p className="text-sm text-muted-foreground">Name</p>
                                    <p className="font-medium">{projectInvestment.name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Status</p>
                                    <StatusBadge status={projectInvestment.status} />
                                </div>
                                {projectInvestment.project_type ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Project Type</p>
                                        <p className="font-medium">{projectInvestment.project_type}</p>
                                    </div>
                                ) : null}
                                {projectInvestment.stage ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Stage</p>
                                        <p className="font-medium capitalize">{projectInvestment.stage}</p>
                                    </div>
                                ) : null}
                                {projectInvestment.business_model ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Business Model</p>
                                        <p className="font-medium capitalize">{projectInvestment.business_model}</p>
                                    </div>
                                ) : null}
                                {projectInvestment.equity_percentage != null ? (
                                    <div>
                                        <p className="text-sm text-muted-foreground">Equity</p>
                                        <p className="font-medium">{projectInvestment.equity_percentage}%</p>
                                    </div>
                                ) : null}
                            </div>
                            {projectInvestment.website_url || projectInvestment.repository_url ? (
                                <>
                                    <Separator />
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        {projectInvestment.website_url ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Website</p>
                                                <a
                                                    href={projectInvestment.website_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                                >
                                                    {projectInvestment.website_url}
                                                    <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        ) : null}
                                        {projectInvestment.repository_url ? (
                                            <div>
                                                <p className="text-sm text-muted-foreground">Repository</p>
                                                <a
                                                    href={projectInvestment.repository_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                                                >
                                                    {projectInvestment.repository_url}
                                                    <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        ) : null}
                                    </div>
                                </>
                            ) : null}
                            {projectInvestment.notes ? (
                                <>
                                    <Separator />
                                    <div>
                                        <p className="text-sm text-muted-foreground">Notes</p>
                                        <p className="mt-1 whitespace-pre-wrap text-sm">{projectInvestment.notes}</p>
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
                                    <p className="text-sm text-muted-foreground">Start Date</p>
                                    <p className="font-medium">{projectInvestment.start_date ? formatDate(projectInvestment.start_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">End Date</p>
                                    <p className="font-medium">{projectInvestment.end_date ? formatDate(projectInvestment.end_date) : '\u2014'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Created</p>
                                    <p className="font-medium">{formatDate(projectInvestment.created_at)}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>Transactions</CardTitle>
                            <Button size="sm" asChild>
                                <Link href={`/project-investments/${projectInvestment.id}/transactions/create`}>
                                    <Plus className="mr-2 h-4 w-4" /> Add Transaction
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {transactions.length > 0 ? (
                                <div className="rounded-md border border-border">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Date</TableHead>
                                                <TableHead>Amount</TableHead>
                                                <TableHead>Notes</TableHead>
                                                <TableHead className="w-[100px]" />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {transactions.map((tx) => (
                                                <TableRow key={tx.id}>
                                                    <TableCell>{formatDate(tx.transaction_date)}</TableCell>
                                                    <TableCell className="font-medium">
                                                        {formatCurrency(tx.amount, tx.currency ?? 'USD')}
                                                    </TableCell>
                                                    <TableCell className="text-sm text-muted-foreground">
                                                        {tx.notes ?? '\u2014'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex gap-1">
                                                            <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                                                <Link href={`/project-investment-transactions/${tx.id}/edit`}>
                                                                    <Pencil className="h-4 w-4" />
                                                                </Link>
                                                            </Button>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-8 w-8 text-destructive"
                                                                onClick={() => setConfirmDeleteTx(tx.id)}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">No transactions recorded yet.</p>
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
                                <p className="text-sm text-muted-foreground">Total Invested</p>
                                <p className="text-2xl font-semibold">{formatCurrency(totalInvested, 'USD')}</p>
                            </div>
                            <Separator />
                            <div>
                                <p className="text-sm text-muted-foreground">Current Value</p>
                                <p className="text-xl font-semibold">
                                    {projectInvestment.current_value != null
                                        ? formatCurrency(projectInvestment.current_value, 'USD')
                                        : '\u2014'}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Gain / Loss</p>
                                <p className={`font-semibold ${gainLoss >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                    {gainLoss >= 0 ? '+' : ''}{formatCurrency(gainLoss, 'USD')}
                                </p>
                            </div>
                            <Separator />
                            {showUpdateValue ? (
                                <form onSubmit={handleUpdateValue} className="space-y-3">
                                    <FormField
                                        label="New Value"
                                        name="current_value"
                                        type="number"
                                        value={valueForm.data.current_value}
                                        onChange={e => valueForm.setData('current_value', e.target.value)}
                                        error={valueForm.errors.current_value}
                                        min="0"
                                        step="0.01"
                                    />
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm" disabled={valueForm.processing}>Save</Button>
                                        <Button type="button" variant="outline" size="sm" onClick={() => setShowUpdateValue(false)}>Cancel</Button>
                                    </div>
                                </form>
                            ) : (
                                <Button variant="outline" size="sm" className="w-full" onClick={() => setShowUpdateValue(true)}>
                                    Update Value
                                </Button>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <ConfirmationDialog
                open={confirmDelete}
                onOpenChange={setConfirmDelete}
                title="Delete Investment"
                description="Are you sure you want to delete this project investment? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />

            <ConfirmationDialog
                open={confirmDeleteTx !== null}
                onOpenChange={(open) => { if (!open) setConfirmDeleteTx(null) }}
                title="Delete Transaction"
                description="Are you sure you want to delete this transaction?"
                onConfirm={handleDeleteTransaction}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
