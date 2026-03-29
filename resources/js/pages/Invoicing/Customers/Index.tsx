import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
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
import { Users, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency } from '@/lib/utils'
import type { Customer } from '@/types/models'
import type { PaginatedData } from '@/types'

interface CustomerIndexProps {
    customers: PaginatedData<Customer & { outstanding_balance?: number; credit_balance?: number }>
    summary: {
        total_customers: number
        total_outstanding: number
        total_credit: number
    }
    filters?: {
        search?: string
    }
}

export default function CustomerIndex({ customers, summary, filters = {} }: CustomerIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const handleSearch = useCallback((e: ChangeEvent<HTMLInputElement>) => {
        setSearch(e.target.value)
    }, [])

    const handleSearchSubmit = useCallback(() => {
        router.get('/invoicing/customers', { ...filters, search: search || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [search, filters])

    const clearFilters = useCallback(() => {
        router.get('/invoicing/customers', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (deleteId === null) return
        router.delete(`/invoicing/customers/${deleteId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    return (
        <AppLayout>
            <Head title="Customers" />

            <PageHeader title="Customers" description="Manage your invoicing customers">
                <Button asChild>
                    <Link href="/invoicing/customers/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Customer
                    </Link>
                </Button>
            </PageHeader>

            {customers.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Customers</p>
                            <p className="text-xl font-semibold">{summary.total_customers}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Outstanding</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.total_outstanding / 100)}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Credit</p>
                            <p className="text-xl font-semibold">{formatCurrency(summary.total_credit / 100)}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search customers..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {customers.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Company</TableHead>
                                    <TableHead>Currency</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {customers.data.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell>
                                            <Link href={`/invoicing/customers/${customer.id}`} className="font-medium hover:underline">
                                                {customer.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {customer.email ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {customer.company_name ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">{customer.currency}</TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="h-8 w-8">
                                                        <MoreHorizontal className="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/invoicing/customers/${customer.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/invoicing/customers/${customer.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setDeleteId(customer.id)}
                                                        className="text-destructive"
                                                    >
                                                        <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {customers.data.map((customer) => (
                            <Card key={customer.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/invoicing/customers/${customer.id}`} className="font-medium hover:underline">
                                                {customer.name}
                                            </Link>
                                            {customer.company_name ? (
                                                <p className="text-sm text-muted-foreground">{customer.company_name}</p>
                                            ) : null}
                                        </div>
                                        <span className="text-sm text-muted-foreground">{customer.currency}</span>
                                    </div>
                                    {customer.email ? (
                                        <p className="mt-1 text-sm text-muted-foreground">{customer.email}</p>
                                    ) : null}
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {customers.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {customers.from} to {customers.to} of {customers.total}
                            </p>
                            <div className="flex gap-2">
                                {customers.links.map((link, i) => (
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
                    icon={Users}
                    title="No customers yet"
                    description="Add your first customer to start invoicing"
                    action={{ label: 'Add Customer', href: '/invoicing/customers/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Customer"
                description="Are you sure you want to delete this customer? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
