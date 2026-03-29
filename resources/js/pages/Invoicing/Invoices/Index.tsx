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
import { FileText, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Invoice, Customer } from '@/types/models'
import type { PaginatedData } from '@/types'

interface InvoiceIndexProps {
    invoices: PaginatedData<Invoice & { customer?: Customer }>
    summary: {
        total_invoices: number
        draft_count: number
        total_outstanding: number
        total_overdue: number
    }
    customers: Customer[]
    filters?: {
        search?: string
        status?: string
        customer_id?: string
    }
}

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'issued', label: 'Issued' },
    { value: 'paid', label: 'Paid' },
    { value: 'partially_paid', label: 'Partially Paid' },
    { value: 'past_due', label: 'Past Due' },
    { value: 'void', label: 'Void' },
]

export default function InvoiceIndex({ invoices, summary, customers, filters = {} }: InvoiceIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/invoicing/invoices', { ...filters, [key]: value || undefined }, {
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
        router.get('/invoicing/invoices', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (deleteId === null) return
        router.delete(`/invoicing/invoices/${deleteId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    return (
        <AppLayout>
            <Head title="Invoices" />

            <PageHeader title="Invoices" description="Manage your invoices">
                <Button asChild>
                    <Link href="/invoicing/invoices/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Invoice
                    </Link>
                </Button>
            </PageHeader>

            {invoices.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-4">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Invoices</p>
                            <p className="text-xl font-semibold">{summary.total_invoices}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Drafts</p>
                            <p className="text-xl font-semibold">{summary.draft_count}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Outstanding</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.total_outstanding / 100)}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Overdue</p>
                            <p className="text-xl font-semibold text-destructive">{formatCurrency(summary.total_overdue / 100)}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search by invoice number..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {statuses.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.customer_id ?? '__all__'} onValueChange={(v) => applyFilter('customer_id', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="Customer" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All Customers</SelectItem>
                        {customers.map(c => (
                            <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {invoices.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Due</TableHead>
                                    <TableHead>Amount Due</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.data.map((invoice) => (
                                    <TableRow key={invoice.id}>
                                        <TableCell>
                                            <Link href={`/invoicing/invoices/${invoice.id}`} className="font-medium hover:underline">
                                                {invoice.number ?? 'Draft'}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {invoice.customer?.name ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm font-medium">
                                            {formatCurrency(invoice.total / 100, invoice.currency)}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {invoice.due_at ? formatDate(invoice.due_at) : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {formatCurrency(invoice.amount_due / 100, invoice.currency)}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={invoice.status} />
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
                                                        <Link href={`/invoicing/invoices/${invoice.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    {invoice.status === 'draft' ? (
                                                        <>
                                                            <DropdownMenuItem asChild>
                                                                <Link href={`/invoicing/invoices/${invoice.id}/edit`}>
                                                                    <Pencil className="mr-2 h-4 w-4" /> Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteId(invoice.id)}
                                                                className="text-destructive"
                                                            >
                                                                <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                            </DropdownMenuItem>
                                                        </>
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
                        {invoices.data.map((invoice) => (
                            <Card key={invoice.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/invoicing/invoices/${invoice.id}`} className="font-medium hover:underline">
                                                {invoice.number ?? 'Draft'}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{invoice.customer?.name}</p>
                                        </div>
                                        <StatusBadge status={invoice.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">{formatCurrency(invoice.total / 100, invoice.currency)}</span>
                                        {invoice.due_at ? (
                                            <span className="text-muted-foreground">Due: {formatDate(invoice.due_at)}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {invoices.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {invoices.from} to {invoices.to} of {invoices.total}
                            </p>
                            <div className="flex gap-2">
                                {invoices.links.map((link, i) => (
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
                    icon={FileText}
                    title="No invoices yet"
                    description="Create your first invoice to get started"
                    action={{ label: 'New Invoice', href: '/invoicing/invoices/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Invoice"
                description="Are you sure you want to delete this draft invoice? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
