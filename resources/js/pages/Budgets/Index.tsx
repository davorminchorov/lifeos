import { Head, Link, router } from '@inertiajs/react'
import { useState, useCallback, type ChangeEvent } from 'react'
import AppLayout from '@/components/shared/app-layout'
import { PageHeader } from '@/components/shared/page-header'
import { StatusBadge } from '@/components/shared/status-badge'
import { EmptyState } from '@/components/shared/empty-state'
import { ConfirmationDialog } from '@/components/shared/confirmation-dialog'
import { Button } from '@/components/ui/button'
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
import { Wallet, Plus, MoreHorizontal, Eye, Pencil, Trash2, BarChart3 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Budget } from '@/types/models'
import type { PaginatedData } from '@/types'

interface BudgetWithSpending extends Budget {
    current_spending: number
    remaining_amount: number
    utilization_percentage: number
    status: string
}

interface SummaryStats {
    total_budgets: number
    total_budgeted: number
    total_spent: number
    total_remaining: number
    overall_utilization: number
    budgets_exceeded: number
}

interface BudgetIndexProps {
    budgets: PaginatedData<BudgetWithSpending>
    categories: string[]
    summaryStats: SummaryStats
    filters?: {
        status?: string
        period?: string
        category?: string
    }
}

const periodOptions = [
    { value: 'monthly', label: 'Monthly' },
    { value: 'quarterly', label: 'Quarterly' },
    { value: 'yearly', label: 'Yearly' },
    { value: 'custom', label: 'Custom' },
]

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
]

export default function BudgetIndex({ budgets, categories, summaryStats, filters = {} }: BudgetIndexProps) {
    const [deleteId, setDeleteId] = useState<number | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/budgets', { ...filters, [key]: value || undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, [filters])

    const clearFilters = useCallback(() => {
        router.get('/budgets', {}, { preserveState: true, replace: true })
    }, [])

    const handleDelete = useCallback(() => {
        if (!deleteId) return
        router.delete(`/budgets/${deleteId}`, {
            preserveScroll: true,
            onFinish: () => setDeleteId(null),
        })
    }, [deleteId])

    return (
        <AppLayout>
            <Head title="Budgets" />

            <PageHeader title="Budgets" description="Track and manage your spending budgets">
                <Button variant="outline" asChild>
                    <Link href="/budgets/analytics">
                        <BarChart3 className="mr-2 h-4 w-4" />
                        Analytics
                    </Link>
                </Button>
                <Button asChild>
                    <Link href="/budgets/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Budget
                    </Link>
                </Button>
            </PageHeader>

            {summaryStats.total_budgets > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Budgeted</p>
                            <p className="text-xl font-semibold">{formatCurrency(summaryStats.total_budgeted, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Total Spent</p>
                            <p className="text-xl font-semibold">{formatCurrency(summaryStats.total_spent, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Remaining</p>
                            <p className="text-xl font-semibold">{formatCurrency(summaryStats.total_remaining, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Utilization</p>
                            <p className="text-xl font-semibold">{summaryStats.overall_utilization}%</p>
                            {summaryStats.budgets_exceeded > 0 ? (
                                <p className="text-xs text-destructive">{summaryStats.budgets_exceeded} exceeded</p>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <Select value={filters.status ?? '__all__'} onValueChange={(v) => applyFilter('status', v === "__all__" ? "" : v)}>
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
                <Select value={filters.period ?? '__all__'} onValueChange={(v) => applyFilter('period', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {periodOptions.map(p => (
                            <SelectItem key={p.value} value={p.value}>{p.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.category ?? '__all__'} onValueChange={(v) => applyFilter('category', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[150px]">
                        <SelectValue placeholder="Category" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {categories.map(c => (
                            <SelectItem key={c} value={c}>{c}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {budgets.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Budget</TableHead>
                                    <TableHead>Spent</TableHead>
                                    <TableHead>Utilization</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {budgets.data.map((budget) => (
                                    <TableRow key={budget.id}>
                                        <TableCell>
                                            <Link href={`/budgets/${budget.id}`} className="font-medium hover:underline">
                                                {budget.category}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="text-sm capitalize text-muted-foreground">
                                            {budget.budget_period}
                                        </TableCell>
                                        <TableCell className="font-medium">
                                            {formatCurrency(budget.amount, budget.currency ?? 'MKD')}
                                        </TableCell>
                                        <TableCell>
                                            {formatCurrency(budget.current_spending, budget.currency ?? 'MKD')}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <div className="h-2 w-16 rounded-full bg-muted">
                                                    <div
                                                        className={`h-2 rounded-full ${
                                                            budget.utilization_percentage >= 100
                                                                ? 'bg-destructive'
                                                                : budget.utilization_percentage >= budget.alert_threshold
                                                                    ? 'bg-yellow-500'
                                                                    : 'bg-green-500'
                                                        }`}
                                                        style={{ width: `${Math.min(budget.utilization_percentage, 100)}%` }}
                                                    />
                                                </div>
                                                <span className="text-sm text-muted-foreground">
                                                    {budget.utilization_percentage}%
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={budget.is_active ? (budget.status ?? 'on_track') : 'inactive'} />
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
                                                        <Link href={`/budgets/${budget.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/budgets/${budget.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => setDeleteId(budget.id)}
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
                        {budgets.data.map((budget) => (
                            <Card key={budget.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/budgets/${budget.id}`} className="font-medium hover:underline">
                                                {budget.category}
                                            </Link>
                                            <p className="text-sm capitalize text-muted-foreground">{budget.budget_period}</p>
                                        </div>
                                        <StatusBadge status={budget.is_active ? (budget.status ?? 'on_track') : 'inactive'} />
                                    </div>
                                    <div className="mt-3">
                                        <div className="flex items-center justify-between text-sm">
                                            <span>{formatCurrency(budget.current_spending, budget.currency ?? 'MKD')} / {formatCurrency(budget.amount, budget.currency ?? 'MKD')}</span>
                                            <span className="text-muted-foreground">{budget.utilization_percentage}%</span>
                                        </div>
                                        <div className="mt-1 h-2 w-full rounded-full bg-muted">
                                            <div
                                                className={`h-2 rounded-full ${
                                                    budget.utilization_percentage >= 100
                                                        ? 'bg-destructive'
                                                        : budget.utilization_percentage >= budget.alert_threshold
                                                            ? 'bg-yellow-500'
                                                            : 'bg-green-500'
                                                }`}
                                                style={{ width: `${Math.min(budget.utilization_percentage, 100)}%` }}
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {budgets.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {budgets.from} to {budgets.to} of {budgets.total}
                            </p>
                            <div className="flex gap-2">
                                {budgets.links.map((link, i) => (
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
                    icon={Wallet}
                    title="No budgets yet"
                    description="Start tracking your spending with budgets"
                    action={{ label: 'Add Budget', href: '/budgets/create' }}
                />
            )}

            <ConfirmationDialog
                open={deleteId !== null}
                onOpenChange={(open) => { if (!open) setDeleteId(null) }}
                title="Delete Budget"
                description="Are you sure you want to delete this budget? This action cannot be undone."
                onConfirm={handleDelete}
                confirmLabel="Delete"
                variant="danger"
            />
        </AppLayout>
    )
}
