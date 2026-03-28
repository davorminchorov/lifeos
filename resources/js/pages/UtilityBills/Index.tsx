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
import { Zap, Plus, Search, MoreHorizontal, Eye, Pencil, Check, ToggleLeft, ToggleRight, Copy, Trash2 } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { UtilityBill } from '@/types/models'
import type { PaginatedData } from '@/types'

interface UtilityBillIndexProps {
    utilityBills: PaginatedData<UtilityBill>
    filters?: {
        search?: string
        utility_type?: string
        payment_status?: string
        due_soon?: string
    }
}

const utilityTypes = [
    { value: 'electricity', label: 'Electricity' },
    { value: 'gas', label: 'Gas' },
    { value: 'water', label: 'Water' },
    { value: 'internet', label: 'Internet' },
    { value: 'phone', label: 'Phone' },
    { value: 'cable_tv', label: 'Cable TV' },
    { value: 'trash', label: 'Trash' },
    { value: 'sewer', label: 'Sewer' },
    { value: 'other', label: 'Other' },
]

const paymentStatuses = [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
    { value: 'disputed', label: 'Disputed' },
]

const dueSoonOptions = [
    { value: '7', label: '7 days' },
    { value: '14', label: '14 days' },
    { value: '30', label: '30 days' },
]

