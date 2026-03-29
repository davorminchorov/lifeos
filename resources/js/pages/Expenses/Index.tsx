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
import { Checkbox } from '@/components/ui/checkbox'
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
import { DollarSign, Plus, Search, MoreHorizontal, Eye, Pencil, Copy, CheckCircle, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Expense } from '@/types/models'
import type { PaginatedData } from '@/types'

interface ExpenseIndexProps {
    expenses: PaginatedData<Expense>
    filters?: {
        search?: string
        category?: string
        expense_type?: string
        status?: string
        start_date?: string
        end_date?: string
    }
}

const categories = ['Food & Dining', 'Transportation', 'Shopping', 'Entertainment', 'Bills & Utilities', 'Healthcare', 'Travel', 'Other']
const expenseTypes = [
    { value: 'personal', label: 'Personal' },
    { value: 'business', label: 'Business' },
]
const statuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'reimbursed', label: 'Reimbursed' },
]

export default function ExpenseIndex({ expenses, filters = {} }: ExpenseIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [selectedIds, setSelectedIds] = useState<number[]>([])
    const [confirmAction, setConfirmAction] = useState<{ id: number; action: 'delete' | 'reimburse' | 'duplicate' } | null>(null)
    const [bulkAction, setBulkAction] = useState<'delete' | 'mark_reimbursed' | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/expenses', { ...filters, [key]: value || undefined }, {
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
        router.get('/expenses', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        const { id, action } = confirmAction

        if (action === 'delete') {
            router.delete(`/expenses/${id}`, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'reimburse') {
            router.patch(`/expenses/${id}/mark-reimbursed`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'duplicate') {
            router.post(`/expenses/${id}/duplicate`, {}, {
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction])

    const handleBulkConfirm = useCallback(() => {
        if (!bulkAction || selectedIds.length === 0) return

        router.post('/expenses/bulk-action', {
            expense_ids: selectedIds,
            action: bulkAction,
        }, {
            preserveScroll: true,
            onFinish: () => {
                setBulkAction(null)
                setSelectedIds([])
            },
        })
    }, [bulkAction, selectedIds])

    const toggleSelectAll = useCallback(() => {
        if (selectedIds.length === expenses.data.length) {
            setSelectedIds([])
        } else {
            setSelectedIds(expenses.data.map(e => e.id))
        }
    }, [selectedIds.length, expenses.data])

    const toggleSelect = useCallback((id: number) => {
        setSelectedIds(prev =>
            prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
        )
    }, [])

    const totalAmount = expenses.data.reduce((sum, e) => sum + Number(e.amount), 0)
    const pendingCount = expenses.data.filter(e => e.status === 'pending').length
    const businessCount = expenses.data.filter(e => e.expense_type === 'business').length

    const confirmTitle = confirmAction?.action === 'delete'
        ? 'Delete Expense'
        : confirmAction?.action === 'reimburse'
            ? 'Mark as Reimbursed'
            : confirmAction?.action === 'duplicate'
                ? 'Duplicate Expense'
                : ''

    const confirmDescription = confirmAction?.action === 'delete'
        ? 'Are you sure you want to delete this expense? This action cannot be undone.'
        : confirmAction?.action === 'reimburse'
            ? 'Are you sure you want to mark this expense as reimbursed?'
            : confirmAction?.action === 'duplicate'
                ? 'This will create a copy of the expense with today\'s date.'
                : ''

    const hasFilters = Object.keys(filters).length > 0

    return (
        <AppLayout>
            <Head title="Expenses" />

            <PageHeader title="Expenses" description="Track your spending and manage your budget">
                <Button asChild>
                    <Link href="/expenses/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Expense
                    </Link>
                </Button>
            </PageHeader>

            {expenses.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Page Total</p>
                            <p className="text-xl font-semibold">{formatCurrency(totalAmount, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Pending</p>
                            <p className="text-xl font-semibold">{pendingCount}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Business</p>
                            <p className="text-xl font-semibold">{businessCount}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search expenses..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.category ?? '__all__'} onValueChange={(v) => applyFilter('category', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[160px]">
                        <SelectValue placeholder="Category" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {categories.map(c => (
                            <SelectItem key={c} value={c}>{c}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.expense_type ?? '__all__'} onValueChange={(v) => applyFilter('expense_type', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">All</SelectItem>
                        {expenseTypes.map(t => (
                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
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
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Input
                    type="date"
                    value={filters.start_date ?? '__all__'}
                    onChange={(e) => applyFilter('start_date', e.target.value)}
                    className="w-[150px]"
                    placeholder="From"
                />
                <Input
                    type="date"
                    value={filters.end_date ?? '__all__'}
                    onChange={(e) => applyFilter('end_date', e.target.value)}
                    className="w-[150px]"
                    placeholder="To"
                />
                {hasFilters ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {selectedIds.length > 0 ? (
                <div className="mb-4 flex items-center gap-3 rounded-md border border-border bg-muted/50 p-3">
                    <span className="text-sm font-medium">{selectedIds.length} selected</span>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setBulkAction('mark_reimbursed')}
                    >
                        <CheckCircle className="mr-2 h-4 w-4" />
                        Mark Reimbursed
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setBulkAction('delete')}
                        className="text-destructive"
                    >
                        <Trash2 className="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                    <Button variant="ghost" size="sm" onClick={() => setSelectedIds([])}>
                        Clear Selection
                    </Button>
                </div>
            ) : null}

            {expenses.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[40px]">
                                        <Checkbox
                                            checked={selectedIds.length === expenses.data.length && expenses.data.length > 0}
                                            onCheckedChange={toggleSelectAll}
                                        />
                                    </TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {expenses.data.map((expense) => (
                                    <TableRow key={expense.id}>
                                        <TableCell>
                                            <Checkbox
                                                checked={selectedIds.includes(expense.id)}
                                                onCheckedChange={() => toggleSelect(expense.id)}
                                            />
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {formatDate(expense.expense_date)}
                                        </TableCell>
                                        <TableCell>
                                            <Link href={`/expenses/${expense.id}`} className="font-medium hover:underline">
                                                {expense.description ?? 'No description'}
                                            </Link>
                                            {expense.merchant ? (
                                                <p className="text-xs text-muted-foreground">{expense.merchant}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">{expense.category}</TableCell>
                                        <TableCell>
                                            <span className="font-medium">{formatCurrency(expense.amount, expense.currency ?? 'MKD')}</span>
                                            {expense.expense_type ? (
                                                <span className="ml-1 text-xs text-muted-foreground capitalize">({expense.expense_type})</span>
                                            ) : null}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1">
                                                <StatusBadge status={expense.status ?? 'pending'} />
                                                {expense.is_tax_deductible ? (
                                                    <span className="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900 dark:text-amber-200">
                                                        Tax
                                                    </span>
                                                ) : null}
                                            </div>
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
                                                        <Link href={`/expenses/${expense.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/expenses/${expense.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => setConfirmAction({ id: expense.id, action: 'duplicate' })}>
                                                        <Copy className="mr-2 h-4 w-4" /> Duplicate
                                                    </DropdownMenuItem>
                                                    {expense.status !== 'reimbursed' ? (
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: expense.id, action: 'reimburse' })}>
                                                            <CheckCircle className="mr-2 h-4 w-4" /> Mark Reimbursed
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                    <DropdownMenuItem
                                                        onClick={() => setConfirmAction({ id: expense.id, action: 'delete' })}
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
                        {expenses.data.map((expense) => (
                            <Card key={expense.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/expenses/${expense.id}`} className="font-medium hover:underline">
                                                {expense.description ?? 'No description'}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{expense.category}</p>
                                            {expense.merchant ? (
                                                <p className="text-xs text-muted-foreground">{expense.merchant}</p>
                                            ) : null}
                                        </div>
                                        <StatusBadge status={expense.status ?? 'pending'} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">{formatCurrency(expense.amount, expense.currency ?? 'MKD')}</span>
                                        <span className="text-muted-foreground">{formatDate(expense.expense_date)}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {expenses.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {expenses.from} to {expenses.to} of {expenses.total}
                            </p>
                            <div className="flex gap-2">
                                {expenses.links.map((link, i) => (
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
                    icon={DollarSign}
                    title="No expenses yet"
                    description="Start tracking your spending by adding your first expense"
                    action={{ label: 'Add Expense', href: '/expenses/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmTitle}
                description={confirmDescription}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction?.action === 'delete' ? 'Delete' : 'Confirm'}
                variant={confirmAction?.action === 'delete' ? 'danger' : 'default'}
            />

            <ConfirmationDialog
                open={bulkAction !== null}
                onOpenChange={(open) => { if (!open) setBulkAction(null) }}
                title={bulkAction === 'delete' ? 'Delete Selected Expenses' : 'Mark Selected as Reimbursed'}
                description={
                    bulkAction === 'delete'
                        ? `Are you sure you want to delete ${selectedIds.length} expense(s)? This action cannot be undone.`
                        : `Are you sure you want to mark ${selectedIds.length} expense(s) as reimbursed?`
                }
                onConfirm={handleBulkConfirm}
                confirmLabel={bulkAction === 'delete' ? 'Delete All' : 'Mark All Reimbursed'}
                variant={bulkAction === 'delete' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
