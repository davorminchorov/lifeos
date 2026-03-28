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
import { Percent, Plus, Search, MoreHorizontal, Pencil, Trash2 } from 'lucide-react'
import { formatDate } from '@/lib/utils'
import type { TaxRate } from '@/types/models'
import type { PaginatedData } from '@/types'

interface TaxRateIndexProps {
    taxRates: PaginatedData<TaxRate>
    summary: {
        total_tax_rates: number
        active_tax_rates: number
    }
    filters?: {
        search?: string
        active?: string
    }
}

export default function TaxRateIndex({ taxRates, summary, filters = {} }: TaxRateIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/invoicing/tax-rates', { ...filters, [key]: value || undefined }, {
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
        router.get('/invoicing/tax-rates', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (deleteId === null) return
        router.delete(`/invoicing/tax-rates/${deleteId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    return (
        <AppLayout>
            <Head title="Tax Rates" />

            <PageHeader title="Tax Rates" description="Manage tax rates for your invoices">
                <Button asChild>
                    <Link href="/invoicing/tax-rates/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Tax Rate
                    </Link>
                </Button>
            </PageHeader>

            {taxRates.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Tax Rates</p>
                            <p className="text-xl font-semibold">{summary.total_tax_rates}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active</p>
                            <p className="text-xl font-semibold">{summary.active_tax_rates}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search tax rates..."
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

            {taxRates.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Rate</TableHead>
                                    <TableHead>Country</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Valid Period</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {taxRates.data.map((taxRate) => (
                                    <TableRow key={taxRate.id}>
                                        <TableCell>
                                            <span className="font-medium">{taxRate.name}</span>
                                            {taxRate.description ? (
                                                <p className="text-xs text-muted-foreground">{taxRate.description}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm font-medium">
                                            {taxRate.percentage_basis_points / 100}%
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {taxRate.country ?? '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                                taxRate.active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                            }`}>
                                                {taxRate.active ? 'Active' : 'Inactive'}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {taxRate.valid_from || taxRate.valid_to
                                                ? `${taxRate.valid_from ? formatDate(taxRate.valid_from) : 'Start'} - ${taxRate.valid_to ? formatDate(taxRate.valid_to) : 'Ongoing'}`
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
                                                        <Link href={`/invoicing/tax-rates/${taxRate.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setDeleteId(taxRate.id)}
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
                        {taxRates.data.map((taxRate) => (
                            <Card key={taxRate.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <span className="font-medium">{taxRate.name}</span>
                                            <p className="text-sm text-muted-foreground">{taxRate.percentage_basis_points / 100}%</p>
                                        </div>
                                        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                            taxRate.active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                                        }`}>
                                            {taxRate.active ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {taxRates.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {taxRates.from} to {taxRates.to} of {taxRates.total}
                            </p>
                            <div className="flex gap-2">
                                {taxRates.links.map((link, i) => (
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
                    icon={Percent}
                    title="No tax rates yet"
                    description="Add tax rates to apply to your invoice line items"
                    action={{ label: 'Add Tax Rate', href: '/invoicing/tax-rates/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Tax Rate"
                description="Are you sure you want to delete this tax rate? Tax rates used in existing invoices cannot be deleted."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