function formatUtilityType(type: string): string {
    return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

export default function UtilityBillIndex({ utilityBills, filters = {} }: UtilityBillIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmAction, setConfirmAction] = useState<{ id: number; action: 'mark-paid' | 'auto-pay-on' | 'auto-pay-off' | 'duplicate' | 'delete' } | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/utility-bills', { ...filters, [key]: value || undefined }, {
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
        router.get('/utility-bills', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        const { id, action } = confirmAction

        if (action === 'mark-paid') {
            router.patch(`/utility-bills/${id}/mark-paid`, {}, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'auto-pay-on') {
            router.patch(`/utility-bills/${id}/set-auto-pay`, { auto_pay_enabled: true }, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'auto-pay-off') {
            router.patch(`/utility-bills/${id}/set-auto-pay`, { auto_pay_enabled: false }, {
                preserveScroll: true,
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'duplicate') {
            router.post(`/utility-bills/${id}/duplicate`, {}, {
                onFinish: () => setConfirmAction(null),
            })
        } else if (action === 'delete') {
            router.delete(`/utility-bills/${id}`, {
                onFinish: () => setConfirmAction(null),
            })
        }
    }, [confirmAction])

    const pendingCount = utilityBills.data.filter(b => b.payment_status === 'pending').length
    const pendingTotal = utilityBills.data
        .filter(b => b.payment_status === 'pending')
        .reduce((sum, b) => sum + Number(b.bill_amount), 0)
    const overdueCount = utilityBills.data.filter(b => {
        if (b.payment_status === 'overdue') return true
        if (b.payment_status === 'pending' && new Date(b.due_date) < new Date()) return true
        return false
    }).length

    const confirmTitle = confirmAction
        ? ({
            'mark-paid': 'Mark as Paid',
            'auto-pay-on': 'Enable Auto-Pay',
            'auto-pay-off': 'Disable Auto-Pay',
            'duplicate': 'Duplicate Bill',
            'delete': 'Delete Bill',
        })[confirmAction.action]
        : ''

    const confirmDescription = confirmAction
        ? ({
            'mark-paid': 'Are you sure you want to mark this bill as paid?',
            'auto-pay-on': 'Are you sure you want to enable auto-pay for this bill?',
            'auto-pay-off': 'Are you sure you want to disable auto-pay for this bill?',
            'duplicate': 'This will create a copy of the bill for the next billing period.',
            'delete': 'Are you sure you want to delete this bill? This action cannot be undone.',
        })[confirmAction.action]
        : ''

    return (
        <AppLayout>
            <Head title="Utility Bills" />

            <PageHeader title="Utility Bills" description="Track your utility bills and monitor usage patterns">
                <Button asChild>
                    <Link href="/utility-bills/create">
                        <Plus className="mr-2 h-4 w-4" />
                        Add Utility Bill
                    </Link>
                </Button>
            </PageHeader>

            {utilityBills.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Pending Bills</p>
                            <p className="text-xl font-semibold">{pendingCount}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Pending Total</p>
                            <p className="text-xl font-semibold">{formatCurrency(pendingTotal, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Overdue</p>
                            <p className="text-xl font-semibold">{overdueCount}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search bills..."
                        value={search}
                        onChange={handleSearch}
                        onKeyDown={(e) => e.key === 'Enter' && handleSearchSubmit()}
                        className="pl-9"
                    />
                </div>
                <Select value={filters.utility_type ?? ''} onValueChange={(v) => applyFilter('utility_type', v)}>
                    <SelectTrigger className="w-[140px]">
                        <SelectValue placeholder="Type" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Types</SelectItem>
                        {utilityTypes.map(t => (
                            <SelectItem key={t.value} value={t.value}>{t.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.payment_status ?? ''} onValueChange={(v) => applyFilter('payment_status', v)}>
                    <SelectTrigger className="w-[140px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Statuses</SelectItem>
                        {paymentStatuses.map(s => (
                            <SelectItem key={s.value} value={s.value}>{s.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={filters.due_soon ?? ''} onValueChange={(v) => applyFilter('due_soon', v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Due soon" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Any time</SelectItem>
                        {dueSoonOptions.map(o => (
                            <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {utilityBills.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Utility</TableHead>
                                    <TableHead>Provider</TableHead>
                                    <TableHead>Bill Period</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Due Date</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {utilityBills.data.map((bill) => {
                                    const isOverdue = bill.payment_status === 'pending' && new Date(bill.due_date) < new Date()

                                    return (
                                        <TableRow key={bill.id}>
                                            <TableCell>
                                                <Link href={`/utility-bills/${bill.id}`} className="font-medium hover:underline">
                                                    {formatUtilityType(bill.utility_type)}
                                                </Link>
                                                {bill.account_number ? (
                                                    <p className="text-xs text-muted-foreground">Acct: {bill.account_number}</p>
                                                ) : null}
                                            </TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {bill.service_provider}
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                {formatDate(bill.bill_period_start)} - {formatDate(bill.bill_period_end)}
                                            </TableCell>
                                            <TableCell>
                                                <span className="font-medium">{formatCurrency(bill.bill_amount, bill.currency ?? 'MKD')}</span>
                                                {bill.usage_amount ? (
                                                    <p className="text-xs text-muted-foreground">
                                                        {Number(bill.usage_amount).toFixed(2)} {bill.usage_unit}
                                                    </p>
                                                ) : null}
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                {formatDate(bill.due_date)}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1">
                                                    <StatusBadge status={isOverdue ? 'overdue' : bill.payment_status} />
                                                    {bill.auto_pay_enabled ? (
                                                        <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                            Auto
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
                                                            <Link href={`/utility-bills/${bill.id}`}>
                                                                <Eye className="mr-2 h-4 w-4" /> View
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/utility-bills/${bill.id}/edit`}>
                                                                <Pencil className="mr-2 h-4 w-4" /> Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        {bill.payment_status !== 'paid' ? (
                                                            <DropdownMenuItem onClick={() => setConfirmAction({ id: bill.id, action: 'mark-paid' })}>
                                                                <Check className="mr-2 h-4 w-4" /> Mark Paid
                                                            </DropdownMenuItem>
                                                        ) : null}
                                                        {bill.auto_pay_enabled ? (
                                                            <DropdownMenuItem onClick={() => setConfirmAction({ id: bill.id, action: 'auto-pay-off' })}>
                                                                <ToggleLeft className="mr-2 h-4 w-4" /> Disable Auto-Pay
                                                            </DropdownMenuItem>
                                                        ) : (
                                                            <DropdownMenuItem onClick={() => setConfirmAction({ id: bill.id, action: 'auto-pay-on' })}>
                                                                <ToggleRight className="mr-2 h-4 w-4" /> Enable Auto-Pay
                                                            </DropdownMenuItem>
                                                        )}
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: bill.id, action: 'duplicate' })}>
                                                            <Copy className="mr-2 h-4 w-4" /> Duplicate
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onClick={() => setConfirmAction({ id: bill.id, action: 'delete' })}
                                                            className="text-destructive"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" /> Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    )
                                })}
                            </TableBody>
                        </Table>
                    </div>

                    <div className="space-y-3 md:hidden">
                        {utilityBills.data.map((bill) => {
                            const isOverdue = bill.payment_status === 'pending' && new Date(bill.due_date) < new Date()

                            return (
                                <Card key={bill.id}>
                                    <CardContent className="p-4">
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <Link href={`/utility-bills/${bill.id}`} className="font-medium hover:underline">
                                                    {formatUtilityType(bill.utility_type)}
                                                </Link>
                                                <p className="text-sm text-muted-foreground">{bill.service_provider}</p>
                                            </div>
                                            <StatusBadge status={isOverdue ? 'overdue' : bill.payment_status} />
                                        </div>
                                        <div className="mt-3 flex items-center justify-between text-sm">
                                            <span className="font-medium">{formatCurrency(bill.bill_amount, bill.currency ?? 'MKD')}</span>
                                            <span className="text-muted-foreground">Due: {formatDate(bill.due_date)}</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            )
                        })}
                    </div>

                    {utilityBills.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {utilityBills.from} to {utilityBills.to} of {utilityBills.total}
                            </p>
                            <div className="flex gap-2">
                                {utilityBills.links.map((link, i) => (
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
                    icon={Zap}
                    title="No utility bills yet"
                    description="Start tracking your utility bills and usage"
                    action={{ label: 'Add Utility Bill', href: '/utility-bills/create' }}
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
        </AppLayout>
    )
}
