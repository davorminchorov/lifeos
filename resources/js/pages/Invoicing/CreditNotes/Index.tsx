import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
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
import { FileText, Plus, Search, Eye } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { CreditNote, Customer } from '@/types/models'
import type { PaginatedData } from '@/types'

interface CreditNoteIndexProps {
    creditNotes: PaginatedData<CreditNote & { customer?: Customer; invoice?: { id: number; number: string | null } }>
    summary: {
        total_credit_notes: number
        total_amount: number
        available_credit: number
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
    { value: 'applied', label: 'Applied' },
    { value: 'void', label: 'Void' },
]

export default function CreditNoteIndex({ creditNotes, summary, customers, filters = {} }: CreditNoteIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/invoicing/credit-notes', { ...filters, [key]: value || undefined }, {
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
        router.get('/invoicing/credit-notes', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    return (
        <AppLayout>
            <Head title="Credit Notes" />

            <PageHeader title="Credit Notes" description="Manage credit notes for your customers">
                <Button asChild>
                    <Link href="/invoicing/credit-notes/create">
                        <Plus className="mr-2 h-4 w-4" />
                        New Credit Note
                    </Link>
                </Button>
            </PageHeader>

            {creditNotes.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Credit Notes</p>
                            <p className="text-xl font-semibold">{summary.total_credit_notes}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Amount</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.total_amount / 100)}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Available Credit</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.available_credit / 100)}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search by credit note number..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
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

            {creditNotes.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Number</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Remaining</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {creditNotes.data.map((cn) => (
                                    <TableRow key={cn.id}>
                                        <TableCell>
                                            <Link href={`/invoicing/credit-notes/${cn.id}`} className="font-medium hover:underline">
                                                {cn.number}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {cn.customer?.name ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm font-medium">
                                            {formatCurrency(cn.total / 100, cn.currency)}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {formatCurrency(cn.amount_remaining / 100, cn.currency)}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={cn.status} />
                                        </TableCell>
                                        <TableCell>
                                            <Button variant="ghost" size="icon" className="h-8 w-8" asChild>
                                                <Link href={`/invoicing/credit-notes/${cn.id}`}>
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {creditNotes.data.map((cn) => (
                            <Card key={cn.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/invoicing/credit-notes/${cn.id}`} className="font-medium hover:underline">
                                                {cn.number}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{cn.customer?.name}</p>
                                        </div>
                                        <StatusBadge status={cn.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">{formatCurrency(cn.total / 100, cn.currency)}</span>
                                        <span className="text-muted-foreground">
                                            Remaining: {formatCurrency(cn.amount_remaining / 100, cn.currency)}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {creditNotes.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {creditNotes.from} to {creditNotes.to} of {creditNotes.total}
                            </p>
                            <div className="flex gap-2">
                                {creditNotes.links.map((link, i) => (
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
                    title="No credit notes yet"
                    description="Issue credit notes to provide refunds or credits to your customers"
                    action={{ label: 'New Credit Note', href: '/invoicing/credit-notes/create' }}
                />
            )}
        </AppLayout>
    )
}
