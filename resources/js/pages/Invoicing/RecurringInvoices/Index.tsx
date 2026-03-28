import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { RefreshCw, Plus, Search, MoreHorizontal, Eye, Pause, Play, X } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { RecurringInvoice, Customer } from '@/types/models'
import type { PaginatedData } from '@/types'

interface RecurringInvoiceIndexProps {
    recurringInvoices: PaginatedData<RecurringInvoice & { customer?: Customer }>
    summary: {
        total: number
        active: number
        paused: number
    }
    customers: Customer[]
    filters?: {
        search?: string
        status?: string
        customer_id?: string
    }
}

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'paused', label: 'Paused' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'completed', label: 'Completed' },
]

export default function RecurringInvoiceIndex({ recurringInvoices, summary, customers, filters = {} }: RecurringInvoiceIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmAction, setConfirmAction] = useState<{ id: number; action: 'pause' | 'resume' | 'cancel' } | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/invoicing/recurring-invoices', { ...filters, [key]: value || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [filters])

    const handleSearch = useCallback((e: ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value)
    }, [])

    const handleSearchSubmit = useCallback(() => {
        applyFilter('search', search)
    }, [search, applyFilter])

    const clearFilters = useCallback(() => {
        router.get('/invoicing/recurring-invoices', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        const { id, action } = confirmAction
        router.post(`/invoicing/recurring-invoices/${id}/${action}`, {}, {
            preserveScroll: true,
            onFinish: () => setConfirmAction(null),
        })
    }, [confirmAction])

    return (
        <AppLayout>
            <Head title="Recurring Invoices" />

            <PageHeader title="Recurring Invoices" description="Manage your recurring invoice templates">
                <Button asChild>
                    <Link href="/invoicing/recurring-invoices/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Recurring Invoice
                    </Link>
                </Button>
            </PageHeader>

            {recurringInvoices.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total</p>
                            <p className="text-xl font-semibold">{summary.total}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active</p>
                            <p className="text-xl font-semibold">{summary.active}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Paused</p>
                            <p className="text-xl font-semibold">{summary.paused}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search by name..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.status ?? ''} onValueChange={(v) => applyFilter('status', v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All</SelectItem>
                        {statuses.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.customer_id ?? ''} onValueChange={(v) => applyFilter('customer_id', v)}>
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="Customer" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Customers</SelectItem>
                        {customers.map(c => (
                            <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {recurringInvoices.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Interval</TableHead>
                                    <TableHead>Next Billing</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recurringInvoices.data.map((ri) => (
                                    <TableRow key={ri.id}>
                                        <TableCell>
                                            <Link href={`/invoicing/recurring-invoices/${ri.id}`} className="font-medium hover:underline">
                                                {ri.name}
                                            </Link>
                                            {ri.description ? (
                                                <p className="text-xs text-muted-foreground">{ri.description}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {ri.customer?.name ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize">
                                            {ri.interval_count > 1 ? `Every ${ri.interval_count} ` : ''}{ri.billing_interval}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {ri.next_billing_date ? formatDate(ri.next_billing_date) : '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={ri.status} />
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="h-8 w-8">
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/invoicing/recurring-invoices/${ri.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    {ri.status === 'active' ? (
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: ri.id, action: 'pause' })}>
                                                            <Pause className="mr-2 h-4 w-4" /> Pause
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                    {ri.status === 'paused' ? (
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: ri.id, action: 'resume' })}>
                                                            <Play className="mr-2 h-4 w-4" /> Resume
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                    {ri.status === 'active' || ri.status === 'paused' ? (
                                                        <DropdownMenuItem
                                                            onClick={() => setConfirmAction({ id: ri.id, action: 'cancel' })}
                                                            className="text-destructive"
                                                        >
                                                            <X className="mr-2 h-4 w-4" /> Cancel
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {recurringInvoices.data.map((ri) => (
                            <Card key={ri.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/invoicing/recurring-invoices/${ri.id}`} className="font-medium hover:underline">
                                                {ri.name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{ri.customer?.name}</p>
                                        </div>
                                        <StatusBadge status={ri.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="capitalize">{ri.billing_interval}</span>
                                        {ri.next_billing_date ? (
                                            <span className="text-muted-foreground">Next: {formatDate(ri.next_billing_date)}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {recurringInvoices.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {recurringInvoices.from} to {recurringInvoices.to} of {recurringInvoices.total}
                            </p>
                            <div className="flex gap-2">
                                {recurringInvoices.links.map((link, i) => (
                                    <Button
                                        key={i}
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        disabled={!link.url}
                                        onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    ) : null}
                </>
            ) : (
                <EmptyState
                    icon={RefreshCw}
                    title="No recurring invoices yet"
                    description="Set up recurring invoices to auto-generate invoices on a schedule"
                    action={{ label: 'New Recurring Invoice', href: '/invoicing/recurring-invoices/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmAction ? `${confirmAction.action.charAt(0).toUpperCase() + confirmAction.action.slice(1)} Recurring Invoice` : ''}
                description={confirmAction ? `Are you sure you want to ${confirmAction.action} this recurring invoice?` : ''}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction?.action === 'cancel' ? 'Cancel Invoice' : 'Confirm'}
                variant={confirmAction?.action === 'cancel' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
