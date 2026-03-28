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
import { Tag, Plus, Search, MoreHorizontal, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Discount } from '@/types/models'
import type { PaginatedData } from '@/types'

interface DiscountIndexProps {
    discounts: PaginatedData<Discount>
    summary: {
        total_discounts: number
        active_discounts: number
        total_redemptions: number
    }
    filters?: {
        search?: string
        active?: string
        type?: string
    }
}

export default function DiscountIndex({ discounts, summary, filters = {} }: DiscountIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/invoicing/discounts', { ...filters, [key]: value || undefined }, {
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
        router.get('/invoicing/discounts', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (deleteId === null) return
        router.delete(`/invoicing/discounts/${deleteId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    function formatDiscountValue(discount: Discount): string {
        if (discount.type === 'percent') {
            return `${discount.value / 100}%`
        }
        return formatCurrency(discount.value / 100, discount.currency ?? 'USD')
    }

    return (
        <AppLayout>
            <Head title="Discounts" />

            <PageHeader title="Discounts" description="Manage discount codes for your invoices">
                <Button asChild>
                    <Link href="/invoicing/discounts/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Discount
                    </Link>
                </Button>
            </PageHeader>

            {discounts.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Discounts</p>
                            <p className="text-xl font-semibold">{summary.total_discounts}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active</p>
                            <p className="text-xl font-semibold">{summary.active_discounts}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Redemptions</p>
                            <p className="text-xl font-semibold">{summary.total_redemptions}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search by code..."
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

            {discounts.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Code</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Value</TableHead>
                                    <TableHead>Redemptions</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Valid Period</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {discounts.data.map((discount) => (
                                    <TableRow key={discount.id}>
                                        <TableCell>
                                            <span className="font-medium font-mono">{discount.code}</span>
                                            {discount.description ? (
                                                <p className="text-xs text-muted-foreground">{discount.description}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize">{discount.type}</TableCell>
                                        <TableCell className="text-sm font-medium">
                                            {formatDiscountValue(discount)}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {discount.current_redemptions}
                                            {discount.max_redemptions ? ` / ${discount.max_redemptions}` : ''}
                                        </TableCell>
                                        <TableCell>
                                            <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                                discount.active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                            }`}>
                                                {discount.active ? 'Active' : 'Inactive'}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {discount.starts_at || discount.ends_at
                                                ? `${discount.starts_at ? formatDate(discount.starts_at) : 'Start'} - ${discount.ends_at ? formatDate(discount.ends_at) : 'Ongoing'}`
                                                : '\u2014'}
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
                                                        <Link href={`/invoicing/discounts/${discount.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setDeleteId(discount.id)}
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
                        {discounts.data.map((discount) => (
                            <Card key={discount.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <span className="font-medium font-mono">{discount.code}</span>
                                            <p className="text-sm text-muted-foreground capitalize">{discount.type}</p>
                                        </div>
                                        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                            discount.active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                        }`}>
                                            {discount.active ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                    <div className="mt-2 text-sm font-medium">
                                        {formatDiscountValue(discount)}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {discounts.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {discounts.from} to {discounts.to} of {discounts.total}
                            </p>
                            <div className="flex gap-2">
                                {discounts.links.map((link, i) => (
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
                    icon={Tag}
                    title="No discounts yet"
                    description="Create discount codes to offer to your customers"
                    action={{ label: 'Add Discount', href: '/invoicing/discounts/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Discount"
                description="Are you sure you want to delete this discount? Discounts used in existing invoices cannot be deleted."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
