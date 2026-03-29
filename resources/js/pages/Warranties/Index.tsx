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
import { Shield, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Warranty } from '@/types/models'
import type { PaginatedData } from '@/types'

interface WarrantyIndexProps {
    warranties: PaginatedData<Warranty>
    filters?: {
        search?: string
        current_status?: string
        warranty_type?: string
    }
}

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'claimed', label: 'Claimed' },
    { value: 'transferred', label: 'Transferred' },
    { value: 'expired', label: 'Expired' },
]

const warrantyTypes = [
    { value: 'manufacturer', label: 'Manufacturer' },
    { value: 'extended', label: 'Extended' },
    { value: 'both', label: 'Both' },
]

export default function WarrantyIndex({ warranties, filters = {} }: WarrantyIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/warranties', { ...filters, [key]: value || undefined }, {
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
        router.get('/warranties', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (!confirmDelete) return
        router.delete(`/warranties/${confirmDelete}`, {
            onFinish: () => setConfirmDelete(null),
        })
    }, [confirmDelete])

    return (
        <AppLayout>
            <Head title="Warranties" />

            <PageHeader title="Warranties" description="Track your product warranties and claims">
                <Button asChild>
                    <Link href="/warranties/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Warranty
                    </Link>
                </Button>
            </PageHeader>

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search warranties..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.current_status ?? '__all__'} onValueChange={(v) => applyFilter('current_status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {statusOptions.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.warranty_type ?? '__all__'} onValueChange={(v) => applyFilter('warranty_type', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {warrantyTypes.map(t => (
                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {warranties.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Brand</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Purchase Price</TableHead>
                                    <TableHead>Expires</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {warranties.data.map((warranty) => (
                                    <TableRow key={warranty.id}>
                                        <TableCell>
                                            <Link href={`/warranties/${warranty.id}`} className="font-medium hover:underline">
                                                {warranty.product_name}
                                            </Link>
                                            {warranty.model ? (
                                                <p className="text-xs text-muted-foreground">{warranty.model}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {warranty.brand ?? '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {warranty.warranty_type ?? '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            {warranty.purchase_price != null
                                                ? formatCurrency(warranty.purchase_price, 'MKD')
                                                : '\u2014'}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {warranty.warranty_expiration_date ? formatDate(warranty.warranty_expiration_date) : '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={warranty.current_status} />
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
                                                        <Link href={`/warranties/${warranty.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/warranties/${warranty.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setConfirmDelete(warranty.id)}
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
                        {warranties.data.map((warranty) => (
                            <Card key={warranty.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/warranties/${warranty.id}`} className="font-medium hover:underline">
                                                {warranty.product_name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{warranty.brand ?? 'No brand'}</p>
                                        </div>
                                        <StatusBadge status={warranty.current_status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">
                                            {warranty.purchase_price != null ? formatCurrency(warranty.purchase_price, 'MKD') : '\u2014'}
                                        </span>
                                        {warranty.warranty_expiration_date ? (
                                            <span className="text-muted-foreground">Expires: {formatDate(warranty.warranty_expiration_date)}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {warranties.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {warranties.from} to {warranties.to} of {warranties.total}
                            </p>
                            <div className="flex gap-2">
                                {warranties.links.map((link, i) => (
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
                    icon={Shield}
                    title="No warranties yet"
                    description="Start tracking your product warranties"
                    action={{ label: 'Add Warranty', href: '/warranties/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete Warranty"
                description="Are you sure you want to delete this warranty? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
