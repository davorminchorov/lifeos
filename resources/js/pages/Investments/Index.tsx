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
import { TrendingUp, Plus, Search, MoreHorizontal, Eye, Pencil, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Investment } from '@/types/models'
import type { PaginatedData } from '@/types'

interface InvestmentIndexProps {
    investments: PaginatedData<Investment>
    filters?: {
        search?: string
        investment_type?: string
        risk_tolerance?: string
        status?: string
        account_broker?: string
        sort_by?: string
        sort_order?: string
    }
}

const investmentTypes = [
    { value: 'stock', label: 'Stocks' },
    { value: 'bond', label: 'Bonds' },
    { value: 'crypto', label: 'Cryptocurrency' },
    { value: 'real_estate', label: 'Real Estate' },
    { value: 'mutual_fund', label: 'Mutual Fund' },
    { value: 'etf', label: 'ETF' },
    { value: 'commodities', label: 'Commodities' },
    { value: 'cash', label: 'Cash' },
]

const riskTolerances = [
    { value: 'conservative', label: 'Conservative' },
    { value: 'moderate', label: 'Moderate' },
    { value: 'aggressive', label: 'Aggressive' },
]

const statuses = ['active', 'monitoring', 'sold']

export default function InvestmentIndex({ investments, filters = {} }: InvestmentIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmDelete, setConfirmDelete] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/investments', { ...filters, [key]: value || undefined }, {
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
        router.get('/investments', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleDelete = useCallback(() => {
        if (confirmDelete === null) return
        router.delete(`/investments/${confirmDelete}`, {
            onFinish: () => setConfirmDelete(null),
        })
    }, [confirmDelete])

    const activeInvestments = investments.data.filter(i => i.status === 'active')
    const totalCurrentValue = activeInvestments.reduce((sum, i) => sum + (i.current_value ?? 0), 0)
    const totalPurchaseValue = activeInvestments.reduce((sum, i) => sum + (i.purchase_price * i.quantity), 0)

    return (
        <AppLayout>
            <Head title="Investments" />

            <PageHeader title="Investments" description="Manage your investment portfolio">
                <Button variant="outline" asChild>
                    <Link href="/investments/analytics">Analytics</Link>
                </Button>
                <Button asChild>
                    <Link href="/investments/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Investment
                    </Link>
                </Button>
            </PageHeader>

            {investments.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active Investments</p>
                            <p className="text-xl font-semibold">{activeInvestments.length}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Current Value</p>
                            <p className="text-xl font-semibold">{formatCurrency(totalCurrentValue, 'USD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Cost Basis</p>
                            <p className="text-xl font-semibold">{formatCurrency(totalPurchaseValue, 'USD')}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search investments..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.investment_type ?? '__all__'} onValueChange={(v) => applyFilter('investment_type', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All Types</SelectItem>
                        {investmentTypes.map(t => (
                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.risk_tolerance ?? '__all__'} onValueChange={(v) => applyFilter('risk_tolerance', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Risk" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All Risk</SelectItem>
                        {riskTolerances.map(r => (
                            <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {statuses.map(s => (
                            <SelectItem key={s} value={s} className="capitalize">{s}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {investments.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Quantity</TableHead>
                                    <TableHead>Purchase Price</TableHead>
                                    <TableHead>Current Value</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {investments.data.map((inv) => (
                                    <TableRow key={inv.id}>
                                        <TableCell>
                                            <Link href={`/investments/${inv.id}`} className="font-medium hover:underline">
                                                {inv.name}
                                            </Link>
                                            {inv.symbol_identifier ? (
                                                <p className="text-xs text-muted-foreground">{inv.symbol_identifier}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {inv.investment_type.replace('_', ' ')}
                                        </TableCell>
                                        <TableCell className="text-sm">{inv.quantity}</TableCell>
                                        <TableCell className="text-sm">
                                            {formatCurrency(inv.purchase_price, inv.currency ?? 'USD')}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {inv.current_value !== null
                                                ? formatCurrency(inv.current_value, inv.currency ?? 'USD')
                                                : '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={inv.status} />
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
                                                        <Link href={`/investments/${inv.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/investments/${inv.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setConfirmDelete(inv.id)}
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
                        {investments.data.map((inv) => (
                            <Card key={inv.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/investments/${inv.id}`} className="font-medium hover:underline">
                                                {inv.name}
                                            </Link>
                                            <p className="text-sm capitalize text-muted-foreground">
                                                {inv.investment_type.replace('_', ' ')}
                                            </p>
                                        </div>
                                        <StatusBadge status={inv.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">
                                            {inv.current_value !== null
                                                ? formatCurrency(inv.current_value, inv.currency ?? 'USD')
                                                : formatCurrency(inv.purchase_price * inv.quantity, inv.currency ?? 'USD')}
                                        </span>
                                        <span className="text-muted-foreground">Qty: {inv.quantity}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {investments.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {investments.from} to {investments.to} of {investments.total}
                            </p>
                            <div className="flex gap-2">
                                {investments.links.map((link, i) => (
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
                    icon={TrendingUp}
                    title="No investments yet"
                    description="Start tracking your investment portfolio"
                    action={{ label: 'Add Investment', href: '/investments/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmDelete !== null}
                onOpenChange={(open) => { if (!open) setConfirmDelete(null) }}
                title="Delete Investment"
                description="Are you sure you want to delete this investment? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
