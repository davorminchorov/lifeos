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
import { CreditCard, Plus, Search, MoreHorizontal, Eye, Pencil, Pause, Play, X, Upload } from 'lucide-react'
import { formatCurrency, formatDate } from '@/lib/utils'
import type { Subscription } from '@/types/models'
import type { PaginatedData } from '@/types'

interface SubscriptionWithCosts extends Subscription {
    monthly_cost: number
    yearly_cost: number
}

interface SubscriptionIndexProps {
    subscriptions: PaginatedData<SubscriptionWithCosts>
    filters?: {
        search?: string
        status?: string
        category?: string
        due_soon?: string
    }
}

const categories = ['Entertainment', 'Software', 'Fitness', 'Storage', 'Productivity', 'Development', 'Health', 'Communication']
const statuses = ['active', 'paused', 'cancelled']
const dueSoonOptions = [
    { value: '7', label: '7 days' },
    { value: '14', label: '14 days' },
    { value: '30', label: '30 days' },
]

export default function SubscriptionIndex({ subscriptions, filters = {} }: SubscriptionIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [confirmAction, setConfirmAction] = useState<{ id: number; action: 'pause' | 'resume' | 'cancel' } | null>(null)

    const applyFilter = useCallback((key: string, value: string) => {
        router.get('/subscriptions', { ...filters, [key]: value || undefined }, {
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
        router.get('/subscriptions', {}, { preserveState: true, replace: true })
        setSearch('')
    }, [])

    const handleConfirmAction = useCallback(() => {
        if (!confirmAction) return
        const { id, action } = confirmAction
        router.patch(`/subscriptions/${id}/${action}`, {}, {
            preserveScroll: true,
            onFinish: () => setConfirmAction(null),
        })
    }, [confirmAction])

    const activeSubscriptions = subscriptions.data.filter(s => s.status === 'active')
    const monthlyTotal = activeSubscriptions.reduce((sum, s) => sum + (s.monthly_cost ?? 0), 0)
    const dueSoonCount = subscriptions.data.filter(s => {
        if (s.status !== 'active' || !s.next_billing_date) return false
        const days = Math.ceil((new Date(s.next_billing_date).getTime() - Date.now()) / (1000 * 60 * 60 * 24))
        return days <= 7
    }).length

    return (
        <AppLayout>
            <Head title="Subscriptions" />

            <PageHeader title="Subscriptions" description="Manage your recurring subscriptions">
                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/subscriptions/import">
                            <Upload className="mr-2 h-4 w-4" />
                            Import CSV
                        </Link>
                    </Button>
                    <Button asChild>
                        <Link href="/subscriptions/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Add Subscription
                        </Link>
                    </Button>
                </div>
            </PageHeader>

            {subscriptions.total > 0 ? (
                <div className="mb-6 grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Monthly Cost</p>
                            <p className="text-xl font-semibold">{formatCurrency(monthlyTotal, 'MKD')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Active</p>
                            <p className="text-xl font-semibold">{activeSubscriptions.length}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Due This Week</p>
                            <p className="text-xl font-semibold">{dueSoonCount}</p>
                        </CardContent>
                    </Card>
                </div>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center gap-3">
                <div className="relative flex-1 sm:max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        placeholder="Search subscriptions..."
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
                            <SelectItem key={s} value={s} className="capitalize">{s}</SelectItem>
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
                <Select value={filters.due_soon ?? '__all__'} onValueChange={(v) => applyFilter('due_soon', v === "__all__" ? "" : v)}>
                    <SelectTrigger className="w-[130px]">
                        <SelectValue placeholder="Due soon" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">Any time</SelectItem>
                        {dueSoonOptions.map(o => (
                            <SelectItem key={o.value} value={o.value}>{o.label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {Object.keys(filters).length > 0 ? (
                    <Button variant="ghost" size="sm" onClick={clearFilters}>Clear</Button>
                ) : null}
            </div>

            {subscriptions.data.length > 0 ? (
                <>
                    <div className="hidden rounded-md border border-border md:block">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Service</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Cost</TableHead>
                                    <TableHead>Next Billing</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[50px]" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {subscriptions.data.map((sub) => (
                                    <TableRow key={sub.id}>
                                        <TableCell>
                                            <Link href={`/subscriptions/${sub.id}`} className="font-medium hover:underline">
                                                {sub.service_name}
                                            </Link>
                                            {sub.description ? (
                                                <p className="text-xs text-muted-foreground">{sub.description}</p>
                                            ) : null}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">{sub.category}</TableCell>
                                        <TableCell>
                                            <span className="font-medium">{formatCurrency(sub.cost, sub.currency ?? 'MKD')}</span>
                                            <span className="text-xs text-muted-foreground">/{sub.billing_cycle}</span>
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {sub.next_billing_date ? formatDate(sub.next_billing_date) : '\u2014'}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={sub.status} />
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
                                                        <Link href={`/subscriptions/${sub.id}`}>
                                                            <Eye className="mr-2 h-4 w-4" /> View
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem asChild>
                                                        <Link href={`/subscriptions/${sub.id}/edit`}>
                                                            <Pencil className="mr-2 h-4 w-4" /> Edit
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    {sub.status === 'active' ? (
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: sub.id, action: 'pause' })}>
                                                            <Pause className="mr-2 h-4 w-4" /> Pause
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                    {sub.status === 'paused' ? (
                                                        <DropdownMenuItem onClick={() => setConfirmAction({ id: sub.id, action: 'resume' })}>
                                                            <Play className="mr-2 h-4 w-4" /> Resume
                                                        </DropdownMenuItem>
                                                    ) : null}
                                                    {sub.status !== 'cancelled' ? (
                                                        <DropdownMenuItem
                                                            onClick={() => setConfirmAction({ id: sub.id, action: 'cancel' })}
                                                            className="text-destructive"
                                                        >
                                                            <X className="mr-2 h-4 w-4" /> Cancel
                                                        </DropdownMenuItem>
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
                        {subscriptions.data.map((sub) => (
                            <Card key={sub.id}>
                                <CardContent className="p-4">
                                    <div className="flex items-start justify-between">
                                        <div>
                                            <Link href={`/subscriptions/${sub.id}`} className="font-medium hover:underline">
                                                {sub.service_name}
                                            </Link>
                                            <p className="text-sm text-muted-foreground">{sub.category}</p>
                                        </div>
                                        <StatusBadge status={sub.status} />
                                    </div>
                                    <div className="mt-3 flex items-center justify-between text-sm">
                                        <span className="font-medium">{formatCurrency(sub.cost, sub.currency ?? 'MKD')}/{sub.billing_cycle}</span>
                                        {sub.next_billing_date ? (
                                            <span className="text-muted-foreground">Next: {formatDate(sub.next_billing_date)}</span>
                                        ) : null}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {subscriptions.last_page > 1 ? (
                        <div className="mt-4 flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Showing {subscriptions.from} to {subscriptions.to} of {subscriptions.total}
                            </p>
                            <div className="flex gap-2">
                                {subscriptions.links.map((link, i) => (
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
                    icon={CreditCard}
                    title="No subscriptions yet"
                    description="Start tracking your recurring subscriptions"
                    action={{ label: 'Add Subscription', href: '/subscriptions/create' }}
                />
            )}

            <ConfirmationDialog
                open={confirmAction !== null}
                onOpenChange={(open) => { if (!open) setConfirmAction(null) }}
                title={confirmAction ? `${confirmAction.action.charAt(0).toUpperCase() + confirmAction.action.slice(1)} Subscription` : ''}
                description={confirmAction ? `Are you sure you want to ${confirmAction.action} this subscription?` : ''}
                onConfirm={handleConfirmAction}
                confirmLabel={confirmAction?.action === 'cancel' ? 'Cancel Subscription' : 'Confirm'}
                variant={confirmAction?.action === 'cancel' ? 'danger' : 'default'}
            />
        </AppLayout>
    )
}
